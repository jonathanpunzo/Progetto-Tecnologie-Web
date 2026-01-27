<?php
session_start();
require_once('db.php');

$user_name = $_SESSION['user_name'];
$role = $_SESSION['user_role'];

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

    // --- 1. GESTIONE FILE LATO SERVER (SICUREZZA VERA) ---
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir); 

        // Whitelist Estensioni
        $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];
        $file_info = pathinfo($_FILES['attachment']['name']);
        $ext = strtolower($file_info['extension']);
        $max_size = 2 * 1024 * 1024; // 2MB

        if (!in_array($ext, $allowed_ext)) {
            $msg = "Errore: Estensione non consentita! (Solo JPG, PNG, PDF)";
        } 
        elseif ($_FILES['attachment']['size'] > $max_size) {
            $msg = "Errore: File troppo grande (Max 2MB).";
        } 
        else {
            $clean_name = preg_replace("/[^a-zA-Z0-9\._-]/", "", basename($_FILES['attachment']['name']));
            $file_name = time() . "_" . $clean_name;
            $target_file = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_file)) {
                $attachment_path = $target_file;
            } else {
                $msg = "Errore nel salvataggio del file.";
            }
        }
    }

    // --- 2. INSERIMENTO NEL DB ---
    if (empty($msg)) {
        $path_sql = $attachment_path ? "'$attachment_path'" : "NULL";
        $query = "INSERT INTO tickets (user_id, title, description, priority, category, attachment_path) 
                  VALUES ($user_id, '$title', '$desc', '$priority', '$category', $path_sql)";

        if (pg_query($db_conn, $query)) {
            header("Location: index.php"); 
            exit;
        } else {
            $msg = "Errore Database: " . pg_last_error($db_conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <link rel="icon" type="image/png" href="icon/favicon.png">
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
    <a href="index.php" style="display:flex; align-items:center;">
        <img src="icon/logobanner.png" alt="Logo" class="brand-logo-img">
    </a>
</nav>

<div class="container" style="max-width: 600px;">
    <h2>🎫 Apri una nuova segnalazione</h2>
    
    <?php if($msg): ?> 
        <div style="color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <strong>Attenzione:</strong> <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <form action="new_ticket.php" method="POST" enctype="multipart/form-data" onsubmit="return validateTicket()">
        
        <div class="form-group">
            <label>Oggetto del problema</label>
            <input type="text" id="title" name="title" required placeholder="Es. Il PC non si accende" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
        </div>

        <div class="form-group">
            <label>Categoria</label>
            <select name="category" id="category">
                <option value="">-- Seleziona Categoria --</option>
                <option value="Hardware" <?php echo (isset($_POST['category']) && $_POST['category']=='Hardware') ? 'selected' : ''; ?>>Hardware</option>
                <option value="Software" <?php echo (isset($_POST['category']) && $_POST['category']=='Software') ? 'selected' : ''; ?>>Software</option>
                <option value="Rete" <?php echo (isset($_POST['category']) && $_POST['category']=='Rete') ? 'selected' : ''; ?>>Problemi di Rete</option>
                <option value="Account" <?php echo (isset($_POST['category']) && $_POST['category']=='Account') ? 'selected' : ''; ?>>Account e Password</option>
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
            <textarea id="description" name="description" rows="5" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
        </div>

        <label>Allegato (Max 2MB - Solo PDF, JPG, PNG)</label>
        
        <div class="drop-zone" id="dropZone">
            <p>📂 Trascina qui il file oppure clicca per selezionarlo</p>
            <span id="fileName" style="font-size: 0.9em; font-weight: bold;"></span>
        </div>

        <input type="file" name="attachment" id="fileInput" accept=".jpg, .jpeg, .png, .pdf">

        <button type="submit" class="btn-style" style="width: 100%;">Invia Ticket</button>
        <p style="text-align: center; margin-top: 15px;"><a href="index.php">Annulla</a></p>
    </form>
</div>

<footer class="main-footer">
    <p>Made with ❤️ da: <strong>iFantastici4</strong></p>
    <a href="chi_siamo.php" class="btn-style">Chi Siamo</a>
</footer>

<script>
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const fileNameDisplay = document.getElementById('fileName');

    // Click sulla zona -> apre il selettore file (che ora è filtrato grazie ad 'accept')
    dropZone.addEventListener('click', () => fileInput.click());

    // Effetti grafici Drag & Drop
    dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('dragover'); });
    dropZone.addEventListener('dragleave', () => { dropZone.classList.remove('dragover'); });

    // Rilascio file (Drag & Drop)
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        if (e.dataTransfer.files.length > 0) {
            // Controlliamo il file PRIMA di accettarlo
            validateAndShowFile(e.dataTransfer.files[0]);
        }
    });

    // Selezione manuale
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
            validateAndShowFile(fileInput.files[0]);
        }
    });

    // VALIDAZIONE JS (Blocca estensioni e dimensioni proibite)
    function validateAndShowFile(file) {
        // 1. Controllo Estensione
        const allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
        const fileExt = file.name.split('.').pop().toLowerCase();

        if (!allowedExtensions.includes(fileExt)) {
            alert("❌ File non valido! Puoi caricare solo immagini (JPG, PNG) o PDF.");
            resetInput();
            return;
        }

        // 2. Controllo Dimensione (Max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert("❌ File troppo pesante! Il limite è 2MB.");
            resetInput();
            return;
        }

        // Se tutto ok:
        // Se arrivava dal drag & drop, dobbiamo passarlo all'input file manualmente
        // (Nota: per sicurezza i browser moderni limitano questa azione, ma per l'input nascosto funziona spesso)
        if (fileInput.files[0] !== file) {
            // Trick per passare il file dal drag all'input (funziona sui browser moderni)
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            fileInput.files = dataTransfer.files;
        }

        fileNameDisplay.innerText = "✅ File pronto: " + file.name;
        fileNameDisplay.style.color = "green";
    }

    function resetInput() {
        fileInput.value = ""; // Svuota l'input
        fileNameDisplay.innerText = "";
    }

    // Validazione finale al submit (già presente, ma per sicurezza)
    function validateTicket() {
        var title = document.getElementById('title').value;
        var cat = document.getElementById('category').value;
        var desc = document.getElementById('description').value;

        if (title.length < 5) { alert("L'oggetto è troppo corto."); return false; }
        if (cat === "") { alert("Devi selezionare una categoria."); return false; }
        if (desc.length < 10) { alert("La descrizione è troppo breve."); return false; }
        return true; 
    }
</script>

</body>
</html>