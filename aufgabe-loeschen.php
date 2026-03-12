<?php
// Backend: Aufgabe loeschen
// Autor: JZi

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$aufgabeId = (int) ($_GET['id'] ?? 0);
$csrfToken = $_GET['csrf_token'] ?? '';

// CSRF Schutz pruefen
if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
    http_response_code(403);
    die('Ungueltige Anfrage.');
}

if ($aufgabeId <= 0) {
    flashSetzen('fehler', 'Ungueltige Aufgaben-ID.');
    header('Location: /m307/index.php');
    exit;
}

require_once __DIR__ . '/includes/db.php';

$pdo  = getDbConnection();
$stmt = $pdo->prepare('DELETE FROM aufgaben WHERE id = :id');
$stmt->execute(['id' => $aufgabeId]);

flashSetzen('erfolg', 'Aufgabe wurde geloescht.');
header('Location: /m307/index.php');
exit;
