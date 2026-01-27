-- Pulizia: Rimuove le tabelle se esistono già
DROP TABLE IF EXISTS messages CASCADE;
DROP TABLE IF EXISTS tickets CASCADE;
DROP TABLE IF EXISTS faqs CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- 1. CREAZIONE TABELLA UTENTI
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('user', 'admin')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
ALTER TABLE users OWNER TO www;

-- 2. CREAZIONE TABELLA FAQ
CREATE TABLE faqs (
    id SERIAL PRIMARY KEY,
    question VARCHAR(255) NOT NULL,
    answer TEXT NOT NULL
);
ALTER TABLE faqs OWNER TO www;

-- 3. CREAZIONE TABELLA TICKET
CREATE TABLE tickets (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    priority VARCHAR(20) CHECK (priority IN ('low', 'medium', 'high', 'urgent')),
    status VARCHAR(20) DEFAULT 'open' CHECK (status IN ('open', 'in-progress', 'resolved', 'closed')),
    category VARCHAR(50),
    attachment_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
ALTER TABLE tickets OWNER TO www;

-- 4. CREAZIONE TABELLA MESSAGGI
CREATE TABLE messages (
    id SERIAL PRIMARY KEY,
    ticket_id INTEGER REFERENCES tickets(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
ALTER TABLE messages OWNER TO www;

-- --- POPOLAMENTO DATI ---

-- FAQ
INSERT INTO faqs (question, answer) VALUES 
('Come posso registrarmi?', 'Clicca sul pulsante Accedi/Registrati in alto a destra.'),
('Che formati di file accettate?', 'Accettiamo immagini (JPG, PNG) e PDF.'),
('Non ricordo le credenziali come posso recuperarle?', 'Puoi invarci una mail alla seguente casella postale : help@ifantastici4.it'),
('Posso modificare un ticket dopo averlo inviato?', 'No, non è possibile modificare la descrizione originale. Tuttavia, puoi aggiungere dettagli o correzioni scrivendo un nuovo messaggio nella chat del ticket.'),
('Quali sono i tempi di risposta?', 'Le tempistiche dipendono dalla priorità assegnata. Per le urgenze interveniamo subito, per le priorità medie o basse solitamente entro 24-48 ore.'),
('Cosa significano gli stati del ticket?', 'APERTO: Segnalazione ricevuta. IN LAVORAZIONE: Un tecnico sta analizzando il problema. RISOLTO: Il problema è stato fixato. CHIUSO: La pratica è archiviata.'),
('Ho sbagliato categoria, cosa faccio?', 'Non preoccuparti. Se un amministratore nota che la categoria è errata (es. Hardware invece di Software), provvederà a gestirlo comunque o a reindirizzarlo al reparto corretto.'),
('C''è un limite per gli allegati?', 'Sì, per garantire le prestazioni del server ti chiediamo di caricare file di dimensioni contenute (max 5MB). Se necessario, usa formati compressi come ZIP.');

-- UTENTI
-- NOTA: La password per TUTTI è 'admin' (hash riutilizzato per comodità)
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@test.com', '$2b$10$6id4I3CIXFfN5PxdQF6AHucK5gpFUT.aXMKCb.KMexBRocb3EJZom', 'admin');

INSERT INTO users (name, email, password, role) VALUES
('Jonathan', 'jojo@test.com', '$2b$10$6id4I3CIXFfN5PxdQF6AHucK5gpFUT.aXMKCb.KMexBRocb3EJZom', 'user'),
('Mattia', 'mattia@test.com', '$2b$10$6id4I3CIXFfN5PxdQF6AHucK5gpFUT.aXMKCb.KMexBRocb3EJZom', 'user'),
('Antonia', 'antonia@test.com', '$2b$10$6id4I3CIXFfN5PxdQF6AHucK5gpFUT.aXMKCb.KMexBRocb3EJZom', 'user'),
('Valentino', 'vale@test.com', '$2b$10$6id4I3CIXFfN5PxdQF6AHucK5gpFUT.aXMKCb.KMexBRocb3EJZom', 'user');

-- TICKET (2 per ogni utente, ID progressivi)

-- Ticket di Jonathan (Jojo) - ID User presumibile: 2
INSERT INTO tickets (user_id, title, description, priority, category) VALUES 
(2, 'Problema CSS Safari', 'Le icone della navbar risultano disallineate su browser Safari mobile.', 'medium', 'Software'),
(2, 'Licenza Adobe Scaduta', 'Mi serve il rinnovo della licenza per completare i mockup.', 'high', 'Account');

-- Ticket di Mattia - ID User presumibile: 3
INSERT INTO tickets (user_id, title, description, priority, category) VALUES 
(3, 'Errore 500 API', 'L''endpoint di login restituisce errore server interno randomico.', 'urgent', 'Software'),
(3, 'Tastiera diffettosa', 'Alcuni tasti rimangono incastrati, impossibile programmare velocemente.', 'low', 'Hardware');

-- Ticket di Antonia - ID User presumibile: 4
INSERT INTO tickets (user_id, title, description, priority, category) VALUES 
(4, 'Connessione DB lenta', 'Le query sul database di staging impiegano più di 3 secondi.', 'high', 'Software'),
(4, 'Accesso VPN negato', 'Le mie credenziali non funzionano più per l''accesso remoto.', 'urgent', 'Rete');

-- Ticket di Valentino (Vale) - ID User presumibile: 5
INSERT INTO tickets (user_id, title, description, priority, category) VALUES 
(5, 'Bug upload immagini', 'Se carico un PNG trasparente lo sfondo diventa nero.', 'medium', 'Software'),
(5, 'Richiesta secondo monitor', 'Per gestire meglio il backend avrei bisogno di uno schermo aggiuntivo.', 'low', 'Hardware');