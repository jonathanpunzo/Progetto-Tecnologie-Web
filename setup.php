<?php
// FILE: setup.php

$host = '127.0.0.1';
$port = '5432'; // stessa porta di db.php
$db   = 'gruppo_ifantastici4';
$user = 'www';
$pass = 'www';

echo "<h2>🚀 Installazione Database</h2>";
echo "<p>Sto configurando il database <b>$db</b>…</p>";

try {
    // Connessione al DB PostgreSQL usando PDO
    $dsn = "pgsql:host=$host;port=$port;dbname=$db";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Legge lo script SQL (tabelle + insert)
    $sqlFile = __DIR__ . DIRECTORY_SEPARATOR . "db_creation.sql";
    if (!file_exists($sqlFile)) {
        throw new Exception("Non trovo db_creation.sql nella stessa cartella di setup.php");
    }

    $sql = file_get_contents($sqlFile);

    // Esegue lo script
    $pdo->exec($sql);

    echo "<p style='color:green'><b>✅ Setup completato: tabelle create e dati inseriti!</b></p>";
    echo "<p><a href='index.php'>Vai alla Home</a></p>";

} catch (PDOException $e) {
    echo "<p style='color:red'><b>❌ ERRORE DATABASE:</b> " . $e->getMessage() . "</p>";
    echo "<p><small>Prima devi creare il database vuoto '<b>$db</b>' in pgAdmin (o con lo script SQL sotto).</small></p>";
} catch (Exception $e) {
    echo "<p style='color:red'><b>❌ ERRORE:</b> " . $e->getMessage() . "</p>";
}
?>
