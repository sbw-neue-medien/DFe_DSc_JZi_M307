<?php
// Backend: Aufgabe loeschen
// Autor: JZi

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Aufgabe löschen</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/validierung.js"></script>
</head>
<body>
<?php
$aufgabeId = (int) ($_GET['id'] ?? 0);
$csrfToken = $_GET['csrf_token'] ?? '';

// CSRF Schutz pruefen
if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
    http_response_code(403);
    die('Ungueltige Anfrage.');
}

if ($aufgabeId <= 0) {
    flashSetzen('fehler', 'Ungueltige Aufgaben-ID.');
    header('Location: index.php');
    exit;
}

$pdo  = getDbConnection();
$stmt = $pdo->prepare('DELETE FROM aufgaben WHERE id = :id');
$stmt->execute(['id' => $aufgabeId]);

flashSetzen('erfolg', 'Aufgabe wurde gelöscht.');
header('Location: index.php');
exit;
?>
</body>
</html>
