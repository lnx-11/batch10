<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f7f8fc">
    <title>{{ $title ?? 'LИX.goods | Toko Pilihan' }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/brand-logo.jpg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --ink: #172033; --muted: #68738a; --brand: #5146e5; --brand-dark: #3930b8; --surface: #fff; --page: #f7f8fc; }
        * { box-sizing: border-box; }
        body { background: var(--page); color: var(--ink); font-family: 'DM Sans', sans-serif; }
        h1, h2, h3, h4, .brand-name { font-family: 'Manrope', sans-serif; }
        a { text-decoration: none; }
        .site-nav { background: rgba(255,255,255,.94); border-bottom: 1px solid #e9ebf2; backdrop-filter: blur(12px); }
        .brand-logo { display:block; width:44px; height:44px; margin-right:10px; flex:0 0 auto; border-radius:10px; object-fit:cover; }
        .brand-name { color:var(--ink); font-size:1.15rem; font-weight:800; letter-spacing:-.04em; }
        .site-nav .nav-link { color:#69738a; font-weight:600; padding:.65rem .9rem!important; }
        .site-nav .nav-link:hover, .site-nav .nav-link.active { color:var(--brand); }
        .btn-brand { background:var(--brand); border:1px solid var(--brand); color:#fff; font-weight:700; border-radius:12px; padding:.8rem 1.15rem; }
        .btn-brand:hover { background:var(--brand-dark); border-color:var(--brand-dark); color:#fff; transform:translateY(-1px); }
        .btn-outline-brand { border:1px solid #d9d8fa; color:var(--brand); font-weight:700; border-radius:12px; padding:.8rem 1.15rem; }
        .btn-outline-brand:hover { background:#f1f0ff; color:var(--brand-dark); }
        .eyebrow { color:var(--brand); font-size:.78rem; font-weight:800; letter-spacing:.13em; text-transform:uppercase; }
        .hero { position:relative; overflow:hidden; border-radius:30px; background:linear-gradient(120deg,#24234f 0%,#4541a9 55%,#7166f0 100%); color:#fff; }
        .hero:after { content:''; position:absolute; width:380px; height:380px; right:-100px; top:-180px; border:1px solid rgba(255,255,255,.16); border-radius:50%; box-shadow:0 0 0 40px rgba(255,255,255,.035),0 0 0 85px rgba(255,255,255,.025); }
        .hero-content { position:relative; z-index:1; }
        .hero h1 { max-width:620px; font-size:clamp(2.6rem,5vw,4.5rem); line-height:1.08; font-weight:800; letter-spacing:-.055em; }
        .hero-copy { max-width:510px; color:#dad9ff; font-size:1.07rem; line-height:1.8; }
        .hero-visual { position:relative; min-height:330px; display:grid; place-items:center; }
        .hero-glow { position:absolute; width:270px; height:270px; border-radius:50%; background:rgba(255,255,255,.13); filter:blur(2px); }
        .hero-product { position:relative; z-index:1; width:min(100%,390px); height:280px; object-fit:cover; border:8px solid rgba(255,255,255,.16); border-radius:28px; transform:rotate(3deg); box-shadow:0 25px 60px rgba(15,14,54,.32); }
        .trust-pill { display:inline-flex; align-items:center; gap:8px; padding:9px 14px; border:1px solid rgba(255,255,255,.24); border-radius:999px; background:rgba(255,255,255,.1); color:#f1efff; font-size:.85rem; font-weight:600; }
        .section-title { font-size:clamp(1.7rem,3vw,2.4rem); font-weight:800; letter-spacing:-.045em; }
        .benefit-card, .product-card, .cart-panel { height:100%; border:1px solid #eaecf3; border-radius:20px; background:var(--surface); box-shadow:0 10px 30px rgba(28,34,67,.035); }
        .benefit-card { padding:22px; }
        .benefit-icon { display:grid; place-items:center; width:44px; height:44px; border-radius:14px; background:#f0efff; color:var(--brand); font-size:1.2rem; font-weight:800; }
        .product-card { overflow:hidden; transition:transform .2s ease, box-shadow .2s ease; }
        .product-card:hover { transform:translateY(-5px); box-shadow:0 18px 38px rgba(28,34,67,.1); }
        .product-image-wrap { position:relative; overflow:hidden; background:#f0f1f7; }
        .product-image { display:block; width:100%; height:230px; object-fit:cover; transition:transform .35s ease; }
        .product-card:hover .product-image { transform:scale(1.04); }
        .product-badge { position:absolute; top:14px; left:14px; padding:6px 10px; border-radius:999px; background:rgba(255,255,255,.92); color:var(--brand); font-size:.72rem; font-weight:800; }
        .product-body { padding:19px; }
        .product-title { font-weight:800; letter-spacing:-.02em; }
        .product-description { min-height:45px; color:var(--muted); font-size:.9rem; line-height:1.55; }
        .product-price { color:var(--brand); font-size:1.1rem; font-weight:800; }
        .page-heading { padding:35px 0 22px; }
        .page-heading h1 { font-size:clamp(2.2rem,4vw,3.25rem); font-weight:800; letter-spacing:-.05em; }
        .category-chip { display:inline-block; padding:8px 14px; border:1px solid #e7e8f0; border-radius:999px; background:#fff; color:#68738a; font-size:.84rem; font-weight:700; }
        .category-chip.active { border-color:#d9d6ff; background:#f0efff; color:var(--brand); }
        .catalog-pagination nav > div.d-sm-flex { column-gap:1rem; }
        .site-footer { margin-top:70px; background:#191c32; color:#b9bdd0; }
        .site-footer a { color:#dfe1ed; }
        .site-footer a:hover { color:#fff; }
        .footer-brand { color:#fff; font-family:'Manrope',sans-serif; font-weight:800; }
        .cart-panel { padding:24px; }
        .cart-table th { color:#798198; font-size:.78rem; letter-spacing:.06em; text-transform:uppercase; }
        .cart-table td, .cart-table th { padding:16px 12px; vertical-align:middle; }
        .cart-total { border-top:1px solid #eceef4; padding-top:18px; }
        .empty-cart { padding:65px 24px; border:1px dashed #d9dce8; border-radius:22px; background:#fff; text-align:center; }
        @media (max-width:767.98px) { .hero { border-radius:22px; } .hero-visual { min-height:240px; } .hero-product { height:210px; } .page-heading { padding-top:25px; } }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <x-navbar />
    <main class="container py-4 py-lg-5 flex-grow-1">
        {{ $slot }}
    </main>
    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('submit', async (event) => {
            const form = event.target.closest('form[data-cart-ajax]');
            if (!form) return;

            event.preventDefault();
            const button = form.querySelector('button[type="submit"]');
            const feedback = document.getElementById('cart-feedback');
            button.disabled = true;

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Produk belum berhasil ditambahkan.');

                if (feedback) {
                    feedback.textContent = result.message;
                    feedback.classList.remove('d-none', 'alert-danger');
                    feedback.classList.add('alert-success');
                }
                document.querySelectorAll('[data-cart-count]').forEach((badge) => {
                    badge.textContent = result.cartCount;
                });
            } catch (error) {
                if (feedback) {
                    feedback.textContent = error.message;
                    feedback.classList.remove('d-none', 'alert-success');
                    feedback.classList.add('alert-danger');
                } else {
                    window.alert(error.message);
                }
            } finally {
                button.disabled = false;
            }
        });
    </script>
</body>
</html>
