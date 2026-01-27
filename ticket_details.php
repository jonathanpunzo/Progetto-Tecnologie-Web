<?php
session_start();
require_once('db.php');

if (!isset($_SESSION['user_id'])) { header("Location: auth.php"); exit; }

$ticket_id = intval($_GET['id']);
$msg_error = "";

// 1. RECUPERO TICKET
$query_ticket = "SELECT t.*, u.name as author_name FROM tickets t JOIN users u ON t.user_id = u.id WHERE t.id = $ticket_id";
$res_ticket = pg_query($db_conn, $query_ticket);
$ticket = pg_fetch_assoc($res_ticket);

if (!$ticket) die("Ticket non trovato.");

// SICUREZZA
if ($_SESSION['user_role'] != 'admin' && $_SESSION['user_id'] != $ticket['user_id']) {
    die("Accesso Negato.");
}

// 2. INVIO MESSAGGIO
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    if ($ticket['status'] == 'closed') {
        $msg_error = "Impossibile inviare messaggi: il ticket è chiuso.";
    } else {
        $msg = pg_escape_string($db_conn, $_POST['message']);
        $uid = $_SESSION['user_id'];
        pg_query($db_conn, "INSERT INTO messages (ticket_id, user_id, message) VALUES ($ticket_id, $uid, '$msg')");
        header("Location: ticket_details.php?id=$ticket_id");
        exit;
    }
}

// 3. CAMBIO STATO (Admin)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_status']) && $_SESSION['user_role'] == 'admin') {
    $new_stat = $_POST['new_status'];
    pg_query($db_conn, "UPDATE tickets SET status = '$new_stat' WHERE id = $ticket_id");
    header("Location: ticket_details.php?id=$ticket_id");
    exit;
}


// 5. RECUPERO MESSAGGI
$query_msgs = "SELECT m.*, u.name, u.role FROM messages m JOIN users u ON m.user_id = u.id WHERE ticket_id = $ticket_id ORDER BY m.created_at ASC";
$res_msgs = pg_query($db_conn, $query_msgs);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #<?php echo $ticket_id; ?> HelpDesk</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .chat-box {
            height: 400px;
            overflow-y: auto;
            background: #f9f9f9;
            border: 1px solid #eee;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: inset 0 2px 5px rgba(0,0,0,0.03);
        }
        .closed-notice {
            background-color: #fee2e2; color: #dc2626; padding: 15px;
            text-align: center; font-weight: bold; border-radius: 5px;
            margin-top: 20px; border: 1px solid #fca5a5;
        }
        nav {
            background-color: var(--sidebar-dark);
            padding: 0.8rem 2rem;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<nav>
    <a href="index.php" style="display:flex; align-items:center;">
        <img src="icon/logobanner.png" alt="Logo" class="brand-logo-img">
    </a>
</nav>

<div class="container">
    <a href="index.php" class="btn-style" style="padding: 5px 15px; font-size: 0.8rem;">&larr; Torna alla lista</a>
    
    <div style="border-bottom: 1px solid #eee; margin-bottom: 20px; padding-bottom: 20px; margin-top: 20px;">
        <h1><?php echo htmlspecialchars($ticket['title']); ?> <span style="font-size:0.5em; color:gray">#<?php echo $ticket['id']; ?></span></h1>
        
        <span class="status-<?php echo $ticket['status']; ?>" style="font-size: 1.2em; border: 1px solid #ddd; padding: 5px 10px; border-radius: 20px;">
            <?php echo strtoupper($ticket['status']); ?>
        </span>
        
        <p style="margin-top: 15px;"><strong>Descrizione:</strong><br><?php echo nl2br(htmlspecialchars($ticket['description'])); ?></p>
        
        <?php if($ticket['attachment_path']): ?>
            <p>📎 Allegato: <a href="<?php echo $ticket['attachment_path']; ?>" target="_blank">Vedi File</a></p>
        <?php endif; ?>
    </div>

    <?php if($_SESSION['user_role'] == 'admin'): ?>
        <div style="background: #2c3e50; color: white; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
            <form method="POST" style="margin-bottom: 10px;">
                <label>Gestione Ticket:</label>
                <div style="display:flex; gap:10px; margin-top:5px;">
                    <select name="new_status" style="width: auto; margin: 0; color: black; flex:1;">
                        <option value="open" <?php echo ($ticket['status']=='open')?'selected':''; ?>>Aperto</option>
                        <option value="in-progress" <?php echo ($ticket['status']=='in-progress')?'selected':''; ?>>In Lavorazione</option>
                        <option value="resolved" <?php echo ($ticket['status']=='resolved')?'selected':''; ?>>Risolto</option>
                        <option value="closed" <?php echo ($ticket['status']=='closed')?'selected':''; ?>>Chiuso</option>
                    </select>
                    <button type="submit" class="btn-style" style="border:1px solid white;">Aggiorna</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <?php if($msg_error): ?> 
        <div style="color:red; background:#ffe6e6; padding:10px; margin-bottom:10px;"><?php echo $msg_error; ?></div> 
    <?php endif; ?>

    <h3>Cronologia Conversazione</h3>
    <div class="chat-box">
        <?php while($msg = pg_fetch_assoc($res_msgs)): 
            $is_admin = ($msg['role'] == 'admin');
            $class = $is_admin ? 'msg-admin' : 'msg-user';
        ?>
            <div class="msg <?php echo $class; ?>">
                <div class="meta">
                    <strong><?php echo htmlspecialchars($msg['name']); ?></strong> 
                    (<?php echo date('d/m H:i', strtotime($msg['created_at'])); ?>)
                </div>
                <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
            </div>
        <?php endwhile; ?>
        <?php if(pg_num_rows($res_msgs) == 0) echo "<p style='color:gray; text-align:center'>Nessun messaggio.</p>"; ?>
    </div>

    <?php if ($ticket['status'] != 'closed'): ?>
        <form method="POST">
            <textarea name="message" style="width: 100%; height: 80px;" placeholder="Scrivi una risposta..." required></textarea>
            <button type="submit" class="btn-style" style="margin-top: 10px; width: 100%;">Invia Risposta</button>
        </form>
    <?php else: ?>
        <div class="closed-notice">🔒 Questo ticket è stato chiuso. La conversazione è terminata.</div>
    <?php endif; ?>

</div>

<footer class="main-footer">
    <p>Made with ❤️ da: <strong>iFantastici4</strong></p>
    <a href="chi_siamo.php" class="btn-style">Chi Siamo</a>
</footer>

</body>
</html>