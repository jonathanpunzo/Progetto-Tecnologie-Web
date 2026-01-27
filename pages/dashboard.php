<?php
// FILE: pages/dashboard.php

// 1. TRADUZIONE MESI
$mesi_it = [
    'Jan'=>'Gen', 'Feb'=>'Feb', 'Mar'=>'Mar', 'Apr'=>'Apr', 'May'=>'Mag', 'Jun'=>'Giu',
    'Jul'=>'Lug', 'Aug'=>'Ago', 'Sep'=>'Set', 'Oct'=>'Ott', 'Nov'=>'Nov', 'Dec'=>'Dic'
];

// 2. QUERY STATISTICHE
$query_stats = "SELECT COUNT(*) as total, 
                SUM(CASE WHEN status = 'open' OR status = 'in-progress' THEN 1 ELSE 0 END) as active, 
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved, 
                SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed 
                FROM tickets";
$stats = pg_fetch_assoc(pg_query($db_conn, $query_stats));

$total = $stats['total'] > 0 ? $stats['total'] : 1;
$perc_active = round(($stats['active'] / $total) * 100);
$perc_resolved = round(($stats['resolved'] / $total) * 100);
$perc_closed = round(($stats['closed'] / $total) * 100);

// Clienti
$query_clients = "SELECT COUNT(*) FROM users WHERE role = 'user'";
$total_clients = pg_fetch_result(pg_query($db_conn, $query_clients), 0, 0);

// 3. QUERY GRAFICO
$daily_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $daily_data[$date] = 0;
}
$query_daily = "SELECT to_char(created_at, 'YYYY-MM-DD') as day, COUNT(*) as cnt 
                FROM tickets 
                WHERE created_at >= NOW() - INTERVAL '7 days' 
                GROUP BY day";
$res_daily = pg_query($db_conn, $query_daily);
while ($row = pg_fetch_assoc($res_daily)) {
    if (isset($daily_data[$row['day']])) $daily_data[$row['day']] = $row['cnt'];
}
$max_daily = max($daily_data) > 0 ? max($daily_data) : 1;

// 4. QUERY RECENTI (5 Ticket)
$query_recent = "SELECT t.*, u.name as author 
                 FROM tickets t 
                 JOIN users u ON t.user_id = u.id 
                 ORDER BY t.created_at DESC LIMIT 5";
$recent_tickets = pg_query($db_conn, $query_recent);
?>

<style>
    /* CHART STYLES AGGIORNATI */
    .chart-container {
        display: flex; align-items: flex-end; justify-content: space-between;
        /* Rimosso height fissa, ora usa flex: 1 per riempire lo spazio */
        flex: 1; 
        min-height: 220px; /* Altezza minima aumentata per alzare il grafico */
        padding-top: 10px; /* Meno padding sopra */
        gap: 12px;
        margin-top: 10px;
    }
    .chart-column {
        flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-end;
        height: 100%; position: relative; cursor: pointer;
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .chart-column:hover { transform: translateY(-5px); }
    
    .bar-track {
        width: 100%; height: 100%; background: #f8fafc;
        border-radius: 10px; position: relative; overflow: hidden;
        display: flex; align-items: flex-end;
    }

    .bar-fill {
        width: 100%;
        background: linear-gradient(180deg, var(--primary-dark) 0%, var(--primary) 100%);
        border-radius: 8px;
        transition: height 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        min-height: 6px;
        position: relative;
    }
    .bar-fill.today-bar {
        background: linear-gradient(180deg, #f59e0b 0%, #d97706 100%);
        box-shadow: 0 0 15px rgba(245, 158, 11, 0.3);
    }

    .chart-meta { margin-top: 10px; text-align: center; }
    .meta-day { display: block; font-weight: 700; font-size: 0.9rem; color: var(--text-main); }
    .meta-month { display: block; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600; }

    .chart-tooltip {
        position: absolute; top: -35px; left: 50%; transform: translateX(-50%);
        background: var(--text-main); color: white;
        padding: 5px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: bold;
        opacity: 0; transition: 0.2s; pointer-events: none; white-space: nowrap;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1); z-index: 10;
    }
    .chart-column:hover .chart-tooltip { opacity: 1; top: -45px; }

    /* PROGRESS BARS */
    .progress-item { width: 100%; }
    .progress-label { display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 8px; font-weight: 600; color: var(--text-main); }
    .progress-track { background: #f1f5f9; height: 10px; border-radius: 99px; overflow: hidden; }
    .progress-fill { height: 100%; border-radius: 99px; transition: width 1s ease; }

    /* ACTIVITY */
    .activity-row { display: flex; justify-content: space-between; align-items: center; width: 100%; }
    .big-number { font-size: 2.2rem; font-weight: 800; color: var(--text-main); line-height: 1; }
    .activity-label { color: var(--text-main); font-weight: 600; font-size: 0.95rem; margin-bottom: 5px; }
    .activity-sub { font-size: 0.8rem; font-weight: 500; }
</style>

<div class="dash-grid">
    
    <div class="dash-card">
        <div>
            <h3>Cronologia Ticket</h3>
            <p style="font-size:0.85rem; color:var(--text-muted)">Andamento ultimi 7 giorni</p>
        </div>
        
        <div class="chart-container">
            <?php 
            $today = date('Y-m-d');
            foreach($daily_data as $day => $count): 
                $height = ($max_daily > 0) ? ($count / $max_daily) * 100 : 0;
                $vis_height = $height == 0 ? 2 : $height;
                
                $day_num = date('d', strtotime($day));
                $eng_month = date('M', strtotime($day));
                $ita_month = isset($mesi_it[$eng_month]) ? $mesi_it[$eng_month] : $eng_month;
                $is_today_class = ($day == $today) ? 'today-bar' : '';
            ?>
                <div class="chart-column">
                    <div class="chart-tooltip"><?php echo $count; ?> Ticket</div>
                    <div class="bar-track">
                        <div class="bar-fill <?php echo $is_today_class; ?>" style="height: <?php echo $vis_height; ?>%;"></div>
                    </div>
                    <div class="chart-meta">
                        <span class="meta-day"><?php echo $day_num; ?></span>
                        <span class="meta-month"><?php echo $ita_month; ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="dash-card">
        <div>
            <h3>Stato dei Ticket</h3>
            <p style="font-size:0.85rem; color:var(--text-muted);">Panoramica risoluzioni</p>
        </div>
        <div style="display: flex; flex-direction: column; justify-content: space-evenly; height: 100%; padding-top: 10px;">
            <div class="progress-item">
                <div class="progress-label"><span>In Lavorazione</span> <span style="color:#f97316"><?php echo $perc_active; ?>%</span></div>
                <div class="progress-track"><div class="progress-fill" style="width: <?php echo $perc_active; ?>%; background: #f97316;"></div></div>
            </div>
            <div class="progress-item">
                <div class="progress-label"><span>Risolti</span> <span style="color:#22c55e"><?php echo $perc_resolved; ?>%</span></div>
                <div class="progress-track"><div class="progress-fill" style="width: <?php echo $perc_resolved; ?>%; background: #22c55e;"></div></div>
            </div>
            <div class="progress-item">
                <div class="progress-label"><span>Archiviati</span> <span style="color:#ef4444"><?php echo $perc_closed; ?>%</span></div>
                <div class="progress-track"><div class="progress-fill" style="width: <?php echo $perc_closed; ?>%; background: #ef4444;"></div></div>
            </div>
        </div>
    </div>

    <div class="dash-card">
        <div>
            <h3>Attività</h3>
            <p style="font-size:0.85rem; color:var(--text-muted)">Metriche della piattaforma</p>
        </div>
        <div style="display: flex; flex-direction: column; justify-content: space-evenly; height: 100%; padding: 10px 0;">
            <div class="activity-row">
                <div>
                    <div class="activity-label">Ticket in Coda</div>
                    <div class="activity-sub" style="color:#f97316;">Richiedono attenzione</div>
                </div>
                <div class="big-number"><?php echo $stats['active']; ?></div>
            </div>
            
            <hr style="border:0; border-top:1px solid #f1f5f9; width:100%; opacity: 0.5;">
            
            <div class="activity-row">
                <div>
                    <div class="activity-label">Clienti Totali</div>
                    <div class="activity-sub" style="color:var(--primary);">Registrati al portale</div>
                </div>
                <div class="big-number"><?php echo $total_clients; ?></div>
            </div>
        </div>
    </div>

</div>

<div class="dash-grid">
    
    <div class="dash-card span-2">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-shrink: 0;">
            <h3>Ultimi Aggiornamenti</h3>
            <span style="background:#f1f5f9; padding:5px 12px; border-radius:8px; font-size:0.75rem; font-weight:700; color:var(--text-muted); text-transform: uppercase;">Oggi</span>
        </div>
        
        <div style="flex: 1; display: flex; flex-direction: column; justify-content: flex-start; margin-top: 15px;">
            <table class="dash-table" style="width:100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <th style="padding: 12px 0; text-align: left; font-size:0.75rem; color:var(--text-muted);">OGGETTO</th>
                        <th style="padding: 12px 15px; text-align: left; font-size:0.75rem; color:var(--text-muted);">AUTORE</th>
                        <th style="padding: 12px 15px; text-align: left; font-size:0.75rem; color:var(--text-muted);">STATO</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($t = pg_fetch_assoc($recent_tickets)): ?>
                    <tr style="border-bottom:1px solid #f8fafc;">
                        <td style="padding:15px 0;"><strong><?php echo htmlspecialchars($t['title']); ?></strong></td>
                        <td style="padding:15px;"><?php echo htmlspecialchars($t['author']); ?></td>
                        <td style="padding:15px;">
                            <?php 
                            $status_label = 'Aperto'; $status_color = '#f97316'; 
                            if ($t['status'] == 'resolved') { $status_label = 'Risolto'; $status_color = '#22c55e'; }
                            if ($t['status'] == 'closed')   { $status_label = 'Chiuso'; $status_color = '#ef4444'; }
                            ?>
                            <span style="color:<?php echo $status_color; ?>; font-weight:bold; font-size:0.85rem;">
                                ● <?php echo $status_label; ?>
                            </span>
                        </td>
                        <td style="text-align:right; padding:15px 0;">
                            <a href="index.php?page=ticket_details&id=<?php echo $t['id']; ?>" class="icon-btn">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="dash-card">
        <div>
            <h3>Azioni Rapide</h3>
            <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:10px;">Strumenti frequenti</p>
        </div>
        <div style="display: flex; flex-direction: column; justify-content: center; gap: 15px; flex: 1;">
            <button class="action-btn btn-outline" onclick="window.location.href='index.php?page=new_ticket'">
                <i class="fas fa-plus-circle" style="color:var(--primary);"></i> Crea Ticket
            </button>
            <button class="action-btn btn-outline">
                <i class="fas fa-file-csv" style="color:#10b981;"></i> Esporta Report CSV
            </button>
            <button class="action-btn btn-outline">
                <i class="fas fa-user-plus" style="color:#f59e0b;"></i> Invita Utente
            </button>
        </div>
    </div>
</div>