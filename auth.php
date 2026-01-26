<?php
// ==========================================================
// FILE: auth.php (Versione Finale: Animazione Ingresso + Layout Fisso)
// ==========================================================
session_start();
require_once('db.php');

$error_msg = "";
$success_msg = "";
$sticky_email = "";

// Determina la vista iniziale
$initial_view = (isset($_POST['action']) && $_POST['action'] == 'register' && empty($success_msg)) ? 'register' : 'login';

// --- LOGICA PHP (Invariata) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action']; 

    // REGISTRAZIONE
    if ($action == 'register') {
        $name = pg_escape_string($db_conn, $_POST['reg_name']);
        $email = pg_escape_string($db_conn, $_POST['reg_email']);
        $pwd = $_POST['reg_pwd'];
        $pwd2 = $_POST['reg_pwd2'];

        if ($pwd !== $pwd2) { $error_msg = "Le password non coincidono!"; }
        elseif (strlen($pwd) < 4) { $error_msg = "Password troppo corta (min 4)."; }
        else {
            $hashed_pwd = password_hash($pwd, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$hashed_pwd', 'user')";
            if (@pg_query($db_conn, $query)) {
                $success_msg = "Registrazione completata! Effettua il login.";
                $initial_view = 'login'; 
            } else { 
                $error_msg = "Email già registrata."; 
                $initial_view = 'register';
            }
        }
    }

    // LOGIN
    if ($action == 'login') {
        $email = pg_escape_string($db_conn, $_POST['log_email']);
        $pwd = $_POST['log_pwd'];
        $result = pg_query($db_conn, "SELECT * FROM users WHERE email = '$email'");
        $user = pg_fetch_assoc($result);

        if ($user && password_verify($pwd, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            header("Location: index.php"); exit;
        } else {
            $error_msg = "Credenziali errate.";
            $sticky_email = htmlspecialchars($email);
            $initial_view = 'login';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Accedi - iFantastici4 HelpDesk</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* --- GESTIONE CONFLITTI CON STYLE.CSS --- */
    body {
        /* Override necessario: style.css mette padding-bottom 220px per il footer.
           Qui lo rimuoviamo per centrare perfettamente il login. */
        padding-bottom: 0 !important; 
        
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        
        /* Sfondo personalizzato per il login */
        background: url('https://images.unsplash.com/photo-1521737711867-e3b97375f902?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80') no-repeat center center fixed;
        background-size: cover;
        backdrop-filter: blur(8px); /* Effetto vetro sullo sfondo */
        margin: 0;
    }

    /* --- ANIMAZIONE DI INGRESSO PAGINA --- */
    @keyframes pageEntrance {
        from {
            opacity: 0;
            transform: translateY(30px) scale(0.98);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .login-container {
        /* Applichiamo l'animazione all'intero contenitore */
        animation: pageEntrance 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
        
        background-color: #fff;
        border-radius: 20px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.3); /* Ombra più profonda */
        overflow: hidden;
        display: grid;
        grid-template-columns: 1fr;
        width: 100%;
        max-width: 1000px;
        min-height: 650px; /* Altezza fissa per evitare scatti */
        position: relative;
    }

    /* --- LATO SINISTRO (IMMAGINE) --- */
    .login-image-side {
        background: url('https://images.unsplash.com/photo-1600880292203-757bb62b4baf?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80') center/cover no-repeat;
        position: relative;
        display: none; 
    }
    .image-overlay {
        position: absolute; bottom: 0; left: 0; width: 100%; padding: 40px;
        background: linear-gradient(to top, rgba(0,0,0,0.9), transparent); 
        color: white;
    }
    .image-overlay h2 { 
        color: white; margin: 0; font-size: 1.8rem; line-height: 1.3;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    /* --- LATO DESTRO (FORM) --- */
    .login-form-side {
        padding: 50px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
    }

    .logo-text {
        color: var(--primary); font-weight: 800; font-size: 1.3rem;
        margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
        z-index: 10; 
    }

    /* --- SISTEMA STACKING PER TRANSIZIONI INTERNE --- */
    .forms-stack {
        display: grid;
        grid-template-columns: 1fr;
        grid-template-rows: 1fr;
        align-items: center; 
    }

    .form-box {
        grid-column: 1 / -1;
        grid-row: 1 / -1;
        transition: all 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55); /* Rimbalzo elastico */
        background: white;
        
        opacity: 0;
        transform: translateX(50px);
        pointer-events: none;
        visibility: hidden;
    }

    .form-box.active {
        opacity: 1;
        transform: translateX(0);
        pointer-events: all;
        visibility: visible;
        z-index: 2;
    }

    /* --- STILI INPUT E TESTI --- */
    h1 { font-size: 2rem; margin-bottom: 5px; color: var(--sidebar-dark); letter-spacing: -1px; }
    .subtitle { color: var(--text-muted); margin-bottom: 25px; font-size: 0.95rem; }

    .input-group { 
        position: relative; 
        margin-bottom: 12px; /* RIDOTTO: Era 20px -> Ora 12px (Più compatto) */
    }
    
    .input-group i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; transition: color 0.3s;}
    
    .input-group input {
        width: 100%; 
        padding: 12px 15px 12px 45px; /* RIDOTTO: Padding verticale da 15px a 12px */
        border: 2px solid #e2e8f0; border-radius: 10px; font-size: 1rem;
        transition: all 0.3s; background: #f8fafc; color: var(--text-main); font-family: inherit;
    }
    .input-group input:focus {
        border-color: var(--primary); background: white; box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); outline: none;
    }
    .input-group input:focus + i { color: var(--primary); }


    .form-options { display: flex; justify-content: space-between; margin-bottom: 25px; font-size: 0.9rem; color: var(--text-muted); }
    .forgot-link { color: var(--primary); font-weight: 600; transition: color 0.2s; }
    .forgot-link:hover { color: var(--primary-hover); text-decoration: underline; }

    /* Bottoni */
    .btn-login {
        width: 100%; padding: 12px; /* Ridotto leggermente */
        font-size: 1.1rem; border-radius: 10px; margin-top: 10px;
        background: var(--primary); box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        border: none; color: white; font-weight: bold; cursor: pointer; transition: all 0.3s ease;
    }
    .btn-login:hover { background-color: var(--primary-hover); transform: translateY(-2px); box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4); }

    /* Bottone Verde per Registrazione */
    .btn-register {
        background-color: #16a34a !important; /* Verde Fisso */
        box-shadow: 0 4px 15px rgba(22, 163, 74, 0.3);
    }
    .btn-register:hover {
        background-color: #15803d !important;
        box-shadow: 0 8px 20px rgba(22, 163, 74, 0.4);
    }

    .switch-link { text-align: center; margin-top: 25px; color: var(--text-muted); }
    .switch-link a { color: var(--primary); font-weight: 700; cursor: pointer; transition: opacity 0.2s;}
    .switch-link a:hover { opacity: 0.8; }

    /* Alert Messaggi */
    .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; animation: slideDown 0.4s ease; font-weight: 500;}
    .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
    .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }

    @keyframes slideDown { from { transform: translateY(-10px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

    /* RESPONSIVE */
    @media (min-width: 992px) {
        .login-container { grid-template-columns: 1fr 1.2fr; }
        .login-image-side { display: block; }
    }
</style>
</head>
<body>

<div class="login-container">
    <div class="login-image-side">
        <div class="image-overlay">
            <h2>Il tuo supporto <br>La nostra priorità. <br>Sempre qui per te.</h2>
        </div>
    </div>

    <div class="login-form-side">
        <div class="logo-text">
            <i class="fas fa-headset"></i> HelpDesk iFantastici4
        </div>

        <div id="alert-area">
            <?php if($error_msg): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?></div><?php endif; ?>
            <?php if($success_msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success_msg; ?></div><?php endif; ?>
        </div>

        <div class="forms-stack">

            <div id="login-box" class="form-box <?php echo ($initial_view == 'login') ? 'active' : ''; ?>">
                <h1>Bentornato!</h1>
                <p class="subtitle">Accedi per continuare a gestire i tuoi ticket.</p>
                
                <form method="POST" action="auth.php">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="input-group">
                        <i class="far fa-envelope"></i>
                        <input type="email" name="log_email" placeholder="Indirizzo Email" value="<?php echo $sticky_email; ?>" required>
                    </div>
                    
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="log_pwd" placeholder="Password" required>
                    </div>
                    
                    <div class="form-options">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" style="width: auto; margin: 0;"> Ricordami
                        </label>
                    </div>
                    
                    <button type="submit" class="btn-login">Accedi</button>
                </form>
                
                <div class="switch-link">
                    Non hai un account? <a onclick="switchView('register')">Registrati</a>
                </div>
            </div>

            <div id="register-box" class="form-box <?php echo ($initial_view == 'register') ? 'active' : ''; ?>">
                <h1>Crea Account</h1>
                <p class="subtitle">Unisciti a noi in pochi secondi.</p>
                
                <form method="POST" action="auth.php" onsubmit="return validateReg()">
                    <input type="hidden" name="action" value="register">
                    
                    <div class="input-group">
                        <i class="far fa-user"></i>
                        <input type="text" name="reg_name" placeholder="Nome Completo" required>
                    </div>
                    <div class="input-group">
                        <i class="far fa-envelope"></i>
                        <input type="email" name="reg_email" placeholder="Indirizzo Email" required>
                    </div>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="reg_pwd" name="reg_pwd" placeholder="Crea Password" required>
                    </div>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="reg_pwd2" name="reg_pwd2" placeholder="Conferma Password" required>
                    </div>
                    
                    <button type="submit" class="btn-login" style="background: #16a34a !important; color: white;">Registrati</button>
                </form>
                
                <div class="switch-link">
                    Hai già un account? <a onclick="switchView('login')">Torna al Login</a>
                </div>
            </div>

        </div> 
    </div> 
</div>

<script>
    function switchView(target) {
        const loginBox = document.getElementById('login-box');
        const regBox = document.getElementById('register-box');
        const alertArea = document.getElementById('alert-area');

        // Reset messaggi errore
        if(alertArea) alertArea.innerHTML = '';

        if (target === 'register') {
            // Animazione Uscita Login
            loginBox.style.transform = "translateX(-50px)";
            loginBox.style.opacity = "0";
            loginBox.classList.remove('active');
            
            // Animazione Entrata Register
            regBox.classList.add('active');
            setTimeout(() => {
                regBox.style.transform = "translateX(0)";
                regBox.style.opacity = "1";
            }, 20);
            
        } else {
            // Animazione Uscita Register
            regBox.style.transform = "translateX(50px)";
            regBox.style.opacity = "0";
            regBox.classList.remove('active');

            // Animazione Entrata Login
            loginBox.classList.add('active');
            setTimeout(() => {
                loginBox.style.transform = "translateX(0)";
                loginBox.style.opacity = "1";
            }, 20);
        }
    }

    function validateReg() {
        var p1 = document.getElementById('reg_pwd').value;
        var p2 = document.getElementById('reg_pwd2').value;
        if (p1 !== p2) { alert("Le password non coincidono!"); return false; }
        if (p1.length < 4) { alert("Password troppo corta!"); return false; }
        return true;
    }
</script>

</body>
</html>