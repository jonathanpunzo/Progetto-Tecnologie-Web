# Manuale di Configurazione Database - macOS / Linux

Il presente documento descrive le procedure necessarie per la corretta inizializzazione del database PostgreSQL richiesto dal progetto "HelpDesk iFantastici4" su sistemi Unix-based.

---

## 🟢 1. Modalità Automatica (Script Bash)
Questa procedura automatizza la creazione del ruolo applicativo e del database tramite script shell.

### Procedura:
1.  Aprire il **Terminale**.
2.  Posizionarsi nella directory degli script di inizializzazione:
    ```bash
    cd /percorso/del/progetto/inizializzatoreDB
    ```
3.  Attribuire i permessi di esecuzione e avviare lo script:
    ```bash
    chmod +x init_db_linux.sh
    ./init_db_linux.sh
    ```
4.  Inserire la password dell'utente di sistema o dell'utente `postgres` ,qualora richiesto, per i privilegi amministrativi.
5.  Al termine dell'operazione, avviare il browser e accedere al seguente indirizzo:
    * `http://localhost/<NOME_CARTELLA_PROGETTO>/setup.php`
6.  La pagina confermerà l'avvenuta creazione delle tabelle e l'inserimento dei dati di default.

> **Esito:** Il sistema è correttamente configurato e pronto per l'autenticazione.

---

## 🟡 2. Modalità Manuale (Ripristino da Backup)
Questa modalità prevede l'utilizzo di strumenti grafici (es. pgAdmin 4) o riga di comando per importare il dump del database.

### Procedura:
1.  Avviare il client di gestione database (es. **pgAdmin 4**).
2.  Creare un nuovo **Database**:
    * **Nome:** `gruppo_ifantastici4`
    * **Proprietario (Owner):** `www`
3.  Selezionare il database appena creato, cliccare con il tasto destro e scegliere **Restore**.
4.  Selezionare il file di backup fornito nella directory `/backup` (formato `.sql`, `.tar` o directory backup).
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