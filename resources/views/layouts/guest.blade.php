<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>ZomatoClone — Good food, good mood</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="preconnect" href="https://images.unsplash.com">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet" media="print" onload="this.media='all'">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            :root { color-scheme:light; --auth-ink:#28211d; --auth-muted:#8b7e73; --auth-brand:#e8342a; --auth-line:#eee7e1; }
            * { box-sizing:border-box; }
            body.auth-page { min-width:320px; min-height:100vh; margin:0; padding:clamp(12px,2vw,24px); display:grid; place-items:center; background:radial-gradient(ellipse at 10% 12%,#fff0e8 0,transparent 35%),radial-gradient(ellipse at 90% 85%,#f9e9dc 0,transparent 34%),#f7f3ef; color:var(--auth-ink); font-family:Figtree,system-ui,sans-serif; -webkit-font-smoothing:antialiased; }
            .auth-shell { display:grid; grid-template-columns:minmax(0,1.08fr) minmax(0,.92fr); width:min(1360px,100%); min-height:min(760px,calc(100vh - 48px)); overflow:hidden; border:1px solid rgba(76,51,35,.1); border-radius:28px; background:#fff; box-shadow:0 30px 90px rgba(72,49,34,.16),0 3px 12px rgba(72,49,34,.04); }
            .auth-story { position:relative; display:flex; flex-direction:column; justify-content:space-between; min-height:100%; overflow:hidden; padding:clamp(28px,4vw,56px); isolation:isolate; color:#fff; background:#251915; }
            .auth-story::before { position:absolute; inset:0; z-index:-3; background:url('https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=1000&h=1300&q=72') center/cover; content:""; transform:scale(1.02); }
            .auth-story-video { position:absolute; inset:0; z-index:-2; width:100%; height:100%; object-fit:cover; opacity:0; filter:saturate(1.16) contrast(1.04); transition:opacity .25s ease; }
            .auth-story-video.is-ready { opacity:1; }
            .auth-story::after { position:absolute; inset:0; z-index:-1; background:linear-gradient(180deg,rgba(26,17,13,.3),rgba(26,17,13,.06) 37%,rgba(26,17,13,.72)),linear-gradient(90deg,rgba(31,20,16,.12),transparent); content:""; }
            .auth-story-top { display:flex; align-items:center; gap:10px; color:#fff; font-size:17px; font-weight:800; letter-spacing:-.04em; }
            .auth-logo-mark { display:grid; place-items:center; width:36px; height:36px; border:1px solid rgba(255,255,255,.3); border-radius:12px; background:rgba(255,255,255,.15); font-size:19px; backdrop-filter:blur(8px); }
            .auth-story-copy { max-width:440px; padding-bottom:14px; }
            .auth-story-kicker { display:inline-flex; align-items:center; gap:7px; padding:7px 11px; border:1px solid rgba(255,255,255,.28); border-radius:30px; background:rgba(255,255,255,.12); color:#ffe0b9; font-size:10px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; backdrop-filter:blur(8px); }
            .auth-story h1 { margin:18px 0 12px; color:#fff; font-family:Georgia,'Times New Roman',serif; font-size:clamp(38px,4.2vw,58px); font-weight:600; line-height:.99; letter-spacing:-.055em; }
            .auth-story h1 span { color:#ffc783; font-style:italic; }
            .auth-story-copy p { max-width:370px; margin:0; color:rgba(255,255,255,.78); font-size:14px; line-height:1.65; }
            .auth-story-proof { display:flex; align-items:center; gap:11px; margin-top:23px; color:rgba(255,255,255,.9); font-size:11px; font-weight:600; }
            .auth-proof-icons { display:flex; }
            .auth-proof-icons span { display:grid; place-items:center; width:28px; height:28px; margin-left:-6px; border:2px solid #6c4030; border-radius:50%; background:#fff4e8; color:#8d3e2d; font-size:12px; }
            .auth-proof-icons span:first-child { margin-left:0; }
            .auth-story-foot { color:rgba(255,255,255,.65); font-size:10px; letter-spacing:.02em; }
            .auth-main { display:flex; flex-direction:column; justify-content:center; padding:clamp(24px,3.2vw,48px) clamp(28px,4vw,60px); }
            .auth-main-top { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:26px; }
            .auth-main-brand { display:flex; align-items:center; gap:8px; color:var(--auth-ink); font-size:17px; font-weight:800; letter-spacing:-.045em; text-decoration:none; }
            .auth-main-brand:hover { color:var(--auth-ink); }
            .auth-main-brand .auth-brand-mark { font-size:21px; }
            .auth-top-link { color:var(--auth-muted); font-size:11px; text-decoration:none; }
            .auth-top-link strong { color:var(--auth-brand); }
            .auth-content { width:100%; max-width:520px; margin:0 auto; }
            .auth-heading { margin:0; color:var(--auth-ink); font-size:28px; font-weight:800; letter-spacing:-.055em; line-height:1.1; }
            .auth-subheading { margin:8px 0 25px; color:var(--auth-muted); font-size:12.5px; line-height:1.55; }
            .auth-field { margin-bottom:13px; }
            .auth-form-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:0 13px; }
            .auth-form-grid .auth-field-wide { grid-column:1 / -1; }
            .auth-label { display:block; margin-bottom:6px; color:#50463e; font-size:11px; font-weight:700; }
            .auth-input-wrap { position:relative; }
            .auth-input-icon { position:absolute; top:50%; left:13px; z-index:1; color:#a59a90; font-size:14px; transform:translateY(-50%); pointer-events:none; }
            .auth-input { display:block; width:100%; height:44px; padding:0 14px 0 39px; border:1px solid #e8e1db; border-radius:12px; outline:0; background:#fdfcfa; color:var(--auth-ink); font:500 12.5px Figtree,system-ui,sans-serif; transition:border-color .18s,box-shadow .18s,background .18s; }
            .auth-input::placeholder { color:#b0a69e; }
            .auth-input:focus { border-color:#e88477; background:#fff; box-shadow:0 0 0 4px rgba(232,52,42,.09); }
            .auth-input.is-invalid { border-color:#d94a42; }
            .auth-password-input { padding-right:48px; }
            .auth-password-toggle { position:absolute; top:50%; right:8px; display:grid; place-items:center; width:34px; height:34px; border:0; border-radius:9px; background:transparent; color:#8c8178; transform:translateY(-50%); }
            .auth-password-toggle:hover { background:#f5efea; color:var(--auth-brand); }
            .auth-error { margin-top:6px; color:#c5332c; font-size:10.5px; }
            .auth-status { margin-bottom:15px; padding:10px 12px; border:1px solid #cfe8d7; border-radius:11px; background:#f1fbf4; color:#267245; font-size:11px; }
            .auth-alert { margin-bottom:15px; padding:10px 12px; border:1px solid #f4d0ca; border-radius:11px; background:#fff5f3; color:#a8322b; font-size:11px; line-height:1.45; }
            .auth-alert ul { margin:5px 0 0; padding-left:18px; }
            .auth-options { display:flex; align-items:center; justify-content:space-between; gap:12px; margin:2px 0 20px; }
            .auth-remember { display:inline-flex; align-items:center; gap:7px; color:#71665c; font-size:11px; cursor:pointer; }
            .auth-remember input { width:15px; height:15px; accent-color:var(--auth-brand); }
            .auth-text-link { color:#9c4b3b; font-size:11px; font-weight:700; text-decoration:none; }
            .auth-text-link:hover { color:var(--auth-brand); text-decoration:underline; }
            .auth-primary { display:flex; align-items:center; justify-content:center; gap:9px; width:100%; min-height:44px; border:0; border-radius:12px; background:linear-gradient(110deg,#ed493a,#cf2826); color:#fff; font:800 12px Figtree,system-ui,sans-serif; letter-spacing:.025em; box-shadow:0 9px 18px rgba(210,47,39,.2); transition:transform .18s,box-shadow .18s; }
            .auth-primary:hover { transform:translateY(-1px); box-shadow:0 12px 22px rgba(210,47,39,.26); }
            .auth-divider { display:flex; align-items:center; gap:12px; margin:19px 0 14px; color:#aaa097; font-size:10px; }
            .auth-divider::before,.auth-divider::after { height:1px; flex:1; background:#eee8e2; content:""; }
            .auth-socials { display:grid; gap:9px; }
            .auth-social-button { display:flex; align-items:center; justify-content:center; gap:10px; min-height:43px; padding:0 14px; border:1px solid #e9e3dd; border-radius:11px; background:#fff; color:#4c443d; font-size:11px; font-weight:700; text-decoration:none; transition:border-color .18s,background .18s,transform .18s; }
            .auth-social-button:hover { transform:translateY(-1px); border-color:#d7c8bb; background:#fdfbf9; color:#352e28; }
            .auth-google-mark { font-size:16px; font-weight:800; }
            @media(min-width:851px) { .auth-socials { grid-template-columns:repeat(2,minmax(0,1fr)); } }
            .auth-footnote { margin:20px 0 0; color:#a0958a; font-size:10px; line-height:1.55; text-align:center; }
            .auth-inline-cta { margin:19px 0 0; color:#81766d; font-size:11px; text-align:center; }
            .auth-inline-cta a { color:var(--auth-brand); font-weight:800; text-decoration:none; }
            .auth-inline-cta a:hover { text-decoration:underline; }
            .auth-referral-note { margin:6px 0 0; color:#298354; font-size:10px; }
            .auth-continue { display:block; margin:22px auto 0; color:#8e8278; font-size:11px; text-align:center; text-decoration:none; }
            .auth-continue:hover { color:#5f5146; text-decoration:underline; }
            @media(max-width:850px) { body.auth-page { padding:18px; } .auth-shell { grid-template-columns:1fr; width:min(560px,100%); min-height:0; } .auth-story { min-height:245px; padding:24px 28px; background-position:center 55%; } .auth-story-copy { max-width:480px; padding:20px 0 0; } .auth-story h1 { margin:10px 0 6px; font-size:38px; } .auth-story-copy p { max-width:440px; font-size:12px; } .auth-story-proof { margin-top:12px; } .auth-story-foot { display:none; } .auth-main { padding:30px 38px; } }
            @media(max-width:680px) { body.auth-page { display:block; padding:0; background:#fff; } .auth-shell { display:flex; flex-direction:column; width:100%; min-height:100vh; border:0; border-radius:0; box-shadow:none; } .auth-story { min-height:210px; padding:20px 22px; background-position:center 47%; } .auth-story-top { font-size:14px; } .auth-logo-mark { width:31px;height:31px; } .auth-story-copy { max-width:440px; padding:22px 0 0; } .auth-story-kicker { padding:5px 8px; font-size:8px; } .auth-story h1 { margin:10px 0 6px; font-size:34px; } .auth-story-copy p { max-width:370px; font-size:11px; } .auth-story-proof,.auth-story-foot { display:none; } .auth-main { flex:1; justify-content:flex-start; padding:22px 23px 27px; } .auth-main-top { justify-content:flex-end; margin-bottom:24px; } .auth-main-brand { display:none; } .auth-heading { font-size:25px; } .auth-subheading { margin:7px 0 20px; } .auth-continue { margin:18px auto 0; } }
            @media(max-height:700px) and (min-width:851px) { .auth-story { padding-top:24px; padding-bottom:24px; } .auth-story-copy { padding-bottom:0; } .auth-story h1 { margin:12px 0 8px; font-size:clamp(34px,3.7vw,48px); } .auth-story-proof { margin-top:14px; } .auth-main { padding-top:20px; padding-bottom:20px; } .auth-main-top { margin-bottom:15px; } .auth-subheading { margin-bottom:15px; } .auth-field { margin-bottom:9px; } .auth-footnote { margin-top:12px; } .auth-inline-cta { margin-top:12px; } .auth-continue { margin-top:14px; } }
            @media(max-width:680px) { body.auth-page { display:block; padding:0; background:#fff; } .auth-shell { display:flex; flex-direction:column; width:100%; min-height:100vh; border:0; border-radius:0; box-shadow:none; } .auth-story { min-height:210px; padding:20px 22px; background-position:center 47%; } .auth-story-top { font-size:14px; } .auth-logo-mark { width:31px;height:31px; } .auth-story-copy { max-width:440px; padding:22px 0 0; } .auth-story-kicker { padding:5px 8px; font-size:8px; } .auth-story h1 { margin:10px 0 6px; font-size:34px; } .auth-story-copy p { max-width:370px; font-size:11px; } .auth-story-proof,.auth-story-foot { display:none; } .auth-main { flex:1; justify-content:flex-start; padding:22px 23px 27px; } .auth-main-top { justify-content:flex-end; margin-bottom:24px; } .auth-main-brand { display:none; } .auth-heading { font-size:25px; } .auth-subheading { margin:7px 0 20px; } .auth-continue { margin:18px auto 0; } }
            @media(max-width:680px) { .auth-form-grid { grid-template-columns:1fr; } .auth-form-grid .auth-field-wide { grid-column:auto; } }
            @media(prefers-reduced-motion:reduce) { *,*::before,*::after { scroll-behavior:auto !important; transition:none !important; } .auth-story-video { display:none; } }
        </style>
    </head>
    <body class="auth-page">
        <main class="auth-shell">
            <aside class="auth-story" aria-label="Food delivery">
                <video class="auth-story-video" autoplay muted loop playsinline preload="none" poster="https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=1000&h=1300&q=72" aria-hidden="true">
                    <source data-src="https://videos.pexels.com/video-files/37848046/16055298_1080_1920_24fps.mp4" type="video/mp4">
                </video>
                <a class="auth-story-top text-decoration-none" href="{{ route('customer.dashboard') }}">
                    <span class="auth-logo-mark" aria-hidden="true">🍔</span>
                    <span>ZomatoClone</span>
                </a>
                <div class="auth-story-copy">
                    <span class="auth-story-kicker"><i class="bi bi-stars"></i> Good food, good mood</span>
                    <h1>Make room<br>for <span>something</span><br>delicious.</h1>
                    <p>Discover neighbourhood favourites, follow every order, and bring your next great meal closer.</p>
                    <div class="auth-story-proof">
                        <span class="auth-proof-icons" aria-hidden="true"><span>🍕</span><span>🍜</span><span>🍔</span></span>
                        <span>Local kitchens. Freshly made. Right to you.</span>
                    </div>
                </div>
                <div class="auth-story-foot">Your next favourite meal is only a few clicks away.</div>
            </aside>
            <section class="auth-main">
                <div class="auth-main-top">
                    <a href="{{ route('customer.dashboard') }}" class="auth-main-brand"><span class="auth-brand-mark" aria-hidden="true">🍔</span><span><span style="color:#e8342a">Zomato</span>Clone</span></a>
                    <a class="auth-top-link" href="{{ route('customer.dashboard') }}">Just looking? <strong>Browse menu</strong></a>
                </div>
                <div class="auth-content">{{ $slot }}</div>
                <a href="{{ route('customer.dashboard') }}" class="auth-continue"><i class="bi bi-arrow-left me-1"></i> Continue browsing without an account</a>
            </section>
        </main>
        <script>
            document.querySelectorAll('[data-auth-password-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    const input = document.getElementById(button.dataset.authPasswordToggle);
                    if (!input) return;
                    const show = input.type === 'password';
                    input.type = show ? 'text' : 'password';
                    button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                    button.innerHTML = show ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
                });
            });

            const storyVideo = document.querySelector('.auth-story-video');
            const storyVideoSource = storyVideo?.querySelector('source[data-src]');
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const prefersDataSaving = navigator.connection && navigator.connection.saveData;

            if (storyVideo && storyVideoSource && !prefersReducedMotion && !prefersDataSaving) {
                window.addEventListener('load', () => {
                    window.setTimeout(() => {
                        storyVideo.addEventListener('canplay', () => storyVideo.classList.add('is-ready'), { once: true });
                        storyVideoSource.src = storyVideoSource.dataset.src;
                        storyVideo.load();
                        storyVideo.play().catch(() => {});
                    }, 2500);
                }, { once: true });
            }
        </script>
    </body>
</html>
