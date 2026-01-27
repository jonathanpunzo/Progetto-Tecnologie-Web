<?php
// FILE: pages/dashboard.php

// --- LOGICA PHP (Query Dati) ---

// 1. Statistiche Generali (Percentuali)
$query_stats = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'open' OR status = 'in-progress' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed
FROM tickets";
$stats = pg_fetch_assoc(pg_query($db_conn, $query_stats));

$total = $stats['total'] > 0 ? $stats['total'] : 1; // Evita div by zero
$perc_active = round(($stats['active'] / $total) * 100);
$perc_resolved = round(($stats['resolved'] / $total) * 100);
$perc_closed = round(($stats['closed'] / $total) * 100);

// 2. Clienti Totali
$query_clients = "SELECT COUNT(*) FROM users WHERE role = 'user'";
$total_clients = pg_fetch_result(pg_query($db_conn, $query_clients), 0, 0);

// 3. Grafico Daily Tickets (Ultimi 7 giorni)
$daily_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $daily_data[$date] = 0; // Inizializza a 0
}
$query_daily = "SELECT to_char(created_at, 'YYYY-MM-DD') as day, COUNT(*) as cnt 
                FROM tickets 
                WHERE created_at >= NOW() - INTERVAL '7 days' 
                GROUP BY day";
$res_daily = pg_query($db_conn, $query_daily);
while ($row = pg_fetch_assoc($res_daily)) {
    if (isset($daily_data[$row['day']])) {
        $daily_data[$row['day']] = $row['cnt'];
    }
}
// Trova il valore massimo per scalare le barre del grafico
$max_daily = max($daily_data) > 0 ? max($daily_data) : 1;

// 4. Ultimi 4 Ticket
$query_recent = "SELECT t.*, u.name as author FROM tickets t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC LIMIT 4";
$recent_tickets = pg_query($db_conn, $query_recent);
?>

<style>

    .dash-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr); /* 3 colonne fisse */
        /* height viene gestito dal flexbox nel CSS principale ora */
    }

    /* Utility */
    .span-2 { grid-column: span 2; }
    
    /* Stile specifico per i grafici interni che non è nel CSS globale */
    .bar-chart {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        height: 120px;
        margin-top: auto; /* Spinge in basso */
        padding-top: 20px;
    }
    
    /* Stile bottoni azioni rapide */
    .action-btn {
        display: flex; align-items: center; justify-content: center; gap: 10px;
        width: 100%; padding: 15px; margin-bottom: 10px;
        border: 1px solid #e2e8f0; border-radius: 12px;
        background: #f8fafc; color: var(--text-main); font-weight: 600;
        cursor: pointer; 
        transition: all 0.2s ease;
    }
    .action-btn:hover {
        background: white;
        border-color: var(--primary);
        color: var(--primary);
        transform: scale(1.02); /* Leggero ingrandimento */
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    /* Grafico a Barre CSS */
    .bar-chart {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        height: 120px;
        margin-top: 15px;
    }
    .bar {
        width: 12%;
        background: var(--primary);
        border-radius: 4px 4px 0 0;
        transition: height 0.3s;
        position: relative;
        min-height: 4px; /* Altezza minima estetica */
    }
    .bar:hover { opacity: 0.8; }
    .bar span {
        position: absolute; bottom: -25px; left: 50%; transform: translateX(-50%);
        font-size: 0.75rem; color: var(--text-muted);
    }

    /* Percentuali Linee */
    .progress-item { margin-bottom: 15px; }
    .progress-label { display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 5px; font-weight: 600; color: var(--text-muted); }
    .progress-track { background: #f1f5f9; height: 8px; border-radius: 10px; overflow: hidden; }
    .progress-fill { height: 100%; border-radius: 10px; }

    /* Activity Box */
    .activity-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .big-number { font-size: 2rem; font-weight: 800; color: var(--text-main); }
    .activity-label { color: var(--text-muted); font-size: 0.9rem; }

    /* Pulsanti Azioni */
    .action-btn {
        display: flex; align-items: center; justify-content: center; gap: 10px;
        width: 100%; padding: 15px; margin-bottom: 10px;
        border: 1px solid #e2e8f0; border-radius: 10px;
        background: white; color: var(--text-main); font-weight: 600;
        cursor: pointer; transition: all 0.2s;
    }
    .action-btn:hover { background: #f8fafc; border-color: var(--primary); color: var(--primary); }
    
    /* Utility */
    .span-2 { grid-column: span 2; }
</style>

<div class="dash-grid">
    
    <div class="dash-card">
        <h3>Daily Tickets</h3>
        <p style="font-size:0.85rem; color:var(--text-muted)">Ultimi 7 Giorni</p>
        
        <div class="bar-chart">
            <?php foreach($daily_data as $day => $count): 
                $height = ($count / $max_daily) * 100; 
                $day_label = date('d', strtotime($day)); // Mostra solo il giorno
            ?>
                <div class="bar" style="height: <?php echo $height; ?>%;">
                    <span><?php echo $day_label; ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="dash-card">
        <h3>Tickets by Status</h3>
        <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:20px;">Panoramica risoluzioni</p>
        
        <div class="progress-item">
            <div class="progress-label">
                <span>In Lavorazione</span> <span><?php echo $perc_active; ?>%</span>
            </div>
            <div class="progress-track">
                <div class="progress-fill" style="width: <?php echo $perc_active; ?>%; background: #f59e0b;"></div>
            </div>
        </div>

        <div class="progress-item">
            <div class="progress-label">
                <span>Risolti</span> <span><?php echo $perc_resolved; ?>%</span>
            </div>
            <div class="progress-track">
                <div class="progress-fill" style="width: <?php echo $perc_resolved; ?>%; background: #3b82f6;"></div>
            </div>
        </div>

        <div class="progress-item">
            <div class="progress-label">
                <span>Chiusi</span> <span><?php echo $perc_closed; ?>%</span>
            </div>
            <div class="progress-track">
                <div class="progress-fill" style="width: <?php echo $perc_closed; ?>%; background: #1e293b;"></div>
            </div>
        </div>
    </div>

    <div class="dash-card">
        <h3>Activity</h3>
        <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:30px;">Statistiche piattaforma</p>
        
        <div class="activity-row">
            <div>
                <div class="activity-label">Ticket Attivi</div>
                <div style="font-size:0.8rem; color:green;">In coda ora</div>
            </div>
            <div class="big-number"><?php echo $stats['active']; ?></div>
        </div>
        
        <hr style="border:0; border-top:1px solid #f1f5f9; margin: 15px 0;">

        <div class="activity-row">
            <div>
                <div class="activity-label">Clienti</div>
                <div style="font-size:0.8rem; color:var(--primary);">Registrati</div>
            </div>
            <div class="big-number"><?php echo $total_clients; ?></div>
        </div>
    </div>

</div>

<div class="dash-grid">

    <div class="dash-card span-2" style="padding:0; overflow:hidden;">
        <div style="padding: 20px; border-bottom: 1px solid #f1f5f9; display:flex; justify-content:space-between;">
            <h3>Last Updates</h3>
            <span style="background:#f1f5f9; padding:5px 10px; border-radius:10px; font-size:0.8rem;">Today</span>
        </div>
        
        <table style="margin:0;">
            <thead style="background:#f8fafc;">
                <tr>
                    <th style="padding-left:25px;">Oggetto</th>
                    <th>Autore</th>
                    <th>Stato</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php while($t = pg_fetch_assoc($recent_tickets)): ?>
                <tr>
                    <td style="padding-left:25px;"><strong><?php echo htmlspecialchars($t['title']); ?></strong></td>
                    <td><?php echo htmlspecialchars($t['author']); ?></td>
                    <td>
                        <?php 
                        $color = ($t['status']=='open'||$t['status']=='in-progress') ? '#f59e0b' : '#3b82f6'; 
                        ?>
                        <span style="color:<?php echo $color; ?>; font-weight:bold; font-size:0.85rem;">● <?php echo ucfirst($t['status']); ?></span>
                    </td>
                    <td style="text-align:right; padding-right:25px;">
                        <a href="index.php?page=ticket_details&id=<?php echo $t['id']; ?>" style="color:var(--text-muted);"><i class="fas fa-chevron-right"></i></a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="dash-card">
        <h3>Quick Actions</h3>
        <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:20px;">Strumenti veloci</p>

        <button class="action-btn" onclick="window.location.href='index.php?page=new_ticket'">
            <i class="fas fa-plus-circle" style="color:var(--primary);"></i> Crea Ticket Manuale
        </button>

        <button class="action-btn">
            <i class="fas fa-file-export" style="color:#10b981;"></i> Esporta Report CSV
        </button>

        <button class="action-btn">
            <i class="fas fa-user-plus" style="color:#f59e0b;"></i> Invita Nuovo Utente
        </button>
    </div>

</div>