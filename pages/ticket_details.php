<?php
session_start();

// Se l'utente non è loggato, reindirizza al login
if (!isset($_SESSION['user_id'])) {
    // Usa ../ perché auth.php è nella cartella superiore (root)
    header("Location: ../auth.php");
    exit;
}
?>

<?php
// FILE: pages/ticket_details.php
if (!isset($_GET['id'])) echo "<script>window.location='index.php';</script>";
$id = intval($_GET['id']);

// --- LOGICA POST (Nuovi Messaggi e Cambio Stato) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Inserimento Messaggio
    if (isset($_POST['msg_text']) && !empty(trim($_POST['msg_text']))) {
        $msg = pg_escape_string($db_conn, trim($_POST['msg_text']));
        $uid = $_SESSION['user_id'];
        
        // Verifica che il ticket non sia chiuso prima di inserire
        $check = pg_fetch_assoc(pg_query($db_conn, "SELECT status FROM tickets WHERE id=$id"));
        if ($check['status'] != 'closed') {
            pg_query($db_conn, "INSERT INTO messages (ticket_id, user_id, message) VALUES ($id, $uid, '$msg')");
        }
    }
    
    // 2. Cambio Stato (Solo Admin)
    if (isset($_POST['new_status']) && $_SESSION['user_role'] == 'admin') {
        $st = pg_escape_string($db_conn, $_POST['new_status']);
        pg_query($db_conn, "UPDATE tickets SET status = '$st' WHERE id = $id");
    }
}

// --- RECUPERO DATI TICKET ---
$query_ticket = "SELECT t.*, u.name as author_name, u.email as author_email 
                 FROM tickets t 
                 JOIN users u ON t.user_id = u.id 
                 WHERE t.id = $id";
$ticket = pg_fetch_assoc(pg_query($db_conn, $query_ticket));

// Sicurezza: Se non sei admin e il ticket non è tuo, via.
if ($_SESSION['user_role'] != 'admin' && $ticket['user_id'] != $_SESSION['user_id']) {
    echo "<script>window.location='index.php';</script>"; exit;
}

// --- RECUPERO MESSAGGI ---
$query_msgs = "SELECT m.*, u.name, u.role 
               FROM messages m 
               JOIN users u ON m.user_id = u.id 
               WHERE ticket_id = $id 
               ORDER BY m.created_at ASC";
$msgs = pg_query($db_conn, $query_msgs);

// --- RECUPERO ALLEGATI (Dal DB) ---
$query_att = "SELECT id, file_name FROM ticket_attachments WHERE ticket_id = $id";
$res_att = pg_query($db_conn, $query_att);
?>

<style>
    /* Layout Chat a 2 Colonne */
    .chat-layout { display: flex; gap: 25px; height: calc(100vh - 140px); }
    
    /* Colonna Sinistra (Info) */
    .info-panel { 
        width: 320px; flex-shrink: 0; background: white; 
        border: 1px solid #e2e8f0; border-radius: 16px; 
        padding: 25px; display: flex; flex-direction: column; 
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        overflow-y: auto;
    }

    /* Colonna Destra (Chat) */
    .chat-panel { 
        flex: 1; background: white; 
        border: 1px solid #e2e8f0; border-radius: 16px; 
        display: flex; flex-direction: column; overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }

    /* Lista Messaggi */
    .messages-area { flex: 1; overflow-y: auto; padding: 25px; background: #f8fafc; display: flex; flex-direction: column; gap: 15px; }
    
    .msg-bubble { 
        max-width: 70%; padding: 15px 18px; border-radius: 18px; 
        font-size: 0.95rem; line-height: 1.5; position: relative; 
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    
    .msg-user { 
        align-self: flex-start; background: white; border: 1px solid #e2e8f0; 
        border-bottom-left-radius: 4px; 
    }
    .msg-me { 
        align-self: flex-end; background: var(--primary); color: white; 
        border-bottom-right-radius: 4px; 
    }
    .msg-admin { 
        align-self: flex-start; background: #fef3c7; border: 1px solid #fcd34d; color: #92400e; /* Giallo per Admin se non sono io */
        border-bottom-left-radius: 4px;
    }

    .msg-meta { font-size: 0.75rem; margin-bottom: 5px; opacity: 0.8; font-weight: 600; display: block; }
    
    /* Area Input */
    .input-area { 
        padding: 20px; background: white; border-top: 1px solid #e2e8f0; 
        display: flex; gap: 15px; align-items: flex-end; 
    }
    .chat-input { 
        flex: 1; padding: 12px 15px; border: 2px solid #e2e8f0; border-radius: 12px; 
        outline: none; transition: 0.2s; font-family: inherit; resize: none; height: 50px;
    }
    .chat-input:focus { border-color: var(--primary); height: 80px; }
    
    .send-btn { 
        width: 50px; height: 50px; background: var(--primary); color: white; 
        border-radius: 12px; border: none; cursor: pointer; display: flex; 
        align-items: center; justify-content: center; font-size: 1.2rem; transition: 0.2s; 
    }
    .send-btn:hover { background: var(--primary-dark); transform: scale(1.05); }

    /* Info Styles */
    .info-label { font-size: 0.75rem; color: #64748b; text-transform: uppercase; font-weight: 700; margin-bottom: 5px; }
    .info-value { font-size: 0.95rem; font-weight: 500; color: #1e293b; margin-bottom: 20px; }
    .status-select { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1; background: #f8fafc; cursor: pointer; }

    /* Allegato */
    .attachment-box {
        display: flex; align-items: center; gap: 10px; padding: 10px;
        background: #f1f5f9; border-radius: 8px; text-decoration: none;
        color: var(--primary); font-weight: 600; font-size: 0.9rem;
        transition: 0.2s; border: 1px solid transparent;
    }
    .attachment-box:hover { background: #e0e7ff; border-color: var(--primary-light); }

    @media (max-width: 900px) {
        .chat-layout { flex-direction: column; height: auto; }
        .info-panel { width: 100%; order: -1; }
        .messages-area { height: 400px; }
    }
</style>

<div style="display:flex; align-items:center; gap:15px; margin-bottom:15px;">
    <a href="index.php?page=all_tickets" class="icon-btn" style="background:white; border:1px solid #e2e8f0;">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h2 style="margin:0; font-size:1.5rem;">Ticket #<?php echo $ticket['id']; ?></h2>
        <p style="margin:0; font-size:0.9rem; color:var(--text-muted);"><?php echo htmlspecialchars($ticket['title']); ?></p>
    </div>
</div>

<div class="chat-layout">
    
    <div class="info-panel">
        
        <div class="info-label">Stato Attuale</div>
        <div class="info-value">
            <?php 
                $st = $ticket['status'];
                $badge = 'badge-open'; 
                if($st=='resolved') $badge='badge-resolved';
                if($st=='closed') $badge='badge-closed';
            ?>
            <span class="badge <?php echo $badge; ?>"><?php echo strtoupper($st); ?></span>
        </div>

        <?php if($_SESSION['user_role'] == 'admin'): ?>
            <div style="background:#f8fafc; padding:15px; border-radius:12px; border:1px solid #e2e8f0; margin-bottom:20px;">
                <form method="POST">
                    <div class="info-label" style="margin-bottom:8px;">Cambia Stato</div>
                    <select name="new_status" class="status-select" onchange="this.form.submit()">
                        <option value="open" <?php echo $st=='open'?'selected':''; ?>>🟢 Aperto</option>
                        <option value="resolved" <?php echo $st=='resolved'?'selected':''; ?>>✅ Risolto</option>
                        <option value="closed" <?php echo $st=='closed'?'selected':''; ?>>🔒 Chiuso</option>
                    </select>
                </form>
            </div>
        <?php endif; ?>

        <div class="info-label">Autore</div>
        <div class="info-value">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:30px; height:30px; background:var(--primary); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.8rem;">
                    <?php echo strtoupper(substr($ticket['author_name'], 0, 1)); ?>
                </div>
                <div>
                    <div><?php echo htmlspecialchars($ticket['author_name']); ?></div>
                    <div style="font-size:0.75rem; color:var(--text-muted);"><?php echo htmlspecialchars($ticket['author_email']); ?></div>
                </div>
            </div>
        </div>

        <div class="info-label">Categoria & Priorità</div>
        <div class="info-value">
            <?php echo $ticket['category']; ?> • 
            <span style="color:<?php echo ($ticket['priority']=='high'||$ticket['priority']=='urgent')?'#ef4444':'inherit'; ?>">
                <?php echo ucfirst($ticket['priority']); ?>
            </span>
        </div>

        <div class="info-label">Data Creazione</div>
        <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($ticket['created_at'])); ?></div>

        <hr style="border:0; border-top:1px solid #e2e8f0; margin:10px 0 20px 0;">

        <div class="info-label">Descrizione Problema</div>
        <div style="font-size:0.9rem; color:#334155; line-height:1.6; margin-bottom:20px;">
            <?php echo nl2br(htmlspecialchars($ticket['description'])); ?>
        </div>

        <?php if (pg_num_rows($res_att) > 0): ?>
            <div class="info-label">Allegati</div>
            <div style="display:flex; flex-direction:column; gap:8px;">
                <?php while($att = pg_fetch_assoc($res_att)): ?>
                    <a href="index.php?page=view_attachment&id=<?php echo $att['id']; ?>" target="_blank" class="attachment-box">
                        <i class="fas fa-paperclip"></i> 
                        <span style="flex:1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            <?php echo htmlspecialchars($att['file_name']); ?>
                        </span>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

    </div>

    <div class="chat-panel">
        
        <div class="messages-area" id="msgArea">
            <?php if (pg_num_rows($msgs) == 0): ?>
                <div style="text-align:center; color:#94a3b8; margin-top:50px;">
                    <i class="far fa-comments" style="font-size:3rem; margin-bottom:15px; opacity:0.5;"></i>
                    <p>Nessun messaggio ancora.<br>Inizia la conversazione!</p>
                </div>
            <?php endif; ?>

            <?php while($m = pg_fetch_assoc($msgs)): 
                $is_me = ($m['user_id'] == $_SESSION['user_id']);
                $is_admin_msg = ($m['role'] == 'admin');
                
                // Determina stile
                if ($is_me) {
                    $cls = 'msg-me'; $author = 'Tu';
                } elseif ($is_admin_msg) {
                    $cls = 'msg-admin'; $author = $m['name'] . ' (Staff)';
                } else {
                    $cls = 'msg-user'; $author = $m['name'];
                }
            ?>
                <div class="msg-bubble <?php echo $cls; ?>">
                    <span class="msg-meta"><?php echo $author; ?> • <?php echo date('H:i', strtotime($m['created_at'])); ?></span>
                    <?php echo nl2br(htmlspecialchars($m['message'])); ?>
                </div>
            <?php endwhile; ?>
        </div>

        <?php if($ticket['status'] != 'closed'): ?>
            <form method="POST" class="input-area">
                <textarea name="msg_text" class="chat-input" placeholder="Scrivi una risposta..." required></textarea>
                <button type="submit" class="send-btn"><i class="fas fa-paper-plane"></i></button>
            </form>
        <?php else: ?>
            <div style="padding:20px; text-align:center; background:#f1f5f9; color:#64748b; font-weight:600; border-top:1px solid #e2e8f0;">
                <i class="fas fa-lock"></i> Questo ticket è chiuso. Non puoi rispondere.
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
    // Scroll automatico in fondo alla chat
    const msgArea = document.getElementById('msgArea');
    msgArea.scrollTop = msgArea.scrollHeight;
</script>