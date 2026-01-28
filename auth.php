<?php
// ==========================================================
// FILE: auth.php (Versione Fix: Switch Button Fisso in Basso a Destra)
// ==========================================================
session_start();
require_once('db.php');

$error_msg = "";
$success_msg = "";
$sticky_email = "";

$initial_view = (isset($_POST['action']) && $_POST['action'] == 'register' && empty($success_msg)) ? 'register' : 'login';

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
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accedi - HelpDesk iFantastici4</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0; padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: url('https://images.unsplash.com/photo-1451187580459-43490279c0fa?q=80&w=2072&auto=format&fit=crop') no-repeat center center fixed;
            background-size: cover;
            position: relative;
            color: var(--text-main);
        }

        body::before {
            content: '';
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(8px);
            z-index: -1;
        }

        @keyframes zoomIn {
            from { opacity: 0; transform: scale(0.95) translateY(20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .login-container {
            width: 90%;
            max-width: 1200px;
            min-height: 720px;
            background-color: #0f172a; /* Colore scuro per evitare bordi bianchi arrotondati */
            border-radius: 24px;
            box-shadow: 0 40px 80px -20px rgba(0,0,0,0.6);
            overflow: hidden;
            
            display: grid;
            grid-template-columns: 1fr;
            animation: zoomIn 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
        }

        /* LATO SINISTRO (Immagine) */
        .login-image-side {
            background: url('https://images.unsplash.com/photo-1550751827-4bd374c3f58b?q=80&w=2070&auto=format&fit=crop') center/cover no-repeat;
            position: relative;
            display: none;
        }
        .image-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(to bottom, rgba(15, 23, 42, 0.4), rgba(15, 23, 42, 0.9)); 
            display: flex; flex-direction: column; justify-content: flex-end;
            padding: 60px;
            color: white;
        }
        .image-overlay h2 { 
            font-size: 2.5rem; line-height: 1.2; margin-bottom: 20px; font-weight: 800;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        .image-overlay p { font-size: 1.1rem; opacity: 0.9; line-height: 1.6; max-width: 90%; }

        /* LATO DESTRO (Form) */
        .login-form-side {
            padding: 60px;
            padding-bottom: 100px; /* Spazio extra in basso per il pulsante fisso */
            display: flex; flex-direction: column; justify-content: center;
            position: relative;
            background: white; 
        }

        .forms-stack {
            display: grid; grid-template-columns: 1fr; grid-template-rows: 1fr;
            align-items: center; position: relative; width: 100%;
        }

        .form-box {
            grid-column: 1 / -1; grid-row: 1 / -1;
            transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            background: white;
            opacity: 0; transform: translateX(60px); pointer-events: none; visibility: hidden;
        }
        .form-box.active { opacity: 1; transform: translateX(0); pointer-events: all; visibility: visible; z-index: 2; }

        h1 { font-size: 2.5rem; font-weight: 900; color: #0f172a; margin-bottom: 10px; letter-spacing: -1px; }
        .subtitle { color: var(--text-muted); margin-bottom: 40px; font-size: 1.05rem; }

        .input-group { position: relative; margin-bottom: 20px; }
        .input-group i {
            position: absolute; left: 18px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; font-size: 1.1rem; transition: color 0.3s;
        }
        .input-group input {
            width: 100%; padding: 16px 20px 16px 50px;
            border: 2px solid #e2e8f0; border-radius: 12px;
            font-size: 1rem; background: #f8fafc; color: var(--text-main); font-family: inherit;
            transition: all 0.2s;
        }
        .input-group input:focus {
            border-color: var(--primary); background: white;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1); outline: none;
        }
        .input-group input:focus + i { color: var(--primary); }

        .btn-login {
            width: 100%; padding: 16px;
            font-size: 1.1rem; border-radius: 12px; margin-top: 15px;
            background: var(--primary); color: white; border: none; font-weight: 700;
            cursor: pointer; transition: all 0.3s ease;
            box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4);
        }
        .btn-login:hover { background-color: var(--primary-hover); transform: translateY(-2px); box-shadow: 0 15px 30px -5px rgba(79, 70, 229, 0.5); }

        .btn-register-green { background-color: #10b981 !important; box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.4); }
        .btn-register-green:hover { background-color: #059669 !important; box-shadow: 0 15px 30px -5px rgba(16, 185, 129, 0.5); }

        /* --- NUOVI STILI SWITCH BUTTON FISSO --- */
        .switch-area-fixed {
            position: absolute;
            bottom: 40px;
            right: 50px;
            text-align: right;
            z-index: 10;
        }

        .switch-content {
            position: absolute;
            bottom: 0; right: 0;
            width: 300px; /* Larghezza fissa per evitare reflow */
            text-align: right;
            color: var(--text-muted); font-size: 0.95rem;
            
            opacity: 0;
            transform: translateY(10px); /* Piccola entrata dal basso */
            transition: all 0.4s ease;
            pointer-events: none;
        }

        .switch-content.active {
            opacity: 1;
            transform: translateY(0);
            pointer-events: all;
        }

        .switch-content a { color: var(--primary); font-weight: 700; cursor: pointer; text-decoration: none; transition: 0.2s; }
        .switch-content a:hover { text-decoration: underline; color: var(--primary-hover); }

        /* ALERTS */
        .alert { padding: 15px; border-radius: 12px; margin-bottom: 25px; display: flex; align-items: center; gap: 12px; font-weight: 600; font-size: 0.95rem; animation: slideDown 0.4s ease; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        @keyframes slideDown { from { transform: translateY(-10px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        @media (min-width: 1024px) {
            .login-container { grid-template-columns: 1fr 1.1fr; }
            .login-image-side { display: block; }
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-image-side">
        <div class="image-overlay">
            <h2>Tecnologia al servizio <br>della tua efficienza.</h2>
            <p>Accedi alla dashboard iFantastici4 per gestire ticket, monitorare le performance e collaborare con il tuo team.</p>
        </div>
    </div>

    <div class="login-form-side">
        <div id="alert-area">
            <?php if($error_msg): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?></div><?php endif; ?>
            <?php if($success_msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success_msg; ?></div><?php endif; ?>
        </div>

        <div class="forms-stack">
            <div id="login-box" class="form-box <?php echo ($initial_view == 'login') ? 'active' : ''; ?>">
                <h1>Bentornato</h1>
                <p class="subtitle">Inserisci le tue credenziali per accedere.</p>
                <form method="POST" action="auth.php">
                    <input type="hidden" name="action" value="login">
                    <div class="input-group">
                        <i class="far fa-envelope"></i>
                        <input type="email" name="log_email" placeholder="Email aziendale" value="<?php echo $sticky_email; ?>" required>
                    </div>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="log_pwd" placeholder="Password" required>
                    </div>
                    <div style="margin-bottom:25px; font-size:0.9rem; color:var(--text-muted);">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox"> Ricordami
                        </label>
                    </div>
                    <button type="submit" class="btn-login">Accedi al Portale</button>
                </form>
            </div>

            <div id="register-box" class="form-box <?php echo ($initial_view == 'register') ? 'active' : ''; ?>">
                <h1>Nuovo Account</h1>
                <p class="subtitle">Unisciti al team in pochi secondi.</p>
                <form method="POST" action="auth.php" onsubmit="return validateReg()">
                    <input type="hidden" name="action" value="register">
                    <div class="input-group">
                        <i class="far fa-user"></i>
                        <input type="text" name="reg_name" placeholder="Nome e Cognome" required>
                    </div>
                    <div class="input-group">
                        <i class="far fa-envelope"></i>
                        <input type="email" name="reg_email" placeholder="Email aziendale" required>
                    </div>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="reg_pwd" name="reg_pwd" placeholder="Crea Password" required>
                    </div>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="reg_pwd2" name="reg_pwd2" placeholder="Conferma Password" required>
                    </div>
                    <button type="submit" class="btn-login btn-register-green">Registrati Ora</button>
                </form>
            </div>
        </div> 

        <div class="switch-area-fixed">
            <div id="switch-to-reg" class="switch-content <?php echo ($initial_view == 'login') ? 'active' : ''; ?>">
                Non hai un account? <a onclick="switchView('register')">Crea un account</a>
            </div>
            <div id="switch-to-log" class="switch-content <?php echo ($initial_view == 'register') ? 'active' : ''; ?>">
                Hai già un account? <a onclick="switchView('login')">Torna al Login</a>
            </div>
        </div>

    </div> 
</div>

<script>
    function switchView(target) {
        const loginBox = document.getElementById('login-box');
        const regBox = document.getElementById('register-box');
        
        // Nuovi elementi switch fissi
        const switchBtnReg = document.getElementById('switch-to-reg');
        const switchBtnLog = document.getElementById('switch-to-log');

        const alertArea = document.getElementById('alert-area');
        if(alertArea) alertArea.innerHTML = '';

        if (target === 'register') {
            // Form Transitions
            loginBox.style.transform = "translateX(-60px)";
            loginBox.style.opacity = "0";
            loginBox.classList.remove('active');
            
            regBox.classList.add('active');
            setTimeout(() => {
                regBox.style.transform = "translateX(0)";
                regBox.style.opacity = "1";
            }, 50);

            // Button Transitions (Fade in place)
            switchBtnReg.classList.remove('active'); // Nascondi "Crea account"
            setTimeout(() => { switchBtnLog.classList.add('active'); }, 200); // Mostra "Torna login"

        } else {
            // Form Transitions
            regBox.style.transform = "translateX(60px)";
            regBox.style.opacity = "0";
            regBox.classList.remove('active');

            loginBox.classList.add('active');
            setTimeout(() => {
                loginBox.style.transform = "translateX(0)";
                loginBox.style.opacity = "1";
            }, 50);

            // Button Transitions (Fade in place)
            switchBtnLog.classList.remove('active'); // Nascondi "Torna login"
            setTimeout(() => { switchBtnReg.classList.add('active'); }, 200); // Mostra "Crea account"
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