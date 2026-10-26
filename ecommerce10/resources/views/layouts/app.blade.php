<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'LИX.goods') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            :root {
                --store-ink: #172033;
                --store-muted: #68738a;
                --store-brand: #5146e5;
                --store-brand-dark: #3930b8;
                --store-surface: #fff;
                --store-page: #f7f8fc;
                --store-border: #eaecf3;
            }
            [x-cloak] { display: none !important; }
            body.store-admin { margin: 0; background: var(--store-page); color: var(--store-ink); font-family: 'DM Sans', sans-serif; }
            .store-admin h1, .store-admin h2, .store-admin h3 { font-family: 'Manrope', sans-serif; }
            .store-admin .bg-gray-100 { background: var(--store-page) !important; }
            .store-admin .bg-white, .store-admin .bg-gray-800 { background: var(--store-surface) !important; }
            .store-admin .bg-gray-50 { background: #f7f8fc !important; }
            .store-admin .bg-gray-700 { background: #eeedff !important; }
            .store-admin .text-gray-900, .store-admin .text-gray-800, .store-admin .text-gray-700,
            .store-admin .text-gray-600, .store-admin .text-gray-200, .store-admin .text-gray-100 { color: var(--store-ink) !important; }
            .store-admin .text-gray-500, .store-admin .text-gray-400 { color: var(--store-muted) !important; }
            .store-admin .border-gray-200, .store-admin .border-gray-300, .store-admin .divide-gray-200 > :not([hidden]) ~ :not([hidden]) { border-color: var(--store-border) !important; }
            .store-admin .bg-indigo-600 { background: var(--store-brand) !important; }
            .store-admin .bg-indigo-600:hover { background: var(--store-brand-dark) !important; }
            .store-admin button.bg-gray-800 { border: 0; border-radius: 11px; background: var(--store-brand) !important; color: #fff !important; font-size: .95rem; letter-spacing: normal; text-transform: none; }
            .store-admin button.bg-gray-800:hover { background: var(--store-brand-dark) !important; }
            .store-admin .text-indigo-600 { color: var(--store-brand) !important; }
            .store-admin .hover\:text-indigo-800:hover { color: var(--store-brand-dark) !important; }
            .store-admin .focus\:border-indigo-500:focus { border-color: var(--store-brand) !important; }
            .store-admin .focus\:ring-indigo-500:focus { --tw-ring-color: var(--store-brand) !important; }
            .store-admin .shadow-sm, .store-admin .shadow { box-shadow: 0 10px 30px rgba(28, 34, 67, .06) !important; }
            .store-admin .rounded-lg { border-radius: 20px !important; }
            .store-admin .admin-navigation { position: sticky; top: 0; z-index: 30; border-bottom: 1px solid var(--store-border); background: rgba(255,255,255,.96); backdrop-filter: blur(12px); }
            .store-admin .admin-nav-inner { display: flex; align-items: center; justify-content: space-between; gap: 1.5rem; max-width: 80rem; min-height: 76px; margin: 0 auto; padding: .75rem 1.5rem; }
            .store-admin .admin-brand { display: inline-flex; flex-shrink: 0; align-items: center; gap: .7rem; color: var(--store-ink); font-family: 'Manrope', sans-serif; font-size: 1.1rem; font-weight: 800; letter-spacing: -.04em; text-decoration: none; }
            .store-admin .admin-brand img { width: 42px; height: 42px; border-radius: 11px; object-fit: cover; }
            .store-admin .admin-nav-links, .store-admin .admin-nav-actions { display: flex; align-items: center; gap: .3rem; }
            .store-admin .admin-nav-links { margin-right: auto; margin-left: 1.5rem; }
            .store-admin .admin-nav-link, .store-admin .admin-nav-action { display: inline-flex; align-items: center; border: 0; border-radius: 10px; background: transparent; padding: .65rem .85rem; color: var(--store-muted); font-size: .92rem; font-weight: 600; text-decoration: none; transition: color .15s ease, background .15s ease; }
            .store-admin .admin-nav-link:hover, .store-admin .admin-nav-action:hover, .store-admin .admin-nav-link.active { background: #f1f0ff; color: var(--store-brand); }
            .store-admin .admin-nav-actions { gap: .4rem; }
            .store-admin .admin-user-name { padding: 0 .45rem; color: var(--store-ink); font-size: .9rem; font-weight: 600; }
            .store-admin .admin-logout { margin: 0; }
            .store-admin .admin-nav-toggle { display: none; border: 1px solid var(--store-border); border-radius: 10px; background: #fff; padding: .55rem .7rem; color: var(--store-ink); }
            .store-admin .admin-mobile-menu { display: none; border-top: 1px solid var(--store-border); padding: .75rem 1.25rem 1rem; }
            .store-admin .admin-mobile-menu .admin-nav-link { display: flex; width: 100%; }
            .store-admin .admin-mobile-user { margin: .5rem 0; padding: .75rem .85rem; border-top: 1px solid var(--store-border); color: var(--store-muted); font-size: .9rem; }
            .store-admin .admin-page-heading { border-bottom: 0; background: transparent; box-shadow: none; }
            .store-admin .admin-content { min-height: calc(100vh - 76px); }
            .store-admin table thead th { letter-spacing: .06em; }
            .store-admin table tbody tr:hover { background: #fafaff; }
            .store-admin input:not([type="checkbox"]):not([type="file"]), .store-admin select, .store-admin textarea { border-color: #d9dce8; border-radius: 11px; background-color: #fff; color: var(--store-ink); }
            .store-admin input[type="file"] { color: var(--store-muted); }
            .store-admin input[type="checkbox"] { accent-color: var(--store-brand); }
            @media (max-width: 900px) {
                .store-admin .admin-nav-inner { min-height: 68px; }
                .store-admin .admin-nav-links, .store-admin .admin-nav-actions { display: none; }
                .store-admin .admin-nav-toggle { display: inline-flex; align-items: center; justify-content: center; }
                .store-admin .admin-mobile-menu { display: block; }
            }
        </style>
    </head>
    <body class="store-admin antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            @isset($header)
                <header class="admin-page-heading">
                    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="admin-content">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
