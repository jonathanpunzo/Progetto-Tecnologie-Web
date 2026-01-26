<?php
session_start();
require_once('db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = pg_escape_string($db_conn, $_POST['title']);
    $desc = pg_escape_string($db_conn, $_POST['description']);
    $category = $_POST['category'];
    $priority = $_POST['priority'];
    $user_id = $_SESSION['user_id'];
    
    $attachment_path = NULL;

    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir); // Crea cartella se non esiste
        
        $file_name = time() . "_" . basename($_FILES['attachment']['name']);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_file)) {
            $attachment_path = $target_file;
        } else {
            $msg = "Errore nel caricamento del file.";
        }
    }

    $query = "INSERT INTO tickets (user_id, title, description, priority, category, attachment_path) 
              VALUES ($user_id, '$title', '$desc', '$priority', '$category', '$attachment_path')"; // Nota: per il path NULL, Postgres gestisce

    if (pg_query($db_conn, $query)) {
        header("Location: index.php"); 
        exit;
    } else {
        $msg = "Errore Database: " . pg_last_error($db_conn);
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <link rel="icon" type="image/png" href="favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuovo Ticket - HelpDesk</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .drop-zone {
            border: 2px dashed #ccc; border-radius: 10px; padding: 40px;
            text-align: center; color: #666; cursor: pointer;
            transition: background 0.3s; background: #fafafa; margin-bottom: 20px;
        }
        .drop-zone.dragover { border-color: #1a73e8; background: #e8f0fe; color: #1a73e8; }
        #fileInput { display: none; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], select, textarea { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 5px; }
    </style>
</head>
<body>

<nav>
    <div class="logo">supporto<strong>iFantastici4</strong></div>
    <div class="menu">
        <a href="index.php">Home</a>
    </div>
</nav>

<div class="container" style="max-width: 600px;">
    <h2>🎫 Apri una nuova segnalazione</h2>
    
    <?php if($msg) echo "<p style='color:red'>$msg</p>"; ?>

    <form action="new_ticket.php" method="POST" enctype="multipart/form-data" onsubmit="return validateTicket()">
        
        <div class="form-group">
            <label>Oggetto del problema</label>
            <input type="text" id="title" name="title" required placeholder="Es. Il PC non si accende">
        </div>

        <div class="form-group">
            <label>Categoria</label>
            <select name="category" id="category">
                <option value="">-- Seleziona Categoria --</option>
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
            <textarea id="description" name="description" rows="5" required></textarea>
        </div>

        <label>Allegato (Max 2MB)</label>
        <div class="drop-zone" id="dropZone">
            <p>📂 Trascina qui il file oppure clicca per selezionarlo</p>
            <span id="fileName" style="font-size: 0.9em; font-weight: bold;"></span>
        </div>
        <input type="file" name="attachment" id="fileInput">

        <button type="submit" class="btn-style" style="width: 100%;">Invia Ticket</button>
        <p style="text-align: center; margin-top: 15px;"><a href="index.php">Annulla</a></p>
    </form>
</div>

<footer class="main-footer">
    <p>
        Made with <span class="heart-beat">❤️</span> da: 
        <strong>Mattia Letteriello</strong>, 
        <strong>Jonathan Punzo</strong>, 
        <strong>Antonia Lucia Lamberti</strong>, 
        <strong>Valentino Potapchuk</strong>.
    </p>
    <p style="opacity: 0.8; font-size: 0.85em;">Esame di Tecnologie Web 2025/2026</p>
    <a href="chi_siamo.php" class="btn-style">Chi Siamo</a>
</footer>

<script>
    // 1. GESTIONE DRAG & DROP
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const fileNameDisplay = document.getElementById('fileName');

    dropZone.addEventListener('click', () => fileInput.click());

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('dragover');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('dragover');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        if (e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            validateAndShowFile(fileInput.files[0]);
        }
    });

    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
            validateAndShowFile(fileInput.files[0]);
        }
    });

    function validateAndShowFile(file) {
        // Controllo Dimensione (Max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert("Il file è troppo grande! Max 2MB.");
            fileInput.value = ""; // Resetta input
            fileNameDisplay.innerText = "";
        } else {
            fileNameDisplay.innerText = "✅ File pronto: " + file.name;
        }
    }

    // 2. NUOVA VALIDAZIONE FORM (Punto 92 Linee Guida)
    function validateTicket() {
        var title = document.getElementById('title').value;
        var cat = document.getElementById('category').value;
        var desc = document.getElementById('description').value;

        if (title.length < 5) {
            alert("L'oggetto è troppo corto. Sii più specifico.");
            return false; // Blocca invio
        }

        if (cat === "") {
            alert("Devi selezionare una categoria.");
            return false;
        }

        if (desc.length < 10) {
            alert("La descrizione è troppo breve. Spiega meglio il problema.");
            return false;
        }

        return true; // Tutto ok, invia
    }
</script>

</body>
</html>