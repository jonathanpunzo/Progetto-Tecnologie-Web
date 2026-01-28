<?php
// FILE: landing.php
// Questa pagina viene inclusa da index.php se non c'è sessione attiva
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benvenuto - HelpDesk iFantastici4</title>
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Override specifici per la Landing Page */
        body {
            /* Resetta il layout flex della dashboard */
            display: block !important; 
            background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
            overflow-x: hidden;
            overflow-y: auto !important; /* Riabilita lo scroll */
            padding-bottom: 0 !important;
        }

        /* Navbar Semplificata */
        .landing-nav {
            display: flex; justify-content: space-between; align-items: center;
            padding: 20px 40px; max-width: 1200px; margin: 0 auto;
        }
        .logo-area { font-size: 1.5rem; font-weight: 800; color: var(--primary-dark); display: flex; align-items: center; gap: 10px; }
        
        /* Hero Section */
        .hero-section {
            text-align: center; padding: 80px 20px;
            max-width: 900px; margin: 0 auto;
            animation: fadeIn Up 0.8s ease-out;
        }
        
        .hero-title {
            font-size: 3.5rem; font-weight: 900; line-height: 1.1; margin-bottom: 20px;
            background: linear-gradient(to right, var(--primary-dark), var(--primary));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        
        .hero-desc { font-size: 1.2rem; color: var(--text-muted); margin-bottom: 40px; line-height: 1.6; }
        
        .cta-btn {
            display: inline-block; padding: 15px 40px;
            background: var(--primary); color: white;
            font-size: 1.1rem; font-weight: 700; border-radius: 50px;
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.4);
            transition: all 0.3s ease;
        }
        .cta-btn:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(79, 70, 229, 0.5); background: var(--primary-dark); }

        /* Features Grid */
        .features-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px; max-width: 1200px; margin: 40px auto 80px auto; padding: 0 20px;
        }
        
        .feature-card {
            background: white; padding: 40px 30px; border-radius: 20px;
            border: 1px solid #f1f5f9; box-shadow: 0 10px 30px -5px rgba(0,0,0,0.05);
            transition: 0.3s; text-align: center;
        }
        .feature-card:hover { transform: translateY(-10px); border-color: var(--primary-light); }
        
        .f-icon {
            width: 70px; height: 70px; background: #eef2ff; color: var(--primary);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem; margin: 0 auto 20px auto;
        }

        /* Footer */
        .landing-footer { text-align: center; padding: 40px; color: var(--text-muted); font-size: 0.9rem; border-top: 1px solid #e2e8f0; }

        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <nav class="landing-nav">
        <div class="logo-area">
            <img src="icon/logo.png" style="height:40px;"> iFantastici4
        </div>
        <div>
            <a href="auth.php" style="font-weight:600; color:var(--text-main); margin-right:20px;">Accedi</a>
            <a href="auth.php" class="btn-primary" style="padding:10px 20px; border-radius:99px; text-decoration:none;">Registrati</a>
        </div>
    </nav>

    <div class="hero-section">
        <h1 class="hero-title">Il Supporto Tecnico <br> Semplice e Veloce.</h1>
        <p class="hero-desc">
            Gestisci ticket, monitora le richieste e risolvi i problemi in tempo record. 
            La piattaforma all-in-one per il team IT moderno.
        </p>
        <a href="auth.php" class="cta-btn">Inizia Subito <i class="fas fa-arrow-right" style="margin-left:10px;"></i></a>
    </div>

    <div class="features-grid">
        <div class="feature-card">
            <div class="f-icon"><i class="fas fa-ticket-alt"></i></div>
            <h3>Ticket System</h3>
            <p style="color:#64748b; margin-top:10px;">Apri segnalazioni in pochi click, allega file e monitora lo stato di avanzamento in tempo reale.</p>
        </div>
        <div class="feature-card">
            <div class="f-icon"><i class="fas fa-users"></i></div>
            <h3>Team Management</h3>
            <p style="color:#64748b; margin-top:10px;">Gestisci ruoli e permessi. Un pannello di amministrazione completo per il tuo staff.</p>
        </div>
        <div class="feature-card">
            <div class="f-icon"><i class="fas fa-chart-line"></i></div>
            <h3>Statistiche</h3>
            <p style="color:#64748b; margin-top:10px;">Analizza le performance con grafici interattivi e reportistica dettagliata sulle risoluzioni.</p>
        </div>
    </div>

    <footer class="landing-footer">
        &copy; <?php echo date('Y'); ?> HelpDesk iFantastici4. Progetto Tecnologie Web.
    </footer>

</body>
</html>