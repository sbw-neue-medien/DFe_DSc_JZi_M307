-- Datenbank Schema fuer M307 Projektmanagement-Tool
-- Erstellt von JZi

CREATE DATABASE IF NOT EXISTS m307_projektmanagement
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE m307_projektmanagement;

-- Benutzertabelle fuer Login und Registrierung
CREATE TABLE IF NOT EXISTS benutzer (
    id INT AUTO_INCREMENT PRIMARY KEY,
    benutzername VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    passwort_hash VARCHAR(255) NOT NULL,
    erstellt_am DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Projekttabelle fuer alle Projekte
CREATE TABLE IF NOT EXISTS projekte (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    beschreibung TEXT,
    abgabedatum DATE NOT NULL,
    erstellt_von INT NOT NULL,
    erstellt_am DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (erstellt_von) REFERENCES benutzer(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Aufgaben innerhalb eines Projekts
CREATE TABLE IF NOT EXISTS aufgaben (
    id INT AUTO_INCREMENT PRIMARY KEY,
    projekt_id INT NOT NULL,
    titel VARCHAR(150) NOT NULL,
    beschreibung TEXT,
    abgabedatum DATE NOT NULL,
    erinnerung_datum DATETIME,
    erinnerung_per ENUM('email', 'push', 'beide') DEFAULT NULL,
    zugewiesen_an INT,
    erstellt_am DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projekt_id) REFERENCES projekte(id) ON DELETE CASCADE,
    FOREIGN KEY (zugewiesen_an) REFERENCES benutzer(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Testdaten Benutzer (Passwort ist jeweils "passwort123")
INSERT INTO benutzer (benutzername, email, passwort_hash) VALUES
('DSc', 'dsc@example.com', '$2y$12$eImiTXuWVxfM37uY4JANjQ.SFVHHlJqRhWGLpT.yKuvxFHJLtHPkW'),
('DFe', 'dfe@example.com', '$2y$12$eImiTXuWVxfM37uY4JANjQ.SFVHHlJqRhWGLpT.yKuvxFHJLtHPkW'),
('JZi', 'jzi@example.com', '$2y$12$eImiTXuWVxfM37uY4JANjQ.SFVHHlJqRhWGLpT.yKuvxFHJLtHPkW');

-- Testdaten Projekte
INSERT INTO projekte (name, beschreibung, abgabedatum, erstellt_von) VALUES
('Social Media Kampagne Q1', 'Kampagne fuer das erste Quartal mit Fokus auf Instagram und LinkedIn', '2026-03-13', 1),
('Website Redesign', 'Komplettes Redesign der Unternehmenswebsite', '2026-03-20', 2);

-- Testdaten Aufgaben
INSERT INTO aufgaben (projekt_id, titel, beschreibung, abgabedatum, erinnerung_datum, erinnerung_per, zugewiesen_an) VALUES
(1, 'Beitrag fuer Social-Media erstellen', 'Instagram Post mit Bildmaterial und Caption', '2026-03-13', '2026-03-10 09:00:00', 'email', 1),
(1, 'Beitrag mit Mockups erstellen', 'LinkedIn Artikel mit Infografiken', '2026-03-13', '2026-03-10 09:00:00', 'push', 3),
(2, 'Startseite neu gestalten', 'Hero Section und Navigation ueberarbeiten', '2026-03-20', NULL, NULL, 2),
(2, 'Mobile Ansicht optimieren', 'Responsive Design fuer alle Geraete sicherstellen', '2026-03-20', '2026-03-17 08:00:00', 'email', 1);

-- View: Aufgaben mit Benutzername des Zugewiesenen
CREATE OR REPLACE VIEW v_aufgaben_mit_benutzer AS
SELECT
    a.id,
    a.projekt_id,
    p.name AS projekt_name,
    a.titel,
    a.beschreibung,
    a.abgabedatum,
    a.erinnerung_datum,
    a.erinnerung_per,
    b.benutzername AS zugewiesen_an,
    a.erstellt_am
FROM aufgaben a
JOIN projekte p ON a.projekt_id = p.id
LEFT JOIN benutzer b ON a.zugewiesen_an = b.id;

-- View: Projektliste mit Anzahl Aufgaben
CREATE OR REPLACE VIEW v_projekte_uebersicht AS
SELECT
    p.id,
    p.name,
    p.beschreibung,
    p.abgabedatum,
    b.benutzername AS erstellt_von,
    COUNT(a.id) AS anzahl_aufgaben,
    p.erstellt_am
FROM projekte p
JOIN benutzer b ON p.erstellt_von = b.id
LEFT JOIN aufgaben a ON p.id = a.projekt_id
GROUP BY p.id, p.name, p.beschreibung, p.abgabedatum, b.benutzername, p.erstellt_am;

-- Stored Procedure: Aufgaben nach Faelligkeitsdatum sortieren und filtern
DELIMITER $$
CREATE PROCEDURE sp_aufgaben_filtern(
    IN p_benutzer_id INT,
    IN p_sortierung VARCHAR(20)
)
BEGIN
    SET @sortCol = IF(p_sortierung = 'abgabedatum', 'a.abgabedatum', 'a.erstellt_am');
    SET @sql = CONCAT(
        'SELECT a.id, a.titel, a.abgabedatum, b.benutzername AS zugewiesen_an, p.name AS projekt_name ',
        'FROM aufgaben a ',
        'JOIN projekte p ON a.projekt_id = p.id ',
        'LEFT JOIN benutzer b ON a.zugewiesen_an = b.id ',
        'WHERE a.zugewiesen_an = ', p_benutzer_id, ' OR p.erstellt_von = ', p_benutzer_id, ' ',
        'ORDER BY ', @sortCol, ' ASC'
    );
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END$$
DELIMITER ;
