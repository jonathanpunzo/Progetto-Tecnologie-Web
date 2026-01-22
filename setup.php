<?php
/**
 * setup.php — Tecnologie Web
 * DB richiesto dalla prof: gruppo_ifantastici4
 * Credenziali richieste: user www / pass www
 *
 * Opzionale (per creare DB/ruolo automaticamente):
 *   DB_ADMIN_USER=postgres
 *   DB_ADMIN_PASS=...
 */

declare(strict_types=1);
header('Content-Type: text/html; charset=utf-8');

function env(string $k, ?string $default = null): ?string {
    $v = getenv($k);
    return ($v === false || $v === '') ? $default : $v;
}
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function out(string $html): void {
    echo "<div style='margin:8px 0; line-height:1.35; font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;'>$html</div>";
}

function pdoConnect(string $host, string $port, string $dbname, string $user, string $pass): PDO {
    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
    return new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
}

function dbExists(PDO $pdo, string $dbName): bool {
    $st = $pdo->prepare("SELECT 1 FROM pg_database WHERE datname = :d");
    $st->execute([':d' => $dbName]);
    return (bool)$st->fetchColumn();
}

function roleExists(PDO $pdo, string $role): bool {
    $st = $pdo->prepare("SELECT 1 FROM pg_roles WHERE rolname = :r");
    $st->execute([':r' => $role]);
    return (bool)$st->fetchColumn();
}

function ensureWwwRole(PDO $pdoAdmin): void {
    if (!roleExists($pdoAdmin, 'www')) {
        $pdoAdmin->exec("CREATE ROLE www LOGIN PASSWORD 'www'");
    } else {
        // utile su PC diversi: forza password corretta
        $pdoAdmin->exec("ALTER ROLE www WITH LOGIN PASSWORD 'www'");
    }
}

function createDb(PDO $pdoAdmin, string $dbName): void {
    $safeDb = str_replace('"', '""', $dbName);
    $pdoAdmin->exec('CREATE DATABASE "' . $safeDb . '" OWNER "www"');
}

function runSql(PDO $pdo, string $sqlPath): void {
    if (!file_exists($sqlPath)) {
        throw new RuntimeException("File SQL non trovato: {$sqlPath}");
    }
    $sql = file_get_contents($sqlPath);
    if ($sql === false || trim($sql) === '') {
        throw new RuntimeException("File SQL vuoto/non leggibile: {$sqlPath}");
    }

    // Prova exec unica
    try {
        $pdo->exec($sql);
        return;
    } catch (PDOException $e) {
        // Fallback split semplice
        $parts = preg_split("/;\s*(\r?\n|$)/", $sql);
        if (!$parts) throw $e;
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p === '') continue;
            $pdo->exec($p);
        }
    }
}

/* ===== CONFIG ===== */
$host = env('DB_HOST', 'localhost');
$port = env('DB_PORT', '5432');

// ✅ Nome DB fissato come richiesto dalla prof
$dbName = env('DB_NAME', 'gruppo_ifantastici4');

// ✅ Credenziali applicazione
$appUser = 'www';
$appPass = 'www';

// Admin opzionale
$adminUser = env('DB_ADMIN_USER', null);
$adminPass = env('DB_ADMIN_PASS', null);

// File SQL
$sqlFile = __DIR__ . DIRECTORY_SEPARATOR . 'db_creation.sql';

/* ===== UI semplice ===== */
echo "<style>
body{background:#f4f6f8;padding:26px}
.box{max-width:860px;margin:0 auto;background:#fff;border-radius:12px;padding:20px;box-shadow:0 8px 25px rgba(0,0,0,.06)}
.ok{background:#d1fae5;color:#065f46;padding:10px;border-radius:8px}
.warn{background:#fff7ed;color:#9a3412;padding:10px;border-radius:8px}
.err{background:#fee2e2;color:#991b1b;padding:10px;border-radius:8px}
pre{background:#f6f6f6;padding:12px;border-radius:8px;overflow:auto}
code{background:#f6f6f6;padding:2px 6px;border-radius:6px}
</style>";

echo "<div class='box'>";
out("<h2 style='margin:0 0 8px 0;'>Setup Database</h2>");
out("<b>Target DB:</b> <code>" . h($dbName) . "</code> &nbsp; <b>User:</b> <code>www</code>/<code>www</code>");
out("<b>Host:</b> <code>" . h($host) . ":" . h($port) . "</code>");

try {
    $serviceDb = 'postgres';
    $canAdmin = ($adminUser !== null && $adminPass !== null);

    if ($canAdmin) {
        out("➡️ Connessione admin a <code>{$serviceDb}</code>…");
        $pdoAdmin = pdoConnect($host, $port, $serviceDb, $adminUser, $adminPass);

        out("➡️ Controllo/creazione ruolo <code>www</code>…");
        ensureWwwRole($pdoAdmin);

        if (!dbExists($pdoAdmin, $dbName)) {
            out("➡️ DB non trovato: lo creo con owner <code>www</code>…");
            createDb($pdoAdmin, $dbName);
            out("<div class='ok'>✅ Database creato.</div>");
        } else {
            out("<div class='ok'>✅ Database già esistente.</div>");
        }
    } else {
        out("<div class='warn'>⚠️ Admin non configurato. Se il DB non esiste, va creato manualmente (vedi istruzioni sotto se fallisce).</div>");
    }

    out("➡️ Connessione al DB target come <code>www</code>…");
    $pdo = pdoConnect($host, $port, $dbName, $appUser, $appPass);

    out("➡️ Esecuzione <code>db_creation.sql</code>…");
    $pdo->beginTransaction();
    runSql($pdo, $sqlFile);
    $pdo->commit();

    out("<div class='ok'>🎉 Setup completato: tabelle e dati installati.</div>");
    out("👉 Ora puoi aprire <code>index.php</code>.");

} catch (PDOException $e) {
    out("<div class='err'><b>❌ Errore:</b> " . h($e->getMessage()) . "</div>");

    out("<div class='warn'><b>Se l’errore è “database does not exist”</b>, crea DB e utente così (come admin, es. postgres) e poi ricarica setup.php:</div>");
    echo "<pre>CREATE ROLE www LOGIN PASSWORD 'www';
CREATE DATABASE \"{$dbName}\" OWNER \"www\";</pre>";

} catch (Throwable $t) {
    out("<div class='err'><b>❌ Errore generale:</b> " . h($t->getMessage()) . "</div>");
}

echo "</div>";
