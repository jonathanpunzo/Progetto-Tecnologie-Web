<?php
// FILE: pages/users_admin.php
if ($_SESSION['user_role'] != 'admin') {
    echo "Accesso negato."; exit;
}

$query = "SELECT u.id, u.name, u.email, COUNT(t.id) as count 
          FROM users u LEFT JOIN tickets t ON u.id = t.user_id 
          WHERE u.role = 'user' 
          GROUP BY u.id, u.name, u.email 
          ORDER BY count DESC";
$res = pg_query($db_conn, $query);
?>

<div class="table-card">
    <h2>👥 Gestione Utenti</h2>
    <table style="margin-top: 20px;">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Ticket Aperti</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php while($u = pg_fetch_assoc($res)): ?>
            <tr>
                <td>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:30px; height:30px; background:#e2e8f0; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold; color:#64748b;">
                            <?php echo strtoupper(substr($u['name'],0,1)); ?>
                        </div>
                        <?php echo htmlspecialchars($u['name']); ?>
                    </div>
                </td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td><span class="badge badge-progress"><?php echo $u['count']; ?> Ticket</span></td>
                <td>
                    <a href="mailto:<?php echo $u['email']; ?>" class="icon-btn" title="Invia Email"><i class="far fa-envelope"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>