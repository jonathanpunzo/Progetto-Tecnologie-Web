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
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - HelpDesk</title>
    
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* --- STILI SPECIFICI PER LA HOME --- */

        /* 1. NAVBAR MIGLIORATA */
        nav {
            background-color: var(--sidebar-dark);
            padding: 0.8rem 2rem; /* Padding ridotto per il logo */
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        /* Logo Composito */
        .brand-container {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            text-decoration: none;
        }
        
        /* Menu Utente */
        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 0.95rem;
        }
        .user-badge {
            background: rgba(255,255,255,0.1);
            padding: 5px 12px;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        /* 2. HERO SECTION */
        .hero-section {
            background: linear-gradient(180deg, rgba(37,99,235,0.03) 0%, rgba(37,99,235,0.08) 100%);
            padding: 80px 40px;
            border-radius: var(--radius);
            margin-bottom: 40px;
            text-align: center;
            border: 1px solid rgba(37, 99, 235, 0.1);
            position: relative;
            overflow: hidden;
        }

        .hero-title { 
            font-size: 3rem; 
            font-weight: 800; 
            margin-bottom: 20px; 
            letter-spacing: -1.5px;
            color: var(--sidebar-dark);
        }

        .hero-text { 
            font-size: 1.15rem; 
            line-height: 1.7;
            max-width: 650px; 
            margin: 0 auto 35px auto; 
            color: var(--text-muted);
            font-weight: 500;
        }
        
        .btn-hero {
            background: var(--primary); 
            color: white; 
            padding: 15px 40px; 
            font-size: 1.1rem;
            border-radius: 50px; 
            font-weight: 700; 
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.25);
            transition: all 0.3s ease;
            display: inline-block;
        }
        .btn-hero:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 15px 35px rgba(37, 99, 235, 0.35); 
            text-decoration: none; 
            color: white;
            background-color: var(--primary-hover);
        }

        /* 3. FAQ GRID */
        .faq-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .faq-card {
            background: white;
            padding: 25px;
            border-radius: var(--radius);
            border: 1px solid #e2e8f0;
            box-shadow: var(--shadow-sm);
            transition: transform 0.2s, border-color 0.2s;
        }
        .faq-card:hover { transform: translateY(-5px); border-color: var(--primary); }
        .faq-question { font-weight: 700; color: var(--sidebar-dark); margin-bottom: 10px; display: flex; gap: 10px; }
        .faq-answer { color: var(--text-muted); font-size: 0.95rem; line-height: 1.6; }

        /* 4. DASHBOARD HEADER */
        .dash-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #f1f5f9;
        }
        .welcome-text h1 { margin: 0; font-size: 1.8rem; }
        .welcome-text p { margin: 5px 0 0 0; color: var(--text-muted); }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            nav { flex-direction: column; gap: 15px; text-align: center; }
            .dash-header { flex-direction: column; gap: 15px; text-align: center; align-items: stretch; }
            .brand-container { justify-content: center; }
            .hero-title { font-size: 1.8rem; }
        }
    </style>
</head>
<body>

    <nav>
        <a href="index.php" class="brand-container">
            <img src="icon/logobanner.png" alt="HelpDesk Logo" class="brand-logo-img">
        </a>

        <div class="user-menu">
            <?php if ($is_logged): ?>
                <div class="desktop-only user-badge">
                    <i class="far fa-user"></i> <strong><?php echo htmlspecialchars($user_name); ?></strong> 
                    <span style="opacity:0.7; font-size:0.85em;">(<?php echo ucfirst($role); ?>)</span>
                </div>
                <a href="logout.php" class="btn-style" style="background-color: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);">
                    <i class="fas fa-sign-out-alt"></i> Esci
                </a>
            <?php else: ?>
                <a href="auth.php" class="btn-style"><i class="fas fa-sign-in-alt"></i> Accedi</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container" style="border:none; box-shadow:none; background:transparent; padding:0; margin-top: 30px;">
        
        <?php if ($is_logged): ?>

            <div style="background: white; padding: 30px; border-radius: var(--radius); box-shadow: var(--card-shadow); border: 1px solid #e2e8f0;">
                
                <div class="dash-header">
                    <div class="welcome-text">
                        <h1><i class="fas fa-columns"></i> Dashboard</h1>
                        <p>Gestisci le tue segnalazioni in modo semplice e veloce.</p>
                    </div>
                    <?php if ($role != 'admin'): ?>
                        <a href="new_ticket.php" class="btn-new">
                            <i class="fas fa-plus-circle"></i> Nuovo Ticket
                        </a>
                    <?php endif; ?>
                </div>

                <?php
                    if ($role == 'admin') {
                        $query = "SELECT t.*, u.name as author_name FROM tickets t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC";
                    } else {
                        $user_id = $_SESSION['user_id'];
                        $query = "SELECT * FROM tickets WHERE user_id = $user_id ORDER BY created_at DESC";
                    }
                    $result = pg_query($db_conn, $query);
                ?>

                <?php if ($result && pg_num_rows($result) > 0): ?>
                    <div class="table-responsive">
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
                                    <td><strong>#<?php echo $row['id']; ?></strong></td>
                                    <?php if($role == 'admin') echo "<td>" . htmlspecialchars($row['author_name']) . "</td>"; ?>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    
                                    <td class="status-<?php echo $row['status']; ?>">
                                        <?php 
                                            $icon = "";
                                            if($row['status']=='open') $icon = "<i class='fas fa-circle-notch'></i>";
                                            if($row['status']=='in-progress') $icon = "<i class='fas fa-wrench'></i>";
                                            if($row['status']=='resolved') $icon = "<i class='fas fa-check'></i>";
                                            if($row['status']=='closed') $icon = "<i class='fas fa-lock'></i>";
                                            echo "$icon " . strtoupper($row['status']); 
                                        ?>
                                    </td>
                                    
                                    <td>
                                        <?php 
                                            $prio = $row['priority'];
                                            $color = ($prio=='urgent' || $prio=='high') ? 'red' : 'inherit';
                                            echo "<span style='color:$color'>" . ucfirst($prio) . "</span>";
                                        ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <a href="ticket_details.php?id=<?php echo $row['id']; ?>" style="font-weight:bold;">
                                            Gestisci &rarr;
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 50px; color: var(--text-muted);">
                        <i class="far fa-folder-open" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.5;"></i>
                        <h3>Nessun ticket presente</h3>
                        <p>Non ci sono segnalazioni da mostrare al momento.</p>
                        <?php if($role != 'admin'): ?>
                            <a href="new_ticket.php" style="color: var(--primary); font-weight: bold;">Aprine uno ora</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            
            <div class="hero-section">
                <h1 class="hero-title">HAI BISOGNO DI <strong>ASSISTENZA</strong>?</h1>
                <p class="hero-text">Benvenuto nel portale di supporto iFantastici4. <br>Apri un ticket, monitora lo stato della tua richiesta e ricevi supporto!</p>
                <a href="auth.php" class="btn-hero"><i class="fas fa-rocket"></i> Inizia Subito</a>
            </div>
            
            <h2 style="text-align: center; color: var(--sidebar-dark); margin-bottom: 10px;">Domande Frequenti</h2>
            <p style="text-align: center; color: var(--text-muted); margin-bottom: 30px;">Trova risposte immediate prima di aprire un ticket</p>

            <div class="faq-grid">
                <?php
                $faq_query = "SELECT * FROM faqs";
                $faq_res = pg_query($db_conn, $faq_query);
                
                if($faq_res):
                    while ($faq = pg_fetch_assoc($faq_res)): 
                ?>
                    <div class="faq-card">
                        <div class="faq-question">
                            <i class="far fa-question-circle" style="color: var(--primary); margin-top: 2px;"></i>
                            <?php echo htmlspecialchars($faq['question']); ?>
                        </div>
                        <div class="faq-answer">
                            <?php echo htmlspecialchars($faq['answer']); ?>
                        </div>
                    </div>
                <?php 
                    endwhile; 
                endif;
                ?>
            </div>

            <div style="margin-top: 50px; text-align: center; padding-bottom: 40px;">
                <p style="color: var(--text-muted);">Non hai trovato la risposta che cercavi?</p>
                <a href="auth.php" style="font-weight: bold;">Contatta il supporto &rarr;</a>
            </div>

        <?php endif; ?>

    </div> 

    <footer class="main-footer">
        <p>
            Made with <span class="heart-beat">❤️</span> da: 
            <strong>I Fantastici 4</strong> 
        </p>
        <p style="opacity: 0.8; font-size: 0.85em;">Esame di Tecnologie Web 2025/2026</p>
        
        <a href="chi_siamo.php" class="btn-style" style="margin-top: 10px; background: rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.3);">
            Chi Siamo
        </a>
    </footer>

</body>
</html>