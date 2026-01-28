<?php
// FILE: pages/ticket_details.php
$id = intval($_GET['id']);
if(!$id) echo "<script>window.location='index.php';</script>";

// LOGICA POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['msg_text'])) {
        $msg = pg_escape_string($db_conn, $_POST['msg_text']);
        $uid = $_SESSION['user_id'];
        pg_query($db_conn, "INSERT INTO messages (ticket_id, user_id, message) VALUES ($id, $uid, '$msg')");
    }
    if (isset($_POST['new_status']) && $_SESSION['user_role'] == 'admin') {
        $st = $_POST['new_status'];
        pg_query($db_conn, "UPDATE tickets SET status = '$st' WHERE id = $id");
    }
}

// RECUPERO DATI
$ticket = pg_fetch_assoc(pg_query($db_conn, "SELECT t.*, u.name FROM tickets t JOIN users u ON t.user_id = u.id WHERE t.id = $id"));
$msgs = pg_query($db_conn, "SELECT m.*, u.name, u.role FROM messages m JOIN users u ON m.user_id = u.id WHERE ticket_id = $id ORDER BY m.created_at ASC");

if(!$ticket) echo "Ticket non trovato.";
?>

<style>
    .chat-container { display: flex; flex-direction: column; gap: 20px; height: calc(100vh - 160px); }
    .chat-main { flex: 1; display: flex; gap: 20px; overflow: hidden; }
    .ticket-info { width: 300px; background: white; padding: 20px; border-radius: 12px; height: fit-content; border: 1px solid #f1f5f9; }
    .chat-area { flex: 1; background: white; border-radius: 12px; display: flex; flex-direction: column; border: 1px solid #f1f5f9; }
    
    .messages-list { flex: 1; overflow-y: auto; padding: 20px; background: #f8fafc; }
    .msg-bubble { max-width: 75%; padding: 12px 16px; border-radius: 12px; margin-bottom: 10px; font-size: 0.95rem; line-height: 1.5; position: relative; }
    
    .msg-user { background: white; border: 1px solid #e2e8f0; margin-right: auto; border-top-left-radius: 0; }
    .msg-admin { background: #e0e7ff; color: #3730a3; margin-left: auto; border-top-right-radius: 0; }
    
    .chat-input-area { padding: 15px; border-top: 1px solid #f1f5f9; display: flex; gap: 10px; background: white; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px; }
    .meta-date { font-size: 0.75rem; opacity: 0.7; display: block; margin-bottom: 4px; }
</style>

<div class="chat-container">
    
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2 style="margin:0;">#<?php echo $ticket['id']; ?> - <?php echo htmlspecialchars($ticket['title']); ?></h2>
        <a href="index.php?page=all_tickets" class="icon-btn" style="background:#ddd; padding:5px 10px; border-radius:5px; font-size:0.9rem;">&larr; Indietro</a>
    </div>

    <div class="chat-main">
        <div class="ticket-info">
            <h4 style="margin-top:0; color:var(--text-muted)">Dettagli</h4>
            <p><strong>Autore:</strong> <?php echo $ticket['name']; ?></p>
            
            <p><strong>Stato:</strong> 
                <?php 
                    $st = $ticket['status'];
                    $badge_cls = 'badge-open'; // Arancio
                    if($st == 'resolved') $badge_cls = 'badge-resolved'; // Verde
                    if($st == 'closed')   $badge_cls = 'badge-closed';   // Rosso
                ?>
                <span class="badge <?php echo $badge_cls; ?>"><?php echo strtoupper($st); ?></span>
            </p>

            <p><strong>Priorità:</strong> <?php echo ucfirst($ticket['priority']); ?></p>
            <p><strong>Data:</strong> <?php echo date('d/m/Y', strtotime($ticket['created_at'])); ?></p>
            <hr style="margin: 15px 0; border:0; border-top:1px solid #eee;">
            <p style="font-size:0.9rem; color:#555;">
                <?php echo nl2br(htmlspecialchars($ticket['description'])); ?>
            </p>
            <?php if($ticket['attachment_path']): ?>
                <a href="<?php echo $ticket['attachment_path']; ?>" target="_blank" style="display:block; margin-top:10px; color:var(--primary); font-size:0.9rem;">📎 Vedi Allegato</a>
            <?php endif; ?>

            <?php if($_SESSION['user_role'] == 'admin'): ?>
                <hr style="margin: 15px 0; border:0; border-top:1px solid #eee;">
                <form method="POST">
                    <label style="font-size:0.8rem; font-weight:bold;">Cambia Stato:</label>
                    <select name="new_status" onchange="this.form.submit()" style="width:100%; padding:8px; margin-top:5px; border-radius:5px; border:1px solid #ccc;">
                        <option value="open" <?php echo $ticket['status']=='open'?'selected':''; ?>>Aperto</option>
                        <option value="resolved" <?php echo $ticket['status']=='resolved'?'selected':''; ?>>Risolto</option>
                        <option value="closed" <?php echo $ticket['status']=='closed'?'selected':''; ?>>Chiuso</option>
                    </select>
                </form>
            <?php endif; ?>
        </div>

        <div class="chat-area">
            <div class="messages-list">
                <?php while($m = pg_fetch_assoc($msgs)): 
                    $is_me = ($m['user_id'] == $_SESSION['user_id']);
                    $style_class = $is_me ? 'msg-admin' : 'msg-user'; 
                ?>
                <div class="msg-bubble <?php echo $style_class; ?>">
                    <span class="meta-date"><?php echo htmlspecialchars($m['name']); ?> • <?php echo date('H:i', strtotime($m['created_at'])); ?></span>
                    <?php echo nl2br(htmlspecialchars($m['message'])); ?>
                </div>
                <?php endwhile; ?>
            </div>
            
            <?php if($ticket['status'] != 'closed'): ?>
            <form method="POST" class="chat-input-area">
                <input type="text" name="msg_text" required placeholder="Scrivi un messaggio..." autocomplete="off"
                       style="flex:1; padding:10px; border:1px solid #e2e8f0; border-radius:8px; outline:none;">
                <button type="submit" style="background:var(--primary); color:white; border:none; padding:0 20px; border-radius:8px; cursor:pointer;">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
            <?php else: ?>
                <div style="padding:15px; text-align:center; background:#fee2e2; color:#991b1b;">Ticket chiuso. Non puoi rispondere.</div>
            <?php endif; ?>
        </div>
    </div>
</div>