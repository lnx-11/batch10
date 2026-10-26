<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'LИX.goods') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            :root {
                --ink: #172033;
                --muted: #68738a;
                --brand: #5146e5;
                --brand-dark: #3930b8;
                --page: #f7f8fc;
            }
            body { margin: 0; background: var(--page); color: var(--ink); font-family: 'DM Sans', sans-serif; }
            .auth-shell { min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2.5rem 1rem; }
            .auth-brand { display: flex; flex-direction: column; align-items: center; gap: .65rem; margin-bottom: 1.5rem; color: var(--ink); text-decoration: none; }
            .auth-logo { width: 64px; height: 64px; border-radius: 18px; object-fit: cover; box-shadow: 0 8px 22px rgba(28, 34, 67, .12); }
            .auth-brand-name { font-family: 'Manrope', sans-serif; font-size: 1.35rem; font-weight: 800; letter-spacing: -.04em; }
            .auth-card { width: 100%; max-width: 28rem; padding: 2rem; border: 1px solid #eaecf3; border-radius: 22px; background: #fff; box-shadow: 0 16px 45px rgba(28, 34, 67, .08); }
            .auth-title { margin: 0; color: var(--ink); font-family: 'Manrope', sans-serif; font-size: 1.5rem; font-weight: 800; letter-spacing: -.04em; }
            .auth-description { margin: .4rem 0 1.5rem; color: var(--muted); font-size: .95rem; line-height: 1.55; }
            .auth-card .auth-label { display: block; margin-bottom: .45rem; color: #374151; font-size: .9rem; font-weight: 600; }
            .auth-card .auth-input { width: 100%; border: 1px solid #d9dce8; border-radius: 11px; background: #fff; padding: .75rem .9rem; color: var(--ink); box-shadow: none; }
            .auth-card .auth-input:focus { border-color: var(--brand); outline: 0; box-shadow: 0 0 0 3px rgba(81, 70, 229, .15); }
            .auth-card .auth-error { margin-top: .4rem; color: #dc2626; font-size: .85rem; }
            .auth-card .auth-link { color: var(--brand); font-size: .9rem; font-weight: 600; text-decoration: none; }
            .auth-card .auth-link:hover { color: var(--brand-dark); text-decoration: underline; }
            .auth-card .auth-button { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: .7rem 1.25rem; border: 0; border-radius: 11px; background: var(--brand); color: #fff; font-size: .95rem; font-weight: 700; text-transform: none; letter-spacing: normal; box-shadow: none; }
            .auth-card .auth-button:hover { background: var(--brand-dark); }
            .auth-card .auth-button:focus { outline: 3px solid rgba(81, 70, 229, .2); outline-offset: 2px; }
            .auth-card .auth-checkbox { accent-color: var(--brand); }
            .auth-divider { height: 1px; margin: 1.25rem 0; border: 0; background: #eaecf3; }
            .auth-footer { margin: 1.25rem 0 0; color: var(--muted); font-size: .9rem; text-align: center; }
            .auth-status { color: #15803d; font-size: .9rem; font-weight: 600; }
            @media (max-width: 480px) { .auth-card { padding: 1.5rem; } }
        </style>
    </head>
    <body class="antialiased">
        <main class="auth-shell">
            <a href="/" class="auth-brand">
                <x-application-logo class="auth-logo" />
                <span class="auth-brand-name">{{ config('app.name', 'LИX.goods') }}</span>
            </a>
            <div class="auth-card">
                {{ $slot }}
            </div>
        </main>
    </body>
</html>
