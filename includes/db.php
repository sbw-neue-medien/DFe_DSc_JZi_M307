<?php
// Datenbankverbindung wird hier zentral konfiguriert
// Alle anderen Dateien binden diese Datei ein um auf die DB zuzugreifen
// Für Docker-Deployments werden die Werte aus Umgebungsvariablen geladen,
// es gibt aber sensible Standardwerte, damit der Code auch lokal weiter


// Die Variablen können in einer `.env`-Datei oder via docker-compose gesetzt werden.
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'm307_projektmanagement');
define('DB_PORT', getenv('DB_PORT') ?: 3306);

function getDbConnection(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $optionen = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $optionen);
        } catch (PDOException $e) {
            // Fehlermeldung im Entwicklungsmodus anzeigen
            die('Datenbankverbindung fehlgeschlagen: ' . $e->getMessage());
        }
    }

    return $pdo;
}
