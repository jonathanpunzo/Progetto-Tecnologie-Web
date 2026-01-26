<?php
session_start();
require_once('db.php');

$is_logged = isset($_SESSION['user_id']);
$user_name = $is_logged ? $_SESSION['user_name'] : 'Ospite';
$role = $is_logged ? $_SESSION['user_role'] : '';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <link rel="icon" type="image/png" href="favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Siamo - HelpDesk</title>
    <link rel="stylesheet" href="chi_siamo_style.css">
    <style>
        /* Override per la nav qui dentro per coerenza */
        nav {
            background-color: var(--sidebar-dark);
            padding: 0.8rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>

    <nav>
        <a href="index.php" style="display:flex; align-items:center;">
            <img src="logobanner.png" alt="Logo" class="brand-logo-img">
        </a>
        <div class="menu">
            <?php if ($is_logged): ?>
                <span style="margin-left:15px; color:white;">Ciao, <strong><?php echo htmlspecialchars($user_name); ?></strong> (<?php echo $role; ?>)</span>
                <span class="desktop-only" style="color:white;"> | </span>   
                <a href="logout.php" style="color: #ff9999;">Esci</a>
            <?php else: ?>
                <a href="auth.php" class="btn-style">Accedi / Registrati</a>
            <?php endif; ?>
        </div>
    </nav>

    <main class="container">
        <h1 style="text-align: center; margin-bottom: 40px;">Il Nostro Team</h1>

        <div class="team-grid">
            <div class="team-card">
                <div class="img-placeholder"><img src="img/Mattia.png" alt="Mattia"></div>
                <h3>Mattia Letteriello</h3><p>Sviluppatore Full Stack</p>
            </div>
            <div class="team-card">
                <div class="img-placeholder"><img src="img/Joanthan.png" alt="Jonathan"></div>
                <h3>Jonathan Punzo</h3><p>Frontend Designer</p>
            </div>
            <div class="team-card">
                <div class="img-placeholder"><img src="img/Antonia.png" alt="Antonia"></div>
                <h3>Antonia Lucia Lamberti</h3><p>Database Administrator</p>
            </div>
            <div class="team-card">
                <div class="img-placeholder"><img src="img/Vale.png" alt="Valentino"></div>
                <h3>Valentino Potapchuk</h3><p>Backend Developer</p>
            </div>
        </div>

        <div style="text-align: center; margin-top: 50px;">
            <a href="index.php" class="btn-style">🏠 Torna alla Home</a>
        </div>
    </main>

    <footer class="main-footer">
        <p>Made with ❤️ da: <strong>iFantastici4</strong></p>
    </footer>

</body>
</html>