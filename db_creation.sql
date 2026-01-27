-- FILE: db_creation.sql

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

-- 3. TICKET (Solo 3 stati: open, resolved, closed)
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

-- UTENTI (Password per tutti: 'admin')
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@test.com', '$2b$10$6id4I3CIXFfN5PxdQF6AHucK5gpFUT.aXMKCb.KMexBRocb3EJZom', 'admin'); -- ID 1

INSERT INTO users (name, email, password, role) VALUES
('Jonathan', 'jojo@test.com', '$2b$10$6id4I3CIXFfN5PxdQF6AHucK5gpFUT.aXMKCb.KMexBRocb3EJZom', 'user'), -- ID 2
('Mattia', 'mattia@test.com', '$2b$10$6id4I3CIXFfN5PxdQF6AHucK5gpFUT.aXMKCb.KMexBRocb3EJZom', 'user'), -- ID 3
('Antonia', 'antonia@test.com', '$2b$10$6id4I3CIXFfN5PxdQF6AHucK5gpFUT.aXMKCb.KMexBRocb3EJZom', 'user'), -- ID 4
('Valentino', 'vale@test.com', '$2b$10$6id4I3CIXFfN5PxdQF6AHucK5gpFUT.aXMKCb.KMexBRocb3EJZom', 'user'); -- ID 5

-- --- TICKET & CONVERSAZIONI ---

-- 1. Jonathan: Problema CSS (Aperto)
INSERT INTO tickets (user_id, title, description, priority, category, status, created_at) VALUES 
(2, 'Problema CSS Safari', 'Le icone della navbar risultano disallineate su browser Safari mobile. Allego screen appena posso.', 'medium', 'Software', 'open', NOW() - INTERVAL '2 days');

-- 2. Jonathan: Licenza Adobe (Chiuso + Chat)
INSERT INTO tickets (user_id, title, description, priority, category, status, created_at) VALUES 
(2, 'Licenza Adobe Scaduta', 'Mi serve il rinnovo della licenza per completare i mockup entro venerdì.', 'high', 'Account', 'closed', NOW() - INTERVAL '5 days');

INSERT INTO messages (ticket_id, user_id, message, created_at) VALUES
((SELECT id FROM tickets WHERE title = 'Licenza Adobe Scaduta'), 1, 'Ciao Jonathan, licenza rinnovata. Riavvia Creative Cloud.', NOW() - INTERVAL '4 days'),
((SELECT id FROM tickets WHERE title = 'Licenza Adobe Scaduta'), 2, 'Perfetto, ora funziona. Grazie mille!', NOW() - INTERVAL '4 days'),
((SELECT id FROM tickets WHERE title = 'Licenza Adobe Scaduta'), 1, 'Ottimo, chiudo il ticket.', NOW() - INTERVAL '3 days');


-- 3. Mattia: Errore 500 (Risolto + Chat)
INSERT INTO tickets (user_id, title, description, priority, category, status, created_at) VALUES 
(3, 'Errore 500 API Login', 'L''endpoint /api/auth restituisce errore 500 se la password contiene caratteri speciali.', 'urgent', 'Software', 'resolved', NOW() - INTERVAL '1 day');

INSERT INTO messages (ticket_id, user_id, message, created_at) VALUES
((SELECT id FROM tickets WHERE title = 'Errore 500 API Login'), 1, 'Mattia, puoi girarmi i log del server?', NOW() - INTERVAL '20 hours'),
((SELECT id FROM tickets WHERE title = 'Errore 500 API Login'), 3, 'Inviati via mail. Sembra un problema di escaping.', NOW() - INTERVAL '18 hours'),
((SELECT id FROM tickets WHERE title = 'Errore 500 API Login'), 1, 'Fixato e deployato in staging. Verifica per favore.', NOW() - INTERVAL '2 hours');


-- 4. Mattia: Tastiera (Aperto - Senza risposte)
INSERT INTO tickets (user_id, title, description, priority, category, status, created_at) VALUES 
(3, 'Tastiera diffettosa', 'Il tasto SPACE rimane incastrato, impossibile programmare velocemente.', 'low', 'Hardware', 'open', NOW() - INTERVAL '6 hours');


-- 5. Antonia: DB Lento (Chiuso)
INSERT INTO tickets (user_id, title, description, priority, category, status, created_at) VALUES 
(4, 'Connessione DB lenta', 'Le query sul database di staging impiegano più di 3 secondi.', 'high', 'Software', 'closed', NOW() - INTERVAL '7 days');

INSERT INTO messages (ticket_id, user_id, message) VALUES
((SELECT id FROM tickets WHERE title = 'Connessione DB lenta'), 1, 'Ho aggiunto un indice sulla tabella users, ora dovrebbe volare.');


-- 6. Antonia: VPN (Risolto)
INSERT INTO tickets (user_id, title, description, priority, category, status, created_at) VALUES 
(4, 'Accesso VPN negato', 'Le mie credenziali non funzionano più per l''accesso remoto da casa.', 'urgent', 'Rete', 'resolved', NOW() - INTERVAL '3 hours');

INSERT INTO messages (ticket_id, user_id, message) VALUES
((SELECT id FROM tickets WHERE title = 'Accesso VPN negato'), 1, 'Password resettata. Prova quella temporanea: Temp1234!');


-- 7. Valentino: Upload Immagini (Aperto)
INSERT INTO tickets (user_id, title, description, priority, category, status, created_at) VALUES 
(5, 'Bug upload immagini PNG', 'Se carico un PNG trasparente lo sfondo diventa nero automaticamente.', 'medium', 'Software', 'open', NOW());


-- 8. Valentino: Monitor (Chiuso)
INSERT INTO tickets (user_id, title, description, priority, category, status, created_at) VALUES 
(5, 'Richiesta secondo monitor', 'Per gestire meglio il backend avrei bisogno di uno schermo aggiuntivo.', 'low', 'Hardware', 'closed', NOW() - INTERVAL '10 days');

-- CORREZIONE: Aggiunto 'created_at' nella lista colonne
INSERT INTO messages (ticket_id, user_id, message, created_at) VALUES
((SELECT id FROM tickets WHERE title = 'Richiesta secondo monitor'), 1, 'Approvato. Passa in magazzino a ritirarlo.', NOW() - INTERVAL '9 days');