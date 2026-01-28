<?php
// FILE: index.php
session_start();

// Questa riga è fondamentale per far funzionare le "pages"
define('ACCESSO_AUTORIZZATO', true); 

require_once('db.php');

// 1. SECURITY CHECK & ROUTING
// Se l'utente NON è loggato, includiamo la Landing Page e fermiamo lo script.
if (!isset($_SESSION['user_id'])) { 
    include('pages/landing.php'); 
    exit; 
}

// 2. GESTIONE DOWNLOAD ALLEGATI (Senza file separato)
if (isset($_GET['page']) && $_GET['page'] == 'view_attachment' && isset($_GET['id'])) {
    $att_id = intval($_GET['id']);
    $query = "SELECT file_name, file_type, file_data FROM ticket_attachments WHERE id = $att_id";
    $result = pg_query($db_conn, $query);

    if ($result && pg_num_rows($result) > 0) {
        $file = pg_fetch_assoc($result);
        $data = pg_unescape_bytea($file['file_data']);
        
        // Header per il download
        header("Content-Type: " . $file['file_type']);
        header("Content-Disposition: inline; filename=\"" . $file['file_name'] . "\"");
        header("Content-Length: " . strlen($data));
        echo $data;
        exit; // IMPORTANTE: Ferma lo script qui
    } else {
        die("File non trovato.");
    }
}

// --- LOGICA SALVATAGGIO PROFILO ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_profile'])) {
    $target_id = intval($_POST['target_id']);
    $new_name = pg_escape_string($db_conn, $_POST['name']);
    
    $my_role = $_SESSION['user_role'];
    $my_id = $_SESSION['user_id'];

    $sql = "UPDATE users SET name = '$new_name'";

    if ($my_role == 'admin' && isset($_POST['role']) && $target_id != $my_id) {
        $new_role = pg_escape_string($db_conn, $_POST['role']);
        $sql .= ", role = '$new_role'";
    }

    $sql .= " WHERE id = $target_id";

    if (pg_query($db_conn, $sql)) {
        if ($target_id == $my_id) $_SESSION['user_name'] = $new_name;
        $redirect_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
        header("Location: index.php?page=" . $redirect_page);
        exit;
    } else {
        echo "<script>alert('Errore nel salvataggio.');</script>";
    }
}

// DATI UTENTE CORRENTE
$user_id_session = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];
$user_initials = strtoupper(substr($user_name, 0, 1));

// --- MODIFICA QUI: RECUPERO EMAIL REALE DAL DB ---
$query_me = "SELECT email FROM users WHERE id = $user_id_session";
$res_me = pg_query($db_conn, $query_me);
// Se trova l'utente usa la sua email, altrimenti stringa vuota
$user_email = ($res_me && pg_num_rows($res_me) > 0) ? pg_fetch_result($res_me, 0, 0) : 'Errore Email';


// ROUTING
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$allowed_pages = [
    'dashboard' => 'pages/dashboard.php',
    'all_tickets' => 'pages/tickets_list.php',
    'users_stats' => 'pages/users_admin.php',
    'new_ticket' => 'pages/new_ticket.php',
    'my_tickets' => 'pages/tickets_list.php',
    'community' => 'pages/tickets_list.php',
    'closed_tickets' => 'pages/tickets_list.php',
    'ticket_details' => 'pages/ticket_details.php',
    'chi_siamo' => 'pages/chi_siamo.php',
    'landing' => 'pages/landing.php'
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
        <div class="brand"><img src="icon/logo.png" style="height:40px;"> iFantastici4</div>
        <nav class="nav-links">
            <a href="index.php?page=dashboard" class="nav-item <?php echo $page=='dashboard'?'active':''; ?>"><i class="fas fa-th-large"></i> Dashboard</a>
            
            <?php if ($user_role == 'admin'): ?>
                <div class="nav-separator">AMMINISTRAZIONE</div>
                <a href="index.php?page=all_tickets" class="nav-item <?php echo $page=='all_tickets'?'active':''; ?>"><i class="fas fa-inbox"></i> Tutti i Ticket</a>
                <a href="index.php?page=closed_tickets&status=closed" class="nav-item <?php echo $page=='closed_tickets'?'active':''; ?>"><i class="fas fa-check-double"></i> Ticket Chiusi</a>
                <a href="index.php?page=users_stats" class="nav-item <?php echo $page=='users_stats'?'active':''; ?>"><i class="fas fa-users"></i> Utenti</a>
            <?php else: ?>
                <div class="nav-separator">MENU UTENTE</div>
                <a href="index.php?page=new_ticket" class="nav-item <?php echo $page=='new_ticket'?'active':''; ?>"><i class="fas fa-plus-circle"></i> Crea Ticket</a>
                <a href="index.php?page=my_tickets" class="nav-item <?php echo $page=='my_tickets'?'active':''; ?>"><i class="fas fa-list"></i> I Miei Ticket</a>
                <a href="index.php?page=community" class="nav-item <?php echo $page=='community'?'active':''; ?>"><i class="fas fa-globe"></i> Community Ticket</a>
                <a href="index.php?page=closed_tickets&status=closed" class="nav-item <?php echo $page=='closed_tickets'?'active':''; ?>"><i class="fas fa-archive"></i> Ticket Chiusi</a>
            <?php endif; ?>

            <div class="nav-separator">INFO</div>
            <a href="index.php?page=chi_siamo" class="nav-item <?php echo $page=='chi_siamo'?'active':''; ?>"><i class="fas fa-info-circle"></i> Chi Siamo</a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="nav-item" style="color: #ef4444;"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>

    <div class="main-content">
        
        <header class="top-header">
            <div class="welcome-text">
                <h3>Ciao, <?php echo htmlspecialchars($user_name); ?>! 👋</h3>
            </div>
            
            <div></div>
            
            <div class="user-menu">
                <div class="profile-dropdown" onclick="toggleMenu()">
                    <div class="avatar"><?php echo $user_initials; ?></div>
                    
                    <div id="dropdownInfo" class="dropdown-content">
                        
                        <div class="dropdown-header">
                            <span class="dropdown-user-name"><?php echo htmlspecialchars($user_name); ?></span>
                            <span class="dropdown-user-role"><?php echo $user_role; ?></span>
                        </div>
                        
                        <div class="dropdown-body">
                            <a href="#" onclick="openUserModal('me')" class="dropdown-link">
                                <i class="fas fa-cog"></i> Impostazioni
                            </a>
                            <a href="logout.php" class="dropdown-link logout">
                                <i class="fas fa-sign-out-alt"></i> Esci
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </header>

        <div class="page-container">
            <?php 
                if (file_exists($page_file)) include($page_file); 
                else echo "<h2>Errore 404</h2><p>Pagina non trovata.</p>";
            ?>
        </div>
    </div>

    <div id="userModal" class="modal-overlay">
        <div class="modal-box">
            <button class="modal-close" onclick="closeUserModal()"><i class="fas fa-times"></i></button>
            
            <div class="profile-avatar-large" id="modalAvatar">A</div>
            <h3 id="modalTitle" style="margin-bottom: 20px;">Modifica Profilo</h3>
            
            <form method="POST" action="">
                <input type="hidden" name="save_profile" value="1">
                <input type="hidden" name="target_id" id="modalTargetId">

                <div class="modal-field">
                    <label>Nome Completo</label>
                    <input type="text" name="name" id="modalName" required>
                </div>
                
                <div class="modal-field">
                    <label>Email (Non modificabile)</label>
                    <input type="email" id="modalEmail" readonly style="opacity:0.7; cursor:not-allowed;">
                </div>
                
                <div class="modal-field">
                    <label>Ruolo</label>
                    <select name="role" id="modalRole" disabled>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div style="margin-top:25px; display:flex; gap:15px;">
                    <button type="button" onclick="closeUserModal()" class="action-btn btn-secondary" style="flex:1;">
                        Annulla
                    </button>
                    <button type="submit" class="action-btn btn-primary" style="flex:1.5;">
                        Salva Modifiche
                    </button>
                </div>
            </form>

        </div>
    </div>

    <script>
        // QUI USIAMO LA VARIABILE PHP $user_email AGGIORNATA
        const currentUser = {
            id: <?php echo $user_id_session; ?>,
            name: "<?php echo htmlspecialchars($user_name); ?>",
            email: "<?php echo htmlspecialchars($user_email); ?>", 
            role: "<?php echo $user_role; ?>",
            initials: "<?php echo $user_initials; ?>"
        };

        function toggleMenu() { document.getElementById("dropdownInfo").classList.toggle("show"); }

        function openUserModal(data) {
            const modal = document.getElementById('userModal');
            let user = {};

            if (data === 'me') {
                user = currentUser;
                document.getElementById('modalTitle').innerText = "Il Tuo Profilo";
            } else {
                user = data;
                document.getElementById('modalTitle').innerText = "Modifica Utente";
            }

            document.getElementById('modalTargetId').value = user.id;
            document.getElementById('modalName').value = user.name;
            document.getElementById('modalEmail').value = user.email;
            document.getElementById('modalRole').value = user.role;
            document.getElementById('modalAvatar').innerText = user.initials;

            const roleSelect = document.getElementById('modalRole');
            if (currentUser.role === 'admin' && user.id != currentUser.id) {
                roleSelect.disabled = false;
                roleSelect.style.cursor = 'pointer';
                roleSelect.style.opacity = '1';
            } else {
                roleSelect.disabled = true;
                roleSelect.style.cursor = 'not-allowed';
                roleSelect.style.opacity = '0.7';
            }

            modal.style.display = 'flex';
            setTimeout(() => { modal.classList.add('show'); }, 10);
            document.getElementById("dropdownInfo").classList.remove("show");
        }

        function closeUserModal() {
            const modal = document.getElementById('userModal');
            modal.classList.remove('show');
            setTimeout(() => { modal.style.display = 'none'; }, 300);
        }

        window.onclick = function(event) {
            const modal = document.getElementById('userModal');
            if (event.target == modal) closeUserModal();
            if (!event.target.closest('.profile-dropdown')) {
                var dropdowns = document.getElementsByClassName("dropdown-content");
                for (var i = 0; i < dropdowns.length; i++) {
                    if (dropdowns[i].classList.contains('show')) dropdowns[i].classList.remove('show');
                }
            }
        }
    </script>
</body>
</html>