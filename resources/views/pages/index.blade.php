<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="description" content="Starcho CRM – Starter kit para Laravel 13 con Livewire 4, PowerGrid y CRUD automático. Desarrollo rápido de aplicaciones CRM y SaaS.">
  <meta name="keywords" content="Laravel 13, CRM Starter Kit, live4crud-tailwind, PowerGrid, Livewire, Rapid Development">
  <meta name="author" content="Starcho Labs">
  <meta property="og:title" content="Starcho CRM – Laravel 13 Rapid Starter Kit">
  <meta property="og:description" content="Construye CRUDs completos en segundos con live4crud-tailwind.">
  <meta property="og:type" content="website">
  <title>Starcho CRM | Laravel 13 + live4crud-tailwind</title>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    :root{
      --bg:#050505;--bg2:#0c0c0f;--card:#111116;--card2:#18181f;
      --text:#f5f5f7;--text2:#a1a1aa;--text3:#71717a;
      --border:#27272a;--border2:#3f3f46;
      --neon:#fe2c55;--neon2:#25f4ee;--neon3:#7c3aed;--neon4:#facc15;
      --glow1:rgba(254,44,85,.35);--glow2:rgba(37,244,238,.3);--glow3:rgba(124,58,237,.3);
      --radius:1.2rem;--radius-lg:2rem;
      --font:'Outfit',sans-serif;--mono:'Space Mono',monospace;
    }
    .light{
      --bg:#fafafa;--bg2:#ffffff;--card:#ffffff;--card2:#f4f4f5;
      --text:#18181b;--text2:#52525b;--text3:#71717a;
      --border:#e4e4e7;--border2:#d4d4d8;
      --glow1:rgba(254,44,85,.15);--glow2:rgba(37,244,238,.12);--glow3:rgba(124,58,237,.12);
    }
    html{scroll-behavior:smooth;overflow-x:hidden}
    body{font-family:var(--font);background:var(--bg);color:var(--text);line-height:1.6;transition:background .4s,color .3s}
    ::selection{background:var(--neon);color:#fff}
    ::-webkit-scrollbar{width:6px}
    ::-webkit-scrollbar-track{background:var(--bg)}
    ::-webkit-scrollbar-thumb{background:var(--neon);border-radius:3px}
    .container{max-width:1320px;margin:0 auto;padding:0 clamp(1rem,4vw,3rem)}
    .gradient-text{background:linear-gradient(135deg,var(--neon),var(--neon2),var(--neon3));-webkit-background-clip:text;background-clip:text;color:transparent}
    .glow-border{border:1px solid var(--border);position:relative}
    .glow-border::after{content:'';position:absolute;inset:-1px;border-radius:inherit;background:linear-gradient(135deg,var(--glow1),transparent 40%,var(--glow2));z-index:-1;opacity:0;transition:opacity .4s}
    .glow-border:hover::after{opacity:1}
    .tag{display:inline-flex;align-items:center;gap:.4rem;background:var(--card2);border:1px solid var(--border);padding:.35rem .9rem;border-radius:2rem;font-size:.78rem;font-weight:600;letter-spacing:.03em}
    .tag i{font-size:.65rem}
    .section-label{font-size:.75rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--neon);margin-bottom:.8rem}
    .btn{display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 1.6rem;border-radius:2.5rem;font-weight:700;font-size:.95rem;text-decoration:none;border:none;cursor:pointer;transition:all .25s;font-family:var(--font)}
    .btn-neon{background:var(--neon);color:#fff;box-shadow:0 0 20px var(--glow1)}
    .btn-neon:hover{transform:translateY(-2px) scale(1.03);box-shadow:0 0 35px var(--glow1)}
    .btn-ghost{background:transparent;border:1.5px solid var(--border2);color:var(--text)}
    .btn-ghost:hover{border-color:var(--neon2);color:var(--neon2);box-shadow:0 0 20px var(--glow2)}
    .btn-sm{padding:.5rem 1.1rem;font-size:.82rem;border-radius:2rem}
    .btn-cyan{background:var(--neon2);color:#000;font-weight:800}
    .btn-cyan:hover{box-shadow:0 0 30px var(--glow2);transform:translateY(-2px)}
    .nav{position:sticky;top:0;z-index:100;backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);background:color-mix(in srgb,var(--bg) 80%,transparent);border-bottom:1px solid var(--border)}
    .nav-inner{display:flex;align-items:center;justify-content:space-between;padding:.9rem 0;gap:1rem}
    .logo{display:flex;align-items:center;gap:.6rem;font-size:1.5rem;font-weight:900;cursor:pointer;letter-spacing:-.03em}
    .logo-icon{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,var(--neon),var(--neon3));display:flex;align-items:center;justify-content:center;color:#fff;font-size:1rem;box-shadow:0 0 15px var(--glow1)}
    .nav-center{display:flex;gap:.2rem;background:var(--card);border:1px solid var(--border);border-radius:2rem;padding:.25rem}
    .nav-center a{padding:.45rem 1rem;border-radius:1.8rem;font-size:.82rem;font-weight:600;color:var(--text2);text-decoration:none;transition:all .2s;cursor:pointer}
    .nav-center a:hover,.nav-center a.active{background:var(--card2);color:var(--text)}
    .nav-right{display:flex;align-items:center;gap:.6rem}
    .theme-btn{width:38px;height:38px;border-radius:50%;border:1px solid var(--border);background:var(--card);color:var(--text);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .3s;font-size:.9rem}
    .theme-btn:hover{border-color:var(--neon2);color:var(--neon2)}
    .lang-btn{background:var(--card);border:1px solid var(--border);border-radius:2rem;padding:.4rem .8rem;color:var(--text);font-size:.78rem;font-weight:600;cursor:pointer;font-family:var(--font)}
    .mobile-toggle{display:none;width:38px;height:38px;border-radius:10px;border:1px solid var(--border);background:var(--card);color:var(--text);cursor:pointer;font-size:1.1rem}
    @media(max-width:860px){
      .nav-center,.nav-right .btn{display:none}
      .mobile-toggle{display:flex;align-items:center;justify-content:center}
      .mobile-menu{display:flex;flex-direction:column;gap:.5rem;padding:1rem;background:var(--card);border:1px solid var(--border);border-radius:var(--radius);margin-top:.5rem}
      .mobile-menu a{padding:.7rem 1rem;border-radius:var(--radius);font-weight:600;color:var(--text2);text-decoration:none}
      .mobile-menu a:hover{background:var(--card2);color:var(--text)}
    }
    .hero{padding:5rem 0 4rem;position:relative;overflow:hidden}
    .hero-grid{display:grid;grid-template-columns:1fr 1fr;gap:3rem;align-items:center}
    .hero-left{position:relative;z-index:2}
    .hero-left h1{font-size:clamp(2.5rem,5.5vw,4.2rem);font-weight:900;line-height:1.08;letter-spacing:-.04em;margin-bottom:1.2rem}
    .hero-left h1 .line2{display:block;background:linear-gradient(90deg,var(--neon),var(--neon2));-webkit-background-clip:text;background-clip:text;color:transparent}
    .hero-left p{font-size:1.15rem;color:var(--text2);margin-bottom:2rem;max-width:520px}
    .hero-btns{display:flex;gap:.8rem;flex-wrap:wrap;margin-bottom:2rem}
    .hero-stats{display:flex;gap:2rem;flex-wrap:wrap}
    .hero-stat{text-align:center}
    .hero-stat .num{font-size:2rem;font-weight:900;background:linear-gradient(135deg,var(--neon),var(--neon2));-webkit-background-clip:text;background-clip:text;color:transparent}
    .hero-stat .label{font-size:.72rem;color:var(--text3);font-weight:600;text-transform:uppercase;letter-spacing:.08em}
    .hero-right{position:relative}
    .hero-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.5rem;box-shadow:0 25px 60px rgba(0,0,0,.4);position:relative;overflow:hidden}
    .hero-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--neon),var(--neon2),var(--neon3))}
    .hero-topbar{display:flex;align-items:center;gap:.5rem;margin-bottom:1.2rem;padding-bottom:.8rem;border-bottom:1px solid var(--border)}
    .hero-topbar .dots{display:flex;gap:6px}
    .hero-topbar .dots span{width:10px;height:10px;border-radius:50%}
    .hero-topbar .dots span:nth-child(1){background:#ff5f57}
    .hero-topbar .dots span:nth-child(2){background:#ffbd2e}
    .hero-topbar .dots span:nth-child(3){background:#28c840}
    .hero-topbar .url{margin-left:auto;font-size:.7rem;color:var(--text3);font-family:var(--mono)}
    .mini-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:.8rem;margin-bottom:1rem}
    .mini-stat{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:.8rem;text-align:center}
    .mini-stat i{font-size:1.2rem;margin-bottom:.3rem}
    .mini-stat .val{font-size:1.3rem;font-weight:800;display:block}
    .mini-stat .lbl{font-size:.65rem;color:var(--text3);text-transform:uppercase;letter-spacing:.06em;font-weight:600}
    .mini-chart{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:1rem}
    .chart-bar-row{display:flex;align-items:end;gap:6px;height:80px;margin-top:.5rem}
    .chart-bar{flex:1;border-radius:4px 4px 0 0;transition:height .6s ease}
    .activity-line{display:flex;align-items:center;gap:.6rem;padding:.6rem .8rem;background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);margin-top:.8rem;font-size:.82rem}
    .activity-line .avatar{width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,var(--neon),var(--neon3));display:flex;align-items:center;justify-content:center;color:#fff;font-size:.65rem;font-weight:700;flex-shrink:0}
    .blob{position:absolute;border-radius:50%;filter:blur(120px);pointer-events:none;z-index:0}
    .blob-1{width:500px;height:500px;background:var(--glow1);top:-10%;right:-5%}
    .blob-2{width:400px;height:400px;background:var(--glow2);bottom:0;left:-10%}
    .blob-3{width:300px;height:300px;background:var(--glow3);top:40%;left:30%}
    .marquee-wrap{padding:2.5rem 0;border-top:1px solid var(--border);border-bottom:1px solid var(--border);overflow:hidden;position:relative}
    .marquee{display:flex;gap:3rem;animation:marquee 25s linear infinite;width:max-content}
    .marquee span{font-size:1rem;font-weight:700;color:var(--text3);white-space:nowrap;display:flex;align-items:center;gap:.5rem}
    .marquee span i{color:var(--neon);font-size:.6rem}
    @keyframes marquee{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}
    .features{padding:5rem 0}
    .features-header{text-align:center;margin-bottom:3.5rem}
    .features-header h2{font-size:clamp(2rem,4vw,3rem);font-weight:900;letter-spacing:-.03em;margin-bottom:.8rem}
    .features-header p{color:var(--text2);max-width:550px;margin:0 auto}
    .feat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem}
    .feat-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:2rem;position:relative;overflow:hidden;transition:all .35s}
    .feat-card:hover{transform:translateY(-6px);border-color:var(--neon)}
    .feat-card:hover .feat-num{color:var(--neon)}
    .feat-num{position:absolute;top:1rem;right:1.2rem;font-size:3.5rem;font-weight:900;color:var(--border);transition:color .35s;line-height:1}
    .feat-icon{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;margin-bottom:1.2rem;color:#fff}
    .feat-card h3{font-size:1.2rem;font-weight:800;margin-bottom:.5rem}
    .feat-card p{font-size:.9rem;color:var(--text2);line-height:1.6}
    .feat-card:nth-child(1) .feat-icon{background:linear-gradient(135deg,var(--neon),#ff6b8a)}
    .feat-card:nth-child(2) .feat-icon{background:linear-gradient(135deg,var(--neon2),#06d6a0)}
    .feat-card:nth-child(3) .feat-icon{background:linear-gradient(135deg,var(--neon3),#a78bfa)}
    .feat-card:nth-child(4) .feat-icon{background:linear-gradient(135deg,var(--neon4),#f59e0b)}
    .feat-card:nth-child(5) .feat-icon{background:linear-gradient(135deg,#ec4899,var(--neon))}
    .feat-card:nth-child(6) .feat-icon{background:linear-gradient(135deg,#06b6d4,var(--neon2))}
    .crud-section{padding:5rem 0}
    .crud-grid{display:grid;grid-template-columns:1fr 1fr;gap:3rem;align-items:center}
    .crud-left h2{font-size:clamp(1.8rem,3.5vw,2.8rem);font-weight:900;letter-spacing:-.03em;margin-bottom:1rem}
    .crud-left p{color:var(--text2);margin-bottom:1.5rem;font-size:1.05rem}
    .crud-steps{display:flex;flex-direction:column;gap:1rem;margin-bottom:1.5rem}
    .crud-step{display:flex;align-items:center;gap:1rem;background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:1rem 1.2rem;transition:all .3s}
    .crud-step:hover{border-color:var(--neon2);transform:translateX(6px)}
    .crud-step .step-num{width:32px;height:32px;border-radius:50%;background:var(--neon);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.82rem;flex-shrink:0}
    .crud-step code{font-family:var(--mono);font-size:.82rem;color:var(--neon2)}
    .crud-right{position:relative}
    .terminal{background:#0d1117;border:1px solid #21262d;border-radius:var(--radius-lg);overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.5)}
    .terminal-bar{display:flex;align-items:center;gap:6px;padding:.8rem 1rem;background:#161b22;border-bottom:1px solid #21262d}
    .terminal-bar span{width:10px;height:10px;border-radius:50%}
    .terminal-bar span:nth-child(1){background:#ff5f57}
    .terminal-bar span:nth-child(2){background:#ffbd2e}
    .terminal-bar span:nth-child(3){background:#28c840}
    .terminal-bar .title{margin-left:auto;font-size:.7rem;color:#8b949e;font-family:var(--mono)}
    .terminal-body{padding:1.2rem;font-family:var(--mono);font-size:.78rem;line-height:2;color:#c9d1d9}
    .terminal-body .prompt{color:var(--neon2)}
    .terminal-body .cmd{color:#f0f6fc}
    .terminal-body .output{color:#8b949e}
    .terminal-body .success{color:#3fb950}
    .terminal-body .file{color:#d2a8ff}
    .terminal-body .blink{animation:blink 1s infinite}
    @keyframes blink{0%,50%{opacity:1}51%,100%{opacity:0}}
    .included{padding:4rem 0}
    .included-header{text-align:center;margin-bottom:3rem}
    .included-header h2{font-size:clamp(1.8rem,3.5vw,2.5rem);font-weight:900;letter-spacing:-.03em;margin-bottom:.6rem}
    .included-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem}
    .inc-item{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:1.3rem;text-align:center;transition:all .3s}
    .inc-item:hover{border-color:var(--neon);transform:translateY(-4px)}
    .inc-item i{font-size:1.8rem;margin-bottom:.6rem;display:block}
    .inc-item h4{font-size:.92rem;font-weight:700;margin-bottom:.3rem}
    .inc-item p{font-size:.75rem;color:var(--text3)}
    .demo{padding:5rem 0}
    .demo-header{text-align:center;margin-bottom:3rem}
    .demo-header h2{font-size:clamp(1.8rem,3.5vw,2.8rem);font-weight:900;letter-spacing:-.03em;margin-bottom:.6rem}
    .demo-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;box-shadow:0 30px 80px rgba(0,0,0,.3)}
    .demo-topbar{display:flex;align-items:center;gap:8px;padding:.8rem 1.2rem;border-bottom:1px solid var(--border);background:var(--bg2)}
    .demo-topbar .dots{display:flex;gap:6px}
    .demo-topbar .dots span{width:10px;height:10px;border-radius:50%}
    .demo-topbar .dots span:nth-child(1){background:#ff5f57}
    .demo-topbar .dots span:nth-child(2){background:#ffbd2e}
    .demo-topbar .dots span:nth-child(3){background:#28c840}
    .demo-topbar .url-bar{margin-left:1rem;flex:1;background:var(--card);border:1px solid var(--border);border-radius:1rem;padding:.3rem .8rem;font-size:.72rem;color:var(--text3);font-family:var(--mono)}
    .demo-layout{display:grid;grid-template-columns:220px 1fr;min-height:420px}
    .demo-sidebar{background:var(--bg2);border-right:1px solid var(--border);padding:1.2rem}
    .demo-sidebar .sidebar-logo{font-weight:800;font-size:1rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:.5rem}
    .demo-sidebar .sidebar-logo i{color:var(--neon)}
    .sidebar-item{display:flex;align-items:center;gap:.6rem;padding:.6rem .8rem;border-radius:.8rem;font-size:.82rem;font-weight:500;color:var(--text2);cursor:pointer;transition:all .2s;margin-bottom:.2rem}
    .sidebar-item:hover,.sidebar-item.active{background:var(--card);color:var(--text)}
    .sidebar-item.active{border-left:3px solid var(--neon);color:var(--neon)}
    .sidebar-item .badge{margin-left:auto;background:var(--neon);color:#fff;font-size:.6rem;padding:.15rem .45rem;border-radius:1rem;font-weight:700}
    .demo-main{padding:1.5rem}
    .demo-main h3{font-size:1.1rem;font-weight:800;margin-bottom:1rem}
    .demo-stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:.8rem;margin-bottom:1.2rem}
    .demo-stat{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);padding:.8rem;text-align:center}
    .demo-stat .icon{font-size:1.1rem;margin-bottom:.3rem}
    .demo-stat .val{font-size:1.4rem;font-weight:900}
    .demo-stat .lbl{font-size:.65rem;color:var(--text3);text-transform:uppercase;font-weight:600}
    .demo-table{width:100%;border-collapse:collapse;font-size:.82rem}
    .demo-table th{text-align:left;padding:.6rem .8rem;border-bottom:1px solid var(--border);color:var(--text3);font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.06em}
    .demo-table td{padding:.6rem .8rem;border-bottom:1px solid var(--border)}
    .status{padding:.2rem .6rem;border-radius:1rem;font-size:.68rem;font-weight:700}
    .status.won{background:rgba(16,185,129,.15);color:#10b981}
    .status.pending{background:rgba(250,204,21,.15);color:#facc15}
    .status.lost{background:rgba(239,68,68,.15);color:#ef4444}
    .status.new{background:rgba(37,244,238,.15);color:var(--neon2)}
    .pricing{padding:5rem 0}
    .pricing-header{text-align:center;margin-bottom:3rem}
    .pricing-header h2{font-size:clamp(1.8rem,3.5vw,2.8rem);font-weight:900;letter-spacing:-.03em;margin-bottom:.6rem}
    .pricing-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem}
    .price-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:2.2rem;position:relative;transition:all .35s}
    .price-card:hover{transform:translateY(-6px)}
    .price-card.popular{border-color:var(--neon);box-shadow:0 0 40px var(--glow1)}
    .price-card.popular::before{content:'POPULAR';position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:var(--neon);color:#fff;padding:.25rem 1rem;border-radius:2rem;font-size:.68rem;font-weight:800;letter-spacing:.05em}
    .price-card h3{font-size:1.1rem;font-weight:800;margin-bottom:.3rem}
    .price-card .price{font-size:2.8rem;font-weight:900;margin:.8rem 0}
    .price-card .price span{font-size:.9rem;color:var(--text3);font-weight:500}
    .price-card .subtitle{color:var(--text3);font-size:.82rem;margin-bottom:1.2rem}
    .price-feat{display:flex;align-items:center;gap:.6rem;font-size:.85rem;margin-bottom:.6rem;color:var(--text2)}
    .price-feat i{color:var(--neon2);font-size:.7rem}
    .testimonials{padding:5rem 0}
    .testimonials-header{text-align:center;margin-bottom:3rem}
    .testimonials-header h2{font-size:clamp(1.8rem,3.5vw,2.8rem);font-weight:900;letter-spacing:-.03em;margin-bottom:.6rem}
    .test-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem}
    .test-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:1.8rem;transition:all .35s}
    .test-card:hover{transform:translateY(-4px);border-color:var(--neon3)}
    .test-stars{color:var(--neon4);margin-bottom:.8rem;font-size:.85rem}
    .test-card blockquote{font-size:.92rem;color:var(--text2);margin-bottom:1.2rem;font-style:italic;line-height:1.7}
    .test-author{display:flex;align-items:center;gap:.8rem}
    .test-avatar{width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:.85rem}
    .test-author .name{font-weight:700;font-size:.88rem}
    .test-author .role{font-size:.72rem;color:var(--text3)}
    .cta{padding:5rem 0}
    .cta-box{background:linear-gradient(135deg,var(--neon),var(--neon3));border-radius:var(--radius-lg);padding:4rem 3rem;text-align:center;position:relative;overflow:hidden}
    .cta-box::before{content:'';position:absolute;width:400px;height:400px;background:rgba(255,255,255,.08);border-radius:50%;top:-150px;right:-100px}
    .cta-box::after{content:'';position:absolute;width:300px;height:300px;background:rgba(0,0,0,.1);border-radius:50%;bottom:-120px;left:-80px}
    .cta-box h2{font-size:clamp(1.8rem,3.5vw,2.8rem);font-weight:900;color:#fff;margin-bottom:1rem;position:relative;z-index:2}
    .cta-box p{color:rgba(255,255,255,.85);font-size:1.05rem;max-width:500px;margin:0 auto 2rem;position:relative;z-index:2}
    .cta-box .btns{display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;position:relative;z-index:2}
    .btn-white{background:#fff;color:var(--neon);font-weight:800;padding:.8rem 2rem;border-radius:2.5rem;border:none;cursor:pointer;font-family:var(--font);font-size:.95rem;transition:all .25s;text-decoration:none;display:inline-flex;align-items:center;gap:.5rem}
    .btn-white:hover{transform:scale(1.05);box-shadow:0 10px 30px rgba(0,0,0,.2)}
    .btn-white-outline{background:transparent;border:2px solid rgba(255,255,255,.4);color:#fff;padding:.8rem 2rem;border-radius:2.5rem;cursor:pointer;font-family:var(--font);font-size:.95rem;font-weight:700;transition:all .25s;text-decoration:none;display:inline-flex;align-items:center;gap:.5rem}
    .btn-white-outline:hover{border-color:#fff;background:rgba(255,255,255,.1)}
    .footer{border-top:1px solid var(--border);padding:3rem 0 2rem}
    .footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:2rem;margin-bottom:2rem}
    .footer-brand .logo{margin-bottom:.8rem}
    .footer-brand p{color:var(--text3);font-size:.85rem;max-width:280px}
    .footer-col h4{font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--text3);margin-bottom:1rem}
    .footer-col a{display:block;color:var(--text2);text-decoration:none;font-size:.88rem;margin-bottom:.5rem;transition:color .2s}
    .footer-col a:hover{color:var(--neon)}
    .footer-bottom{display:flex;justify-content:space-between;align-items:center;padding-top:1.5rem;border-top:1px solid var(--border);color:var(--text3);font-size:.78rem}
    .footer-socials{display:flex;gap:1rem}
    .footer-socials a{color:var(--text3);text-decoration:none;font-size:1.1rem;transition:color .2s}
    .footer-socials a:hover{color:var(--neon)}
    @keyframes fadeUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
    .animate-in{animation:fadeUp .7s ease forwards}
    .delay-1{animation-delay:.1s}
    .delay-2{animation-delay:.2s}
    .delay-3{animation-delay:.3s}
    .delay-4{animation-delay:.4s}
    @media(max-width:1024px){
      .hero-grid{grid-template-columns:1fr}
      .crud-grid{grid-template-columns:1fr}
      .feat-grid{grid-template-columns:repeat(2,1fr)}
      .pricing-grid{grid-template-columns:1fr}
      .test-grid{grid-template-columns:1fr}
      .included-grid{grid-template-columns:repeat(2,1fr)}
      .demo-layout{grid-template-columns:1fr}
      .demo-sidebar{display:none}
      .footer-grid{grid-template-columns:1fr 1fr}
      .demo-stats-row{grid-template-columns:repeat(2,1fr)}
    }
    @media(max-width:600px){
      .feat-grid{grid-template-columns:1fr}
      .included-grid{grid-template-columns:1fr}
      .footer-grid{grid-template-columns:1fr}
      .hero-btns{flex-direction:column}
      .hero-stats{gap:1.5rem}
    }
  </style>
</head>
<body x-data="app()" x-init="init()" :class="isLight ? 'light' : ''">

<!-- ── NAV ── -->
<nav class="nav">
  <div class="container">
    <div class="nav-inner">
      <div class="logo" @click="scrollTo('home')">
        <div class="logo-icon"><i class="fas fa-bolt"></i></div>
        <span>Starcho</span>
      </div>
      <div class="nav-center">
        <a @click="scrollTo('features')" x-text="t('nav_features')"></a>
        <a @click="scrollTo('crud')" x-text="t('nav_crud')"></a>
        <a @click="scrollTo('demo')" x-text="t('nav_demo')"></a>
        <a @click="scrollTo('pricing')" x-text="t('nav_pricing')"></a>
      </div>
      <div class="nav-right">
        <select class="lang-btn" x-model="lang" @change="switchLang(lang)">
          <option value="es">ES</option>
          <option value="en">EN</option>
          <option value="pt_BR">PT</option>
        </select>
        <button class="theme-btn" @click="toggleTheme">
          <i :class="isLight ? 'fas fa-moon' : 'fas fa-sun'"></i>
        </button>
        @auth
          <a href="{{ route('app.dashboard') }}" class="btn btn-neon btn-sm"><i class="fas fa-bolt"></i> <span x-text="t('go_app')"></span></a>
        @else
          <a href="{{ route('login') }}" class="btn btn-ghost btn-sm" x-text="t('login')"></a>
          <a href="{{ route('register') }}" class="btn btn-neon btn-sm" x-text="t('register')"></a>
        @endauth
        <button class="mobile-toggle" @click="mobileOpen=!mobileOpen"><i class="fas fa-bars"></i></button>
      </div>
    </div>
    <div class="mobile-menu" x-show="mobileOpen" x-transition>
      <a @click="scrollTo('features');mobileOpen=false" x-text="t('nav_features')"></a>
      <a @click="scrollTo('crud');mobileOpen=false" x-text="t('nav_crud')"></a>
      <a @click="scrollTo('demo');mobileOpen=false" x-text="t('nav_demo')"></a>
      <a @click="scrollTo('pricing');mobileOpen=false" x-text="t('nav_pricing')"></a>
      @auth
        <a href="{{ route('app.dashboard') }}" class="btn btn-neon" style="text-align:center"><i class="fas fa-bolt"></i> <span x-text="t('go_app')"></span></a>
      @else
        <a href="{{ route('login') }}" x-text="t('login')"></a>
        <a href="{{ route('register') }}" class="btn btn-neon" style="text-align:center" x-text="t('register')"></a>
      @endauth
    </div>
  </div>
</nav>

<!-- ── HERO ── -->
<section id="home" class="hero">
  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>
  <div class="blob blob-3"></div>
  <div class="container">
    <div class="hero-grid">
      <div class="hero-left animate-in">
        <div class="tag" style="margin-bottom:1.5rem"><i class="fas fa-circle" style="color:var(--neon)"></i> <span x-text="t('hero_badge')"></span></div>
        <h1>
          <span x-text="t('hero_t1')"></span>
          <span class="line2" x-text="t('hero_t2')"></span>
        </h1>
        <p x-text="t('hero_desc')"></p>
        <div class="hero-btns">
          <a href="{{ route('register') }}" class="btn btn-neon"><i class="fas fa-rocket"></i> <span x-text="t('start')"></span></a>
          <a href="https://packagist.org/packages/galax13a/live4crud-tailwind" target="_blank" class="btn btn-ghost"><i class="fab fa-github"></i> live4crud-tailwind</a>
        </div>
        <div class="hero-stats">
          <div class="hero-stat"><div class="num">10k+</div><div class="label" x-text="t('hs_downloads')"></div></div>
          <div class="hero-stat"><div class="num">60%</div><div class="label" x-text="t('hs_faster')"></div></div>
          <div class="hero-stat"><div class="num">MIT</div><div class="label" x-text="t('hs_license')"></div></div>
        </div>
      </div>
      <div class="hero-right animate-in delay-2">
        <div class="hero-card">
          <div class="hero-topbar">
            <div class="dots"><span></span><span></span><span></span></div>
            <div class="url">starcho.test/app</div>
          </div>
          <div class="mini-stats">
            <div class="mini-stat"><i class="fas fa-users" style="color:var(--neon)"></i><span class="val">1,245</span><span class="lbl" x-text="t('ms_users')"></span></div>
            <div class="mini-stat"><i class="fas fa-chart-line" style="color:var(--neon2)"></i><span class="val">+32%</span><span class="lbl" x-text="t('ms_growth')"></span></div>
            <div class="mini-stat"><i class="fas fa-dollar-sign" style="color:var(--neon4)"></i><span class="val">$45k</span><span class="lbl" x-text="t('ms_revenue')"></span></div>
          </div>
          <div class="mini-chart">
            <div style="display:flex;justify-content:space-between;align-items:center"><span style="font-weight:700;font-size:.85rem" x-text="t('mc_weekly')"></span><span style="color:var(--neon2);font-size:.78rem;font-weight:600">+18%</span></div>
            <div class="chart-bar-row">
              <div class="chart-bar" style="height:40%;background:var(--neon)"></div>
              <div class="chart-bar" style="height:65%;background:var(--neon)"></div>
              <div class="chart-bar" style="height:45%;background:var(--neon)"></div>
              <div class="chart-bar" style="height:80%;background:linear-gradient(to top,var(--neon),var(--neon2))"></div>
              <div class="chart-bar" style="height:55%;background:var(--neon)"></div>
              <div class="chart-bar" style="height:90%;background:linear-gradient(to top,var(--neon),var(--neon2))"></div>
              <div class="chart-bar" style="height:70%;background:var(--neon)"></div>
            </div>
          </div>
          <div class="activity-line">
            <div class="avatar">OV</div>
            <div><strong>Olivia</strong> <span x-text="t('al_closed')" style="color:var(--text3)"></span> <strong style="color:var(--neon2)">$12,400</strong></div>
            <span style="margin-left:auto;font-size:.68rem;color:var(--text3)">2m</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── MARQUEE ── -->
<div class="marquee-wrap">
  <div class="marquee">
    <span><i class="fas fa-circle"></i> Laravel 13</span>
    <span><i class="fas fa-circle"></i> Livewire 4</span>
    <span><i class="fas fa-circle"></i> PowerGrid 6</span>
    <span><i class="fas fa-circle"></i> Tailwind CSS</span>
    <span><i class="fas fa-circle"></i> Alpine.js</span>
    <span><i class="fas fa-circle"></i> Sanctum API</span>
    <span><i class="fas fa-circle"></i> Blade Components</span>
    <span><i class="fas fa-circle"></i> Dark Mode</span>
    <span><i class="fas fa-circle"></i> Multi-lang</span>
    <span><i class="fas fa-circle"></i> CRUD Generator</span>
    <span><i class="fas fa-circle"></i> Laravel 13</span>
    <span><i class="fas fa-circle"></i> Livewire 4</span>
    <span><i class="fas fa-circle"></i> PowerGrid 6</span>
    <span><i class="fas fa-circle"></i> Tailwind CSS</span>
    <span><i class="fas fa-circle"></i> Alpine.js</span>
    <span><i class="fas fa-circle"></i> Sanctum API</span>
    <span><i class="fas fa-circle"></i> Blade Components</span>
    <span><i class="fas fa-circle"></i> Dark Mode</span>
    <span><i class="fas fa-circle"></i> Multi-lang</span>
    <span><i class="fas fa-circle"></i> CRUD Generator</span>
  </div>
</div>

<!-- ── FEATURES ── -->
<section id="features" class="features">
  <div class="container">
    <div class="features-header">
      <div class="section-label" x-text="t('feat_label')"></div>
      <h2 x-text="t('feat_title')"></h2>
      <p x-text="t('feat_sub')"></p>
    </div>
    <div class="feat-grid">
      <div class="feat-card glow-border">
        <div class="feat-num">01</div>
        <div class="feat-icon"><i class="fab fa-laravel"></i></div>
        <h3 x-text="t('f1_t')"></h3>
        <p x-text="t('f1_d')"></p>
      </div>
      <div class="feat-card glow-border">
        <div class="feat-num">02</div>
        <div class="feat-icon"><i class="fas fa-wand-magic-sparkles"></i></div>
        <h3 x-text="t('f2_t')"></h3>
        <p x-text="t('f2_d')"></p>
      </div>
      <div class="feat-card glow-border">
        <div class="feat-num">03</div>
        <div class="feat-icon"><i class="fas fa-table-cells"></i></div>
        <h3 x-text="t('f3_t')"></h3>
        <p x-text="t('f3_d')"></p>
      </div>
      <div class="feat-card glow-border">
        <div class="feat-num">04</div>
        <div class="feat-icon"><i class="fas fa-moon"></i></div>
        <h3 x-text="t('f4_t')"></h3>
        <p x-text="t('f4_d')"></p>
      </div>
      <div class="feat-card glow-border">
        <div class="feat-num">05</div>
        <div class="feat-icon"><i class="fas fa-plug"></i></div>
        <h3 x-text="t('f5_t')"></h3>
        <p x-text="t('f5_d')"></p>
      </div>
      <div class="feat-card glow-border">
        <div class="feat-num">06</div>
        <div class="feat-icon"><i class="fas fa-gauge-high"></i></div>
        <h3 x-text="t('f6_t')"></h3>
        <p x-text="t('f6_d')"></p>
      </div>
    </div>
  </div>
</section>

<!-- ── CRUD SECTION ── -->
<section id="crud" class="crud-section">
  <div class="container">
    <div class="crud-grid">
      <div class="crud-left">
        <div class="section-label">LIVE4CRUD-TAILWIND</div>
        <h2 x-text="t('crud_title')"></h2>
        <p x-text="t('crud_desc')"></p>
        <div class="crud-steps">
          <div class="crud-step">
            <div class="step-num">1</div>
            <div><span x-text="t('cs1_t')" style="font-weight:700;display:block;margin-bottom:2px"></span><code>composer require galax13a/live4crud-tailwind</code></div>
          </div>
          <div class="crud-step">
            <div class="step-num">2</div>
            <div><span x-text="t('cs2_t')" style="font-weight:700;display:block;margin-bottom:2px"></span><code>php artisan live4crud:install</code></div>
          </div>
          <div class="crud-step">
            <div class="step-num">3</div>
            <div><span x-text="t('cs3_t')" style="font-weight:700;display:block;margin-bottom:2px"></span><code>php artisan live4crud:generate products</code></div>
          </div>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:.5rem">
          <span class="tag"><i class="fas fa-check" style="color:var(--neon2)"></i> Model</span>
          <span class="tag"><i class="fas fa-check" style="color:var(--neon2)"></i> Factory</span>
          <span class="tag"><i class="fas fa-check" style="color:var(--neon2)"></i> Livewire</span>
          <span class="tag"><i class="fas fa-check" style="color:var(--neon2)"></i> PowerGrid</span>
          <span class="tag"><i class="fas fa-check" style="color:var(--neon2)"></i> Blade</span>
          <span class="tag"><i class="fas fa-check" style="color:var(--neon2)"></i> Routes</span>
        </div>
      </div>
      <div class="crud-right">
        <div class="terminal">
          <div class="terminal-bar"><span></span><span></span><span></span><span class="title">~/starcho-crm</span></div>
          <div class="terminal-body">
            <div><span class="prompt">$</span> <span class="cmd">php artisan live4crud:generate products</span></div>
            <br>
            <div class="output">Scanning table: <span style="color:#79c0ff">products</span></div>
            <div class="output">Found 8 columns...</div>
            <br>
            <div class="success">✓ Created</div>
            <div>&nbsp;&nbsp;<span class="file">app/Models/Product.php</span></div>
            <div>&nbsp;&nbsp;<span class="file">app/Livewire/Products/ProductTable.php</span></div>
            <div>&nbsp;&nbsp;<span class="file">app/Livewire/Products/ProductForm.php</span></div>
            <div>&nbsp;&nbsp;<span class="file">resources/views/livewire/products/index.blade.php</span></div>
            <div>&nbsp;&nbsp;<span class="file">resources/views/livewire/products/form.blade.php</span></div>
            <div>&nbsp;&nbsp;<span class="file">database/factories/ProductFactory.php</span></div>
            <div>&nbsp;&nbsp;<span class="file">routes/web.php</span> <span class="output">(appended)</span></div>
            <br>
            <div class="success">✓ CRUD for "products" generated successfully!</div>
            <div class="output"><span class="prompt">$</span> <span class="blink">_</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── WHAT'S INCLUDED ── -->
<section class="included">
  <div class="container">
    <div class="included-header">
      <div class="section-label" x-text="t('inc_label')"></div>
      <h2 x-text="t('inc_title')"></h2>
    </div>
    <div class="included-grid">
      <div class="inc-item glow-border"><i class="fas fa-shield-halved" style="color:var(--neon)"></i><h4 x-text="t('i1_t')"></h4><p x-text="t('i1_d')"></p></div>
      <div class="inc-item glow-border"><i class="fas fa-user-gear" style="color:var(--neon2)"></i><h4 x-text="t('i2_t')"></h4><p x-text="t('i2_d')"></p></div>
      <div class="inc-item glow-border"><i class="fas fa-language" style="color:var(--neon3)"></i><h4 x-text="t('i3_t')"></h4><p x-text="t('i3_d')"></p></div>
      <div class="inc-item glow-border"><i class="fas fa-bell" style="color:var(--neon4)"></i><h4 x-text="t('i4_t')"></h4><p x-text="t('i4_d')"></p></div>
      <div class="inc-item glow-border"><i class="fas fa-file-export" style="color:#ec4899"></i><h4 x-text="t('i5_t')"></h4><p x-text="t('i5_d')"></p></div>
      <div class="inc-item glow-border"><i class="fas fa-database" style="color:#06b6d4"></i><h4 x-text="t('i6_t')"></h4><p x-text="t('i6_d')"></p></div>
      <div class="inc-item glow-border"><i class="fas fa-vial" style="color:#f97316"></i><h4 x-text="t('i7_t')"></h4><p x-text="t('i7_d')"></p></div>
      <div class="inc-item glow-border"><i class="fas fa-terminal" style="color:#a78bfa"></i><h4 x-text="t('i8_t')"></h4><p x-text="t('i8_d')"></p></div>
    </div>
  </div>
</section>

<!-- ── DEMO ── -->
<section id="demo" class="demo">
  <div class="container">
    <div class="demo-header">
      <div class="section-label" x-text="t('demo_label')"></div>
      <h2 x-text="t('demo_title')"></h2>
    </div>
    <div class="demo-card">
      <div class="demo-topbar">
        <div class="dots"><span></span><span></span><span></span></div>
        <div class="url-bar"><i class="fas fa-lock" style="font-size:.6rem;margin-right:.3rem"></i> app.starcho.test/app</div>
      </div>
      <div class="demo-layout">
        <div class="demo-sidebar">
          <div class="sidebar-logo"><i class="fas fa-bolt"></i> Starcho</div>
          <div class="sidebar-item active"><i class="fas fa-chart-pie"></i> Dashboard</div>
          <div class="sidebar-item"><i class="fas fa-users"></i> <span x-text="t('dm_contacts')"></span> <span class="badge">128</span></div>
          <div class="sidebar-item"><i class="fas fa-handshake"></i> <span x-text="t('dm_deals')"></span></div>
          <div class="sidebar-item"><i class="fas fa-box"></i> <span x-text="t('dm_products')"></span></div>
          <div class="sidebar-item"><i class="fas fa-chart-line"></i> <span x-text="t('dm_analytics')"></span></div>
          <div class="sidebar-item"><i class="fas fa-envelope"></i> <span x-text="t('dm_email')"></span></div>
          <hr style="border-color:var(--border);margin:.8rem 0">
          <div class="sidebar-item"><i class="fas fa-cog"></i> <span x-text="t('dm_settings')"></span></div>
        </div>
        <div class="demo-main">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem">
            <h3><i class="fas fa-chart-pie"></i> Dashboard</h3>
            <div style="display:flex;gap:.5rem">
              <a href="{{ route('register') }}" class="btn btn-neon btn-sm">+ <span x-text="t('dm_new_deal')"></span></a>
              <span class="btn btn-ghost btn-sm"><i class="fas fa-download"></i> <span x-text="t('dm_export')"></span></span>
            </div>
          </div>
          <div class="demo-stats-row">
            <div class="demo-stat"><div class="icon" style="color:var(--neon)"><i class="fas fa-dollar-sign"></i></div><div class="val">$142k</div><div class="lbl" x-text="t('ds_revenue')"></div></div>
            <div class="demo-stat"><div class="icon" style="color:var(--neon2)"><i class="fas fa-handshake"></i></div><div class="val">89</div><div class="lbl" x-text="t('ds_deals')"></div></div>
            <div class="demo-stat"><div class="icon" style="color:var(--neon4)"><i class="fas fa-user-plus"></i></div><div class="val">+34</div><div class="lbl" x-text="t('ds_leads')"></div></div>
            <div class="demo-stat"><div class="icon" style="color:var(--neon3)"><i class="fas fa-percent"></i></div><div class="val">67%</div><div class="lbl" x-text="t('ds_rate')"></div></div>
          </div>
          <table class="demo-table">
            <thead><tr><th x-text="t('dt_contact')"></th><th x-text="t('dt_deal')"></th><th x-text="t('dt_value')"></th><th>Status</th></tr></thead>
            <tbody>
              <tr><td><strong>Olivia Martins</strong></td><td>Enterprise Plan</td><td>$12,400</td><td><span class="status won" x-text="t('st_won')"></span></td></tr>
              <tr><td><strong>Carlos Vega</strong></td><td>API Integration</td><td>$8,500</td><td><span class="status pending" x-text="t('st_pending')"></span></td></tr>
              <tr><td><strong>Nina Kim</strong></td><td>SaaS Migration</td><td>$22,000</td><td><span class="status new">New</span></td></tr>
              <tr><td><strong>Diego Rojas</strong></td><td>Custom Module</td><td>$5,200</td><td><span class="status lost" x-text="t('st_lost')"></span></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── PRICING ── -->
<section id="pricing" class="pricing">
  <div class="container">
    <div class="pricing-header">
      <div class="section-label" x-text="t('pr_label')"></div>
      <h2 x-text="t('pr_title')"></h2>
      <p style="color:var(--text2)" x-text="t('pr_sub')"></p>
    </div>
    <div class="pricing-grid">
      <div class="price-card glow-border">
        <h3>Starter</h3>
        <div class="subtitle" x-text="t('pr_starter_sub')"></div>
        <div class="price">$0 <span>/ forever</span></div>
        <a href="{{ route('register') }}" class="btn btn-ghost" style="width:100%;justify-content:center;margin-bottom:1.5rem" x-text="t('pr_get_started')"></a>
        <div class="price-feat"><i class="fas fa-check"></i> <span x-text="t('pf_crud')"></span></div>
        <div class="price-feat"><i class="fas fa-check"></i> <span x-text="t('pf_auth')"></span></div>
        <div class="price-feat"><i class="fas fa-check"></i> <span x-text="t('pf_dark')"></span></div>
        <div class="price-feat"><i class="fas fa-check"></i> <span x-text="t('pf_api')"></span></div>
        <div class="price-feat"><i class="fas fa-check"></i> MIT License</div>
      </div>
      <div class="price-card popular glow-border">
        <h3>Pro</h3>
        <div class="subtitle" x-text="t('pr_pro_sub')"></div>
        <div class="price">$49 <span>/ <span x-text="t('pr_once')"></span></span></div>
        <a href="{{ route('register') }}" class="btn btn-neon" style="width:100%;justify-content:center;margin-bottom:1.5rem" x-text="t('pr_get_pro')"></a>
        <div class="price-feat"><i class="fas fa-check"></i> <span x-text="t('pf_all_starter')"></span></div>
        <div class="price-feat"><i class="fas fa-check"></i> <span x-text="t('pf_roles')"></span></div>
        <div class="price-feat"><i class="fas fa-check"></i> <span x-text="t('pf_analytics')"></span></div>
        <div class="price-feat"><i class="fas fa-check"></i> <span x-text="t('pf_email')"></span></div>
        <div class="price-feat"><i class="fas fa-check"></i> <span x-text="t('pf_support')"></span></div>
      </div>
      <div class="price-card glow-border">
        <h3>Enterprise</h3>
        <div class="subtitle" x-text="t('pr_ent_sub')"></div>
        <div class="price" x-text="t('pr_custom')"></div>
        <a href="{{ route('register') }}" class="btn btn-ghost" style="width:100%;justify-content:center;margin-bottom:1.5rem" x-text="t('pr_contact')"></a>
        <div class="price-feat"><i class="fas fa-check"></i> <span x-text="t('pf_all_pro')"></span></div>
        <div class="price-feat"><i class="fas fa-check"></i> <span x-text="t('pf_custom_modules')"></span></div>
        <div class="price-feat"><i class="fas fa-check"></i> <span x-text="t('pf_sla')"></span></div>
        <div class="price-feat"><i class="fas fa-check"></i> <span x-text="t('pf_onboarding')"></span></div>
      </div>
    </div>
  </div>
</section>

<!-- ── TESTIMONIALS ── -->
<section class="testimonials">
  <div class="container">
    <div class="testimonials-header">
      <div class="section-label" x-text="t('te_label')"></div>
      <h2 x-text="t('te_title')"></h2>
    </div>
    <div class="test-grid">
      <div class="test-card glow-border">
        <div class="test-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
        <blockquote x-text="t('te1_text')"></blockquote>
        <div class="test-author">
          <div class="test-avatar" style="background:linear-gradient(135deg,var(--neon),#ff6b8a)">LM</div>
          <div><div class="name">Laura Mendoza</div><div class="role">CTO @ Saleflow</div></div>
        </div>
      </div>
      <div class="test-card glow-border">
        <div class="test-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
        <blockquote x-text="t('te2_text')"></blockquote>
        <div class="test-author">
          <div class="test-avatar" style="background:linear-gradient(135deg,var(--neon2),#06d6a0)">CR</div>
          <div><div class="name">Carlos Reyes</div><div class="role">Full-stack Developer</div></div>
        </div>
      </div>
      <div class="test-card glow-border">
        <div class="test-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div>
        <blockquote x-text="t('te3_text')"></blockquote>
        <div class="test-author">
          <div class="test-avatar" style="background:linear-gradient(135deg,var(--neon3),#a78bfa)">NK</div>
          <div><div class="name">Nina Kim</div><div class="role">Founder @ SwiftStack</div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── CTA ── -->
<section class="cta">
  <div class="container">
    <div class="cta-box">
      <h2 x-text="t('cta_title')"></h2>
      <p x-text="t('cta_desc')"></p>
      <div class="btns">
        <a href="{{ route('register') }}" class="btn-white"><i class="fas fa-rocket"></i> <span x-text="t('cta_btn1')"></span></a>
        <a href="https://packagist.org/packages/galax13a/live4crud-tailwind" target="_blank" class="btn-white-outline"><i class="fab fa-github"></i> GitHub</a>
      </div>
      <div style="margin-top:1.5rem;font-size:.78rem;color:rgba(255,255,255,.7)">
        <i class="fas fa-bolt"></i> <span x-text="t('cta_footer')"></span>
      </div>
    </div>
  </div>
</section>

<!-- ── FOOTER ── -->
<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <div class="logo"><div class="logo-icon"><i class="fas fa-bolt"></i></div><span>Starcho</span></div>
        <p x-text="t('footer_desc')"></p>
      </div>
      <div class="footer-col">
        <h4 x-text="t('ft_product')"></h4>
        <a @click="scrollTo('features')" x-text="t('nav_features')" style="cursor:pointer"></a>
        <a @click="scrollTo('crud')" x-text="t('nav_crud')" style="cursor:pointer"></a>
        <a @click="scrollTo('pricing')" x-text="t('nav_pricing')" style="cursor:pointer"></a>
        <a @click="scrollTo('demo')" style="cursor:pointer">Demo</a>
      </div>
      <div class="footer-col">
        <h4 x-text="t('ft_resources')"></h4>
        <a href="https://packagist.org/packages/galax13a/live4crud-tailwind" target="_blank">Packagist</a>
        <a href="#">Docs</a>
        <a href="#">Changelog</a>
        <a href="#">API Reference</a>
      </div>
      <div class="footer-col">
        <h4 x-text="t('ft_legal')"></h4>
        <a href="#">Privacy</a>
        <a href="#">Terms</a>
        <a href="#">MIT License</a>
      </div>
    </div>
    <div class="footer-bottom">
      <div>&copy; 2025 Starcho Labs. <span x-text="t('footer_rights')"></span></div>
      <div class="footer-socials">
        <a href="#"><i class="fab fa-github"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
        <a href="#"><i class="fab fa-discord"></i></a>
        <a href="#"><i class="fab fa-youtube"></i></a>
      </div>
    </div>
  </div>
</footer>

<script>
// ── Laravel routes available to Alpine ──
const _routes = {
  langSwitch: '{{ route("language.switch", ["locale" => "__LOCALE__"]) }}'
};

function app(){
  return {
    // Server-detected locale initializes the Alpine lang
    lang: '{{ in_array(app()->getLocale(), ["en", "pt_BR"]) ? app()->getLocale() : "es" }}',
    isLight: false,
    mobileOpen: false,

    translations:{
      es:{
        nav_features:'Funciones',nav_crud:'CRUD',nav_demo:'Demo',nav_pricing:'Precios',
        login:'Entrar',register:'Comenzar gratis',go_app:'Ir a la app',
        hero_badge:'Laravel 13 + Livewire 4 + PowerGrid',
        hero_t1:'Construye tu CRM ',hero_t2:'a velocidad TikTok.',
        hero_desc:'El starter kit definitivo para Laravel 13. CRUD automático, dashboard listo, API, modo oscuro y todo lo que necesitas para lanzar en horas.',
        start:'Empezar ahora',
        hs_downloads:'Descargas',hs_faster:'Más rápido',hs_license:'Licencia',
        ms_users:'Usuarios',ms_growth:'Crecimiento',ms_revenue:'Ingresos',
        mc_weekly:'Ventas semanales',al_closed:'cerró trato por',
        feat_label:'FUNCIONES',feat_title:'Todo para escalar tu negocio',feat_sub:'Desde autenticación hasta analytics, Starcho CRM te da superpoderes de desarrollo.',
        f1_t:'Laravel 13 Ready',f1_d:'Optimizado para Laravel 13 con Breeze, Sanctum y estructura MVC lista para producción.',
        f2_t:'CRUD en 1 Comando',f2_d:'live4crud-tailwind genera Model, Livewire, PowerGrid, Factory y vistas Blade automáticamente.',
        f3_t:'PowerGrid + Livewire 4',f3_d:'Tablas avanzadas con filtros, búsqueda, paginación y exportación a Excel/CSV.',
        f4_t:'Dark / Light Mode',f4_d:'Tema nativo con sincronización de preferencias del sistema. Perfecto para SaaS moderno.',
        f5_t:'API REST con Sanctum',f5_d:'API lista para apps móviles e integraciones externas con autenticación por tokens.',
        f6_t:'Rápido como TikTok',f6_d:'Prototipado ultra rápido. Auth, roles, permisos y TALL stack friendly.',
        crud_title:'CRUD completo en un comando',crud_desc:'live4crud-tailwind escanea tu base de datos y genera todo el scaffolding que necesitas. Compatible con Laravel 13, Livewire 4 y PowerGrid 6.',
        cs1_t:'Instalar paquete',cs2_t:'Configurar',cs3_t:'Generar CRUD',
        inc_label:'INCLUIDO',inc_title:'Todo en la caja',
        i1_t:'Auth completa',i1_d:'Login, registro, reset password, verificación email',
        i2_t:'Roles y permisos',i2_d:'Sistema de roles basado en Spatie con middleware',
        i3_t:'Multi-idioma',i3_d:'Soporte ES/EN listo con sistema de traducciones',
        i4_t:'Notificaciones',i4_d:'Sistema de notificaciones en tiempo real',
        i5_t:'Export Excel/CSV',i5_d:'PowerGrid con exportación integrada',
        i6_t:'Seeders y Factories',i6_d:'Datos de prueba listos para desarrollo',
        i7_t:'Tests incluidos',i7_d:'PHPUnit y Pest pre-configurados',
        i8_t:'CLI Generator',i8_d:'Artisan commands para scaffolding rápido',
        demo_label:'DEMO',demo_title:'Así se ve Starcho CRM',
        dm_contacts:'Contactos',dm_deals:'Tratos',dm_products:'Productos',dm_analytics:'Analíticas',dm_email:'Email',dm_settings:'Configuración',
        dm_new_deal:'Nuevo trato',dm_export:'Exportar',
        ds_revenue:'Ingresos',ds_deals:'Tratos activos',ds_leads:'Leads nuevos',ds_rate:'Conversión',
        dt_contact:'Contacto',dt_deal:'Trato',dt_value:'Valor',
        st_won:'Ganado',st_pending:'Pendiente',st_lost:'Perdido',
        pr_label:'PRECIOS',pr_title:'Simple y transparente',pr_sub:'Empieza gratis. Escala cuando lo necesites.',
        pr_starter_sub:'Para desarrolladores individuales',pr_pro_sub:'Para equipos y startups',pr_ent_sub:'Para empresas grandes',
        pr_get_started:'Empezar gratis',pr_get_pro:'Obtener Pro',pr_contact:'Contactar ventas',
        pr_once:'único',pr_custom:'A medida',
        pf_crud:'Generador CRUD completo',pf_auth:'Autenticación con Breeze',pf_dark:'Modo oscuro/claro',pf_api:'API REST básica',
        pf_all_starter:'Todo de Starter',pf_roles:'Roles y permisos avanzados',pf_analytics:'Dashboard de analíticas',pf_email:'Sistema de email integrado',pf_support:'Soporte prioritario',
        pf_all_pro:'Todo de Pro',pf_custom_modules:'Módulos personalizados',pf_sla:'SLA dedicado',pf_onboarding:'Onboarding personalizado',
        te_label:'TESTIMONIOS',te_title:'Lo que dicen los devs',
        te1_text:'Starcho CRM redujo nuestro tiempo de desarrollo un 60%. El generador CRUD es magia pura. Lo usamos en todos nuestros proyectos.',
        te2_text:'El modo oscuro y el sistema de diseño se sienten premium. El mejor starter kit de Laravel que he probado. Lo recomiendo totalmente.',
        te3_text:'SEO optimizado, código limpio y API lista en minutos. Perfecto para lanzar startups SaaS rápidamente.',
        cta_title:'Lanza tu próximo proyecto hoy',cta_desc:'Clona Starcho CRM y construye tu app Laravel 13 en horas, no semanas.',
        cta_btn1:'Comenzar gratis',cta_footer:'composer create-project · Starter gratuito · Licencia MIT',
        footer_desc:'El starter kit más rápido para Laravel 13. CRUD automático, dashboard y API listos.',
        ft_product:'Producto',ft_resources:'Recursos',ft_legal:'Legal',footer_rights:'Todos los derechos reservados.'
      },
      en:{
        nav_features:'Features',nav_crud:'CRUD',nav_demo:'Demo',nav_pricing:'Pricing',
        login:'Login',register:'Get started free',go_app:'Go to app',
        hero_badge:'Laravel 13 + Livewire 4 + PowerGrid',
        hero_t1:'Build your CRM ',hero_t2:'at TikTok speed.',
        hero_desc:'The ultimate starter kit for Laravel 13. Auto CRUD, ready dashboard, API, dark mode and everything you need to ship in hours.',
        start:'Start now',
        hs_downloads:'Downloads',hs_faster:'Faster',hs_license:'License',
        ms_users:'Users',ms_growth:'Growth',ms_revenue:'Revenue',
        mc_weekly:'Weekly sales',al_closed:'closed deal for',
        feat_label:'FEATURES',feat_title:'Everything to scale your business',feat_sub:'From auth to analytics, Starcho CRM gives you dev superpowers.',
        f1_t:'Laravel 13 Ready',f1_d:'Optimized for Laravel 13 with Breeze, Sanctum and production-ready MVC structure.',
        f2_t:'CRUD in 1 Command',f2_d:'live4crud-tailwind generates Model, Livewire, PowerGrid, Factory and Blade views automatically.',
        f3_t:'PowerGrid + Livewire 4',f3_d:'Advanced tables with filters, search, pagination and Excel/CSV export.',
        f4_t:'Dark / Light Mode',f4_d:'Native theme switcher with system preference sync. Perfect for modern SaaS.',
        f5_t:'REST API with Sanctum',f5_d:'API ready for mobile apps and external integrations with token authentication.',
        f6_t:'Fast like TikTok',f6_d:'Ultra-fast prototyping. Auth, roles, permissions and TALL stack friendly.',
        crud_title:'Full CRUD in one command',crud_desc:'live4crud-tailwind scans your database and generates all the scaffolding you need. Compatible with Laravel 13, Livewire 4 and PowerGrid 6.',
        cs1_t:'Install package',cs2_t:'Configure',cs3_t:'Generate CRUD',
        inc_label:'INCLUDED',inc_title:'Everything in the box',
        i1_t:'Full Auth',i1_d:'Login, register, password reset, email verification',
        i2_t:'Roles & Permissions',i2_d:'Spatie-based role system with middleware',
        i3_t:'Multi-language',i3_d:'ES/EN ready with translation system',
        i4_t:'Notifications',i4_d:'Real-time notification system',
        i5_t:'Export Excel/CSV',i5_d:'PowerGrid with built-in export',
        i6_t:'Seeders & Factories',i6_d:'Test data ready for development',
        i7_t:'Tests included',i7_d:'PHPUnit and Pest pre-configured',
        i8_t:'CLI Generator',i8_d:'Artisan commands for rapid scaffolding',
        demo_label:'DEMO',demo_title:'This is Starcho CRM',
        dm_contacts:'Contacts',dm_deals:'Deals',dm_products:'Products',dm_analytics:'Analytics',dm_email:'Email',dm_settings:'Settings',
        dm_new_deal:'New deal',dm_export:'Export',
        ds_revenue:'Revenue',ds_deals:'Active deals',ds_leads:'New leads',ds_rate:'Conversion',
        dt_contact:'Contact',dt_deal:'Deal',dt_value:'Value',
        st_won:'Won',st_pending:'Pending',st_lost:'Lost',
        pr_label:'PRICING',pr_title:'Simple and transparent',pr_sub:'Start free. Scale when you need to.',
        pr_starter_sub:'For individual developers',pr_pro_sub:'For teams and startups',pr_ent_sub:'For large companies',
        pr_get_started:'Get started free',pr_get_pro:'Get Pro',pr_contact:'Contact sales',
        pr_once:'one-time',pr_custom:'Custom',
        pf_crud:'Full CRUD generator',pf_auth:'Auth with Breeze',pf_dark:'Dark/light mode',pf_api:'Basic REST API',
        pf_all_starter:'Everything in Starter',pf_roles:'Advanced roles & permissions',pf_analytics:'Analytics dashboard',pf_email:'Built-in email system',pf_support:'Priority support',
        pf_all_pro:'Everything in Pro',pf_custom_modules:'Custom modules',pf_sla:'Dedicated SLA',pf_onboarding:'Custom onboarding',
        te_label:'TESTIMONIALS',te_title:'What devs say',
        te1_text:'Starcho CRM cut our development time by 60%. The CRUD generator is pure magic. We use it on every project.',
        te2_text:'Dark mode and design system feel premium. Best Laravel starter kit I have tried. Totally recommend it.',
        te3_text:'SEO optimized, clean code and API ready in minutes. Perfect for launching SaaS startups fast.',
        cta_title:'Launch your next project today',cta_desc:'Clone Starcho CRM and build your Laravel 13 app in hours, not weeks.',
        cta_btn1:'Get started free',cta_footer:'composer create-project · Free starter · MIT license',
        footer_desc:'The fastest starter kit for Laravel 13. Auto CRUD, dashboard and API ready.',
        ft_product:'Product',ft_resources:'Resources',ft_legal:'Legal',footer_rights:'All rights reserved.'
      },
      pt_BR:{
        nav_features:'Recursos',nav_crud:'CRUD',nav_demo:'Demo',nav_pricing:'Preços',
        login:'Entrar',register:'Começar grátis',go_app:'Ir para o app',
        hero_badge:'Laravel 13 + Livewire 4 + PowerGrid',
        hero_t1:'Construa seu CRM ',hero_t2:'na velocidade do TikTok.',
        hero_desc:'O starter kit definitivo para Laravel 13. CRUD automático, dashboard pronto, API, modo escuro e tudo que você precisa para lançar em horas.',
        start:'Começar agora',
        hs_downloads:'Downloads',hs_faster:'Mais rápido',hs_license:'Licença',
        ms_users:'Usuários',ms_growth:'Crescimento',ms_revenue:'Receita',
        mc_weekly:'Vendas semanais',al_closed:'fechou negócio por',
        feat_label:'RECURSOS',feat_title:'Tudo para escalar seu negócio',feat_sub:'De autenticação a analytics, o Starcho CRM te dá superpoderes de desenvolvimento.',
        f1_t:'Laravel 13 Pronto',f1_d:'Otimizado para Laravel 13 com Breeze, Sanctum e estrutura MVC pronta para produção.',
        f2_t:'CRUD em 1 Comando',f2_d:'live4crud-tailwind gera Model, Livewire, PowerGrid, Factory e views Blade automaticamente.',
        f3_t:'PowerGrid + Livewire 4',f3_d:'Tabelas avançadas com filtros, busca, paginação e exportação para Excel/CSV.',
        f4_t:'Dark / Light Mode',f4_d:'Tema nativo com sincronização de preferências do sistema. Perfeito para SaaS moderno.',
        f5_t:'API REST com Sanctum',f5_d:'API pronta para apps móveis e integrações externas com autenticação por tokens.',
        f6_t:'Rápido como o TikTok',f6_d:'Prototipagem ultra rápida. Auth, roles, permissões e TALL stack friendly.',
        crud_title:'CRUD completo em um comando',crud_desc:'live4crud-tailwind escaneia seu banco de dados e gera todo o scaffolding necessário. Compatível com Laravel 13, Livewire 4 e PowerGrid 6.',
        cs1_t:'Instalar pacote',cs2_t:'Configurar',cs3_t:'Gerar CRUD',
        inc_label:'INCLUÍDO',inc_title:'Tudo na caixa',
        i1_t:'Auth completa',i1_d:'Login, registro, reset de senha, verificação de email',
        i2_t:'Roles e permissões',i2_d:'Sistema de roles baseado no Spatie com middleware',
        i3_t:'Multi-idioma',i3_d:'Suporte ES/EN/PT pronto com sistema de traduções',
        i4_t:'Notificações',i4_d:'Sistema de notificações em tempo real',
        i5_t:'Export Excel/CSV',i5_d:'PowerGrid com exportação integrada',
        i6_t:'Seeders e Factories',i6_d:'Dados de teste prontos para desenvolvimento',
        i7_t:'Testes incluídos',i7_d:'PHPUnit e Pest pré-configurados',
        i8_t:'CLI Generator',i8_d:'Artisan commands para scaffolding rápido',
        demo_label:'DEMO',demo_title:'Assim é o Starcho CRM',
        dm_contacts:'Contatos',dm_deals:'Negócios',dm_products:'Produtos',dm_analytics:'Análises',dm_email:'Email',dm_settings:'Configurações',
        dm_new_deal:'Novo negócio',dm_export:'Exportar',
        ds_revenue:'Receita',ds_deals:'Negócios ativos',ds_leads:'Novos leads',ds_rate:'Conversão',
        dt_contact:'Contato',dt_deal:'Negócio',dt_value:'Valor',
        st_won:'Ganho',st_pending:'Pendente',st_lost:'Perdido',
        pr_label:'PREÇOS',pr_title:'Simples e transparente',pr_sub:'Comece grátis. Escale quando precisar.',
        pr_starter_sub:'Para desenvolvedores individuais',pr_pro_sub:'Para equipes e startups',pr_ent_sub:'Para grandes empresas',
        pr_get_started:'Começar grátis',pr_get_pro:'Obter Pro',pr_contact:'Falar com vendas',
        pr_once:'único',pr_custom:'Personalizado',
        pf_crud:'Gerador CRUD completo',pf_auth:'Autenticação com Breeze',pf_dark:'Modo escuro/claro',pf_api:'API REST básica',
        pf_all_starter:'Tudo do Starter',pf_roles:'Roles e permissões avançados',pf_analytics:'Dashboard de análises',pf_email:'Sistema de email integrado',pf_support:'Suporte prioritário',
        pf_all_pro:'Tudo do Pro',pf_custom_modules:'Módulos personalizados',pf_sla:'SLA dedicado',pf_onboarding:'Onboarding personalizado',
        te_label:'DEPOIMENTOS',te_title:'O que os devs dizem',
        te1_text:'O Starcho CRM reduziu nosso tempo de desenvolvimento em 60%. O gerador CRUD é pura magia. Usamos em todos os projetos.',
        te2_text:'O modo escuro e o sistema de design parecem premium. O melhor starter kit de Laravel que já testei. Recomendo totalmente.',
        te3_text:'SEO otimizado, código limpo e API pronta em minutos. Perfeito para lançar startups SaaS rapidamente.',
        cta_title:'Lance seu próximo projeto hoje',cta_desc:'Clone o Starcho CRM e construa seu app Laravel 13 em horas, não semanas.',
        cta_btn1:'Começar grátis',cta_footer:'composer create-project · Starter gratuito · Licença MIT',
        footer_desc:'O starter kit mais rápido para Laravel 13. CRUD automático, dashboard e API prontos.',
        ft_product:'Produto',ft_resources:'Recursos',ft_legal:'Legal',footer_rights:'Todos os direitos reservados.'
      }
    },

    t(k){ return this.translations[this.lang]?.[k] || k },

    // ── Language switcher: calls server route to persist locale in session ──
    switchLang(l){
      const url = _routes.langSwitch.replace('__LOCALE__', l);
      window.location.href = url;
    },

    init(){
      try{
        const st = localStorage.getItem('starcho_theme');
        this.isLight = st === 'light' || (st === null && !window.matchMedia('(prefers-color-scheme: dark)').matches);
      }catch(e){}
    },

    toggleTheme(){
      this.isLight = !this.isLight;
      try{ localStorage.setItem('starcho_theme', this.isLight ? 'light' : 'dark') }catch(e){}
    },

    scrollTo(id){
      document.getElementById(id)?.scrollIntoView({behavior:'smooth'});
    }
  }
}
</script>
</body>
</html>
