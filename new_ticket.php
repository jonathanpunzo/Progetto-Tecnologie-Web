<?php
session_start();
require_once('db.php');

// Se non sei loggato, via di qui!
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

$msg = "";

// LOGICA DI SALVATAGGIO (Quando premi "Invia Ticket")
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = pg_escape_string($db_conn, $_POST['title']);
    $desc = pg_escape_string($db_conn, $_POST['description']);
    $category = $_POST['category'];
    $priority = $_POST['priority'];
    $user_id = $_SESSION['user_id'];
    
    $attachment_path = NULL;

    // GESTIONE FILE UPLOAD
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
        $upload_dir = 'uploads/';
        $file_name = time() . "_" . basename($_FILES['attachment']['name']); // Aggiungo timestamp per evitare nomi duplicati
        $target_file = $upload_dir . $file_name;

        // Spostiamo il file dalla cartella temporanea alla nostra cartella
        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_file)) {
            $attachment_path = $target_file;
        } else {
            $msg = "Errore nel caricamento del file.";
        }
    }

    // INSERIMENTO NEL DATABASE
    $query = "INSERT INTO tickets (user_id, title, description, priority, category, attachment_path) 
              VALUES ($user_id, '$title', '$desc', '$priority', '$category', '$attachment_path')"; // Nota: se path è nullo, PostgreSQL capisce

    if (pg_query($db_conn, $query)) {
        header("Location: index.php"); // Successo! Torna alla home
        exit;
    } else {
        $msg = "Errore Database: " . pg_last_error($db_conn);
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Nuovo Ticket</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* STILE PER L'AREA DRAG & DROP */
        .drop-zone {
            border: 2px dashed #ccc;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            color: #666;
            cursor: pointer;
            transition: background 0.3s, border-color 0.3s;
            background: #fafafa;
            margin-bottom: 20px;
        }
        /* Quando trascini il file sopra */
        .drop-zone.dragover {
            border-color: #1a73e8;
            background: #e8f0fe;
            color: #1a73e8;
        }
        /* Nascondiamo il vero input file brutto */
        #fileInput { display: none; }
        
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], select, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; }
        button { background: #1a73e8; color: white; border: none; padding: 10px 20px; cursor: pointer; font-size: 16px; border-radius: 5px; }
    </style>
</head>
<body>

<div class="container" style="max-width: 600px; margin: 40px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <h2>🎫 Apri una nuova segnalazione</h2>
    
    <?php if($msg) echo "<p style='color:red'>$msg</p>"; ?>

    <form action="new_ticket.php" method="POST" enctype="multipart/form-data">
        
        <div class="form-group">
            <label>Oggetto del problema</label>
            <input type="text" name="title" required placeholder="Es. Il PC non si accende">
        </div>

        <div class="form-group">
            <label>Categoria</label>
            <select name="category">
                <option value="Hardware">Hardware</option>
                <option value="Software">Software</option>
                <option value="Rete">Problemi di Rete</option>
                <option value="Account">Account e Password</option>
            </select>
        </div>

        <div class="form-group">
            <label>Priorità</label>
            <select name="priority">
                <option value="low">Bassa</option>
                <option value="medium" selected>Media</option>
                <option value="high">Alta</option>
                <option value="urgent">Urgente</option>
            </select>
        </div>

        <div class="form-group">
            <label>Descrizione Dettagliata</label>
            <textarea name="description" rows="5" required></textarea>
        </div>

        <label>Allegato (Screenshot o Log)</label>
        <div class="drop-zone" id="dropZone">
            <p>📂 Trascina qui il file oppure clicca per selezionarlo</p>
            <span id="fileName" style="font-size: 0.9em; font-weight: bold;"></span>
        </div>
        <input type="file" name="attachment" id="fileInput">

        <button type="submit" style="width: 100%;">Invia Ticket</button>
        <p style="text-align: center;"><a href="index.php">Annulla</a></p>
    </form>
</div>

<script>
    // SCRIPT JAVASCRIPT PER GESTIRE IL DRAG & DROP
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const fileNameDisplay = document.getElementById('fileName');

    // 1. Cliccare sulla zona apre il selettore file classico
    dropZone.addEventListener('click', () => fileInput.click());

    // 2. Quando un file viene trascinato sopra
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault(); // Impedisce al browser di aprire il file da solo
        dropZone.classList.add('dragover');
    });

    // 3. Quando il file esce dalla zona (senza essere lasciato)
    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('dragover');
    });

    // 4. Quando il file viene RILASCIATO (Drop)
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');

        // Prendiamo i file trascinati
        if (e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files; // Assegniamo i file all'input nascosto
            fileNameDisplay.innerText = "✅ File selezionato: " + e.dataTransfer.files[0].name;
        }
    });

    // 5. Se l'utente usa il metodo classico (click), aggiorniamo il testo
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
            fileNameDisplay.innerText = "✅ File selezionato: " + fileInput.files[0].name;
        }
    });
</script>

</body>
</html>