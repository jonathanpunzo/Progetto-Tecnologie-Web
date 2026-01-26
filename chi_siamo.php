<?php
session_start();
// Non serve connettersi al DB qui se non dobbiamo tirare fuori dati dinamici, 
// ma includiamo db.php se serve in futuro o per coerenza.
require_once('db.php');

$is_logged = isset($_SESSION['user_id']);
$user_name = $is_logged ? $_SESSION['user_name'] : 'Ospite';
$role = $is_logged ? $_SESSION['user_role'] : '';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Siamo - iFantastici4</title>
    <link rel="stylesheet" href="chi_siamo_style.css">
</head>
<body>

    <nav>
        <div class="logo">supporto<strong>iFantastici4</strong></div>
        <div class="menu">
            
            <?php if ($is_logged): ?>
                <span style="margin-left:15px">Ciao, <strong><?php echo htmlspecialchars($user_name); ?></strong></span>
                | <a href="logout.php" style="color: #ff9999;">Esci</a>
            <?php else: ?>
                <a href="auth.php" class="btn-style">Accedi / Registrati</a>
            <?php endif; ?>
        </div>
    </nav>

    <main class="container">
        <h1 style="text-align: center; margin-bottom: 40px;">Il Nostro Team</h1>

        <div class="team-grid">
            
            <div class="team-card">
                <div class="img-placeholder">
                    <img src="img/Mattia.png" alt="Mattia Letteriello">
                </div>
                <h3>Mattia Letteriello</h3>
                <p>Sviluppatore Full Stack</p>
            </div>

            <div class="team-card">
                <div class="img-placeholder">
                    <img src="img/Joanthan.png" alt="Jonathan Punzo">
                </div>
                <h3>Jonathan Punzo</h3>
                <p>Frontend Designer</p>
            </div>

            <div class="team-card">
                <div class="img-placeholder">
                    <img src="img/Antonia.png" alt="Antonia Lucia Lamberti">
                </div>
                <h3>Antonia Lucia Lamberti</h3>
                <p>Database Administrator</p>
            </div>

            <div class="team-card">
                <div class="img-placeholder">
                    <img src="img/Vale.png" alt="Valentino Potapchuk">
                </div>
                <h3>Valentino Potapchuk</h3>
                <p>Backend Developer</p>
            </div>

        </div>

        <div style="text-align: center; margin-top: 50px;">
            <a href="index.php" class="btn-style">🏠 Torna alla Home</a>
        </div>
    </main>

    <footer class="main-footer">
        <p>
            Made with <span class="heart-beat">❤️</span> da: 
            <strong>Mattia Letteriello</strong>, 
            <strong>Jonathan Punzo</strong>, 
            <strong>Antonia Lucia Lamberti</strong>, 
            <strong>Valentino Potapchuk</strong>.
        </p>
        <p style="opacity: 0.8; font-size: 0.85em;">Esame di Tecnologie Web 2025/2026</p>
        
     
    </footer>

</body>
</html>