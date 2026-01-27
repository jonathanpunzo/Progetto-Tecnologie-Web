<?php
// FILE: pages/dashboard.php

// QUERY STATS
$query_stats = "SELECT COUNT(*) as total, SUM(CASE WHEN status = 'open' OR status = 'in-progress' THEN 1 ELSE 0 END) as active, SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved, SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed FROM tickets";
$stats = pg_fetch_assoc(pg_query($db_conn, $query_stats));

$total = $stats['total'] > 0 ? $stats['total'] : 1;
$perc_active = round(($stats['active'] / $total) * 100);
$perc_resolved = round(($stats['resolved'] / $total) * 100);
$perc_closed = round(($stats['closed'] / $total) * 100);

$query_clients = "SELECT COUNT(*) FROM users WHERE role = 'user'";
$total_clients = pg_fetch_result(pg_query($db_conn, $query_clients), 0, 0);

// QUERY GRAFICO
$daily_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $daily_data[$date] = 0;
}
$query_daily = "SELECT to_char(created_at, 'YYYY-MM-DD') as day, COUNT(*) as cnt FROM tickets WHERE created_at >= NOW() - INTERVAL '7 days' GROUP BY day";
$res_daily = pg_query($db_conn, $query_daily);
while ($row = pg_fetch_assoc($res_daily)) {
    if (isset($daily_data[$row['day']])) $daily_data[$row['day']] = $row['cnt'];
}
$max_daily = max($daily_data) > 0 ? max($daily_data) : 1;

// QUERY RECENTI
$query_recent = "SELECT t.*, u.name as author FROM tickets t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC LIMIT 4";
$recent_tickets = pg_query($db_conn, $query_recent);
?>

<style>
    /* Rimossi stili locali dei bottoni perché ora sono in style.css */
    .progress-item { margin-bottom: 20px; }
    .progress-label { display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 8px; font-weight: 600; color: var(--text-muted); }
    .progress-track { background: #f1f5f9; height: 8px; border-radius: 10px; overflow: hidden; }
    .progress-fill { height: 100%; border-radius: 10px; transition: width 1s ease; }

    .activity-row { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; }
    .big-number { font-size: 2rem; font-weight: 800; color: var(--text-main); line-height: 1; }
    .activity-label { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 5px; }
</style>

<div class="dash-grid">
    
    <div class="dash-card">
        <div><h3>Daily Tickets</h3><p style="font-size:0.85rem; color:var(--text-muted)">Ultimi 7 Giorni</p></div>
        <div class="bar-chart">
            <?php 
            $today = date('Y-m-d');
            foreach($daily_data as $day => $count): 
                $height = ($max_daily > 0) ? ($count / $max_daily) * 100 : 0;
                $vis_height = $height == 0 ? 5 : $height;
                $day_label = date('d', strtotime($day)); $month_label = date('M', strtotime($day)); $is_today = ($day == $today) ? 'active' : ''; 
            ?>
                <div class="bar-group">
                    <div class="bar <?php echo $is_today; ?>" style="height: <?php echo $vis_height; ?>%;"><div class="tooltip"><?php echo $count; ?> Ticket</div></div>
                    <div class="bar-meta"><span class="day"><?php echo $day_label; ?></span><span class="month"><?php echo $month_label; ?></span></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="dash-card">
        <div><h3>Tickets by Status</h3><p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:20px;">Panoramica risoluzioni</p></div>
        <div style="display: flex; flex-direction: column; justify-content: center; height: 100%;">
            <div class="progress-item">
                <div class="progress-label"><span>In Lavorazione</span> <span><?php echo $perc_active; ?>%</span></div>
                <div class="progress-track"><div class="progress-fill" style="width: <?php echo $perc_active; ?>%; background: #f97316;"></div></div>
            </div>
            <div class="progress-item">
                <div class="progress-label"><span>Risolti</span> <span><?php echo $perc_resolved; ?>%</span></div>
                <div class="progress-track"><div class="progress-fill" style="width: <?php echo $perc_resolved; ?>%; background: #22c55e;"></div></div>
            </div>
            <div class="progress-item" style="margin-bottom: 0;">
                <div class="progress-label"><span>Chiusi</span> <span><?php echo $perc_closed; ?>%</span></div>
                <div class="progress-track"><div class="progress-fill" style="width: <?php echo $perc_closed; ?>%; background: #ef4444;"></div></div>
            </div>
        </div>
    </div>

    <div class="dash-card">
        <div><h3>Activity</h3><p style="font-size:0.85rem; color:var(--text-muted)">Statistiche piattaforma</p></div>
        <div style="display: flex; flex-direction: column; justify-content: center; height: 100%;">
            <div class="activity-row"><div><div class="activity-label">Ticket Attivi</div><div class="activity-sub" style="color:green;">In coda ora</div></div><div class="big-number"><?php echo $stats['active']; ?></div></div>
            <hr style="border:0; border-top:1px solid #f1f5f9; margin: 20px 0;">
            <div class="activity-row"><div><div class="activity-label">Clienti</div><div class="activity-sub" style="color:var(--primary);">Registrati</div></div><div class="big-number"><?php echo $total_clients; ?></div></div>
        </div>
    </div>

</div>

<div class="dash-grid">
    <div class="dash-card span-2">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <h3>Last Updates</h3>
            <span style="background:#f1f5f9; padding:5px 10px; border-radius:8px; font-size:0.75rem; font-weight:600; color:var(--text-muted);">Today</span>
        </div>
        <table class="dash-table">
            <thead><tr><th>Oggetto</th><th>Autore</th><th>Stato</th><th></th></tr></thead>
            <tbody>
                <?php while($t = pg_fetch_assoc($recent_tickets)): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($t['title']); ?></strong></td>
                    <td><?php echo htmlspecialchars($t['author']); ?></td>
                    <td>
                        <?php 
                        $status_color = '#f97316'; 
                        if ($t['status'] == 'resolved') $status_color = '#22c55e';
                        if ($t['status'] == 'closed')   $status_color = '#ef4444';
                        ?>
                        <span style="color:<?php echo $status_color; ?>; font-weight:bold; font-size:0.85rem;">● <?php echo ucfirst($t['status']); ?></span>
                    </td>
                    <td style="text-align:right;"><a href="index.php?page=ticket_details&id=<?php echo $t['id']; ?>" style="color:var(--text-muted);"><i class="fas fa-chevron-right"></i></a></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="dash-card">
        <div><h3>Quick Actions</h3><p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:20px;">Strumenti veloci</p></div>
        <div style="display: flex; flex-direction: column; justify-content: center;">
            <button class="action-btn btn-outline" onclick="window.location.href='index.php?page=new_ticket'">
                <i class="fas fa-plus-circle" style="color:var(--primary);"></i> Crea Ticket Manuale
            </button>
            <button class="action-btn btn-outline">
                <i class="fas fa-file-export" style="color:#10b981;"></i> Esporta Report CSV
            </button>
            <button class="action-btn btn-outline">
                <i class="fas fa-user-plus" style="color:#f59e0b;"></i> Invita Nuovo Utente
            </button>
        </div>
    </div>
</div>