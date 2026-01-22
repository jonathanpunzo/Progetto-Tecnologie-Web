-- Esegui come superuser (es. utente "postgres") in pgAdmin
-- Apri Query Tool sul database "postgres"

-- 1) Crea l'utente www se non esiste
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'www') THEN
        CREATE ROLE www LOGIN PASSWORD 'www';
    END IF;
END $$;

-- 2) Crea il database (se esiste già, PostgreSQL darà errore: in quel caso è già ok)
CREATE DATABASE gruppo_ifantastici4 OWNER www ENCODING 'UTF8';

-- 3) Privilegi espliciti
GRANT ALL PRIVILEGES ON DATABASE gruppo_ifantastici4 TO www;
-- Fine dello script
