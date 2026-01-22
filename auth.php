<?php
// AVVIA LA SESSIONE
session_start();
require_once('db.php');

$error_msg = "";
$success_msg = "";

// LOGICA PHP (Identica a prima)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action']; 

    // REGISTRAZIONE
    if ($action == 'register') {
        $name = pg_escape_string($db_conn, $_POST['reg_name']);
        $email = pg_escape_string($db_conn, $_POST['reg_email']);
        $pwd = $_POST['reg_pwd'];
        $pwd2 = $_POST['reg_pwd2'];

        if ($pwd !== $pwd2) {
            $error_msg = "Le password non coincidono!";
        } else {
            $hashed_pwd = password_hash($pwd, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$hashed_pwd', 'user')";
            
            // Usiamo il silence operator (@) per gestire l'errore duplicate key manualmente
            $result = @pg_query($db_conn, $query);

            if ($result) {
                $success_msg = "Registrazione completata! Ora effettua il login.";
            } else {
                $error_msg = "Errore: Email già registrata o dati non validi.";
            }
        }
    }

    // LOGIN
    if ($action == 'login') {
        $email = pg_escape_string($db_conn, $_POST['log_email']);
        $pwd = $_POST['log_pwd'];

        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = pg_query($db_conn, $query);
        $user = pg_fetch_assoc($result);

        if ($user && password_verify($pwd, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            header("Location: index.php");
            exit;
        } else {
            $error_msg = "Credenziali errate.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Benvenuto - HelpDesk</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* CSS SPECIFICO PER QUESTA PAGINA */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #e9ecef;
        }
        .auth-card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .hidden { display: none; }
        .toggle-link {
            margin-top: 20px;
            font-size: 0.9em;
            color: #666;
        }
        .toggle-link a {
            color: #1a73e8;
            cursor: pointer;
            font-weight: bold;
        }
        input { margin-bottom: 15px; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 5px; font-size: 0.9em;}
        .alert-error { background: #fee2e2; color: #dc2626; }
        .alert-success { background: #d1fae5; color: #059669; }
    </style>
</head>
<body>

<div class="auth-card">
    <h2 style="margin-bottom: 20px; color: #1a73e8;">HelpDesk Login</h2>

    <?php if($error_msg): ?> <div class="alert alert-error"><?php echo $error_msg; ?></div> <?php endif; ?>
    <?php if($success_msg): ?> <div class="alert alert-success"><?php echo $success_msg; ?></div> <?php endif; ?>

    <div id="login-form">
        <form method="POST" action="auth.php">
            <input type="hidden" name="action" value="login">
            <input type="email" name="log_email" placeholder="Email" required>
            <input type="password" name="log_pwd" placeholder="Password" required>
            <button type="submit" style="width:100%">Accedi</button>
        </form>
        <div class="toggle-link">
            Non hai un account? <a onclick="toggleForms()">Registrati ora</a>
        </div>
    </div>

    <div id="register-form" class="hidden">
        <form method="POST" action="auth.php" onsubmit="return validateRegister()">
            <input type="hidden" name="action" value="register">
            <input type="text" name="reg_name" placeholder="Nome Completo" required>
            <input type="email" name="reg_email" placeholder="Email" required>
            <input type="password" name="reg_pwd" id="reg_pwd" placeholder="Password" required>
            <input type="password" name="reg_pwd2" id="reg_pwd2" placeholder="Conferma Password" required>
            <button type="submit" style="width:100%; background-color: #28a745;">Crea Account</button>
        </form>
        <div class="toggle-link">
            Hai già un account? <a onclick="toggleForms()">Accedi</a>
        </div>
    </div>
</div>

<script>
    function toggleForms() {
        var login = document.getElementById('login-form');
        var reg = document.getElementById('register-form');
        
        // Se Login è nascosto, mostralo e nascondi Register. E viceversa.
        if (login.classList.contains('hidden')) {
            login.classList.remove('hidden');
            reg.classList.add('hidden');
        } else {
            login.classList.add('hidden');
            reg.classList.remove('hidden');
        }
    }

    function validateRegister() {
        var p1 = document.getElementById('reg_pwd').value;
        var p2 = document.getElementById('reg_pwd2').value;
        if (p1 !== p2) {
            alert("Le password non coincidono!");
            return false;
        }
        if (p1.length < 4) {
             alert("Password troppo corta!");
             return false;
        }
        return true;
    }
</script>

</body>
</html>