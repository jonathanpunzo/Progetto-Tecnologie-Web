<?php
// FILE: db.php
$host = 'localhost';
$port = '5432';
$db   = 'gruppo_ifantastici4'; // Il nome che abbiamo visto nel tuo setup
$user = 'www';
$pass = 'www';

$connection_string = "host=$host port=$port dbname=$db user=$user password=$pass";

$db_conn = pg_connect($connection_string);

if (!$db_conn) {
    die("<h1>Errore Database</h1><p>Impossibile connettersi al database '$db'.</p>");
}
?>