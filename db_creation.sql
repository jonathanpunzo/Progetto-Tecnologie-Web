-- FILE: db_creation.sql
-- VERSIONE ORDINATA: ID 1 = Più Vecchio, ID 30 = Più Recente

-- Pulizia Completa
DROP TABLE IF EXISTS messages CASCADE;
DROP TABLE IF EXISTS tickets CASCADE;
DROP TABLE IF EXISTS faqs CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- 1. UTENTI
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('user', 'admin')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
ALTER TABLE users OWNER TO www;

-- 2. FAQ
CREATE TABLE faqs (
    id SERIAL PRIMARY KEY,
    question VARCHAR(255) NOT NULL,
    answer TEXT NOT NULL
);
ALTER TABLE faqs OWNER TO www;

-- 3. TICKET
CREATE TABLE tickets (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    priority VARCHAR(20) CHECK (priority IN ('low', 'medium', 'high', 'urgent')),
    status VARCHAR(20) DEFAULT 'open' CHECK (status IN ('open', 'resolved', 'closed')),
    category VARCHAR(50),
    attachment_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
ALTER TABLE tickets OWNER TO www;

-- 4. MESSAGGI
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
('Cosa significano gli stati del ticket?', 'APERTO: Segnalazione ricevuta. RISOLTO: Il problema è stato fixato. CHIUSO: La pratica è archiviata.'),
('Ho sbagliato categoria, cosa faccio?', 'Non preoccuparti. Se un amministratore nota che la categoria è errata (es. Hardware invece di Software), provvederà a gestirlo comunque o a reindirizzarlo al reparto corretto.'),
('C''è un limite per gli allegati?', 'Sì, per garantire le prestazioni del server ti chiediamo di caricare file di dimensioni contenute (max 5MB). Se necessario, usa formati compressi come ZIP.');

-- UTENTI
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@test.com', '$2b$10$6id4I3CIXFfN5PxdQF6AHucK5gpFUT.aXMKCb.KMexBRocb3EJZom', 'admin'); -- ID 1

INSERT INTO users (name, email, password, role) VALUES
('Jonathan', 'jojo@test.com', '$2b$10$6id4I3CIXFfN5PxdQF6AHucK5gpFUT.aXMKCb.KMexBRocb3EJZom', 'user'), -- ID 2
('Mattia', 'mattia@test.com', '$2b$10$6id4I3CIXFfN5PxdQF6AHucK5gpFUT.aXMKCb.KMexBRocb3EJZom', 'user'), -- ID 3
('Antonia', 'antonia@test.com', '$2b$10$6id4I3CIXFfN5PxdQF6AHucK5gpFUT.aXMKCb.KMexBRocb3EJZom', 'user'), -- ID 4
('Valentino', 'vale@test.com', '$2b$10$6id4I3CIXFfN5PxdQF6AHucK5gpFUT.aXMKCb.KMexBRocb3EJZom', 'user'); -- ID 5


-- --- TICKET (Inseriti dal più vecchio al più recente per avere ID coerenti) ---

-- 1 Mese fa
INSERT INTO tickets (user_id, title, description, priority, category, status, created_at) VALUES 
(4, 'Firewall blocca porta 5432', 'Non riesco a collegarmi al DB di test.', 'high', 'Rete', 'closed', NOW() - INTERVAL '30 days');

-- 20 Giorni fa
INSERT INTO tickets (user_id, title, description, priority, category, status, created_at) VALUES 
(2, 'Richiesta Tablet Grafico', 'Avrei bisogno di una Wacom per le illustrazioni.', 'low', 'Hardware', 'closed', NOW() - INTERVAL '20 days'),
(5, 'Mouse non funziona', 'Il tasto destro non clicca più.', 'low', 'Hardware', 'closed', NOW() - INTERVAL '19 days');

-- 15 Giorni fa
INSERT INTO tickets (user_id, title, description, priority, category, status, created_at) VALUES 
(3, 'Permessi Root mancanti', 'Non riesco a installare pacchetti su server dev.', 'urgent', 'Account', 'closed', NOW() - INTERVAL '15 days');

-- 10 Giorni fa
INSERT INTO tickets (user_id, title, description, priority, category, status, created_at) VALUES 
(5, 'Richiesta secondo monitor', 'Necessario per debuggare meglio.', 'low', 'Hardware', 'closed', NOW() - INTERVAL '10 days'),
(3, 'Docker non parte', 'Il demone docker non si avvia al boot.', 'high', 'Software', 'closed', NOW() - INTERVAL '10 days');

-- 1 Settimana fa
INSERT INTO tickets (user_id, title, description, priority, category, status, created_at) VALUES 
(3, 'Cavo Ethernet rotto', 'La linguetta di plastica si è staccata.', 'low', 'Hardware', 'resolved', NOW() - INTERVAL '8 days'),
(4, 'Connessione DB lenta', 'Query time > 3s su tabella users.', 'high', 'Software', 'closed', NOW() - INTERVAL '7 days'),
(2, 'Errore esportazione PDF', 'Acrobat crasha quando salvo in alta qualità.', 'medium', 'Software', 'resolved', NOW() - INTERVAL '6 days');

-- 5 Giorni fa
INSERT INTO tickets (user_id, title, description, priority, category, status, created_at) VALUES 
(2, 'Licenza Adobe Scaduta', 'Mi serve rinnovo Photoshop urgente.', 'high', 'Account', 'closed', NOW() - INTERVAL '5 days'),
(5, 'Email phishing sospetta', 'Ho ricevuto una mail strana da "Amministratore".', 'urgent', 'Rete', 'resolved', NOW() - INTERVAL '5 days');

-- 3-4 Giorni fa
INSERT INTO tickets (user_id, title, description, priority, category, status, created_at) VALUES 
(4, 'Password scaduta', 'Il sistema mi chiede il reset ogni 2 giorni.', 'medium', 'Account', 'resolved', NOW() - INTERVAL '4 days'),
(2, 'Font non caricati', 'Il server non serve i file .woff2 correttamente.', 'low', 'Software', 'resolved', NOW() - INTERVAL '3 days'),
(5, 'Creazione utente stagista', 'Serve account per il nuovo arrivato.', 'medium', 'Account', 'open', NOW() - INTERVAL '3 days');

-- 2 Giorni fa
INSERT INTO tickets (user_id, title, description, priority, category, status, created_at) VALUES 
(4, 'Richiesta SSD esterno', 'Per archiviare i log vecchi.', 'medium', 'Hardware', 'open', NOW() - INTERVAL '2 days'),
(3, 'Update PHP 8.2', 'Dobbiamo aggiornare i server di produzione.', 'medium', 'Software', 'open', NOW() - INTERVAL '2 days'),
(5, 'Aggiornamento Windows bloccato', 'Resta al 99% da stamattina.', 'medium', 'Software', 'resolved', NOW() - INTERVAL '2 days');

-- Ieri
INSERT INTO tickets (user_id, title, description, priority, category, status, created_at) VALUES 
(2, 'Schermo sfarfalla', 'Il secondo monitor sfarfalla quando apro Figma.', 'medium', 'Hardware', 'open', NOW() - INTERVAL '1 day'),
(3, 'Errore 500 API Login', 'Endpoint auth restituisce 500 random.', 'urgent', 'Software', 'resolved', NOW() - INTERVAL '23 hours'),
(5, 'Wifi lento sala riunioni', 'Impossibile fare call video.', 'high', 'Rete', 'open', NOW() - INTERVAL '20 hours');

-- Oggi (Ultime 12 ore)
INSERT INTO tickets (user_id, title, description, priority, category, status, created_at) VALUES 
(3, 'Redis Pieno', 'La cache di Redis satura la RAM.', 'high', 'Software', 'resolved', NOW() - INTERVAL '12 hours'),
(4, 'Monitoraggi Grafana down', 'La dashboard non mostra i dati in real time.', 'medium', 'Software', 'open', NOW() - INTERVAL '6 hours'),
(3, 'Ventola PC rumorosa', 'Sembra un elicottero in decollo.', 'low', 'Hardware', 'open', NOW() - INTERVAL '5 hours'),
(3, 'Tastiera diffettosa', 'Il tasto Space si incastra.', 'low', 'Hardware', 'open', NOW() - INTERVAL '4 hours'),
(4, 'Accesso VPN negato', 'Credenziali non valide da remoto.', 'urgent', 'Rete', 'resolved', NOW() - INTERVAL '3 hours'),
(2, 'Problema CSS Safari', 'Le icone della navbar sono disallineate su iOS.', 'medium', 'Software', 'open', NOW() - INTERVAL '2 hours'),
(5, 'Stampante inceppata', 'Vassoio 2 bloccato carta.', 'low', 'Hardware', 'open', NOW() - INTERVAL '2 hours'),
(4, 'Backup fallito', 'Il dump notturno di Postgres è incompleto.', 'urgent', 'Software', 'open', NOW() - INTERVAL '1 hour'),
(2, 'Accesso FTP negato', 'Non riesco a caricare i nuovi asset sul server di staging.', 'urgent', 'Rete', 'open', NOW() - INTERVAL '30 minutes'),
(5, 'Bug upload PNG', 'Sfondo nero su immagini trasparenti.', 'medium', 'Software', 'open', NOW());


-- --- CONVERSAZIONI (Usano SELECT per trovare l'ID giusto indipendentemente dall'ordine) ---

-- Licenza Adobe (Jonathan - Chiuso 5gg fa)
INSERT INTO messages (ticket_id, user_id, message, created_at) VALUES
((SELECT id FROM tickets WHERE title = 'Licenza Adobe Scaduta'), 1, 'Ciao Jonathan, licenza rinnovata. Riavvia Creative Cloud.', NOW() - INTERVAL '4 days 23 hours'),
((SELECT id FROM tickets WHERE title = 'Licenza Adobe Scaduta'), 2, 'Perfetto, ora funziona. Grazie mille!', NOW() - INTERVAL '4 days 22 hours'),
((SELECT id FROM tickets WHERE title = 'Licenza Adobe Scaduta'), 1, 'Ottimo, chiudo il ticket.', NOW() - INTERVAL '4 days 20 hours');

-- Errore 500 (Mattia - Risolto Ieri)
INSERT INTO messages (ticket_id, user_id, message, created_at) VALUES
((SELECT id FROM tickets WHERE title = 'Errore 500 API Login'), 1, 'Mattia, puoi girarmi i log del server?', NOW() - INTERVAL '22 hours'),
((SELECT id FROM tickets WHERE title = 'Errore 500 API Login'), 3, 'Inviati via mail. Sembra un problema di escaping.', NOW() - INTERVAL '21 hours'),
((SELECT id FROM tickets WHERE title = 'Errore 500 API Login'), 1, 'Fixato e deployato in staging. Verifica per favore.', NOW() - INTERVAL '20 hours');

-- Backup Fallito (Antonia - Aperto 1 ora fa)
INSERT INTO messages (ticket_id, user_id, message, created_at) VALUES
((SELECT id FROM tickets WHERE title = 'Backup fallito'), 1, 'Sto controllando lo spazio su disco, potrebbe essere pieno.', NOW() - INTERVAL '10 minutes');

-- Phishing (Valentino - Risolto 5gg fa)
INSERT INTO messages (ticket_id, user_id, message, created_at) VALUES
((SELECT id FROM tickets WHERE title = 'Email phishing sospetta'), 1, 'NON CLICCARE SUL LINK. È un tentativo di phishing noto.', NOW() - INTERVAL '5 days'),
((SELECT id FROM tickets WHERE title = 'Email phishing sospetta'), 5, 'Ricevuto, cestinata subito.', NOW() - INTERVAL '5 days');

-- Monitor (Valentino - Chiuso 10gg fa)
INSERT INTO messages (ticket_id, user_id, message, created_at) VALUES
((SELECT id FROM tickets WHERE title = 'Richiesta secondo monitor'), 1, 'Approvato. Passa in magazzino.', NOW() - INTERVAL '9 days');