<?php
// CONFIGURAZIONE DEL DATABASE
// Qui inseriamo il nome specifico scelto dal tuo gruppo
$host = 'localhost';
$db   = 'gruppo_ifantastici4'; // ECCOLO QUI! Il nome scelto da voi
$user = 'www';
$pass = 'www';

// Un po' di stile per rendere la pagina carina
echo "<style>
    body{font-family: Arial, sans-serif; background: #f0f2f5; padding: 40px;}
    .container{max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);}
    h1{color: #1a73e8; margin-top: 0;}
    .success{color: #059669; background: #d1fae5; padding: 10px; border-radius: 5px; margin: 10px 0;}
    .error{color: #dc2626; background: #fee2e2; padding: 10px; border-radius: 5px;}
    a.btn{display: inline-block; background: #1a73e8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 20px;}
    a.btn:hover{background: #1557b0;}
</style>";

echo "<div class='container'>";
echo "<h1>🚀 Installazione Database</h1>";
echo "<p>Sto configurando il database <strong>$db</strong> per il gruppo <strong>ifantastici4</strong>...</p>";

try {
    // 1. TENTATIVO DI CONNESSIONE
    $dsn = "pgsql:host=$host;port=5432;dbname=$db";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    // 2. LETTURA DEL FILE SQL
    $sql_file = 'db_creation.sql';
    if (!file_exists($sql_file)) {
        throw new Exception("❌ ERRORE: Non trovo il file '$sql_file'. Assicurati che sia nella stessa cartella di questo file.");
    }
    
    $sql_content = file_get_contents($sql_file);
    
    // 3. ESECUZIONE (INSTALLAZIONE)
    $pdo->exec($sql_content);
    
    echo "<div class='success'>✅ Tabelle create con successo!</div>";
    echo "<div class='success'>✅ Dati di prova inseriti!</div>";
    echo "<p>Il sistema è pronto.</p>";
    echo "<a href='index.php' class='btn'>Vai alla Home</a>"; // index.php ancora non esiste, darà errore se ci clicchi ora, è normale.

} catch (PDOException $e) {
    echo "<div class='error'>❌ ERRORE DATABASE:</div>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p><small>Suggerimento: Hai creato il database vuoto 'ifantastici4' su pgAdmin?</small></p>";
} catch (Exception $e) {
    echo "<div class='error'>❌ ERRORE GENERICO:</div>";
    echo "<p>" . $e->getMessage() . "</p>";
}
echo "</div>";
?>