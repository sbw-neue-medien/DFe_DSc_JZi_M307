<?php
// Backend: Projekt loeschen
// Autor: DSc

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Projekt löschen</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/validierung.js"></script>
</head>
<body>
<?php
$projektId = (int) ($_GET['id'] ?? 0);
$csrfToken = $_GET['csrf_token'] ?? '';

if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
    http_response_code(403);
    die('Ungueltige Anfrage.');
}

if ($projektId <= 0) {
    flashSetzen('fehler', 'Ungueltige Projekt-ID.');
    header('Location: /index.php');
    exit;
}

$pdo  = getDbConnection();
// Aufgaben werden durch CASCADE automatisch mit geloescht
$stmt = $pdo->prepare('DELETE FROM projekte WHERE id = :id');
$stmt->execute(['id' => $projektId]);

flashSetzen('erfolg', 'Projekt wurde geloescht.');
header('Location: /index.php');
exit;
?>
</body>
</html>
