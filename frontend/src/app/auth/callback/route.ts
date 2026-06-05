import { NextRequest, NextResponse } from "next/server";
import { createServerClient } from "@supabase/ssr";

const BASE64_PREFIX = "base64-";

const SUPABASE_URL = process.env.NEXT_PUBLIC_SUPABASE_URL!;
const SUPABASE_ANON_KEY = process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY!;
const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api";
const projectRef = SUPABASE_URL.match(/https?:\/\/(.+?)\./)?.[1] ?? "";

function base64UrlToString(b64url: string): string {
  let base64 = b64url.replace(/-/g, "+").replace(/_/g, "/");
  while (base64.length % 4) base64 += "=";
  return atob(base64);
}

function getDecodedCookie(request: NextRequest, name: string): string | null {
  const cookie = request.cookies.get(name);
  if (!cookie) return null;
  const raw = cookie.value;
  if (raw.startsWith(BASE64_PREFIX)) {
    return base64UrlToString(raw.slice(BASE64_PREFIX.length));
  }
  return raw;
}

export async function GET(request: NextRequest) {
  const { searchParams, origin } = new URL(request.url);
  const code = searchParams.get("code");
  const next = searchParams.get("next") ?? "/dashboard";

  if (!code) {
    return NextResponse.redirect(`${origin}/auth/login?error=auth_callback_error`);
  }

  const cookieName = `sb-${projectRef}-auth-token-code-verifier`;
  const raw = getDecodedCookie(request, cookieName);

  if (!raw) {
    console.error("Auth callback: code_verifier cookie not found:", cookieName);
    return NextResponse.redirect(`${origin}/auth/login?error=auth_callback_error`);
  }

  let codeVerifier: string | null = null;
  try {
    const parsed = JSON.parse(raw);
    codeVerifier = typeof parsed === "string" ? parsed.split("/")[0] : null;
  } catch {
    codeVerifier = raw.split("/")[0];
  }

  if (!codeVerifier) {
    console.error("Auth callback: could not extract code_verifier from:", raw);
    return NextResponse.redirect(`${origin}/auth/login?error=auth_callback_error`);
  }

  const tokenRes = await fetch(`${SUPABASE_URL}/auth/v1/token?grant_type=pkce`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      apikey: SUPABASE_ANON_KEY,
    },
    body: JSON.stringify({
      auth_code: code,
      code_verifier: codeVerifier,
    }),
  });

  if (!tokenRes.ok) {
    const errBody = await tokenRes.text();
    console.error("Auth callback: Supabase token error", tokenRes.status, errBody);
    return NextResponse.redirect(`${origin}/auth/login?error=auth_callback_error`);
  }

  const tokenData = await tokenRes.json();
  const accessToken = tokenData.access_token;
  const maxAge = tokenData.expires_in ?? 3600;

  const syncedRes = await fetch(`${API_URL}/auth/sync`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${accessToken}`,
      Accept: "application/json",
    },
  });

  let needsOnboarding = false;
  if (syncedRes.ok) {
    const body = await syncedRes.json().catch(() => ({}));
    needsOnboarding = !body.data?.last_login_at;
  } else {
    const errBody = await syncedRes.text().catch(() => "");
    console.error("Auth callback: sync failed", syncedRes.status, errBody);
  }

  const dest = needsOnboarding ? "/onboarding" : next;

  const response = NextResponse.redirect(`${origin}${dest}`);

  const supabaseServer = createServerClient(SUPABASE_URL, SUPABASE_ANON_KEY, {
    cookies: {
      getAll: () => request.cookies.getAll().map(c => ({ name: c.name, value: c.value })),
      setAll: (cookiesToSet) => {
        cookiesToSet.forEach(({ name, value, options }) =>
          response.cookies.set(name, value, options)
        );
      },
    },
  });

  await supabaseServer.auth.setSession({
    access_token: tokenData.access_token,
    refresh_token: tokenData.refresh_token,
  });

  response.cookies.set(`sb-${projectRef}-auth-code-verifier`, "", {
    path: "/",
    maxAge: 0,
  });

  return response;
}
