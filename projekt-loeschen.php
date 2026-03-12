<?php
// Backend: Projekt loeschen
// Autor: DSc

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$projektId = (int) ($_GET['id'] ?? 0);
$csrfToken = $_GET['csrf_token'] ?? '';

if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
    http_response_code(403);
    die('Ungueltige Anfrage.');
}

if ($projektId <= 0) {
    flashSetzen('fehler', 'Ungueltige Projekt-ID.');
    header('Location: /m307/index.php');
    exit;
}

require_once __DIR__ . '/includes/db.php';

$pdo  = getDbConnection();
// Aufgaben werden durch CASCADE automatisch mit geloescht
$stmt = $pdo->prepare('DELETE FROM projekte WHERE id = :id');
$stmt->execute(['id' => $projektId]);

flashSetzen('erfolg', 'Projekt wurde geloescht.');
header('Location: /m307/index.php');
exit;
