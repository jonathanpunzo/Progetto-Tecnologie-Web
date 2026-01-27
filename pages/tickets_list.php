<?php
// FILE: pages/tickets_list.php
if (!isset($_SESSION['user_id'])) exit;

$role = $_SESSION['user_role'];
$uid = $_SESSION['user_id'];

// Recuperiamo i parametri dalla URL
$page_code = isset($_GET['page']) ? $_GET['page'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// --- 1. CONFIGURAZIONE TITOLI DINAMICI ---
if ($status_filter == 'closed') {
    $table_title = "🗄️ Archivio Ticket Chiusi";
    $table_desc = "Storico delle segnalazioni completate e archiviate.";
} elseif ($page_code == 'community') {
    $table_title = "🌍 Community Ticket";
    $table_desc = "Segnalazioni pubbliche condivise dagli altri utenti.";
} elseif ($page_code == 'my_tickets') {
    $table_title = "👤 I Miei Ticket Attivi";
    $table_desc = "Le tue segnalazioni attualmente in corso.";
} else {
    // Admin View "all_tickets"
    $table_title = "📋 Tutti i Ticket Attivi"; 
    $table_desc = "Gestione operativa delle segnalazioni aperte.";
}

// --- 2. COSTRUZIONE QUERY ---
$query = "SELECT t.*, u.name as author FROM tickets t JOIN users u ON t.user_id = u.id ";
$conditions = [];

// A. FILTRO PER UTENTE
if ($role != 'admin') {
    if ($page_code == 'community') {
        // Vede tutto
    } else {
        $conditions[] = "t.user_id = $uid";
    }
}

// B. FILTRO PER STATO
if ($status_filter == 'closed') {
    $conditions[] = "t.status = 'closed'";
} else {
    $conditions[] = "t.status != 'closed'";
}

// Unione condizioni SQL
if (count($conditions) > 0) {
    $query .= " WHERE " . implode(' AND ', $conditions);
}

$query .= " ORDER BY t.created_at DESC";
$result = pg_query($db_conn, $query);
?>

<div class="table-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
        
        <div>
            <h2><?php echo $table_title; ?></h2>
            <p style="color:var(--text-muted)"><?php echo $table_desc; ?></p>
        </div>

        <div style="position: relative;">
            <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
            <input type="text" id="ticketSearch" placeholder="Cerca in questa lista..." 
                   style="padding: 10px 10px 10px 35px; border: 1px solid #e2e8f0; border-radius: 8px; width: 250px; outline: none; transition:border 0.2s;">
        </div>

    </div>

    <table id="ticketsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Oggetto</th>
                <th>Autore</th>
                <th>Priorità</th>
                <th>Stato</th>
                <th>Data</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (pg_num_rows($result) == 0): ?>
                <tr><td colspan="7" style="text-align:center; padding:30px; color:#94a3b8;">Nessun ticket trovato in questa sezione.</td></tr>
            <?php endif; ?>

            <?php while ($row = pg_fetch_assoc($result)): ?>
            <tr>
                <td>#<?php echo $row['id']; ?></td>
                <td style="font-weight:600;"><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo htmlspecialchars($row['author']); ?></td>
                <td>
                    <?php 
                        $p = $row['priority'];
                        $c = ($p=='urgent'||$p=='high') ? '#ef4444' : 'inherit';
                        echo "<span style='color:$c; font-weight:bold'>".ucfirst($p)."</span>";
                    ?>
                </td>
                <td>
                    <?php 
                        $st = $row['status'];
                        
                        // Default Arancione (badge-open)
                        $cls = 'badge-open';
                        $icon = '●';
                        
                        // Verde (Resolved)
                        if($st=='resolved') {
                            $cls = 'badge-resolved';
                            $icon = '✅';
                        }
                        
                        // Rosso (Closed)
                        if($st=='closed') {
                            $cls = 'badge-closed';
                            $icon = '🔒';
                        }
                    ?>
                    <span class="badge <?php echo $cls; ?>"><?php echo $icon . ' ' . strtoupper($st); ?></span>
                </td>
                <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                <td>
                    <a href="index.php?page=ticket_details&id=<?php echo $row['id']; ?>" class="icon-btn" title="Vedi Dettagli">
                        <i class="fas fa-edit"></i>
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
    document.getElementById('ticketSearch').addEventListener('keyup', function() {
        let val = this.value.toLowerCase();
        let rows = document.querySelectorAll('#ticketsTable tbody tr');
        
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            if(text.includes("nessun ticket trovato")) return;
            row.style.display = text.includes(val) ? '' : 'none';
        });
    });
</script>