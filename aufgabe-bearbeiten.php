<?php
// Backend: Bestehende Aufgabe aendern
// Autor: JZi

require_once __DIR__ . '/includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /m307/index.php');
    exit;
}

csrfPruefen();
require_once __DIR__ . '/includes/db.php';

$pdo       = getDbConnection();
$aufgabeId = (int) ($_POST['aufgabe_id'] ?? 0);
$projektId = (int) ($_POST['projekt_id'] ?? 0);

if ($aufgabeId <= 0) {
    flashSetzen('fehler', 'Ungueltige Aufgaben-ID.');
    header('Location: /m307/index.php');
    exit;
}

$titel            = trim($_POST['titel'] ?? '');
$beschreibung     = trim($_POST['beschreibung'] ?? '');
$abgabedatum      = trim($_POST['abgabedatum'] ?? '');
$erinnerungDatum  = trim($_POST['erinnerung_datum'] ?? '');
$erinnerungPer    = trim($_POST['erinnerung_per'] ?? '');
$zugewiesen       = $_POST['zugewiesen_an'] ?? [];

$fehler = [];

if (empty($titel)) {
    $fehler[] = 'Titel darf nicht leer sein.';
}

if (empty($abgabedatum) || !strtotime($abgabedatum)) {
    $fehler[] = 'Abgabedatum ist ungueltig.';
}

if (!empty($fehler)) {
    foreach ($fehler as $f) {
        flashSetzen('fehler', $f);
    }
    header('Location: /m307/index.php');
    exit;
}

// Erinnerungswerte aufbereiten
$erinnerungDatumDb = !empty($erinnerungDatum) ? date('Y-m-d H:i:s', strtotime($erinnerungDatum)) : null;
$erinnerungPerDb   = in_array($erinnerungPer, ['email', 'push', 'beide']) ? $erinnerungPer : null;

// Erste zugewiesene Person nehmen (vereinfacht)
$zugewiesenAn = !empty($zugewiesen) ? (int) $zugewiesen[0] : null;

// Aufgabe aktualisieren
$stmt = $pdo->prepare(
    'UPDATE aufgaben SET
        titel            = :titel,
        beschreibung     = :beschreibung,
        abgabedatum      = :abgabedatum,
        erinnerung_datum = :erinnerung_datum,
        erinnerung_per   = :erinnerung_per,
        zugewiesen_an    = :zugewiesen_an
     WHERE id = :id'
);
$stmt->execute([
    'titel'            => $titel,
    'beschreibung'     => $beschreibung ?: null,
    'abgabedatum'      => $abgabedatum,
    'erinnerung_datum' => $erinnerungDatumDb,
    'erinnerung_per'   => $erinnerungPerDb,
    'zugewiesen_an'    => $zugewiesenAn,
    'id'               => $aufgabeId,
]);

flashSetzen('erfolg', 'Aufgabe wurde erfolgreich aktualisiert.');
// PRG Pattern Weiterleitung
header('Location: /m307/index.php');
exit;
