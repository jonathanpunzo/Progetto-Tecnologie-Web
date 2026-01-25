<?php
session_start();
require_once('db.php');

// Verifichiamo se l'utente è loggato
$is_logged = isset($_SESSION['user_id']);
$user_name = $is_logged ? $_SESSION['user_name'] : 'Ospite';
$role = $is_logged ? $_SESSION['user_role'] : '';

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - HelpDesk</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <nav>
        <div class="logo">supporto<strong>iFantastici4</strong></div>
        <div class="menu">
            <?php if ($is_logged): ?>
                <span>Ciao, <strong><?php echo htmlspecialchars($user_name); ?></strong> (<?php echo $role; ?>)</span>
                | <a href="logout.php" style="color: #ff9999;">Esci</a>
            <?php else: ?>
                <a href="auth.php" class="btn-style">Accedi / Registrati</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container" >
        
        <?php if ($is_logged): ?>

        <!--             
            // LOGICA RUOLI:
            // Admin -> Vede TUTTI i ticket
            // User  -> Vede solo i SUOI ticket
        -->
            <?php if ($role == 'admin') { ?>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h1>I tuoi Ticket</h1>
                </div>

            <?php
                // Qui usiamo l'alias "t" per tickets, quindi "t.created_at" funziona
                $query = "SELECT t.*, u.name as author_name FROM tickets t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC";
            } else { ?>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h1>I tuoi Ticket</h1>
                    <a href="new_ticket.php" class="btn-new">+ Nuovo Ticket</a>
                </div>
            <?php

                $user_id = $_SESSION['user_id'];
                // CORREZIONE QUI SOTTO: Ho rimosso "t." prima di created_at perché non stiamo usando alias
                $query = "SELECT * FROM tickets WHERE user_id = $user_id ORDER BY created_at DESC";
            }

            $result = pg_query($db_conn, $query);
            ?>

            <?php if ($result && pg_num_rows($result) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <?php if($role == 'admin') echo "<th>Utente</th>"; ?>
                            <th>Oggetto</th>
                            <th>Stato</th>
                            <th>Priorità</th>
                            <th>Data</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = pg_fetch_assoc($result)): ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <?php if($role == 'admin') echo "<td>" . htmlspecialchars($row['author_name']) . "</td>"; ?>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td class="status-<?php echo $row['status']; ?>"><?php echo strtoupper($row['status']); ?></td>
                            <td><?php echo $row['priority']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                            <td><a href="ticket_details.php?id=<?php echo $row['id']; ?>">Vedi</a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Non hai ancora aperto nessun ticket.</p>
            <?php endif; ?>

        <?php else: ?>
            <h1>Benvenuto nel Centro Assistenza</h1>
            <p>Accedi per aprire una segnalazione.</p>
            
            <h2>Domande Frequenti (FAQ)</h2>
            <?php
            // Recuperiamo le FAQ dal database
            $faq_query = "SELECT * FROM faqs";
            $faq_res = pg_query($db_conn, $faq_query);
            
            while ($faq = pg_fetch_assoc($faq_res)) {
                echo "<div style='background: #fff; border: 1px solid #ddd; padding: 15px; margin-bottom: 10px;'>";
                echo "<h3 style='margin-top:0;'>❓ " . htmlspecialchars($faq['question']) . "</h3>";
                echo "<p>💡 " . htmlspecialchars($faq['answer']) . "</p>";
                echo "</div>";
            }
            ?>
        <?php endif; ?>

    </div>

    <footer class="main-footer">
        <p>
            Made with <span class="heart-beat">❤️</span> da: 
            <strong>Mattia Letteriello</strong>, 
            <strong>Jonathan Punzo</strong>, 
            <strong>Antonia Lucia Lamberti</strong>, 
            <strong>Valentino Potapchuk</strong>.
        </p>
        <p style="opacity: 0.8; font-size: 0.85em;">Esame di Tecnologie Web 2025/2026</p>
        
        <a href="chi_siamo.php" class="btn-style">Chi Siamo</a>
    </footer>

</body>
</html>