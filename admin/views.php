<?php
// Admin-Seite: SQL Queries und Datenbankansichten
// Autor: DSc

require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/includes/db.php';

$pdo = getDbConnection();

// Alle Daten aus den Views laden
$aufgabenView  = $pdo->query('SELECT * FROM v_aufgaben_mit_benutzer ORDER BY abgabedatum ASC')->fetchAll();
$projekteView  = $pdo->query('SELECT * FROM v_projekte_uebersicht ORDER BY abgabedatum ASC')->fetchAll();

$benutzer = aktuellerBenutzer();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - SQL Übersicht</title>
    <link rel="stylesheet" href="/m307/css/style.css">
</head>
<body>
    <header class="header">
        <span class="benutzername"><?= htmlspecialchars($benutzer['benutzername']) ?></span>
        <a href="/m307/index.php" class="btn btn-logout">Zurück</a>
    </header>

    <main class="hauptbereich">
        <h1 style="margin-bottom:32px; font-size:2rem; font-weight:900;">Datenbankansichten (Views)</h1>

        <h2 style="font-size:1.3rem; font-weight:700; margin-bottom:12px;">v_projekte_uebersicht</h2>
        <div class="tabelle-wrapper" style="margin-bottom:40px;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Abgabedatum</th>
                        <th>Erstellt von</th>
                        <th>Anzahl Aufgaben</th>
                        <th>Erstellt am</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projekteView as $p): ?>
                        <tr>
                            <td><?= (int) $p['id'] ?></td>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td><?= htmlspecialchars($p['abgabedatum']) ?></td>
                            <td><?= htmlspecialchars($p['erstellt_von']) ?></td>
                            <td><?= (int) $p['anzahl_aufgaben'] ?></td>
                            <td><?= htmlspecialchars($p['erstellt_am']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h2 style="font-size:1.3rem; font-weight:700; margin-bottom:12px;">v_aufgaben_mit_benutzer</h2>
        <div class="tabelle-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Projekt</th>
                        <th>Titel</th>
                        <th>Abgabedatum</th>
                        <th>Erinnerung</th>
                        <th>Zugewiesen an</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($aufgabenView as $a): ?>
                        <tr>
                            <td><?= (int) $a['id'] ?></td>
                            <td><?= htmlspecialchars($a['projekt_name']) ?></td>
                            <td><?= htmlspecialchars($a['titel']) ?></td>
                            <td><?= htmlspecialchars($a['abgabedatum']) ?></td>
                            <td><?= htmlspecialchars($a['erinnerung_datum'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($a['zugewiesen_an'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
