<?php
// FILE: pages/new_ticket.php

$msg = "";
$msg_type = "";

// LOGICA SALVATAGGIO TICKET
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_ticket'])) {
    $title = pg_escape_string($db_conn, $_POST['title']);
    $desc = pg_escape_string($db_conn, $_POST['description']);
    $category = $_POST['category'];
    $priority = $_POST['priority'];
    $user_id = $_SESSION['user_id'];
    
    $saved_paths = []; // Array per salvare i percorsi caricati
    $upload_error = false;

    // Gestione File Multipli
    if (isset($_FILES['attachments']) && count($_FILES['attachments']['name']) > 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];
        $max_size = 2 * 1024 * 1024; // 2MB per file
        
        // Cicliamo su ogni file caricato
        $count = count($_FILES['attachments']['name']);
        
        for ($i = 0; $i < $count; $i++) {
            $name = $_FILES['attachments']['name'][$i];
            $tmp_name = $_FILES['attachments']['tmp_name'][$i];
            $size = $_FILES['attachments']['size'][$i];
            $error = $_FILES['attachments']['error'][$i];

            // Se il file è vuoto (nessun upload), saltiamo
            if ($error == UPLOAD_ERR_NO_FILE) continue;

            if ($error == 0) {
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                // Validazione
                if (!in_array($ext, $allowed_ext)) {
                    $msg = "❌ Il file '$name' non ha un formato valido (solo PDF, JPG, PNG).";
                    $msg_type = "error";
                    $upload_error = true;
                    break;
                }
                if ($size > $max_size) {
                    $msg = "❌ Il file '$name' è troppo grande (Max 2MB).";
                    $msg_type = "error";
                    $upload_error = true;
                    break;
                }

                // Upload
                $clean_name = time() . "_" . $i . "_" . preg_replace("/[^a-zA-Z0-9\._-]/", "", $name);
                $target = "uploads/" . $clean_name;
                
                if (!is_dir('uploads')) mkdir('uploads');

                if (move_uploaded_file($tmp_name, $target)) {
                    $saved_paths[] = $target;
                } else {
                    $msg = "❌ Errore nel caricamento del file '$name'.";
                    $msg_type = "error";
                    $upload_error = true;
                    break;
                }
            }
        }
    }

    // Se non ci sono errori bloccanti, salviamo nel DB
    if (!$upload_error) {
        // Uniamo i percorsi con un punto e virgola (es: uploads/1.jpg;uploads/2.png)
        $attachment_sql = empty($saved_paths) ? "NULL" : "'" . implode(';', $saved_paths) . "'";

        $sql = "INSERT INTO tickets (user_id, title, description, priority, category, attachment_path) 
                VALUES ($user_id, '$title', '$desc', '$priority', '$category', $attachment_sql)";
        
        if (pg_query($db_conn, $sql)) {
            echo "<script>window.location.href='index.php?page=all_tickets';</script>";
            exit;
        } else {
            $msg = "Errore Database: " . pg_last_error($db_conn);
            $msg_type = "error";
        }
    }
}
?>

<style>
    .upload-area {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 30px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #f8fafc;
    }
    .upload-area:hover, .upload-area.drag-over {
        border-color: var(--primary);
        background: #eef2ff;
        transform: scale(0.99);
    }
    .upload-icon { font-size: 2rem; color: #94a3b8; margin-bottom: 10px; transition: color 0.3s; }
    .upload-area:hover .upload-icon { color: var(--primary); }
    
    /* Lista File Caricati */
    .file-list {
        margin-top: 15px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .file-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: white;
        border: 1px solid #e2e8f0;
        padding: 10px 15px;
        border-radius: 8px;
        animation: fadeIn 0.3s ease;
    }
    .file-info { display: flex; align-items: center; gap: 10px; color: var(--text-main); font-size: 0.9rem; font-weight: 500; }
    .file-remove-btn {
        background: #fee2e2; color: #ef4444; border: none;
        width: 30px; height: 30px; border-radius: 50%;
        cursor: pointer; display: flex; align-items: center; justify-content: center;
        transition: all 0.2s;
    }
    .file-remove-btn:hover { background: #ef4444; color: white; transform: scale(1.1); }
    
    .msg-box { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
    .msg-error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    
    #filesInput { display: none; }
</style>

<div class="page-container" style="max-width: 800px; margin: 0 auto;">
    <div class="table-card">
        <h2>🎫 Nuovo Ticket</h2>
        <p style="margin-bottom: 25px; color: var(--text-muted);">Descrivi il problema per ricevere assistenza.</p>

        <?php if($msg): ?>
            <div class="msg-box <?php echo ($msg_type == 'error') ? 'msg-error' : ''; ?>">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" id="ticketForm">
            <input type="hidden" name="create_ticket" value="1">
            
            <label style="font-weight:bold; display:block; margin-bottom: 8px;">Oggetto</label>
            <input type="text" name="title" required placeholder="Es. Errore login" 
                   style="width:100%; padding:12px; margin-bottom:20px; border:1px solid #e2e8f0; border-radius:8px; background:#fff;">

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom:20px;">
                <div>
                    <label style="font-weight:bold; display:block; margin-bottom: 8px;">Categoria</label>
                    <select name="category" style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:8px; background:#fff;">
                        <option value="Hardware">Hardware</option>
                        <option value="Software">Software</option>
                        <option value="Rete">Rete</option>
                        <option value="Account">Account</option>
                    </select>
                </div>
                <div>
                    <label style="font-weight:bold; display:block; margin-bottom: 8px;">Priorità</label>
                    <select name="priority" style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:8px; background:#fff;">
                        <option value="low">Bassa</option>
                        <option value="medium" selected>Media</option>
                        <option value="high">Alta</option>
                        <option value="urgent">Urgente</option>
                    </select>
                </div>
            </div>

            <label style="font-weight:bold; display:block; margin-bottom: 8px;">Descrizione</label>
            <textarea name="description" rows="5" required placeholder="Dettagli del problema..."
                      style="width:100%; padding:12px; margin-bottom:25px; border:1px solid #e2e8f0; border-radius:8px; background:#fff; font-family:inherit;"></textarea>

            <label style="font-weight:bold; display:block; margin-bottom: 8px;">Allegati (Opzionale - Max 4MB ciascuno)</label>
            
            <div class="upload-area" id="dropArea">
                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                <div class="upload-text">Trascina qui i file o clicca per caricare</div>
                <div class="upload-hint">Puoi caricare più file (PDF, JPG, PNG)</div>
                <input type="file" name="attachments[]" id="filesInput" multiple accept=".jpg,.jpeg,.png,.pdf">
            </div>

            <div class="file-list" id="fileListContainer"></div>

            <div style="margin-top: 35px; text-align: right; display:flex; justify-content:flex-end; gap:15px; align-items:center;">
                <a href="index.php" style="color: var(--text-muted); font-weight:500;">Annulla</a>
                <button type="submit" style="background: var(--primary); color: white; border: none; padding: 12px 30px; border-radius: 99px; font-weight: bold; cursor: pointer; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);">
                    Invia Segnalazione
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const dropArea = document.getElementById('dropArea');
    const filesInput = document.getElementById('filesInput');
    const fileListContainer = document.getElementById('fileListContainer');
    
    // Array globale per gestire i file in memoria
    let storedFiles = [];

    // Trigger Click
    dropArea.addEventListener('click', () => filesInput.click());

    // Drag Effects
    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, (e) => {
            e.preventDefault(); e.stopPropagation();
            dropArea.classList.add('drag-over');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, (e) => {
            e.preventDefault(); e.stopPropagation();
            dropArea.classList.remove('drag-over');
        });
    });

    // Handle Drop
    dropArea.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        handleFiles(dt.files);
    });

    // Handle Selection
    filesInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        const validExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
        const maxBytes = 4 * 1024 * 1024; // 4MB

        Array.from(files).forEach(file => {
            const ext = file.name.split('.').pop().toLowerCase();
            
            // Validazione
            if (!validExtensions.includes(ext)) {
                alert(`⚠️ Il file "${file.name}" non è supportato!`);
                return;
            }
            if (file.size > maxBytes) {
                alert(`⚠️ Il file "${file.name}" è troppo grande!`);
                return;
            }
            
            // Evita duplicati (opzionale)
            if (!storedFiles.some(f => f.name === file.name && f.size === file.size)) {
                storedFiles.push(file);
            }
        });

        updateFileList();
        updateInputFiles();
    }

    function removeFile(index) {
        storedFiles.splice(index, 1);
        updateFileList();
        updateInputFiles();
    }

    // Aggiorna la grafica della lista
    function updateFileList() {
        fileListContainer.innerHTML = "";
        
        storedFiles.forEach((file, index) => {
            const div = document.createElement('div');
            div.className = 'file-item';
            
            // Icona in base al tipo
            let icon = 'fa-file';
            if(file.type.includes('image')) icon = 'fa-file-image';
            if(file.type.includes('pdf')) icon = 'fa-file-pdf';

            div.innerHTML = `
                <div class="file-info">
                    <i class="fas ${icon}" style="color:var(--primary); font-size:1.2em;"></i>
                    <span>${file.name} <small style="color:#aaa;">(${formatSize(file.size)})</small></span>
                </div>
                <button type="button" class="file-remove-btn" onclick="removeFile(${index})" title="Rimuovi">
                    <i class="fas fa-times"></i>
                </button>
            `;
            fileListContainer.appendChild(div);
        });
    }

    // Sincronizza l'array storedFiles con l'input reale del form
    function updateInputFiles() {
        const dataTransfer = new DataTransfer();
        storedFiles.forEach(file => {
            dataTransfer.items.add(file);
        });
        filesInput.files = dataTransfer.files;
    }

    function formatSize(bytes) {
        if(bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
</script>