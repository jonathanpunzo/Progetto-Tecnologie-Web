<?php
session_start();
require_once('db.php');

// 1. SECURITY CHECK
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];

// 2. ROUTING SYSTEM
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

$allowed_pages = [
    'dashboard'      => 'pages/dashboard.php',
    'all_tickets'    => 'pages/tickets_list.php',
    'users_stats'    => 'pages/users_admin.php',
    'new_ticket'     => 'pages/new_ticket.php',
    'my_tickets'     => 'pages/tickets_list.php',
    'community'      => 'pages/tickets_list.php',
    'closed_tickets' => 'pages/tickets_list.php',
    'ticket_details' => 'pages/ticket_details.php',
    'chi_siamo'      => 'pages/chi_siamo.php'
];

$page_file = array_key_exists($page, $allowed_pages) ? $allowed_pages[$page] : 'pages/dashboard.php';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - HelpDesk iFantastici4</title>
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="page-<?php echo $page; ?>">

    <aside class="sidebar">
        <div class="brand">
            <i class="fas fa-shield-alt" style="color: #6366f1;"></i> iFantastici4
        </div>

        <nav class="nav-links">
            <a href="index.php?page=dashboard" class="nav-item <?php echo $page=='dashboard'?'active':''; ?>">
                <i class="fas fa-th-large"></i> Dashboard
            </a>

            <?php if ($user_role == 'admin'): ?>
                <div class="nav-separator">AMMINISTRAZIONE</div>
                <a href="index.php?page=all_tickets" class="nav-item <?php echo $page=='all_tickets'?'active':''; ?>">
                    <i class="fas fa-inbox"></i> Tutti i Ticket
                </a>
                <a href="index.php?page=closed_tickets&status=closed" class="nav-item <?php echo $page=='closed_tickets'?'active':''; ?>">
                    <i class="fas fa-check-double"></i> Ticket Chiusi
                </a>
                <a href="index.php?page=users_stats" class="nav-item <?php echo $page=='users_stats'?'active':''; ?>">
                    <i class="fas fa-users"></i> Utenti
                </a>
            <?php else: ?>
                <div class="nav-separator">MENU UTENTE</div>
                <a href="index.php?page=new_ticket" class="nav-item <?php echo $page=='new_ticket'?'active':''; ?>">
                    <i class="fas fa-plus-circle"></i> Crea Ticket
                </a>
                <a href="index.php?page=my_tickets" class="nav-item <?php echo $page=='my_tickets'?'active':''; ?>">
                    <i class="fas fa-list"></i> I Miei Ticket
                </a>
                <a href="index.php?page=community" class="nav-item <?php echo $page=='community'?'active':''; ?>">
                    <i class="fas fa-globe"></i> Community Ticket
                </a>
                <a href="index.php?page=closed_tickets&status=closed" class="nav-item <?php echo $page=='closed_tickets'?'active':''; ?>">
                    <i class="fas fa-archive"></i> Ticket Chiusi
                </a>
            <?php endif; ?>

            <div class="nav-separator">INFO</div>
            <a href="index.php?page=chi_siamo" class="nav-item <?php echo $page=='chi_siamo'?'active':''; ?>">
                <i class="fas fa-info-circle"></i> Chi Siamo
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="logout.php" class="nav-item" style="color: #ef4444;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </aside>

    <div class="main-content">
        
        <header class="top-header">
            <div class="welcome-text">
                <h3>Ciao, <?php echo htmlspecialchars($user_name); ?>! 👋</h3>
                <small style="color:var(--text-muted)">Ruolo: <?php echo ucfirst($user_role); ?></small>
            </div>

            <div></div> <div class="user-menu">
                <div class="profile-dropdown" onclick="toggleMenu()">
                    <div class="avatar">
                        <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                    </div>
                    <div id="dropdownInfo" class="dropdown-content">
                        <div style="padding: 10px; border-bottom: 1px solid #eee;">
                            <strong><?php echo htmlspecialchars($user_name); ?></strong>
                        </div>
                        <a href="#">Impostazioni</a>
                        <a href="logout.php" style="color:red;">Esci</a>
                    </div>
                </div>
            </div>
        </header>

        <div class="page-container">
            <?php 
                if (file_exists($page_file)) {
                    include($page_file); 
                } else {
                    echo "<h2>Errore 404</h2><p>Pagina non trovata.</p>";
                }
            ?>
        </div>

    </div>

    <script>
        function toggleMenu() {
            document.getElementById("dropdownInfo").classList.toggle("show");
        }

        // Chiudi dropdown se clicco fuori
        window.onclick = function(event) {
            if (!event.target.closest('.profile-dropdown')) {
                var dropdowns = document.getElementsByClassName("dropdown-content");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
    </script>
</body>
</html>