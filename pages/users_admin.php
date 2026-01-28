<?php
session_start();

// Se l'utente non è loggato, reindirizza al login
if (!isset($_SESSION['user_id'])) {
    // Usa ../ perché auth.php è nella cartella superiore (root)
    header("Location: ../auth.php");
    exit;
}
?>

<?php
// FILE: pages/users_admin.php
if ($_SESSION['user_role'] != 'admin') { echo "Accesso negato."; exit; }

// QUERY UTENTI
$query_users = "SELECT u.id, u.name, u.email, u.role, COUNT(t.id) as count 
                FROM users u LEFT JOIN tickets t ON u.id = t.user_id 
                WHERE u.role = 'user' 
                GROUP BY u.id, u.name, u.email, u.role 
                ORDER BY count DESC";
$res_users = pg_query($db_conn, $query_users);

// QUERY ADMIN
$query_admins = "SELECT u.id, u.name, u.email, u.role FROM users u 
                 WHERE u.role = 'admin' 
                 ORDER BY u.name ASC";
$res_admins = pg_query($db_conn, $query_admins);
?>

<style>
    .users-grid-layout {
        display: grid;
        grid-template-columns: 1fr;
        grid-template-rows: 3fr 1fr; 
        gap: 25px;
        height: 100%; 
    }
</style>

<div class="users-grid-layout">

    <div class="dash-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; flex-shrink:0;">
            <div>
                <h3>Utenti Registrati</h3>
                <p style="color:var(--text-muted); font-size:0.85rem; margin:0;">Clienti attivi sulla piattaforma</p>
            </div>
            <span class="badge badge-open" style="font-size:0.8rem;">Clienti</span>
        </div>

        <div class="card-scroll-area">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #f1f5f9;">
                        <th style="text-align:left; padding:15px 10px; color:var(--text-muted);">Utente</th>
                        <th style="text-align:left; padding:15px 10px; color:var(--text-muted);">Email</th>
                        <th style="text-align:center; padding:15px 10px; color:var(--text-muted);">Ticket</th>
                        <th style="text-align:center; padding:15px 10px; color:var(--text-muted);">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($u = pg_fetch_assoc($res_users)): 
                        $initials = strtoupper(substr($u['name'], 0, 1));
                        // CREAZIONE OGGETTO JSON SICURO
                        $userDataJs = htmlspecialchars(json_encode([
                            'id' => $u['id'], 
                            'name' => $u['name'], 
                            'email' => $u['email'], 
                            'role' => $u['role'], 
                            'initials' => $initials
                        ]), ENT_QUOTES, 'UTF-8');
                    ?>
                    <tr style="border-bottom: 1px solid #f8fafc;">
                        <td style="padding:15px 10px;">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div style="width:35px; height:35px; background:#f1f5f9; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; color:#64748b;">
                                    <?php echo $initials; ?>
                                </div>
                                <span style="font-weight:600;"><?php echo htmlspecialchars($u['name']); ?></span>
                            </div>
                        </td>
                        <td style="padding:15px 10px; color:#64748b;"><?php echo htmlspecialchars($u['email']); ?></td>
                        <td style="text-align:center;">
                            <span class="badge badge-progress"><?php echo $u['count']; ?></span>
                        </td>
                        <td style="text-align:center;">
                            <button class="icon-btn" onclick='openUserModal(<?php echo $userDataJs; ?>)' title="Modifica">
                                <i class="fas fa-cog"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="dash-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; flex-shrink:0;">
            <h3 style="color:var(--primary-dark); margin:0;">Staff Amministrativo</h3>
            <span class="badge" style="background:#e0e7ff; color:var(--primary);">Admin</span>
        </div>

        <div class="card-scroll-area" style="display:flex; gap:15px; flex-wrap:wrap; align-content:flex-start;">
            <?php while($a = pg_fetch_assoc($res_admins)): 
                $initials = strtoupper(substr($a['name'], 0, 1));
                $adminDataJs = htmlspecialchars(json_encode([
                    'id' => $a['id'], 
                    'name' => $a['name'], 
                    'email' => $a['email'], 
                    'role' => $a['role'], 
                    'initials' => $initials
                ]), ENT_QUOTES, 'UTF-8');
            ?>
            <div style="background:#f8fafc; padding:10px 15px; border-radius:12px; border:1px solid #e2e8f0; display:flex; align-items:center; gap:15px; flex: 1 1 250px; min-width: 250px; height:fit-content;">
                <div style="width:38px; height:38px; background:var(--primary); border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; color:white; flex-shrink:0;">
                    <?php echo $initials; ?>
                </div>
                <div style="flex:1; overflow:hidden;">
                    <div style="font-weight:700; color:var(--text-main);"><?php echo htmlspecialchars($a['name']); ?></div>
                    <div style="font-size:0.75rem; color:var(--text-muted);"><?php echo htmlspecialchars($a['email']); ?></div>
                </div>
                <button class="icon-btn" onclick='openUserModal(<?php echo $adminDataJs; ?>)'>
                    <i class="fas fa-cog"></i>
                </button>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<style> .icon-btn:hover i.fa-cog { color: var(--primary) !important; transform: rotate(90deg); transition:0.3s; } </style>