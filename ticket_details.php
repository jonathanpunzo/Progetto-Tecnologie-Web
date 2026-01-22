<?php
session_start();
require_once('db.php');

if (!isset($_SESSION['user_id'])) { header("Location: auth.php"); exit; }

$ticket_id = $_GET['id'];
$msg_error = "";

// 1. RECUPERO INFO TICKET
$query_ticket = "SELECT t.*, u.name as author_name FROM tickets t JOIN users u ON t.user_id = u.id WHERE t.id = $ticket_id";
$res_ticket = pg_query($db_conn, $query_ticket);
$ticket = pg_fetch_assoc($res_ticket);

if (!$ticket) die("Ticket non trovato.");

// SICUREZZA ACCESSO
if ($_SESSION['user_role'] != 'admin' && $_SESSION['user_id'] != $ticket['user_id']) {
    die("Accesso Negato.");
}

// 2. GESTIONE INVIO NUOVO MESSAGGIO
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    
    // --- IL BLOCCO DI SICUREZZA ---
    // Se il ticket è chiuso, nessuno deve poter scrivere (nemmeno l'admin, a meno che non lo riapra)
    if ($ticket['status'] == 'closed') {
        $msg_error = "Impossibile inviare messaggi: il ticket è chiuso.";
    } else {
        $msg = pg_escape_string($db_conn, $_POST['message']);
        $uid = $_SESSION['user_id'];
        
        $q_msg = "INSERT INTO messages (ticket_id, user_id, message) VALUES ($ticket_id, $uid, '$msg')";
        pg_query($db_conn, $q_msg);
        
        header("Location: ticket_details.php?id=$ticket_id");
        exit;
    }
}

// 3. GESTIONE CAMBIO STATO (Solo Admin)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_status']) && $_SESSION['user_role'] == 'admin') {
    $new_stat = $_POST['new_status'];
    $q_upd = "UPDATE tickets SET status = '$new_stat' WHERE id = $ticket_id";
    pg_query($db_conn, $q_upd);
    
    // Ricarichiamo la pagina per aggiornare la variabile $ticket e l'interfaccia
    header("Location: ticket_details.php?id=$ticket_id");
    exit;
}

// 4. RECUPERO MESSAGGI
$query_msgs = "SELECT m.*, u.name, u.role FROM messages m JOIN users u ON m.user_id = u.id WHERE ticket_id = $ticket_id ORDER BY m.created_at ASC";
$res_msgs = pg_query($db_conn, $query_msgs);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Ticket #<?php echo $ticket_id; ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Un po' di stile extra per l'avviso di chiusura */
        .closed-notice {
            background-color: #fee2e2;
            color: #dc2626;
            padding: 15px;
            text-align: center;
            font-weight: bold;
            border-radius: 5px;
            margin-top: 20px;
            border: 1px solid #fca5a5;
        }
    </style>
</head>
<body>

<div class="container" style="max-width: 800px;">
    <a href="index.php">&larr; Torna alla lista</a>
    
    <div style="border-bottom: 1px solid #eee; margin-bottom: 20px; padding-bottom: 20px;">
        <h1><?php echo htmlspecialchars($ticket['title']); ?> <span style="font-size:0.5em; color:gray">#<?php echo $ticket['id']; ?></span></h1>
        
        <span class="status-<?php echo $ticket['status']; ?>" style="font-size: 1.2em; border: 1px solid #ddd; padding: 5px 10px; border-radius: 20px;">
            <?php echo strtoupper($ticket['status']); ?>
        </span>
        
        <p style="margin-top: 15px;"><strong>Descrizione:</strong><br><?php echo htmlspecialchars($ticket['description']); ?></p>
        
        <?php if($ticket['attachment_path']): ?>
            <p>📎 Allegato: <a href="<?php echo $ticket['attachment_path']; ?>" target="_blank">Vedi File</a></p>
        <?php endif; ?>
    </div>

    <?php if($_SESSION['user_role'] == 'admin'): ?>
        <div style="background: #2c3e50; color: white; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
            <form method="POST" style="display:flex; align-items:center; gap: 10px;">
                <label>Gestione Ticket:</label>
                <select name="new_status" style="width: auto; margin: 0;">
                    <option value="open" <?php echo ($ticket['status']=='open')?'selected':''; ?>>Aperto</option>
                    <option value="in-progress" <?php echo ($ticket['status']=='in-progress')?'selected':''; ?>>In Lavorazione</option>
                    <option value="resolved" <?php echo ($ticket['status']=='resolved')?'selected':''; ?>>Risolto</option>
                    <option value="closed" <?php echo ($ticket['status']=='closed')?'selected':''; ?>>Chiuso (Blocca Chat)</option>
                </select>
                <button type="submit" style="padding: 8px 15px; font-size: 0.9em;">Aggiorna Stato</button>
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
            <button type="submit" style="margin-top: 10px; width: 100%;">Invia Risposta</button>
        </form>
    <?php else: ?>
        <div class="closed-notice">
            🔒 Questo ticket è stato chiuso. La conversazione è terminata.
            <?php if($_SESSION['user_role'] == 'admin'): ?>
                <br><small>(Per rispondere, riapri il ticket dal menu in alto)</small>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

</body>
</html>