<?php
// FILE: pages/landing.php

// Protezione accesso diretto
if (!defined('ACCESSO_AUTORIZZATO')) {
    header("Location: ../index.php");
    exit;
}

global $db_conn; // Forza PHP a cercare la connessione definita in db.php

$query_faq = "SELECT * FROM faqs ORDER BY id ASC";
$res_faq = @pg_query($db_conn, $query_faq); 
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benvenuto - HelpDesk iFantastici4</title>
    <link rel="icon" type="image/png" href="icon/favicon.png">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- RESET & BASICS --- */
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        * { box-sizing: border-box; } 

        body {
            font-family: 'Inter', sans-serif;
            margin: 0; padding: 0;
            background-color: #f1f5f9;
            color: var(--text-main);
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* --- HEADER DINAMICO (BIG to SMALL) --- */
        .landing-nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
            display: flex; justify-content: space-between; align-items: center;
            
            /* STATO INIZIALE: GRANDE E ARIOSO */
            padding: 45px 6%; 
            background: transparent;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); /* Animazione fluida */
        }

        /* STATO SCROLLED: COMPATTO E SFOCATO */
        .landing-nav.scrolled {
            background: rgba(255, 255, 255, 0.9); 
            backdrop-filter: blur(12px);
            box-shadow: 0 4px 30px rgba(0,0,0,0.03); 
            padding: 15px 5%; /* Si restringe qui */
            border-bottom: 1px solid rgba(255,255,255,0.5);
        }

        .logo-area { 
            font-size: 1.6rem; /* Font grande */
            font-weight: 800; 
            color: var(--text-main); 
            display: flex; align-items: center; gap: 12px; 
            text-decoration: none; 
            transition: all 0.4s ease;
        }

        .logo-img {
            height: 55px; /* Logo grande */
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); /* Rimbalzo leggero */
        }

        /* MODIFICHE QUANDO SCROLLI (Shrink effect) */
        .landing-nav.scrolled .logo-area { font-size: 1.3rem; }
        .landing-nav.scrolled .logo-img { height: 38px; transform: rotate(-5deg); }


        .nav-links a { text-decoration: none; font-weight: 600; color: var(--text-main); margin-left: 25px; transition: color 0.2s; }
        .nav-links a:hover { color: var(--primary); }
        .btn-cta-nav { 
            background: var(--text-main); color: white !important; 
            padding: 12px 28px; border-radius: 99px; transition: all 0.2s !important; 
            font-weight: 700;
        }
        .landing-nav.scrolled .btn-cta-nav { padding: 8px 20px; } /* Bottone più piccolo allo scroll */
        .btn-cta-nav:hover { transform: scale(1.05); background: var(--primary); box-shadow: 0 5px 15px rgba(79, 70, 229, 0.3); }

        /* --- HERO SECTION --- */
        .hero-section { padding: 200px 20px 100px 20px; text-align: center; position: relative; overflow: hidden; }
        .blob { position: absolute; border-radius: 50%; filter: blur(90px); opacity: 0.5; z-index: -1; animation: float 10s infinite alternate; }
        .blob-1 { width: 400px; height: 400px; background: #818cf8; top: -100px; left: -100px; }
        .blob-2 { width: 500px; height: 500px; background: #34d399; bottom: -100px; right: -150px; animation-delay: -5s; }
        @keyframes float { 0% { transform: translate(0, 0); } 100% { transform: translate(40px, 60px); } }

        .hero-title { 
            font-size: 4.5rem; font-weight: 900; line-height: 1.1; margin-bottom: 25px; 
            letter-spacing: -2px; 
            background: linear-gradient(135deg, #1e293b 0%, #4f46e5 100%); 
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; 
        }
        .hero-desc { font-size: 1.25rem; color: var(--text-muted); margin-bottom: 45px; max-width: 600px; margin-left: auto; margin-right: auto; }
        
        .cta-btn-hero { 
            display: inline-flex; align-items: center; gap: 10px; 
            padding: 18px 45px; background: var(--primary); color: white; 
            font-size: 1.2rem; font-weight: 700; border-radius: 50px; text-decoration: none; 
            box-shadow: 0 15px 40px -10px rgba(79, 70, 229, 0.5); transition: all 0.3s; 
        }
        .cta-btn-hero:hover { transform: translateY(-3px); box-shadow: 0 25px 50px -10px rgba(79, 70, 229, 0.6); }

        /* --- FEATURES --- */
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px; max-width: 1200px; margin: 0 auto 120px auto; padding: 0 20px; }
        .feature-card { 
            background: rgba(255,255,255,0.8); backdrop-filter: blur(10px);
            padding: 40px; border-radius: 24px; border: 1px solid white;
            box-shadow: 0 10px 30px -5px rgba(0,0,0,0.03); 
            transition: 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); 
            opacity: 0; transform: translateY(30px); 
        }
        .feature-card.visible { opacity: 1; transform: translateY(0); }
        .feature-card:hover { transform: translateY(-10px) scale(1.02); box-shadow: 0 20px 40px -5px rgba(0,0,0,0.08); border-color: var(--primary); }
        .f-icon { 
            width: 60px; height: 60px; border-radius: 16px; background: #eef2ff; color: var(--primary); 
            font-size: 1.5rem; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; 
        }

        /* --- FAQ --- */
        .faq-section { max-width: 900px; margin: 0 auto 100px auto; padding: 0 20px; opacity: 0; transition: 1s; }
        .faq-section.visible { opacity: 1; }
        .faq-box { background: white; border-radius: 24px; box-shadow: 0 20px 60px -10px rgba(0,0,0,0.08); overflow: hidden; display: flex; flex-direction: column; height: 600px; border: 1px solid #e2e8f0; }
        .faq-box-header { padding: 40px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
        .faq-scroll-area { flex: 1; overflow-y: auto; }
        .faq-item { border-bottom: 1px solid #f1f5f9; background: white; }
        .faq-question { width: 100%; padding: 25px 40px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; background: none; border: none; font-weight: 600; color: var(--text-main); font-size: 1.05rem; transition: background 0.2s; }
        .faq-question:hover { background: #f8fafc; }
        .faq-answer { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; padding: 0 40px; }
        .faq-item.active .faq-answer { max-height: 500px; padding-bottom: 25px; }

        /* --- FOOTER --- */
        footer { text-align: center; padding: 60px 20px; background: white; border-top: 1px solid #e2e8f0; margin-top: 60px; }
        .footer-btn { 
            display: inline-flex; align-items: center; gap: 8px; 
            padding: 12px 25px; border-radius: 50px; 
            background: #f1f5f9; color: var(--text-main); font-weight: 600; 
            cursor: pointer; transition: all 0.2s; border: 1px solid transparent;
        }
        .footer-btn:hover { background: #eef2ff; color: var(--primary); border-color: #c7d2fe; transform: translateY(-2px); }

        /* =========================================
           ULTIMATE TEAM MODAL (COMPACT + FIXED)
           ========================================= */
        .modal-overlay {
            position: fixed; 
            top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(15, 23, 42, 0.7); 
            backdrop-filter: blur(10px); 
            z-index: 100000; 
            display: none; align-items: center; justify-content: center;
            opacity: 0; transition: opacity 0.3s ease;
            padding: 20px;
            overflow: hidden; 
            box-sizing: border-box;
        }
        .modal-overlay.show { display: flex; opacity: 1; }

        .team-modal-content {
            background: white;
            width: 100%; max-width: 1100px;
            border-radius: 30px; 
            padding: 40px; 
            position: relative;
            box-shadow: 0 50px 100px -20px rgba(0, 0, 0, 0.4);
            transform: scale(0.95); transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            max-height: 90vh; 
            overflow-y: auto; 
            display: flex; flex-direction: column; align-items: center;
        }
        .modal-overlay.show .team-modal-content { transform: scale(1); }

        .team-header { text-align: center; margin-bottom: 30px; }
        .team-header h2 { font-size: 2.2rem; font-weight: 800; margin: 0 0 5px 0; color: #1e293b; letter-spacing: -1px; }
        .team-header p { color: #64748b; font-size: 1rem; margin: 0; }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px; 
            width: 100%;
            padding: 5px;
        }

        .team-card {
            background: #fff;
            border-radius: 20px;
            padding: 25px 20px;
            text-align: center;
            border: 1px solid #f1f5f9;
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex; flex-direction: column; align-items: center; height: 100%;
            opacity: 0; transform: translateY(30px);
        }

        .team-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px -12px rgba(79, 70, 229, 0.25);
            border-color: #c7d2fe;
        }

        .member-img-wrapper {
            width: 85px; height: 85px; margin-bottom: 15px;
            position: relative;
        }
        .member-img {
            width: 100%; height: 100%; border-radius: 50%; object-fit: cover;
            border: 3px solid white; box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }
        .member-badge {
            position: absolute; bottom: 0; right: 0; background: white; color: var(--primary);
            width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1); font-size: 0.8rem;
        }

        .member-name { font-size: 1.15rem; font-weight: 700; color: #1e293b; margin: 0 0 2px 0; }
        .member-role { 
            font-size: 0.7rem; font-weight: 800; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px;
            background: #e0e7ff; padding: 4px 10px; border-radius: 15px; margin-bottom: 10px; display: inline-block;
        }
        .member-desc { font-size: 0.85rem; color: #64748b; line-height: 1.4; }

        .close-modal-btn {
            position: absolute; top: 20px; right: 20px;
            width: 40px; height: 40px; border-radius: 50%;
            background: #f1f5f9; color: #64748b; font-size: 1.4rem;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all 0.2s; z-index: 10;
        }
        .close-modal-btn:hover { background: #fee2e2; color: #ef4444; transform: rotate(90deg); }

        /* Animazioni Ingresso Sequenziali */
        @keyframes slideUpFade { to { opacity: 1; transform: translateY(0); } }
        .modal-overlay.show .team-card { animation: slideUpFade 0.5s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; }
        .modal-overlay.show .team-card:nth-child(1) { animation-delay: 0.05s; }
        .modal-overlay.show .team-card:nth-child(2) { animation-delay: 0.1s; }
        .modal-overlay.show .team-card:nth-child(3) { animation-delay: 0.15s; }
        .modal-overlay.show .team-card:nth-child(4) { animation-delay: 0.2s; }

        @media (max-width: 768px) {
            .hero-title { font-size: 2.8rem; }
            .nav-links a:not(.btn-cta-nav) { display: none; }
            .team-modal-content { padding: 30px 20px; }
            .landing-nav { padding: 30px 5%; }
            .logo-img { height: 40px; }
        }
    </style>
</head>
<body>

    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <nav class="landing-nav" id="mainNav">
        <a href="#" class="logo-area">
            <img src="icon/logo.png" class="logo-img"> iFantastici4
        </a>
        <div class="nav-links">
            <a href="auth.php">Accedi</a>
            <a href="auth.php" class="btn-cta-nav">Inizia Ora</a>
        </div>
    </nav>

    <div class="hero-section">
        <h1 class="hero-title">Supporto IT <br> Senza Confini.</h1>
        <p class="hero-desc">La piattaforma definitiva per gestire ticket e risolvere problemi alla velocità della luce.</p>
        <a href="auth.php" class="cta-btn-hero">Apri la Dashboard <i class="fas fa-arrow-right"></i></a>
    </div>

    <div class="features-grid">
        <div class="feature-card reveal-on-scroll"><div class="f-icon"><i class="fas fa-bolt"></i></div><h3>Velocità</h3><p>Ottimizzato per ridurre i tempi di attesa.</p></div>
        <div class="feature-card reveal-on-scroll" style="transition-delay:100ms"><div class="f-icon"><i class="fas fa-shield-alt"></i></div><h3>Sicurezza</h3><p>Dati criptati e backup automatici.</p></div>
        <div class="feature-card reveal-on-scroll" style="transition-delay:200ms"><div class="f-icon"><i class="fas fa-chart-pie"></i></div><h3>Analytics</h3><p>Statistiche dettagliate sulle performance.</p></div>
    </div>

    <section class="faq-section reveal-on-scroll">
        <div class="faq-box">
            <div class="faq-box-header">
                <div><h2 style="margin:0;">Domande Frequenti</h2><p style="margin:5px 0 0 0; color:var(--text-muted);">Tutto quello che devi sapere.</p></div>
                <span style="background:#eef2ff; color:var(--primary); padding:6px 16px; border-radius:20px; font-weight:700; font-size:0.85rem;">
                    <?php echo $res_faq ? pg_num_rows($res_faq) : 0; ?> FAQ
                </span>
            </div>
            <div class="faq-scroll-area">
                <?php if ($res_faq && pg_num_rows($res_faq) > 0): while($row = pg_fetch_assoc($res_faq)): ?>
                <div class="faq-item">
                    <button class="faq-question">
                        <span><i class="far fa-question-circle" style="color:#94a3b8; margin-right:10px;"></i> <?php echo htmlspecialchars($row['question']); ?></span>
                        <i class="fas fa-chevron-down" style="font-size:0.8rem; color:#cbd5e1;"></i>
                    </button>
                    <div class="faq-answer"><p><?php echo htmlspecialchars($row['answer']); ?></p></div>
                </div>
                <?php endwhile; endif; ?>
            </div>
        </div>
    </section>

    <footer>
        <p style="margin-bottom:15px;">&copy; <?php echo date('Y'); ?> <strong>HelpDesk iFantastici4</strong>.</p>
        <div onclick="openTeamModal()" class="footer-btn">
            <i class="fas fa-users"></i> Conosci il Team
        </div>
    </footer>

    <div id="teamModal" class="modal-overlay">
        <div class="team-modal-content">
            <div class="close-modal-btn" onclick="closeTeamModal()">&times;</div>
            
            <div class="team-header">
                <h2>Il Nostro Team</h2>
                <p>Le menti creative dietro il progetto.</p>
            </div>

            <div class="team-grid">
                
                <div class="team-card">
                    <div class="member-img-wrapper">
                        <img src="img/Mattia.png" class="member-img" alt="Mattia">
                        <div class="member-badge"><i class="fas fa-code"></i></div>
                    </div>
                    <h3 class="member-name">Mattia Letteriello</h3>
                    <span class="member-role">Full Stack Dev</span>
                    <p class="member-desc">Architetto del software, coordina frontend e backend per un'esperienza fluida e scalabile.</p>
                </div>

                <div class="team-card">
                    <div class="member-img-wrapper">
                        <img src="img/Jonathan.png" class="member-img" alt="Jonathan">
                        <div class="member-badge"><i class="fas fa-paint-brush"></i></div>
                    </div>
                    <h3 class="member-name">Jonathan Punzo</h3>
                    <span class="member-role">Frontend Designer</span>
                    <p class="member-desc">Il mago della UI/UX. Trasforma requisiti complessi in interfacce pulite e intuitive.</p>
                </div>

                <div class="team-card">
                    <div class="member-img-wrapper">
                        <img src="img/Antonia.png" class="member-img" alt="Antonia">
                        <div class="member-badge"><i class="fas fa-database"></i></div>
                    </div>
                    <h3 class="member-name">Antonia Lamberti</h3>
                    <span class="member-role">DB Administrator</span>
                    <p class="member-desc">Custode dei dati. Progetta schemi database ottimizzati per sicurezza e velocità.</p>
                </div>

                <div class="team-card">
                    <div class="member-img-wrapper">
                        <img src="img/Vale.png" class="member-img" alt="Valentino">
                        <div class="member-badge"><i class="fas fa-server"></i></div>
                    </div>
                    <h3 class="member-name">Valentino Potapchuk</h3>
                    <span class="member-role">Backend Developer</span>
                    <p class="member-desc">Il motore sotto il cofano. Sviluppa API robuste e gestisce la logica server-side.</p>
                </div>

            </div>
        </div>
    </div>

    <script>
        // Header Scroll
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('mainNav');
            if (window.scrollY > 20) nav.classList.add('scrolled'); else nav.classList.remove('scrolled');
        });

        // Scroll Reveal
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.reveal-on-scroll').forEach(el => observer.observe(el));

        // FAQ Logic
        document.querySelector('.faq-scroll-area').addEventListener('click', function(e) {
            const btn = e.target.closest('.faq-question');
            if(!btn) return;
            const item = btn.parentElement;
            const answerDiv = item.querySelector('.faq-answer');
            const isActive = item.classList.contains('active');
            
            document.querySelectorAll('.faq-item').forEach(i => {
                i.classList.remove('active');
                i.querySelector('.faq-answer').style.maxHeight = null;
            });

            if (!isActive) {
                item.classList.add('active');
                answerDiv.style.maxHeight = answerDiv.scrollHeight + "px";
            }
        });

        // --- MODAL LOGIC ---
        function openTeamModal() {
            const modal = document.getElementById('teamModal');
            document.body.appendChild(modal);
            modal.style.display = 'flex';
            setTimeout(() => { modal.classList.add('show'); }, 10);
            document.body.style.overflow = 'hidden'; 
        }

        function closeTeamModal() {
            const modal = document.getElementById('teamModal');
            modal.classList.remove('show');
            setTimeout(() => { modal.style.display = 'none'; }, 300);
            document.body.style.overflow = 'auto';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('teamModal');
            if (event.target == modal) closeTeamModal();
        }
    </script>

</body>
</html>