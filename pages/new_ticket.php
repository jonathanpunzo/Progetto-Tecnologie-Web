<?php
// FILE: pages/new_ticket.php

$msg = "";
$msg_type = "";

// LOGICA PHP (Invariata)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_ticket'])) {
    $title = pg_escape_string($db_conn, $_POST['title']);
    $desc = pg_escape_string($db_conn, $_POST['description']);
    $category = $_POST['category'];
    $priority = $_POST['priority'];
    $user_id = $_SESSION['user_id'];
    
    $saved_paths = [];
    $upload_error = false;

    if (isset($_FILES['attachments']) && count($_FILES['attachments']['name']) > 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];
        $max_size = 4 * 1024 * 1024; // 4MB
        
        $count = count($_FILES['attachments']['name']);
        for ($i = 0; $i < $count; $i++) {
            $name = $_FILES['attachments']['name'][$i];
            $tmp_name = $_FILES['attachments']['tmp_name'][$i];
            $size = $_FILES['attachments']['size'][$i];
            $error = $_FILES['attachments']['error'][$i];

            if ($error == UPLOAD_ERR_NO_FILE) continue;

            if ($error == 0) {
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed_ext)) {
                    $msg = "❌ File '$name' non supportato."; $msg_type = "error"; $upload_error = true; break;
                }
                if ($size > $max_size) {
                    $msg = "❌ File '$name' troppo grande."; $msg_type = "error"; $upload_error = true; break;
                }
                $clean_name = time() . "_" . $i . "_" . preg_replace("/[^a-zA-Z0-9\._-]/", "", $name);
                $target = "uploads/" . $clean_name;
                if (!is_dir('uploads')) mkdir('uploads');
                if (move_uploaded_file($tmp_name, $target)) {
                    $saved_paths[] = $target;
                } else {
                    $msg = "❌ Errore upload '$name'."; $msg_type = "error"; $upload_error = true; break;
                }
            }
        }
    }

    if (!$upload_error) {
        $attachment_sql = empty($saved_paths) ? "NULL" : "'" . implode(';', $saved_paths) . "'";
        $sql = "INSERT INTO tickets (user_id, title, description, priority, category, attachment_path) 
                VALUES ($user_id, '$title', '$desc', '$priority', '$category', $attachment_sql)";
        
        if (pg_query($db_conn, $sql)) {
            echo "<script>window.location.href='index.php?page=all_tickets';</script>";
            exit;
        } else {
            $msg = "Errore Database: " . pg_last_error($db_conn); $msg_type = "error";
        }
    }
}
?>

<style>
    /* Centramento perfetto */
    .ticket-page-layout {
        height: 100%; width: 100%;
        display: flex; align-items: center; justify-content: center;
    }

    /* La Card Principale */
    .ticket-card-wide {
        width: 100%; max-width: 1200px; /* Widescreen */
        background: white;
        border-radius: 24px;
        box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.15);
        border: 1px solid #f1f5f9;
        display: flex; flex-direction: column;
        overflow: hidden;
        animation: zoomInUp 0.5s cubic-bezier(0.2, 0.8, 0.2, 1);
        max-height: 90vh;
    }

    @keyframes zoomInUp { from { opacity: 0; transform: scale(0.98) translateY(20px); } to { opacity: 1; transform: scale(1) translateY(0); } }

    /* Header */
    .ticket-header {
        padding: 25px 40px;
        border-bottom: 1px solid #f1f5f9;
        display: flex; justify-content: space-between; align-items: center;
        background: #fff; flex-shrink: 0;
    }
    .ticket-header h2 { margin: 0; font-size: 1.6rem; color: var(--text-main); font-weight: 800; }
    .ticket-header p { margin: 0; font-size: 0.9rem; color: var(--text-muted); }

    /* Layout Interno a 2 Colonne */
    .ticket-split-body {
        display: grid;
        grid-template-columns: 1.4fr 1fr; /* Sinistra 60%, Destra 40% */
        height: 100%;
        overflow: hidden;
    }

    /* Colonna Sinistra (Form) */
    .col-left {
        padding: 40px;
        overflow-y: auto;
        display: flex; flex-direction: column; gap: 25px;
    }

    /* Colonna Destra (Upload) */
    .col-right {
        background: #fcfcfc;
        border-left: 1px solid #f1f5f9;
        padding: 40px;
        display: flex; flex-direction: column;
    }

    /* Input Styling */
    .form-label { font-size: 0.85rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px; display: block; text-transform: uppercase; letter-spacing: 0.5px; }
    .form-control {
        width: 100%; padding: 14px 18px; font-size: 1rem;
        background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 12px;
        transition: all 0.2s ease; outline: none; font-family: inherit; color: var(--text-main);
    }
    .form-control:focus { background: white; border-color: var(--primary); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.1); }
    
    .row-2-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }

    /* Upload Area Verticale */
    .upload-zone-vertical {
        flex: 1; 
        border: 3px dashed #cbd5e1; border-radius: 16px;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        text-align: center; cursor: pointer; transition: all 0.2s;
        background: white; min-height: 200px;
        margin-bottom: 20px;
    }
    .upload-zone-vertical:hover { border-color: var(--primary); background: #eef2ff; }
    .upload-icon { font-size: 3rem; color: #94a3b8; margin-bottom: 15px; transition:0.3s; }
    .upload-zone-vertical:hover .upload-icon { color: var(--primary); transform: scale(1.1); }

    /* File List Compact (Migliorata) */
    .file-list-compact { 
        flex-shrink: 0; 
        max-height: 200px; 
        overflow-y: auto; 
        padding-right: 5px; /* Spazio per scrollbar lista */
    }
    
    .file-item-mini {
        background: white; 
        border: 1px solid #e2e8f0; 
        padding: 12px; 
        border-radius: 12px;
        display: flex; 
        align-items: center; 
        justify-content: space-between; 
        margin-bottom: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        transition: transform 0.2s;
    }
    .file-item-mini:hover { transform: translateX(5px); border-color: var(--primary); }

    /* --- NUOVA CLASSE PER NOME FILE SCROLLABILE --- */
    .file-name-scroll {
        font-weight: 600;
        color: var(--text-main);
        white-space: nowrap; 
        overflow-x: hidden;      /* Di base nascosto */
        text-overflow: ellipsis; /* Di base puntini */
        display: block;
        padding-bottom: 2px;
    }
    /* Al passaggio del mouse compare lo scroll */
    .file-name-scroll:hover {
        overflow-x: auto;
        text-overflow: clip; /* Rimuove i puntini per mostrare il testo */
    }
    /* Scrollbar sottile e carina */
    .file-name-scroll::-webkit-scrollbar { height: 4px; }
    .file-name-scroll::-webkit-scrollbar-thumb { background: var(--primary); border-radius: 10px; }
    .file-name-scroll::-webkit-scrollbar-track { background: #f1f5f9; }


    /* Footer Azioni */
    .form-footer {
        grid-column: span 2;
        padding: 20px 40px;
        border-top: 1px solid #f1f5f9;
        display: flex; justify-content: flex-end; align-items: center; gap: 20px;
        background: #fff;
    }

    .btn-submit {
        background: var(--primary); color: white; border: none; padding: 14px 40px;
        border-radius: 12px; font-size: 1rem; font-weight: 700; cursor: pointer;
        box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4); transition: 0.2s;
        display: flex; align-items: center; gap: 10px;
    }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 15px 30px -5px rgba(79, 70, 229, 0.5); }

    @media (max-width: 1000px) {
        .ticket-split-body { grid-template-columns: 1fr; overflow-y: auto; }
        .col-right { border-left: none; border-top: 1px solid #f1f5f9; }
        .upload-zone-vertical { min-height: 150px; }
    }
</style>

<div class="ticket-page-layout">
    
    <div class="ticket-card-wide">
        
        <div class="ticket-header">
            <div>
                <h2>Nuova Segnalazione</h2>
                <p>Inserisci i dettagli per aprire un ticket.</p>
            </div>
            <div style="background:#f1f5f9; color:var(--text-muted); padding:8px 15px; border-radius:8px; font-weight:600; font-size:0.85rem;">
                <i class="fas fa-life-ring"></i> Supporto
            </div>
        </div>

        <form action="" method="POST" enctype="multipart/form-data" id="ticketForm" style="display:contents;">
            <input type="hidden" name="create_ticket" value="1">

            <div class="ticket-split-body">
                
                <div class="col-left">
                    
                    <?php if($msg): ?>
                        <div style="padding:12px; border-radius:8px; font-weight:600; background:<?php echo ($msg_type=='error')?'#fee2e2':'#dcfce7'; ?>; color:<?php echo ($msg_type=='error')?'#991b1b':'#166534'; ?>;">
                            <?php echo $msg; ?>
                        </div>
                    <?php endif; ?>

                    <div>
                        <label class="form-label">Oggetto</label>
                        <input type="text" name="title" class="form-control" required placeholder="Es. Errore login..." style="font-weight:600;">
                    </div>

                    <div class="row-2-cols">
                        <div>
                            <label class="form-label">Categoria</label>
                            <select name="category" class="form-control">
                                <option value="Software">💻 Software</option>
                                <option value="Hardware">🖨️ Hardware</option>
                                <option value="Rete">🌐 Rete</option>
                                <option value="Account">👤 Account</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Priorità</label>
                            <select name="priority" class="form-control">
                                <option value="low">🟢 Bassa</option>
                                <option value="medium" selected>🟠 Media</option>
                                <option value="high">🔴 Alta</option>
                                <option value="urgent">🔥 Urgente</option>
                            </select>
                        </div>
                    </div>

                    <div style="flex:1; display:flex; flex-direction:column;">
                        <label class="form-label">Descrizione</label>
                        <textarea name="description" class="form-control" required placeholder="Dettagli del problema..." style="flex:1; resize:none; min-height:150px; line-height:1.6;"></textarea>
                    </div>
                </div>

                <div class="col-right">
                    <label class="form-label">Allegati</label>
                    <p style="font-size:0.85rem; color:#94a3b8; margin-bottom:15px;">Carica screenshot o log (Max 4MB).</p>
                    
                    <div class="upload-zone-vertical" id="dropArea">
                        <i class="fas fa-cloud-upload-alt upload-icon"></i>
                        <div style="font-weight:700; color:var(--text-main); margin-bottom:5px;">Trascina File</div>
                        <div style="font-size:0.8rem; color:#94a3b8;">o clicca per sfogliare</div>
                        <input type="file" name="attachments[]" id="filesInput" multiple accept=".jpg,.jpeg,.png,.pdf" style="display:none;">
                    </div>

                    <div class="file-list-compact" id="fileListContainer"></div>
                </div>

            </div>

            <div class="form-footer">
                <a href="index.php" style="color:var(--text-muted); font-weight:600; text-decoration:none; transition:0.2s;">Annulla</a>
                <button type="submit" class="btn-submit">Invia Ticket <i class="fas fa-paper-plane"></i></button>
            </div>

        </form>
    </div>
</div>

<script>
    const dropArea = document.getElementById('dropArea');
    const filesInput = document.getElementById('filesInput');
    const fileListContainer = document.getElementById('fileListContainer');
    let storedFiles = [];

    dropArea.addEventListener('click', () => filesInput.click());

    ['dragenter', 'dragover'].forEach(e => {
        dropArea.addEventListener(e, (ev) => { ev.preventDefault(); dropArea.style.borderColor = 'var(--primary)'; dropArea.style.background = '#eef2ff'; });
    });
    ['dragleave', 'drop'].forEach(e => {
        dropArea.addEventListener(e, (ev) => { ev.preventDefault(); dropArea.style.borderColor = '#cbd5e1'; dropArea.style.background = '#fff'; });
    });

    dropArea.addEventListener('drop', (e) => handleFiles(e.dataTransfer.files));
    filesInput.addEventListener('change', function() { handleFiles(this.files); });

    function handleFiles(files) {
        const validExt = ['jpg', 'jpeg', 'png', 'pdf'];
        Array.from(files).forEach(file => {
            const ext = file.name.split('.').pop().toLowerCase();
            if(validExt.includes(ext) && file.size <= 4*1024*1024) {
                // Evita duplicati
                if(!storedFiles.some(f => f.name === file.name && f.size === file.size)) {
                    storedFiles.push(file);
                }
            }
        });
        updateUI();
    }

    function removeFile(index) {
        storedFiles.splice(index, 1);
        updateUI();
    }

    // Funzione per formattare i byte in KB/MB
    function formatSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function updateUI() {
        fileListContainer.innerHTML = "";
        const dt = new DataTransfer();
        
        storedFiles.forEach((file, index) => {
            dt.items.add(file);
            let icon = file.type.includes('pdf') ? 'fa-file-pdf' : 'fa-file-image';
            
            // Qui uso la nuova classe file-name-scroll
            fileListContainer.innerHTML += `
                <div class="file-item-mini">
                    <div style="display:flex; align-items:center; gap:12px; width:100%; overflow:hidden;">
                        <i class="fas ${icon}" style="color:var(--primary); font-size:1.2rem;"></i>
                        
                        <div style="flex:1; min-width:0; display:flex; flex-direction:column;">
                            <div class="file-name-scroll" title="${file.name}">
                                ${file.name}
                            </div>
                            <span style="font-size:0.75rem; color:#94a3b8;">${formatSize(file.size)}</span>
                        </div>
                    </div>
                    
                    <i class="fas fa-times" onclick="removeFile(${index})" style="cursor:pointer; color:#ef4444; padding:5px; margin-left:10px;"></i>
                </div>`;
        });
        filesInput.files = dt.files;
    }
</script>