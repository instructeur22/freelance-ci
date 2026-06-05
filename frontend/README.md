# Freelance CI — Frontend

> Plateforme de freelancing ivoirienne — Interface utilisateur Next.js

---

## Stack

| Technologie | Rôle |
|-------------|------|
| **Next.js 16** (App Router) | Framework React full-stack |
| **TypeScript** | Typage statique |
| **Tailwind CSS** | Styles utilitaires |
| **daisyUI 5** | Composants UI (tailwind plugin) |
| **Shadcn/ui** | Composants headless |
| **Zustand** | State management global |
| **Axios** | Client HTTP |
| **Supabase JS SDK** | Auth (SSR + browser) |
| **Lucide React** | Icônes |

## Authentification

Le flux d'auth est géré côté frontend via **Supabase SSR** (`@supabase/ssr`) avec un endpoint Laravel comme source de vérité pour le profil utilisateur.

### Flux

```
1. Login (Google)     → Supabase redirect → /auth/callback
2. /auth/callback     → setSession()      → redirect /dashboard
3. /dashboard         → AuthProvider.init() → authApi.me()
                        ├─ getToken() → Supabase session || fc_at cookie
                        └─ Authorization: Bearer <token>
4. Backend            → SupabaseJwtMiddleware (JWKS) → User
5. AuthProvider       → setUser(res.data) → Dashboard affiché
```

### Fichiers clés

| Fichier | Rôle |
|---------|------|
| `src/providers/auth-provider.tsx` | Provider React qui hydrate le store zustand via `authApi.me()` |
| `src/stores/auth.ts` | Store zustand (user, loading) |
| `src/lib/api/client.ts` | Client Axios avec intercepteur `getToken()` |
| `src/lib/api/endpoints/auth.ts` | Appels API auth (`me`, `login`, `register`, etc.) |
| `src/lib/supabase/client.ts` | `createBrowserClient()` Supabase |
| `src/lib/supabase/middleware.ts` | `createServerClient()` pour SSR |

### Cookie `fc_at`

Le token d'accès Supabase est stocké dans un cookie `fc_at` en complément des cookies de session Supabase.
`getToken()` tente d'abord de récupérer la session Supabase active, puis tombe sur `fc_at` en fallback.

## Structure

```
src/
├── app/                     # App Router (pages, layouts, api)
│   ├── auth/
│   │   ├── callback/        # Route handler Supabase OAuth
│   │   ├── login/
│   │   └── register/
│   ├── dashboard/           # Routes protégées
│   ├── onboarding/
│   └── ...
├── components/              # Composants réutilisables
│   ├── ui/                  # Shadcn/ui
│   └── ...
├── lib/
│   ├── api/                 # Client Axios, endpoints
│   └── supabase/            # Client + middleware SSR
├── providers/               # React providers (auth)
└── stores/                  # Zustand stores
```

## Commandes

```bash
npm run dev         # Développement (localhost:3000)
npm run build       # Production build
npm run lint        # ESLint
```

## Variables d'environnement

```env
NEXT_PUBLIC_SUPABASE_URL=https://<project>.supabase.co
NEXT_PUBLIC_SUPABASE_ANON_KEY=<anon-key>
NEXT_PUBLIC_API_URL=http://localhost:8000/api
NEXT_PUBLIC_APP_URL=http://localhost:3000
```
