# M307 Projektmanagement-Tool

**Team:** DFe, DSc, JZi  
**Modul:** 307 – Webapplikationen realisieren  
**Abgabe:** 10.03.2026

---

## Anforderungen (User Stories)

| ID | Als...           | möchte ich...                                      | damit...                                   |
|----|------------------|----------------------------------------------------|--------------------------------------------|
| 1  | Benutzer         | mich registrieren und einloggen können             | ich sicher auf meine Projekte zugreifen kann |
| 2  | Benutzer         | Projekte erstellen, bearbeiten und löschen können  | ich meine Arbeit organisieren kann         |
| 3  | Benutzer         | Aufgaben zu Projekten hinzufügen können            | Arbeit auf Teammitglieder aufgeteilt wird  |
| 4  | Benutzer         | Aufgaben nach Datum filtern und sortieren           | ich den Überblick behalte                  |
| 5  | Benutzer         | eine Erinnerung per E-Mail oder Push erhalten      | ich keine Deadlines verpasse              |
| 6  | Teamleiter       | Aufgaben Teammitgliedern zuweisen                  | Verantwortlichkeiten klar sind            |

---

## Technische Überlegungen

### Frontend
- HTML5 mit semantischen Tags (`fieldset`, `legend`, `label`)
- CSS3 in einer zentralen Stylesheet-Datei (`css/style.css`)
- JavaScript für Frontend-Validierung und Zwischenspeicherung (sessionStorage)
- Responsives Design via CSS Grid und Flexbox

### Backend
- PHP 8.x mit PDO für sichere Datenbankabfragen (Prepared Statements)
- Sessions für Authentifizierung
- CSRF-Schutz für alle Formulare
- POST/REDIRECT/GET Pattern zur Vermeidung doppelter Einträge

### Datenbank
- MySQL mit 3 Tabellen: `benutzer`, `projekte`, `aufgaben`
- 2 Views: `v_projekte_uebersicht`, `v_aufgaben_mit_benutzer`
- 1 Stored Procedure: `sp_aufgaben_filtern`

---

## Skizzen (Mockups)

Die Mockups liegen im Ordner `/mockups/` als PDF:
- `login.png` – Login-Seite
- `registrieren.png` – Registrierungsseite
- `projekt-erstellen.png` – Formular für neues Projekt
- `projekt-bearbeiten.png` – Formular für Projektbearbeitung
- `projektübersicht.png` – Hauptübersicht aller Projekte

---

## Gestalterische Umsetzung

Das Design basiert auf einem hellen Grünton (`#b7f5c8`) als Primärfarbe, angelehnt an die gelieferten Mockups. Alle Formulare verwenden abgerundete Eingabefelder und einen klaren, modernen Stil. Die Benutzeroberfläche ist vollständig responsiv.

---

## Setup-Anleitung

Dieses Projekt wurde auf Docker umgestellt und benötigt keine lokale XAMPP/MAMP-Installation.

### Voraussetzungen
- Docker Engine & Docker Compose (Version 1.27+)
- Ein moderner Browser (Chrome, Firefox, Edge, …)

### Vorbereitung
1. Kopiere die Beispiel-Umgebungsdatei und passe sie ggf. an:
   ```bash
   cp .env.example .env
   ```
   Die wichtigsten Variablen sind:
   ```
   DB_HOST=db
   DB_USER=root
   DB_PASS=example
   DB_NAME=m307_projektmanagement
   DB_PORT=3306
   MYSQL_ROOT_PASSWORD=example
   ```

### Container starten
```bash
docker compose up --build -d
```
- Der PHP/Apache-Container (`web`) ist danach unter `http://localhost:8080` erreichbar.
- Die MySQL‑Datenbank läuft im Service `db`.

### Datenbank initialisieren
```bash
docker exec -i $(docker compose ps -q db) mysql -uroot -pexample m307_projektmanagement < database.sql
```
Alternativ kann man `phpmyadmin` oder ein anderes SQL-Tool im Container verwenden.

### Anwendung verwenden
Öffne den Browser und rufe die Login‑Seite auf:

```
http://localhost:8080/login.php
```

### Stoppen / Aufräumen
Mit `docker compose down` werden die Container gestoppt und die Netzwerke entfernt.
Der Datenbankinhalt bleibt in einem Docker‑Volume (`db_data`) erhalten.

---

## Ordnerstruktur

```

├── includes/
│   ├── auth.php        – Session, CSRF, Flash-Nachrichten
│   └── db.php          – Datenbankverbindung (zentral)
├── css/
│   └── style.css       – Alle Styles (einzige CSS-Datei)
├── js/
│   └── validierung.js  – Frontend-Validierung & Zwischenspeicherung
├── admin/
│   └── views.php       – SQL Views anzeigen
├── login.php
├── registrieren.php
├── logout.php
├── index.php           – Projektübersicht (Hauptseite)
├── projekt-erstellen.php
├── projekt-loeschen.php
├── aufgabe-bearbeiten.php
├── aufgabe-loeschen.php
├── database.sql        – Datenbankschema + Testdaten
└── README.md
```

---

## Usability- und Testprotokoll

### Test-Case 1: Registrierung mit ungültigen Daten
**Vorbedingung:** Benutzer ist nicht eingeloggt, öffnet `/registrieren.php`  
**Aktion:** Formular mit ungültiger E-Mail und Passwort < 8 Zeichen abschicken  
**Erwartetes Resultat:** Fehlermeldungen werden direkt beim Feld angezeigt, Formular wird nicht abgeschickt  
**Resultat:** ✅ Fehlermeldungen erscheinen inline bei den jeweiligen Feldern (JS + PHP)

### Test-Case 2: Projekt erstellen
**Vorbedingung:** Benutzer ist eingeloggt  
**Aktion:** Neues Projekt über das Modal erstellen mit Name und Abgabedatum  
**Erwartetes Resultat:** Projekt erscheint in der Übersicht, Flash-Nachricht "Projekt wurde erstellt"  
**Resultat:** ✅ Projekt wird gespeichert, PRG-Pattern verhindert doppelte Einträge

### Test-Case 3: Aufgabe löschen
**Vorbedingung:** Mindestens eine Aufgabe vorhanden  
**Aktion:** Auf "löschen" klicken, Bestätigung im Dialog bestätigen  
**Erwartetes Resultat:** Aufgabe verschwindet aus der Tabelle, Bestätigungsmeldung  
**Resultat:** ✅ Aufgabe wird gelöscht, CSRF-Schutz verhindert unautorisierten Zugriff

---

## Aufgabenverteilung (Kanban)

| Aufgabe | Person | Status |
|---------|--------|--------|
| Datenbank Design | JZi | Done |
| Basis grafisches Design | DFe | Done |
| SQL Queries sammeln | DSc | Done |
| BE: Formularinhalt in Datenbank | DSc | Done |
| BE: DB View erstellen | DSc | Done |
| BE: Formularentgegennahme | JZi | Done |
| FE: Formular 1 in HTML | DFe | Done |
| BE: Validierung und Rückmeldung | DFe | Done |
| FE: Validierung | DSc | Done |
| FE: Zwischenspeicherung | DSc | Done |
| FE: Valiedierung mit JavaScript | JZi | Done |
| BE: Bestehende Daten ändern | JZi | Done |
| BE: Bestehenden Daten löschen | JZi | Done |
| BE: Views in HTML auflisten | DFe | Done |
| FE: Auflistung Tabelleninhalt | JZi | Done |
| BE: Filtering der Auflistung | DSc | Done |
| BE: Sortierung der Auflistungen | DSc | Done |
| T: Datenbank Testdaten erfassen | JZi | Done |
