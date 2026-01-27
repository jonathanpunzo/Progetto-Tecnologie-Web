<?php
// FILE: pages/tickets_list.php
if (!isset($_SESSION['user_id'])) exit;

$role = $_SESSION['user_role'];
$uid = $_SESSION['user_id'];

// Query Differenziata: Admin vede tutto, User vede i suoi
if ($role == 'admin') {
    $query = "SELECT t.*, u.name as author FROM tickets t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC";
} else {
    $query = "SELECT t.*, u.name as author FROM tickets t JOIN users u ON t.user_id = u.id WHERE t.user_id = $uid ORDER BY t.created_at DESC";
}
$result = pg_query($db_conn, $query);
?>

<div class="table-card">
    <div style="margin-bottom: 20px;">
        <h2>📋 Elenco Ticket</h2>
        <p style="color:var(--text-muted)">Visualizza e gestisci tutte le segnalazioni.</p>
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
                        $cls = 'badge-open';
                        if($st=='closed'||$st=='resolved') $cls='badge-closed';
                        if($st=='in-progress') $cls='badge-progress';
                    ?>
                    <span class="badge <?php echo $cls; ?>"><?php echo strtoupper($st); ?></span>
                </td>
                <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                <td>
                    <a href="index.php?page=ticket_details&id=<?php echo $row['id']; ?>" class="icon-btn">
                        <i class="fas fa-edit"></i>
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
    // Questo script collega la barra di ricerca dell'header (in index.php) a questa tabella
    document.querySelector('.search-box input').addEventListener('keyup', function() {
        let val = this.value.toLowerCase();
        let rows = document.querySelectorAll('#ticketsTable tbody tr');
        
        rows.forEach(row => {
            let text = row.textContent.toLowerCase();
            row.style.display = text.includes(val) ? '' : 'none';
        });
    });
</script>