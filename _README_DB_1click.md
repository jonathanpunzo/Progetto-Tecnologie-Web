# Avvio rapido DB (gruppo_ifantastici4)

## Obiettivo
Con **1 click** creare ruolo `www` + database `gruppo_ifantastici4`.
Poi aprire `setup.php` per creare **tabelle + dati**.

---

## Windows (1 click)
Doppio click su:

- `init_db_windows.bat`

Se viene richiesto, inserire la **password dell’utente `postgres`** (quella scelta durante l’installazione di PostgreSQL).

Poi aprire:
- `http://localhost/<NOME_CARTELLA_PROGETTO>/setup.php`

---

## Linux/macOS
```bash
chmod +x init_db_linux.sh
./init_db_linux.sh
```

Poi aprire:
- `http://localhost/<NOME_CARTELLA_PROGETTO>/setup.php`

---

## Parametri usati
- DB: `gruppo_ifantastici4`
- User: `www`
- Pass: `www`
- Host: `127.0.0.1`
- Port: `5432`

> Nota: lo script crea solo **ruolo+database**.
> Le tabelle/dati vengono creati da `setup.php` (che esegue `db_creation.sql`).
