-- =============================================================
-- migrations.sql
-- Ricostruisce da zero lo schema del database per il progetto Orizon
-- Eseguire con: mysql -u root -p < migrations.sql
-- oppure importare da phpMyAdmin
-- =============================================================

CREATE DATABASE IF NOT EXISTS orizon
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE orizon;

-- Ordine di drop inverso per rispettare i vincoli di foreign key
DROP TABLE IF EXISTS viaggio_paese;
DROP TABLE IF EXISTS viaggi;
DROP TABLE IF EXISTS paesi;

-- -------------------------------------------------------------
-- Tabella: paesi
-- Un paese ha una sola caratteristica richiesta: il nome
-- -------------------------------------------------------------
CREATE TABLE paesi (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- Tabella: viaggi
-- Un viaggio ha un numero di posti disponibili.
-- I paesi coinvolti sono gestiti tramite la tabella ponte viaggio_paese
-- -------------------------------------------------------------
CREATE TABLE viaggi (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    posti_disponibili INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- Tabella ponte: viaggio_paese
-- Relazione molti-a-molti tra viaggi e paesi:
-- un viaggio puo' coinvolgere piu' paesi, e un paese puo' essere
-- coinvolto in piu' viaggi.
-- -------------------------------------------------------------
CREATE TABLE viaggio_paese (
    viaggio_id INT UNSIGNED NOT NULL,
    paese_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (viaggio_id, paese_id),
    CONSTRAINT fk_viaggio_paese_viaggio
        FOREIGN KEY (viaggio_id) REFERENCES viaggi(id) ON DELETE CASCADE,
    CONSTRAINT fk_viaggio_paese_paese
        FOREIGN KEY (paese_id) REFERENCES paesi(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- (Opzionale) Dati di esempio per testare subito le API
-- -------------------------------------------------------------
INSERT INTO paesi (nome) VALUES
    ('Vietnam'),
    ('Cambogia'),
    ('Thailandia'),
    ('Perù'),
    ('Bolivia');

INSERT INTO viaggi (posti_disponibili) VALUES
    (4),   -- id 1
    (0),   -- id 2
    (2);   -- id 3

INSERT INTO viaggio_paese (viaggio_id, paese_id) VALUES
    (1, 1), (1, 2),   -- viaggio 1: Vietnam + Cambogia
    (2, 3),           -- viaggio 2: Thailandia
    (3, 4), (3, 5);   -- viaggio 3: Perù + Bolivia
