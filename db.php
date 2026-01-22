<?php
// FILE: db.php

$host = '127.0.0.1';   // meglio di 'localhost' per evitare IPv6 ::1
$port = '5432';        // se in pgAdmin è diversa, cambia qui
$db   = 'gruppo_ifantastici4';
$user = 'www';
$pass = 'www';

$connStr = "host=$host port=$port dbname=$db user=$user password=$pass";

$db_conn = pg_connect($connStr);

if (!$db_conn) {
    die("Errore Database<br>Impossibile connettersi al database '$db'.<br>" . pg_last_error());
}
?>
