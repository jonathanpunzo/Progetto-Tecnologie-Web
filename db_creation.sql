-- Pulizia: Rimuove le tabelle se esistono già (per evitare errori di reinstallazione)
DROP TABLE IF EXISTS messages CASCADE;
DROP TABLE IF EXISTS tickets CASCADE;
DROP TABLE IF EXISTS faqs CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- 1. CREAZIONE TABELLA UTENTI
-- Gestisce chi può accedere al sito
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('user', 'admin')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- Assegna il proprietario 'www' come richiesto dalle specifiche
ALTER TABLE users OWNER TO www;

-- 2. CREAZIONE TABELLA FAQ
-- Domande e risposte visibili anche ai non loggati
CREATE TABLE faqs (
    id SERIAL PRIMARY KEY,
    question VARCHAR(255) NOT NULL,
    answer TEXT NOT NULL
);
ALTER TABLE faqs OWNER TO www;

-- 3. CREAZIONE TABELLA TICKET
-- Il cuore del progetto: le segnalazioni
CREATE TABLE tickets (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    priority VARCHAR(20) CHECK (priority IN ('low', 'medium', 'high', 'urgent')),
    status VARCHAR(20) DEFAULT 'open' CHECK (status IN ('open', 'in-progress', 'resolved', 'closed')),
    category VARCHAR(50),
    attachment_path VARCHAR(255), -- Percorso del file caricato (HTML5 Drag&Drop)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
ALTER TABLE tickets OWNER TO www;

-- 4. CREAZIONE TABELLA MESSAGGI
-- La chat tra utente e admin dentro il ticket
CREATE TABLE messages (
    id SERIAL PRIMARY KEY,
    ticket_id INTEGER REFERENCES tickets(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
ALTER TABLE messages OWNER TO www;

-- --- POPOLAMENTO DATI DI PROVA ---

-- FAQ Iniziali
INSERT INTO faqs (question, answer) VALUES 
('Come posso registrarmi?', 'Clicca sul pulsante Accedi/Registrati in alto a destra.'),
('Che formati di file accettate?', 'Accettiamo immagini (JPG, PNG) e PDF.'),
('Non ricordo le credenziali come posso recuperarle?', 'Puoi invarci una mail alla seguente casella postale : help@ifantastici4.it');

-- UTENTI PREDEFINITI (bcrypt)
-- Password admin: admin
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@test.com', '$2b$10$6id4I3CIXFfN5PxdQF6AHucK5gpFUT.aXMKCb.KMexBRocb3EJZom', 'admin');

-- Password utente: utente
INSERT INTO users (name, email, password, role) VALUES
('Utente', 'user@test.com', '$2b$10$hzRyMYnojJJ5ezGOC3ESVOgyxbHEwrZOKYcKR5k5a0j1OFS9ufUeW', 'user');


-- TICKET DI PROVA
INSERT INTO tickets (user_id, title, description, priority, category) VALUES 
(2, 'Problema Accesso', 'Non riesco a resettare la password.', 'medium', 'Account');