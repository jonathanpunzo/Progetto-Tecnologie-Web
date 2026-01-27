<?php
session_start();
require_once('db.php');

// Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];

// --- LOGICA DI NAVIGAZIONE (ROUTING) ---
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Whitelist delle pagine consentite
$allowed_pages = [
    'dashboard'     => 'pages/dashboard.php',
    'all_tickets'   => 'pages/tickets_list.php',
    'ticket_details'=> 'pages/ticket_details.php',
    'users_stats'   => 'pages/users_admin.php',
    'new_ticket'    => 'pages/new_ticket.php',
    'chi_siamo'     => 'pages/chi_siamo.php'
];

// Fallback se la pagina non esiste
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
                <a href="index.php?page=all_tickets" class="nav-item <?php echo $page=='all_tickets'?'active':''; ?>">
                    <i class="fas fa-ticket-alt"></i> Tutti i Ticket
                </a>
                <a href="index.php?page=users_stats" class="nav-item <?php echo $page=='users_stats'?'active':''; ?>">
                    <i class="fas fa-users"></i> Utenti
                </a>
            <?php else: ?>
                <a href="index.php?page=new_ticket" class="nav-item <?php echo $page=='new_ticket'?'active':''; ?>">
                    <i class="fas fa-plus-circle"></i> Nuovo Ticket
                </a>
                <a href="index.php?page=all_tickets" class="nav-item <?php echo $page=='all_tickets'?'active':''; ?>">
                    <i class="fas fa-list"></i> I Miei Ticket
                </a>
            <?php endif; ?>

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
                <h3>Bentornato, <?php echo htmlspecialchars($user_name); ?>! 👋</h3>
            </div>

            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Cerca nella dashboard...">
            </div>

            <div class="user-menu">
                <button class="icon-btn" onclick="copyEmail()" title="Copia Email Supporto">
                    <i class="far fa-envelope"></i>
                </button>
                <button class="icon-btn"><i class="far fa-bell"></i></button>
                
                <div class="profile-dropdown" onclick="toggleMenu()">
                    <div class="avatar">
                        <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                    </div>
                    <div id="dropdownInfo" class="dropdown-content">
                        <div style="padding: 10px; border-bottom: 1px solid #eee;">
                            <strong><?php echo htmlspecialchars($user_name); ?></strong><br>
                            <small style="color:gray"><?php echo ucfirst($user_role); ?></small>
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
        
        function copyEmail() {
            navigator.clipboard.writeText("help@ifantastici4.it");
            alert("Email copiata: help@ifantastici4.it");
        }

        // Chiudi dropdown se clicco fuori
        window.onclick = function(event) {
            if (!event.target.closest('.profile-dropdown')) {
                document.getElementById("dropdownInfo").classList.remove('show');
            }
        }
    </script>
</body>
</html>