<?php
// FILE: pages/new_ticket.php

$msg = "";
// LOGICA SALVATAGGIO TICKET
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_ticket'])) {
    $title = pg_escape_string($db_conn, $_POST['title']);
    $desc = pg_escape_string($db_conn, $_POST['description']);
    $category = $_POST['category'];
    $priority = $_POST['priority'];
    $user_id = $_SESSION['user_id'];
    $attachment_path = "NULL";

    // Gestione File (Semplificata)
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
        $clean_name = time() . "_" . preg_replace("/[^a-zA-Z0-9\._-]/", "", basename($_FILES['attachment']['name']));
        $target = "uploads/" . $clean_name;
        if (!is_dir('uploads')) mkdir('uploads');
        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target)) {
            $attachment_path = "'$target'";
        }
    }

    $sql = "INSERT INTO tickets (user_id, title, description, priority, category, attachment_path) 
            VALUES ($user_id, '$title', '$desc', '$priority', '$category', $attachment_path)";
    
    if (pg_query($db_conn, $sql)) {
        // Redirect alla lista ticket dopo il salvataggio
        echo "<script>window.location.href='index.php?page=all_tickets';</script>";
        exit;
    } else {
        $msg = "Errore durante il salvataggio.";
    }
}
?>

<div class="page-container" style="max-width: 800px; margin: 0 auto;">
    <div class="table-card"> <h2>🎫 Nuovo Ticket</h2>
        <p style="margin-bottom: 20px; color: var(--text-muted);">Descrivi il problema per ricevere assistenza.</p>

        <?php if($msg): ?><div style="color:red; margin-bottom:10px;"><?php echo $msg; ?></div><?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="create_ticket" value="1">
            
            <label style="font-weight:bold; display:block; margin-top:15px;">Oggetto</label>
            <input type="text" name="title" required placeholder="Es. PC non si accende" 
                   style="width:100%; padding:10px; margin-top:5px; border:1px solid #ddd; border-radius:8px;">

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top:15px;">
                <div>
                    <label style="font-weight:bold;">Categoria</label>
                    <select name="category" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; margin-top:5px;">
                        <option value="Hardware">Hardware</option>
                        <option value="Software">Software</option>
                        <option value="Rete">Rete</option>
                        <option value="Account">Account</option>
                    </select>
                </div>
                <div>
                    <label style="font-weight:bold;">Priorità</label>
                    <select name="priority" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; margin-top:5px;">
                        <option value="low">Bassa</option>
                        <option value="medium" selected>Media</option>
                        <option value="high">Alta</option>
                        <option value="urgent">Urgente</option>
                    </select>
                </div>
            </div>

            <label style="font-weight:bold; display:block; margin-top:15px;">Descrizione</label>
            <textarea name="description" rows="5" required 
                      style="width:100%; padding:10px; margin-top:5px; border:1px solid #ddd; border-radius:8px;"></textarea>

            <label style="font-weight:bold; display:block; margin-top:15px;">Allegato (Opzionale)</label>
            <input type="file" name="attachment" style="margin-top:5px;">

            <div style="margin-top: 30px; text-align: right;">
                <a href="index.php" style="margin-right: 15px; color: #666;">Annulla</a>
                <button type="submit" style="background: var(--primary); color: white; border: none; padding: 10px 25px; border-radius: 20px; font-weight: bold; cursor: pointer;">
                    Invia Segnalazione
                </button>
            </div>
        </form>
    </div>
</div>