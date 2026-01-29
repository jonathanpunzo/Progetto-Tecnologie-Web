# Manuale di Configurazione Database - Windows

Il presente documento descrive le procedure necessarie per la corretta inizializzazione del database PostgreSQL richiesto dal progetto "HelpDesk iFantastici4".
Sono disponibili due modalità di configurazione: automatizzata (tramite script batch) o manuale (tramite ripristino di backup).

---

## 🟢 1. Modalità Automatica (Script Batch)
Questa procedura utilizza gli script forniti nella directory del progetto per configurare ruolo e database in modo automatico.

### Procedura:
1.  Navigare all'interno della cartella `inizializzatoreDB`.
2.  Eseguire il file `init_db_windows.bat`.
    * *Nota tecnica:* Qualora lo script non rilevasse automaticamente l'installazione di PostgreSQL, verrà richiesto di specificare manualmente il percorso dell'eseguibile `psql.exe` (trascinando il file nella finestra del terminale).
3.  Inserire la password dell'utente amministratore `postgres` (definita durante l'installazione di PostgreSQL) quando richiesto.
4.  Al termine dell'esecuzione, avviare il browser e accedere al seguente indirizzo per popolare il database:
    * `http://localhost/<NOME_CARTELLA_PROGETTO>/setup.php`

> **Esito:** Il sistema è correttamente configurato e pronto per l'autenticazione.

---

## 🟡 2. Modalità Manuale (Ripristino da Backup)
Questa modalità prevede l'utilizzo di strumenti di gestione database (es. pgAdmin 4) per importare un dump preesistente. È consigliata in caso di mancato funzionamento degli script automatici.

### Procedura:
1.  Avviare **pgAdmin 4** (o client equivalente).
2.  Creare un nuovo **Database**:
    * **Name:** `gruppo_ifantastici4`
    * **Owner:** `www`
3.  Selezionare il database appena creato, cliccare con il tasto destro e scegliere **Restore**.
4.  Selezionare il file di backup fornito nella documentazione di progetto (formato `.sql`, `.tar` o directory backup).
5.  Avviare il processo di ripristino.

> **Attenzione:** Utilizzando questa modalità, le tabelle e i dati vengono popolati direttamente dal backup. Pertanto, **NON** è necessario eseguire lo script `setup.php`. Si proceda direttamente alla pagina iniziale:
> `http://localhost/<NOME_CARTELLA_PROGETTO>`

---

## Appendice: Parametri di Connessione
I parametri di configurazione utilizzati dall'applicazione (definiti in `db.php`) sono i seguenti:

* **Host:** `127.0.0.1`
* **Port:** `5432`
* **Database Name:** `gruppo_ifantastici4`
* **Username:** `www`
* **Password:** `www`