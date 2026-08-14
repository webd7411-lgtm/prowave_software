<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ setting('company.name', app_name()) }} — {{ setting('company.tagline', 'Enterprise Software Solutions') }}</title>
    <meta name="description" content="{{ setting('company.name', app_name()) }} — Run your entire business from one beautiful platform.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --w-primary: #4e8bff;
            --w-secondary: #818cf8;
            --w-accent: #22d3ee;
            --w-emerald: #34d399;
            --w-rose: #fb7185;
            --w-amber: #fbbf24;
            --w-ink: #eaeeff;
            --w-muted: #7e8db8;
            --w-surface: rgba(255, 255, 255, 0.04);
            --w-surface-2: rgba(255, 255, 255, 0.07);
            --w-border: rgba(255, 255, 255, 0.1);
            --w-border-strong: rgba(255, 255, 255, 0.18);
            --w-gradient-1: linear-gradient(135deg, #4e8bff 0%, #818cf8 50%, #c084fc 100%);
            --w-gradient-2: linear-gradient(135deg, #22d3ee 0%, #4e8bff 100%);
            --w-gradient-3: linear-gradient(135deg, #34d399 0%, #22d3ee 100%);
            --w-gradient-4: linear-gradient(135deg, #fb7185 0%, #fbbf24 100%);
            --w-shadow-glow: 0 0 60px rgba(78, 139, 255, 0.3);
        }

        *, *::before, *::after { box-sizing: border-box; }

        html, body { height: 100%; margin: 0; }

        .welcome-page {
            min-height: 100vh;
            background: #060a18;
            color: var(--w-ink);
            font-family: "Inter", system-ui, -apple-system, sans-serif;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        /* ===== animated background ===== */
        .bg-layer { position: fixed; inset: 0; z-index: 0; pointer-events: none; overflow: hidden; }

        .bg-grid {
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(126, 141, 184, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(126, 141, 184, 0.05) 1px, transparent 1px);
            background-size: 64px 64px;
            mask-image: radial-gradient(ellipse 75% 60% at 50% 40%, black 25%, transparent 72%);
            -webkit-mask-image: radial-gradient(ellipse 75% 60% at 50% 40%, black 25%, transparent 72%);
            animation: grid-pan 28s linear infinite;
        }
        @keyframes grid-pan {
            from { background-position: 0 0; }
            to   { background-position: 128px 128px; }
        }

        .bg-glow {
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse 50% 40% at 50% -5%, rgba(78, 139, 255, 0.15), transparent 65%),
                radial-gradient(ellipse 40% 35% at 80% 10%, rgba(129, 140, 248, 0.1), transparent 60%);
        }

        .orb {
            position: absolute; border-radius: 50%; filter: blur(100px); opacity: 0.5;
            will-change: transform;
        }
        .orb-1 { width: 550px; height: 550px; left: -160px; top: -180px; background: radial-gradient(circle, rgba(78, 139, 255, 0.7), transparent 70%); animation: orb-float 20s ease-in-out infinite alternate; }
        .orb-2 { width: 480px; height: 480px; right: -130px; top: -100px; background: radial-gradient(circle, rgba(129, 140, 248, 0.6), transparent 70%); animation: orb-float 25s ease-in-out infinite alternate-reverse; }
        .orb-3 { width: 440px; height: 440px; left: 20%; bottom: -220px; background: radial-gradient(circle, rgba(34, 211, 238, 0.4), transparent 70%); animation: orb-float 22s ease-in-out infinite alternate; }
        .orb-4 { width: 340px; height: 340px; right: 22%; bottom: -140px; background: radial-gradient(circle, rgba(251, 113, 133, 0.35), transparent 70%); animation: orb-float 18s ease-in-out infinite alternate-reverse; }
        .orb-5 { width: 260px; height: 260px; left: 45%; top: 30%; background: radial-gradient(circle, rgba(52, 211, 153, 0.3), transparent 70%); animation: orb-float 16s ease-in-out infinite alternate; }

        @keyframes orb-float {
            0%   { transform: translate(0, 0) scale(1); }
            50%  { transform: translate(50px, -30px) scale(1.1); }
            100% { transform: translate(-30px, 50px) scale(0.95); }
        }

        /* mouse-follow glow */
        .mouse-glow {
            position: fixed; width: 500px; height: 500px; border-radius: 50%;
            background: radial-gradient(circle, rgba(78, 139, 255, 0.07), transparent 70%);
            pointer-events: none; z-index: 1; transform: translate(-50%, -50%);
            transition: left 0.3s ease-out, top 0.3s ease-out;
        }

        /* floating particles */
        .particle {
            position: absolute; width: 4px; height: 4px; border-radius: 50%;
            background: var(--w-primary); opacity: 0; animation: particle-rise 8s ease-in infinite;
        }
        .particle:nth-child(1) { left: 10%; animation-delay: 0s; background: var(--w-primary); }
        .particle:nth-child(2) { left: 25%; animation-delay: 1.2s; background: var(--w-accent); }
        .particle:nth-child(3) { left: 40%; animation-delay: 2.5s; background: var(--w-secondary); }
        .particle:nth-child(4) { left: 55%; animation-delay: 0.8s; background: var(--w-emerald); }
        .particle:nth-child(5) { left: 70%; animation-delay: 3s; background: var(--w-rose); }
        .particle:nth-child(6) { left: 85%; animation-delay: 1.8s; background: var(--w-amber); }
        .particle:nth-child(7) { left: 35%; animation-delay: 4s; background: var(--w-primary); }
        .particle:nth-child(8) { left: 60%; animation-delay: 3.5s; background: var(--w-accent); }
        .particle:nth-child(9) { left: 15%; animation-delay: 5s; background: var(--w-secondary); }
        .particle:nth-child(10) { left: 90%; animation-delay: 2.2s; background: var(--w-emerald); }

        @keyframes particle-rise {
            0%   { transform: translateY(100vh) scale(0); opacity: 0; }
            10%  { opacity: 0.8; }
            90%  { opacity: 0.3; }
            100% { transform: translateY(-10vh) scale(1.5); opacity: 0; }
        }

        /* ===== layout ===== */
        .welcome-nav {
            position: relative; z-index: 10;
            display: flex; align-items: center; justify-content: space-between;
            padding: 1.2rem 3rem;
            backdrop-filter: blur(16px);
            background: rgba(6, 10, 24, 0.6);
            border-bottom: 1px solid var(--w-border);
            animation: nav-in 1s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        @keyframes nav-in {
            from { opacity: 0; transform: translateY(-20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .welcome-main {
            position: relative; z-index: 5;
            flex: 1; display: flex; flex-direction: column;
            padding: 0 1.5rem;
        }

        .welcome-footer {
            position: relative; z-index: 5;
            text-align: center; padding: 2rem 1.5rem;
            font-size: 0.82rem; color: var(--w-muted);
            border-top: 1px solid var(--w-border);
        }

        /* ===== brand ===== */
        .brand {
            display: flex; align-items: center; gap: 0.8rem;
            text-decoration: none; color: var(--w-ink);
            transition: opacity 0.2s;
        }
        .brand:hover { opacity: 0.85; color: var(--w-ink); }
        .brand-logo {
            width: 42px; height: 42px; border-radius: 12px; object-fit: cover;
            box-shadow: 0 0 0 1px var(--w-border), 0 8px 24px rgba(78, 139, 255, 0.3);
        }
        .brand-name { font-weight: 700; font-size: 1.05rem; letter-spacing: 0.01em; }

        .nav-links { display: flex; align-items: center; gap: 0.5rem; }
        .nav-link {
            color: var(--w-muted); text-decoration: none; font-size: 0.9rem; font-weight: 500;
            padding: 0.5rem 1rem; border-radius: 10px;
            transition: color 0.2s, background 0.2s;
        }
        .nav-link:hover { color: var(--w-ink); background: var(--w-surface); }

        /* ===== buttons ===== */
        .btn-premium {
            display: inline-flex; align-items: center; gap: 0.6rem;
            padding: 0.8rem 1.8rem; border-radius: 14px;
            font-size: 0.95rem; font-weight: 700; color: #fff; text-decoration: none;
            background: var(--w-gradient-1);
            box-shadow: 0 0 0 1px rgba(255,255,255,0.12) inset, 0 12px 40px rgba(78, 139, 255, 0.4);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s;
            position: relative; overflow: hidden;
        }
        .btn-premium::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(120deg, transparent 30%, rgba(255,255,255,0.2) 50%, transparent 70%);
            transform: translateX(-100%); transition: transform 0.6s;
        }
        .btn-premium:hover::before { transform: translateX(100%); }
        .btn-premium:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 0 0 1px rgba(255,255,255,0.18) inset, 0 20px 55px rgba(78, 139, 255, 0.55);
            color: #fff;
        }
        .btn-premium:active { transform: translateY(-1px) scale(0.99); }
        .btn-premium i { transition: transform 0.3s; }
        .btn-premium:hover i { transform: translateX(5px); }

        .btn-ghost {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.8rem 1.5rem; border-radius: 14px;
            font-size: 0.95rem; font-weight: 600; color: var(--w-ink); text-decoration: none;
            background: var(--w-surface); border: 1px solid var(--w-border);
            backdrop-filter: blur(10px);
            transition: transform 0.25s, border-color 0.25s, background 0.25s;
        }
        .btn-ghost:hover {
            transform: translateY(-2px); border-color: var(--w-border-strong);
            background: var(--w-surface-2); color: var(--w-ink);
        }

        /* ===== hero ===== */
        .hero {
            display: flex; flex-direction: column; align-items: center;
            text-align: center; padding: 5rem 0 4rem;
        }

        .hero-badge {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.45rem 1.1rem; border-radius: 999px;
            font-size: 0.78rem; font-weight: 600; color: var(--w-accent);
            background: rgba(34, 211, 238, 0.08); border: 1px solid rgba(34, 211, 238, 0.2);
            margin-bottom: 1.8rem;
            animation: badge-in 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.2s both;
        }
        .hero-badge .badge-dot {
            width: 7px; height: 7px; border-radius: 50%; background: var(--w-emerald);
            box-shadow: 0 0 8px var(--w-emerald); animation: pulse-dot 2s ease-in-out infinite;
        }
        @keyframes badge-in {
            from { opacity: 0; transform: translateY(15px) scale(0.9); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%      { opacity: 0.5; transform: scale(0.7); }
        }

        .hero-logo {
            width: 88px; height: 88px; border-radius: 24px; object-fit: cover;
            box-shadow: 0 0 0 1px var(--w-border), 0 0 50px rgba(78, 139, 255, 0.5), 0 20px 50px rgba(0,0,0,0.5);
            margin-bottom: 2rem;
            animation: logo-in 1s cubic-bezier(0.22, 1.2, 0.36, 1) 0.1s both;
        }
        @keyframes logo-in {
            from { opacity: 0; transform: scale(0.5) rotate(-8deg); }
            to   { opacity: 1; transform: scale(1) rotate(0deg); }
        }

        .hero-title {
            margin: 0 0 0.8rem;
            font-size: clamp(2.4rem, 6vw, 4rem);
            font-weight: 900; letter-spacing: -0.03em; line-height: 1.05;
            animation: hero-text-in 0.9s cubic-bezier(0.16, 1, 0.3, 1) 0.3s both;
        }
        .hero-title .gradient-text {
            background: linear-gradient(120deg, #ffffff 20%, #93c5fd 40%, #a5b4fc 60%, #c4b5fd 80%);
            -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
            background-size: 200% auto;
            animation: gradient-shift 6s ease-in-out infinite;
        }
        @keyframes gradient-shift {
            0%, 100% { background-position: 0% center; }
            50%      { background-position: 200% center; }
        }
        @keyframes hero-text-in {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .hero-tagline {
            font-size: clamp(0.85rem, 1.5vw, 1rem);
            font-weight: 700; color: var(--w-accent);
            letter-spacing: 0.18em; text-transform: uppercase;
            margin: 0 0 1.2rem;
            animation: hero-text-in 0.9s cubic-bezier(0.16, 1, 0.3, 1) 0.4s both;
        }

        .hero-text {
            max-width: 580px; color: var(--w-muted);
            font-size: 1.08rem; line-height: 1.75;
            margin: 0 auto 2.5rem;
            animation: hero-text-in 0.9s cubic-bezier(0.16, 1, 0.3, 1) 0.5s both;
        }

        .hero-actions {
            display: flex; flex-wrap: wrap; gap: 1rem; justify-content: center;
            margin-bottom: 1.2rem;
            animation: hero-text-in 0.9s cubic-bezier(0.16, 1, 0.3, 1) 0.6s both;
        }

        .hero-note {
            font-size: 0.8rem; color: var(--w-muted);
            animation: hero-text-in 0.9s cubic-bezier(0.16, 1, 0.3, 1) 0.7s both;
        }

        /* ===== stats ===== */
        .stats-row {
            display: flex; flex-wrap: wrap; gap: 2.5rem; justify-content: center;
            padding: 2rem 0 4rem;
            animation: hero-text-in 1s cubic-bezier(0.16, 1, 0.3, 1) 0.8s both;
        }
        .stat-item { text-align: center; }
        .stat-value {
            font-size: 2rem; font-weight: 800; letter-spacing: -0.02em;
            background: var(--w-gradient-2);
            -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
        }
        .stat-label { font-size: 0.78rem; color: var(--w-muted); font-weight: 500; margin-top: 0.2rem; }

        /* ===== features ===== */
        .section {
            padding: 4rem 0; max-width: 1080px; margin: 0 auto; width: 100%;
        }
        .section-header { text-align: center; margin-bottom: 3rem; }
        .section-label {
            display: inline-block; font-size: 0.75rem; font-weight: 700;
            letter-spacing: 0.15em; text-transform: uppercase;
            color: var(--w-primary); margin-bottom: 0.8rem;
        }
        .section-title {
            font-size: clamp(1.6rem, 3.5vw, 2.4rem); font-weight: 800;
            letter-spacing: -0.02em; margin: 0 0 0.8rem;
        }
        .section-desc {
            color: var(--w-muted); font-size: 1rem; max-width: 480px;
            margin: 0 auto; line-height: 1.65;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.2rem;
        }
        .feature-card {
            padding: 1.8rem; border-radius: 20px;
            background: var(--w-surface); border: 1px solid var(--w-border);
            backdrop-filter: blur(12px);
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), border-color 0.35s, box-shadow 0.35s;
            opacity: 0; transform: translateY(40px);
        }
        .feature-card.reveal { opacity: 1; transform: translateY(0); }
        .feature-card:hover {
            transform: translateY(-6px);
            border-color: var(--w-border-strong);
            box-shadow: 0 20px 50px rgba(0,0,0,0.3), 0 0 30px rgba(78, 139, 255, 0.08);
        }
        .feature-icon {
            width: 50px; height: 50px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; margin-bottom: 1.2rem;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .feature-icon.i-blue   { background: linear-gradient(135deg, rgba(78,139,255,0.2), rgba(78,139,255,0.05)); color: #7db3ff; }
        .feature-icon.i-purple { background: linear-gradient(135deg, rgba(129,140,248,0.2), rgba(129,140,248,0.05)); color: #a5b4fc; }
        .feature-icon.i-cyan   { background: linear-gradient(135deg, rgba(34,211,238,0.2), rgba(34,211,238,0.05)); color: #22d3ee; }
        .feature-icon.i-green  { background: linear-gradient(135deg, rgba(52,211,153,0.2), rgba(52,211,153,0.05)); color: #34d399; }
        .feature-icon.i-rose   { background: linear-gradient(135deg, rgba(251,113,133,0.2), rgba(251,113,133,0.05)); color: #fb7185; }
        .feature-icon.i-amber  { background: linear-gradient(135deg, rgba(251,191,36,0.2), rgba(251,191,36,0.05)); color: #fbbf24; }
        .feature-title { font-size: 1.05rem; font-weight: 700; margin: 0 0 0.5rem; }
        .feature-text { font-size: 0.88rem; color: var(--w-muted); line-height: 1.6; margin: 0; }

        /* ===== highlights / trust ===== */
        .highlights {
            display: flex; flex-wrap: wrap; gap: 0.7rem; justify-content: center;
            padding: 2rem 0 4rem; max-width: 700px; margin: 0 auto;
        }
        .chip {
            display: inline-flex; align-items: center; gap: 0.45rem;
            padding: 0.5rem 1rem; border-radius: 999px;
            font-size: 0.82rem; font-weight: 500; color: #cbd5e1;
            background: var(--w-surface); border: 1px solid var(--w-border);
            backdrop-filter: blur(8px);
            opacity: 0; transform: translateY(20px);
            transition: transform 0.25s, border-color 0.25s, background 0.25s;
        }
        .chip.reveal { opacity: 1; transform: translateY(0); }
        .chip:hover { border-color: var(--w-border-strong); background: var(--w-surface-2); transform: translateY(-2px); }
        .chip i { font-size: 0.8rem; }

        /* ===== contact ===== */
        .contact-section {
            max-width: 860px; margin: 0 auto; padding: 3rem 0 2rem;
        }
        .contact-title {
            font-size: 0.82rem; font-weight: 700; letter-spacing: 0.14em;
            text-transform: uppercase; color: var(--w-muted);
            text-align: center; margin-bottom: 1.4rem;
        }
        .contact-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        .contact-card {
            display: flex; align-items: center; gap: 0.9rem;
            padding: 1rem 1.2rem; border-radius: 16px;
            background: var(--w-surface); border: 1px solid var(--w-border);
            backdrop-filter: blur(10px); text-decoration: none; color: var(--w-ink);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), border-color 0.3s, box-shadow 0.3s;
            opacity: 0; transform: translateY(30px);
        }
        .contact-card.reveal { opacity: 1; transform: translateY(0); }
        .contact-card:hover {
            transform: translateY(-5px);
            border-color: rgba(129, 140, 248, 0.4);
            box-shadow: 0 15px 40px rgba(0,0,0,0.25);
            color: var(--w-ink);
        }
        .contact-icon {
            width: 42px; height: 42px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            border-radius: 12px; font-size: 1.1rem;
            background: var(--w-gradient-1);
            border: 1px solid rgba(255,255,255,0.12);
            color: #fff;
        }
        .contact-label {
            font-size: 0.68rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: 0.08em; color: var(--w-muted); margin-bottom: 0.15rem;
        }
        .contact-value { font-size: 0.88rem; font-weight: 600; word-break: break-word; }

        /* ===== footer ===== */
        .footer-inner {
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
            gap: 1rem; max-width: 1080px; margin: 0 auto;
        }
        .footer-brand { display: flex; align-items: center; gap: 0.6rem; }
        .footer-brand img { width: 28px; height: 28px; border-radius: 8px; }
        .footer-brand span { font-size: 0.85rem; font-weight: 600; }
        .footer-copy { font-size: 0.78rem; color: var(--w-muted); }

        /* ===== responsive ===== */
        @media (max-width: 768px) {
            .welcome-nav { padding: 1rem 1.2rem; }
            .nav-links .nav-link { display: none; }
            .hero { padding: 3rem 0 2.5rem; }
            .stats-row { gap: 1.8rem; }
            .section { padding: 2.5rem 0; }
        }
        @media (max-width: 576px) {
            .hero-actions { flex-direction: column; align-items: center; }
            .btn-premium, .btn-ghost { width: 100%; justify-content: center; }
        }

        /* ===== reduced motion ===== */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body class="welcome-page">

    <!-- animated background -->
    <div class="bg-layer">
        <div class="bg-grid"></div>
        <div class="bg-glow"></div>
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
        <div class="orb orb-4"></div>
        <div class="orb orb-5"></div>
        <div class="particle"></div><div class="particle"></div><div class="particle"></div>
        <div class="particle"></div><div class="particle"></div><div class="particle"></div>
        <div class="particle"></div><div class="particle"></div><div class="particle"></div>
        <div class="particle"></div>
    </div>
    <div class="mouse-glow" id="mouseGlow"></div>

    <!-- nav -->
    <nav class="welcome-nav">
        <a href="{{ route('home') }}" class="brand">
            <img src="{{ app_logo() }}" alt="{{ setting('company.name', app_name()) }}" class="brand-logo">
            <span class="brand-name">{{ setting('company.name', app_name()) }}</span>
        </a>
        <div class="nav-links">
            <a href="#features" class="nav-link">Features</a>
            <a href="#contact" class="nav-link">Contact</a>
            <a href="{{ route('login') }}" class="btn-premium btn-login-sm">
                <i class="bi bi-box-arrow-in-right"></i> {{ __('Sign In') }}
            </a>
        </div>
    </nav>

    <!-- main -->
    <main class="welcome-main">

        <!-- hero -->
        <section class="hero">
            <div class="hero-badge">
                <span class="badge-dot"></span> Now Live — Premium ERP Platform
            </div>

            <img src="{{ app_logo() }}" alt="{{ setting('company.name', app_name()) }}" class="hero-logo">

            <h1 class="hero-title">
                <span class="gradient-text">{{ setting('company.name', app_name()) }}</span>
            </h1>
            <p class="hero-tagline">{{ setting('company.tagline', 'Enterprise Software Solutions') }}</p>
            <p class="hero-text">
                Run your entire business from one place — projects, people, finance, clients, and operations
                together in a single, beautiful platform built for growing companies.
            </p>

            <div class="hero-actions">
                <a href="{{ route('login') }}" class="btn-premium">
                    {{ __('Login to Dashboard') }} <i class="bi bi-arrow-right"></i>
                </a>
                <a href="#features" class="btn-ghost">
                    <i class="bi bi-stars"></i> Explore Features
                </a>
            </div>
            <p class="hero-note">Secure access for authorized personnel</p>
        </section>

        <!-- stats -->
        <div class="stats-row">
            <div class="stat-item">
                <div class="stat-value" data-count="150">0</div>
                <div class="stat-label">Active Companies</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" data-count="12">0</div>
                <div class="stat-label">Core Modules</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" data-count="99">0</div>
                <div class="stat-label">Uptime %</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" data-count="24">0</div>
                <div class="stat-label">/7 Support</div>
            </div>
        </div>

        <!-- features -->
        <section class="section" id="features">
            <div class="section-header">
                <span class="section-label">What's Included</span>
                <h2 class="section-title">Everything you need to scale</h2>
                <p class="section-desc">Powerful modules that work together seamlessly — from hiring to invoicing, all in one platform.</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon i-blue"><i class="bi bi-kanban-fill"></i></div>
                    <h3 class="feature-title">Projects & Tasks</h3>
                    <p class="feature-text">Kanban boards, timelines, and real-time collaboration to keep every project on track.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon i-purple"><i class="bi bi-people-fill"></i></div>
                    <h3 class="feature-title">Employee Management</h3>
                    <p class="feature-text">Attendance, leaves, payroll, shifts, and performance — your entire HR lifecycle automated.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon i-cyan"><i class="bi bi-cash-coin"></i></div>
                    <h3 class="feature-title">Finance & Invoicing</h3>
                    <p class="feature-text">Income, expenses, bank reconciliation, tax, and beautiful invoices generated in seconds.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon i-green"><i class="bi bi-graph-up-arrow"></i></div>
                    <h3 class="feature-title">Analytics & Reports</h3>
                    <p class="feature-text">Live dashboards, custom reports, and data-driven insights to fuel smarter decisions.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon i-rose"><i class="bi bi-shield-check"></i></div>
                    <h3 class="feature-title">Role-based Security</h3>
                    <p class="feature-text">Granular permissions, audit logs, and encrypted data — enterprise-grade security built in.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon i-amber"><i class="bi bi-robot"></i></div>
                    <h3 class="feature-title">Smart Automation</h3>
                    <p class="feature-text">Workflows, notifications, reminders, and approvals that run themselves 24/7.</p>
                </div>
            </div>
        </section>

        <!-- trust chips -->
        <div class="highlights">
            <span class="chip"><i class="bi bi-cloud-check" style="color:#34d399"></i> Cloud Hosted</span>
            <span class="chip"><i class="bi bi-phone" style="color:#22d3ee"></i> Mobile Ready</span>
            <span class="chip"><i class="bi bi-lightning-charge" style="color:#fbbf24"></i> Lightning Fast</span>
            <span class="chip"><i class="bi bi-lock-fill" style="color:#fb7185"></i> Encrypted</span>
            <span class="chip"><i class="bi bi-globe2" style="color:#a5b4fc"></i> Multi-language</span>
            <span class="chip"><i class="bi bi-plug" style="color:#7db3ff"></i> API Ready</span>
        </div>

        <!-- contact -->
        <section class="contact-section" id="contact">
            <p class="contact-title">Get in Touch</p>
            <div class="contact-grid">
                @if(setting('general.email'))
                <a href="mailto:{{ setting('general.email') }}" class="contact-card">
                    <div class="contact-icon"><i class="bi bi-envelope-fill"></i></div>
                    <div>
                        <div class="contact-label">Email</div>
                        <div class="contact-value">{{ setting('general.email') }}</div>
                    </div>
                </a>
                @endif
                @if(setting('general.phone'))
                <a href="tel:{{ setting('general.phone') }}" class="contact-card">
                    <div class="contact-icon"><i class="bi bi-telephone-fill"></i></div>
                    <div>
                        <div class="contact-label">Phone</div>
                        <div class="contact-value">{{ setting('general.phone') }}</div>
                    </div>
                </a>
                @endif
                @if(setting('general.address'))
                <div class="contact-card">
                    <div class="contact-icon"><i class="bi bi-geo-alt-fill"></i></div>
                    <div>
                        <div class="contact-label">Address</div>
                        <div class="contact-value">{{ setting('general.address') }}</div>
                    </div>
                </div>
                @endif
                @if(setting('general.website'))
                <a href="{{ setting('general.website') }}" target="_blank" class="contact-card">
                    <div class="contact-icon"><i class="bi bi-globe2"></i></div>
                    <div>
                        <div class="contact-label">Website</div>
                        <div class="contact-value">{{ setting('general.website') }}</div>
                    </div>
                </a>
                @endif
            </div>
        </section>
    </main>

    <!-- footer -->
    <footer class="welcome-footer">
        <div class="footer-inner">
            <div class="footer-brand">
                <img src="{{ app_logo() }}" alt="{{ setting('company.name', app_name()) }}">
                <span>{{ setting('company.name', app_name()) }}</span>
            </div>
            <div class="footer-copy">&copy; {{ date('Y') }} {{ setting('company.name', app_name()) }}. All rights reserved.</div>
        </div>
    </footer>

    <script>
        // mouse-follow glow
        (function () {
            var glow = document.getElementById('mouseGlow');
            if (!glow) return;
            window.addEventListener('mousemove', function (e) {
                glow.style.left = e.clientX + 'px';
                glow.style.top = e.clientY + 'px';
            });
        })();

        // scroll-reveal animations
        (function () {
            var targets = document.querySelectorAll('.feature-card, .chip, .contact-card');
            if (!('IntersectionObserver' in window)) {
                targets.forEach(function (el) { el.classList.add('reveal'); });
                return;
            }
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry, i) {
                    if (entry.isIntersecting) {
                        setTimeout(function () {
                            entry.target.classList.add('reveal');
                        }, i * 60);
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
            targets.forEach(function (el) { observer.observe(el); });
        })();

        // animated stat counters
        (function () {
            var stats = document.querySelectorAll('.stat-value[data-count]');
            if (!('IntersectionObserver' in window)) {
                stats.forEach(function (el) { el.textContent = el.dataset.count + '+'; });
                return;
            }
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    var el = entry.target;
                    var target = parseInt(el.dataset.count, 10);
                    var duration = 1600;
                    var start = performance.now();
                    function tick(now) {
                        var progress = Math.min((now - start) / duration, 1);
                        var eased = 1 - Math.pow(1 - progress, 3);
                        el.textContent = Math.floor(eased * target) + '+';
                        if (progress < 1) requestAnimationFrame(tick);
                    }
                    requestAnimationFrame(tick);
                    observer.unobserve(el);
                });
            }, { threshold: 0.5 });
            stats.forEach(function (el) { observer.observe(el); });
        })();
    </script>
</body>
</html>
