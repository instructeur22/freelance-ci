<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name')) — Freelance CI</title>
    <style>
        /*! tailwindcss v4 */
        *,:before,:after,:backdrop{--tw-translate-x:0;--tw-translate-y:0;--tw-translate-z:0;--tw-rotate-x:initial;--tw-rotate-y:initial;--tw-rotate-z:initial;--tw-skew-x:initial;--tw-skew-y:initial;--tw-space-x-reverse:0;--tw-border-style:solid;--tw-leading:initial;--tw-font-weight:initial;--tw-shadow:0 0 #0000;--tw-shadow-color:initial;--tw-shadow-alpha:100%;--tw-inset-shadow:0 0 #0000;--tw-inset-shadow-color:initial;--tw-inset-shadow-alpha:100%;--tw-ring-color:initial;--tw-ring-shadow:0 0 #0000;--tw-inset-ring-color:initial;--tw-inset-ring-shadow:0 0 #0000;--tw-ring-inset:initial;--tw-ring-offset-width:0px;--tw-ring-offset-color:#fff;--tw-ring-offset-shadow:0 0 #0000}
        @layer theme{:host,:root{--font-sans:ui-sans-serif,system-ui,sans-serif;--color-white:#fff;--color-gray-50:#f9fafb;--color-gray-100:#f3f4f6;--color-gray-200:#e5e7eb;--color-gray-300:#d1d5db;--color-gray-400:#9ca3af;--color-gray-500:#6b7280;--color-gray-600:#4b5563;--color-gray-700:#374151;--color-gray-800:#1f2937;--color-gray-900:#111827;--color-orange-500:#f97316;--color-orange-600:#ea580c}}
        body{font-family:var(--font-sans);margin:0;background:#fdfcfc;color:#1b1b18}
        .container{max-width:1024px;margin:0 auto;padding:0 1.5rem}
        nav{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.5rem;border-bottom:1px solid #e5e7eb}
        nav a{text-decoration:none;color:#4b5563;font-size:.875rem}
        nav a:hover{color:#111827}
        nav .logo{font-weight:600;font-size:1.125rem;color:#1b1b18;text-decoration:none}
        .content{padding:3rem 0;min-height:calc(100vh - 140px)}
        .content h1{font-size:1.875rem;font-weight:700;margin-bottom:1.5rem;color:#111827}
        .content h2{font-size:1.25rem;font-weight:600;margin-top:2rem;margin-bottom:.75rem;color:#374151}
        .content p{line-height:1.75;color:#4b5563;margin-bottom:1rem}
        .content ul{list-style:disc;padding-left:1.5rem;margin-bottom:1rem}
        .content li{line-height:1.75;color:#4b5563;margin-bottom:.25rem}
        footer{text-align:center;padding:2rem 1.5rem;border-top:1px solid #e5e7eb;font-size:.875rem;color:#9ca3af}
        .hero{text-align:center;padding:4rem 0 2rem}
        .hero h1{font-size:2.5rem;margin-bottom:.5rem}
        .hero p{font-size:1.125rem;color:#6b7280;max-width:600px;margin:0 auto}
        .card{background:white;border:1px solid #e5e7eb;border-radius:.5rem;padding:1.5rem;margin-bottom:1.5rem}
        .grid-3{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.5rem}
        .btn{padding:.5rem 1.25rem;border-radius:.375rem;font-size:.875rem;text-decoration:none;display:inline-block}
        .btn-primary{background:#1b1b18;color:white}
        .btn-primary:hover{background:#374151}
    </style>
</head>
<body>
    <nav>
        <a href="{{ url('/') }}" class="logo">{{ config('app.name', 'Freelance CI') }}</a>
        <div>
            <a href="{{ url('/about') }}" style="margin-right:1.5rem">About</a>
            <a href="{{ url('/contact') }}" style="margin-right:1.5rem">Contact</a>
            <a href="{{ url('/terms') }}" style="margin-right:1.5rem">Terms</a>
            <a href="{{ url('/privacy') }}">Privacy</a>
        </div>
    </nav>

    <div class="content">
        <div class="container">
            @yield('content')
        </div>
    </div>

    <footer>
        &copy; {{ date('Y') }} {{ config('app.name', 'Freelance CI') }}. All rights reserved.
    </footer>
</body>
</html>
