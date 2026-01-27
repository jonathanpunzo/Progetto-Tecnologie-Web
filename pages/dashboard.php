<?php
// FILE: pages/dashboard.php
// Nota: $db_conn è già disponibile perché incluso in index.php

// 1. STATISTICHE (Solo per Admin per ora, come richiesto)
if ($user_role == 'admin') {
    // Totale Ticket
    $res_total = pg_query($db_conn, "SELECT COUNT(*) FROM tickets");
    $total_tickets = pg_fetch_result($res_total, 0, 0);

    // Ticket Aperti
    $res_open = pg_query($db_conn, "SELECT COUNT(*) FROM tickets WHERE status = 'open'");
    $open_tickets = pg_fetch_result($res_open, 0, 0);

    // Ticket Chiusi
    $res_closed = pg_query($db_conn, "SELECT COUNT(*) FROM tickets WHERE status = 'closed'");
    $closed_tickets = pg_fetch_result($res_closed, 0, 0);

    // Utenti Totali
    $res_users = pg_query($db_conn, "SELECT COUNT(*) FROM users WHERE role = 'user'");
    $total_users = pg_fetch_result($res_users, 0, 0);

    // Query Ultimi Ticket
    $query_recent = "SELECT t.*, u.name as author FROM tickets t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC LIMIT 5";
} else {
    // Statistiche Utente Semplice
    $uid = $_SESSION['user_id'];
    $res_total = pg_query($db_conn, "SELECT COUNT(*) FROM tickets WHERE user_id = $uid");
    $total_tickets = pg_fetch_result($res_total, 0, 0);
    
    // Per l'utente mostriamo i suoi ultimi ticket
    $query_recent = "SELECT t.*, u.name as author FROM tickets t JOIN users u ON t.user_id = u.id WHERE t.user_id = $uid ORDER BY t.created_at DESC LIMIT 5";
    
    // Placeholder per layout grafico (evita errori)
    $open_tickets = "-";
    $closed_tickets = "-";
    $total_users = "-";
}

$recent_tickets = pg_query($db_conn, $query_recent);
?>

<h2 style="margin-bottom: 20px;">Panoramica Attività</h2>

<div class="stats-grid">
    <div class="stat-card" style="border-left: 5px solid #6366f1;">
        <div>
            <div class="stat-title">TICKET TOTALI</div>
            <div class="stat-value"><?php echo $total_tickets; ?></div>
        </div>
        <i class="fas fa-folder-open stat-icon" style="color: #6366f1;"></i>
    </div>

    <div class="stat-card" style="border-left: 5px solid #10b981;">
        <div>
            <div class="stat-title">APERTI / IN LAVORAZIONE</div>
            <div class="stat-value"><?php echo $open_tickets; ?></div>
        </div>
        <i class="fas fa-tools stat-icon" style="color: #10b981;"></i>
    </div>

    <div class="stat-card" style="border-left: 5px solid #ef4444;">
        <div>
            <div class="stat-title">CHIUSI / RISOLTI</div>
            <div class="stat-value"><?php echo $closed_tickets; ?></div>
        </div>
        <i class="fas fa-check-circle stat-icon" style="color: #ef4444;"></i>
    </div>

    <div class="stat-card" style="border-left: 5px solid #f59e0b;">
        <div>
            <div class="stat-title">CLIENTI ATTIVI</div>
            <div class="stat-value"><?php echo $total_users; ?></div>
        </div>
        <i class="fas fa-users stat-icon" style="color: #f59e0b;"></i>
    </div>
</div>

<div class="table-card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h3>Ultimi Ticket Ricevuti</h3>
        <a href="index.php?page=all_tickets" style="color:var(--primary); font-weight:600; font-size:0.9rem;">Vedi Tutti &rarr;</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Oggetto</th>
                <th>Autore</th>
                <th>Data</th>
                <th>Priorità</th>
                <th>Stato</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = pg_fetch_assoc($recent_tickets)): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($row['title']); ?></strong></td>
                <td><?php echo htmlspecialchars($row['author']); ?></td>
                <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                <td>
                    <?php 
                    $prio = $row['priority'];
                    $color = ($prio=='high'||$prio=='urgent') ? 'red' : 'gray';
                    echo "<span style='color:$color; font-weight:bold;'>".ucfirst($prio)."</span>";
                    ?>
                </td>
                <td>
                    <?php 
                        $status_class = 'badge-open'; // Default
                        if($row['status'] == 'closed') $status_class = 'badge-closed';
                        if($row['status'] == 'resolved') $status_class = 'badge-closed';
                        if($row['status'] == 'in-progress') $status_class = 'badge-progress';
                    ?>
                    <span class="badge <?php echo $status_class; ?>">
                        <?php echo strtoupper($row['status']); ?>
                    </span>
                </td>
                <td style="text-align:right;">
                    <a href="index.php?page=ticket_details&id=<?php echo $row['id']; ?>" class="icon-btn">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>