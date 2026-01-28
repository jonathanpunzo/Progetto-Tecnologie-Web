<?php
// FILE: pages/landing.php

// Protezione accesso diretto
if (!defined('ACCESSO_AUTORIZZATO')) {
    header("Location: ../index.php");
    exit;
}

global $db_conn; // Forza PHP a cercare la connessione definita in db.php

$query_faq = "SELECT * FROM faqs ORDER BY id ASC";
$res_faq = @pg_query($db_conn, $query_faq); // Ora $db_conn non sarà più null
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benvenuto - HelpDesk iFantastici4</title>
    <link rel="icon" type="image/png" href="icon/favicon.png">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- STILI GENERALI --- */
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0; padding: 0;
            background-color: #f8fafc;
            color: var(--text-main);
            overflow-x: hidden;
        }

        /* --- HEADER & HERO (Invariati) --- */
        .landing-nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
            display: flex; justify-content: space-between; align-items: center;
            padding: 20px 5%; transition: all 0.3s ease;
        }
        .landing-nav.scrolled {
            background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(12px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.05); padding: 15px 5%;
        }
        .logo-area { font-size: 1.4rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .nav-links a { text-decoration: none; font-weight: 600; color: var(--text-main); margin-left: 25px; }
        .btn-cta-nav { background: var(--text-main); color: white !important; padding: 10px 20px; border-radius: 99px; transition: transform 0.2s; }
        .btn-cta-nav:hover { transform: scale(1.05); background: black; }

        .hero-section { padding: 180px 20px 100px 20px; text-align: center; position: relative; overflow: hidden; }
        .blob { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.6; z-index: -1; animation: float 10s infinite alternate; }
        .blob-1 { width: 350px; height: 350px; background: #e0e7ff; top: -50px; left: -50px; }
        .blob-2 { width: 450px; height: 450px; background: #f0fdf4; bottom: 0; right: -100px; animation-delay: -5s; }
        .blob-3 { width: 250px; height: 250px; background: #fef2f2; top: 40%; left: 20%; animation-duration: 15s; }
        @keyframes float { 0% { transform: translate(0, 0); } 100% { transform: translate(30px, 50px); } }

        .hero-title { font-size: 4rem; font-weight: 900; line-height: 1.1; margin-bottom: 25px; letter-spacing: -2px; background: linear-gradient(135deg, var(--text-main), var(--primary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .hero-desc { font-size: 1.25rem; color: var(--text-muted); margin-bottom: 40px; }
        .cta-btn-hero { display: inline-flex; align-items: center; gap: 10px; padding: 18px 45px; background: var(--primary); color: white; font-size: 1.2rem; font-weight: 700; border-radius: 50px; text-decoration: none; box-shadow: 0 10px 30px rgba(79, 70, 229, 0.4); transition: all 0.3s; }
        .cta-btn-hero:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(79, 70, 229, 0.5); }

        /* --- FEATURES --- */
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 40px; max-width: 1200px; margin: 0 auto 120px auto; padding: 0 20px; }
        .feature-card { background: white; padding: 50px 35px; border-radius: 24px; border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05); transition: 0.4s; opacity: 0; transform: translateY(40px); }
        .feature-card.visible { opacity: 1; transform: translateY(0); }
        .feature-card:hover { transform: translateY(-10px); border-color: var(--primary); }
        .f-icon { width: 60px; height: 60px; border-radius: 16px; background: #eef2ff; color: var(--primary); font-size: 1.5rem; display: flex; align-items: center; justify-content: center; margin-bottom: 25px; }

        /* --- FAQ BOX --- */
        .faq-section { max-width: 900px; margin: 0 auto 140px auto; padding: 0 20px; }
        .faq-box { background: white; border-radius: 24px; border: 1px solid var(--border-color); box-shadow: 0 20px 50px -10px rgba(0,0,0,0.08); overflow: hidden; display: flex; flex-direction: column; height: 600px; }
        .faq-box-header { padding: 35px 40px; background: #fff; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
        .faq-scroll-area { flex: 1; overflow-y: auto; }
        .faq-item { border-bottom: 1px solid var(--border-color); background: white; transition: background 0.2s; }
        .faq-item:hover { background: #fafafa; }
        .faq-question { width: 100%; padding: 25px 40px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; background: none; border: none; font-family: inherit; }
        .faq-answer { max-height: 0; overflow: hidden; transition: max-height 0.35s cubic-bezier(0, 1, 0, 1); padding: 0 40px; }
        .faq-answer p { margin: 0; padding-bottom: 25px; color: var(--text-muted); line-height: 1.6; }
        .faq-item.active .faq-answer { max-height: 500px; } /* Fallback */

        /* --- STILE DEL MODALE (POPUP TEAM) --- */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(8px);
            z-index: 9999; display: none; align-items: center; justify-content: center;
            opacity: 0; transition: opacity 0.3s ease;
        }
        .modal-overlay.show { display: flex; opacity: 1; }

        .team-modal-content {
            background: #f8fafc; width: 90%; max-width: 1100px;
            border-radius: 24px; padding: 40px; position: relative;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            transform: scale(0.95); transition: transform 0.3s;
            max-height: 90vh; overflow-y: auto;
        }
        .modal-overlay.show .team-modal-content { transform: scale(1); }

        .close-modal {
            position: absolute; top: 20px; right: 25px;
            font-size: 2rem; color: #94a3b8; cursor: pointer; transition: 0.2s; z-index: 10;
        }
        .close-modal:hover { color: #ef4444; transform: rotate(90deg); }

        /* Griglia Team dentro il Modale */
        .team-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-top: 30px; }
        .team-card {
            background: white; border-radius: 16px; padding: 25px; text-align: center;
            border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            opacity: 0; transform: translateY(20px); /* Per animazione */
        }
        
        /* Animazione Ingresso Card Team */
        @keyframes popIn { to { opacity: 1; transform: translateY(0); } }
        .modal-overlay.show .team-card { animation: popIn 0.5s ease-out forwards; }
        .modal-overlay.show .team-card:nth-child(1) { animation-delay: 0.1s; }
        .modal-overlay.show .team-card:nth-child(2) { animation-delay: 0.2s; }
        .modal-overlay.show .team-card:nth-child(3) { animation-delay: 0.3s; }
        .modal-overlay.show .team-card:nth-child(4) { animation-delay: 0.4s; }

        .member-img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.1); margin-bottom: 15px; }
        .member-role { font-size: 0.75rem; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 1px; background: #eef2ff; padding: 4px 10px; border-radius: 20px; display: inline-block; margin-bottom: 10px; }

        /* Footer */
        footer { text-align: center; padding: 40px; border-top: 1px solid #e2e8f0; color: var(--text-muted); font-size: 0.9rem; background: white; margin-top: 60px;}
        .footer-link { color: var(--primary); text-decoration: none; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px; border-radius: 8px; transition: 0.2s; }
        .footer-link:hover { background: #eef2ff; }

        @media (max-width: 768px) {
            .hero-title { font-size: 2.8rem; }
            .nav-links a:not(.btn-cta-nav) { display: none; }
            .team-modal-content { padding: 25px; width: 95%; }
        }
    </style>
</head>
<body>

    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <nav class="landing-nav" id="mainNav">
        <a href="#" class="logo-area">
            <img src="icon/logo.png" style="height:40px;"> iFantastici4
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
                <div><h2>Domande Frequenti</h2><p>Le risposte dal nostro database.</p></div>
                <span style="background:#eef2ff; color:var(--primary); padding:5px 15px; border-radius:20px; font-weight:700; font-size:0.8rem;">
                    <?php echo $res_faq ? pg_num_rows($res_faq) : 0; ?> FAQ
                </span>
            </div>
            <div class="faq-scroll-area">
                <?php if ($res_faq && pg_num_rows($res_faq) > 0): while($row = pg_fetch_assoc($res_faq)): ?>
                <div class="faq-item">
                    <button class="faq-question">
                        <span style="font-weight:600; color:var(--text-main); font-size:1.05rem; display:flex; align-items:center; gap:15px;">
                            <i class="far fa-question-circle" style="color:#94a3b8;"></i> <?php echo htmlspecialchars($row['question']); ?>
                        </span>
                        <i class="fas fa-chevron-down" style="color:#94a3b8;"></i>
                    </button>
                    <div class="faq-answer"><p><?php echo htmlspecialchars($row['answer']); ?></p></div>
                </div>
                <?php endwhile; endif; ?>
            </div>
        </div>
    </section>

    <footer style="display:flex; flex-direction:column; gap:10px; align-items:center;">
        <p>&copy; <?php echo date('Y'); ?> <strong>HelpDesk iFantastici4</strong>. Tutti i diritti riservati.</p>
        
        <a onclick="openTeamModal()" class="footer-link">
            <i class="fas fa-users"></i> Chi Siamo & Team
        </a>
    </footer>


    <div id="teamModal" class="modal-overlay">
        <div class="team-modal-content">
            <span class="close-modal" onclick="closeTeamModal()">&times;</span>
            
            <div style="text-align:center; margin-bottom:30px;">
                <h2 style="font-size:2rem; font-weight:800; color:var(--text-main); margin-bottom:10px;">Il Nostro Team</h2>
                <p style="color:var(--text-muted);">Incontra le menti dietro il codice.</p>
            </div>

            <div class="team-grid">
                <div class="team-card">
                    <img src="img/Mattia.png" class="member-img" alt="Mattia">
                    <h3 style="margin:0 0 5px 0;">Mattia Letteriello</h3>
                    <span class="member-role">Full Stack Dev</span>
                    <p style="font-size:0.85rem; color:var(--text-muted); line-height:1.4;">Architetto del software, coordina frontend e backend in perfetta armonia.</p>
                </div>
                <div class="team-card">
                    <img src="img/Joanthan.png" class="member-img" alt="Jonathan">
                    <h3 style="margin:0 0 5px 0;">Jonathan Punzo</h3>
                    <span class="member-role">Frontend Designer</span>
                    <p style="font-size:0.85rem; color:var(--text-muted); line-height:1.4;">Il mago della UI/UX. Trasforma idee complesse in interfacce semplici.</p>
                </div>
                <div class="team-card">
                    <img src="img/Antonia.png" class="member-img" alt="Antonia">
                    <h3 style="margin:0 0 5px 0;">Antonia Lamberti</h3>
                    <span class="member-role">DB Administrator</span>
                    <p style="font-size:0.85rem; color:var(--text-muted); line-height:1.4;">Custode dei dati. Garantisce sicurezza, integrità e performance.</p>
                </div>
                <div class="team-card">
                    <img src="img/Vale.png" class="member-img" alt="Valentino">
                    <h3 style="margin:0 0 5px 0;">Valentino Potapchuk</h3>
                    <span class="member-role">Backend Developer</span>
                    <p style="font-size:0.85rem; color:var(--text-muted); line-height:1.4;">Il motore sotto il cofano. Sviluppa API robuste e veloci.</p>
                </div>
            </div>
        </div>
    </div>


    <script>
        // Header Scroll
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('mainNav');
            if (window.scrollY > 50) nav.classList.add('scrolled'); else nav.classList.remove('scrolled');
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
            
            // Chiudi altri
            document.querySelectorAll('.faq-item').forEach(i => {
                i.classList.remove('active');
                i.querySelector('.faq-answer').style.maxHeight = null;
            });

            if (!isActive) {
                item.classList.add('active');
                answerDiv.style.maxHeight = answerDiv.scrollHeight + "px";
            }
        });

        // --- TEAM MODAL LOGIC ---
        function openTeamModal() {
            const modal = document.getElementById('teamModal');
            modal.style.display = 'flex';
            // Timeout per permettere la transizione CSS (opacity)
            setTimeout(() => { modal.classList.add('show'); }, 10);
            document.body.style.overflow = 'hidden'; // Blocca scroll pagina sotto
        }

        function closeTeamModal() {
            const modal = document.getElementById('teamModal');
            modal.classList.remove('show');
            setTimeout(() => { modal.style.display = 'none'; }, 300);
            document.body.style.overflow = 'auto'; // Sblocca scroll
        }

        // Chiudi cliccando fuori
        window.onclick = function(event) {
            const modal = document.getElementById('teamModal');
            if (event.target == modal) closeTeamModal();
        }
    </script>

</body>
</html>