<?php
// Backend: Neues Projekt in der Datenbank speichern
// Autor: DSc

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Projekt erstellen</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/validierung.js"></script>
</head>
<body>
<?php
// Nur POST-Anfragen erlaubt
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /index.php');
    exit;
}

csrfPruefen();

$benutzer = aktuellerBenutzer();
$pdo      = getDbConnection();

$name          = trim($_POST['name'] ?? '');
$beschreibung  = trim($_POST['beschreibung'] ?? '');
$abgabedatum   = trim($_POST['abgabedatum'] ?? '');
$zugewiesen    = $_POST['zugewiesen_an'] ?? [];

$fehler = [];

// Serverseitige Validierung
if (empty($name)) {
    $fehler[] = 'Projektname darf nicht leer sein.';
}

if (empty($abgabedatum) || !strtotime($abgabedatum)) {
    $fehler[] = 'Abgabedatum ist ungueltig.';
}

if (!empty($fehler)) {
    foreach ($fehler as $f) {
        flashSetzen('fehler', $f);
    }
    header('Location: /index.php');
    exit;
}

// Projekt einfuegen
$stmt = $pdo->prepare(
    'INSERT INTO projekte (name, beschreibung, abgabedatum, erstellt_von)
     VALUES (:name, :beschreibung, :abgabedatum, :erstellt_von)'
);
$stmt->execute([
    'name'         => $name,
    'beschreibung' => $beschreibung ?: null,
    'abgabedatum'  => $abgabedatum,
    'erstellt_von' => $benutzer['id'],
]);

$projektId = (int) $pdo->lastInsertId();

// Zugewiesene Benutzer als Standardaufgabe eintragen wenn vorhanden
foreach ($zugewiesen as $benutzerId) {
    $benutzerId = (int) $benutzerId;
    if ($benutzerId > 0) {
        $aufgabeStmt = $pdo->prepare(
            'INSERT INTO aufgaben (projekt_id, titel, abgabedatum, zugewiesen_an)
             VALUES (:projekt_id, :titel, :abgabedatum, :zugewiesen_an)'
        );
        $aufgabeStmt->execute([
            'projekt_id'    => $projektId,
            'titel'         => 'Aufgabe in ' . $name,
            'abgabedatum'   => $abgabedatum,
            'zugewiesen_an' => $benutzerId,
        ]);
    }
}

// PRG Pattern: nach dem Speichern weiterleiten
flashSetzen('erfolg', 'Projekt "' . $name . '" wurde erfolgreich erstellt.');
header('Location: /index.php');
exit;
?>
</body>
</html>
