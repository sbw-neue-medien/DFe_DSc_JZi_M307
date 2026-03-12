<?php
// Login-Seite
// Autorin: DFe

require_once __DIR__ . '/includes/auth.php';

// Wenn bereits angemeldet dann direkt weiterleiten
if (istAngemeldet()) {
    header('Location: /m307/index.php');
    exit;
}

$fehler = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfPruefen();

    require_once __DIR__ . '/includes/db.php';

    $eingabe = trim($_POST['benutzer'] ?? '');
    $passwort = $_POST['passwort'] ?? '';

    if (empty($eingabe)) {
        $fehler['benutzer'] = 'Benutzername oder E-Mail darf nicht leer sein.';
    }
    if (empty($passwort)) {
        $fehler['passwort'] = 'Passwort darf nicht leer sein.';
    }

    if (empty($fehler)) {
        $pdo  = getDbConnection();
        // Benutzer anhand von Benutzername oder E-Mail suchen
        $stmt = $pdo->prepare('SELECT * FROM benutzer WHERE benutzername = :eingabe1 OR email = :eingabe2 LIMIT 1');
        $stmt->execute(['eingabe1' => $eingabe, 'eingabe2' => $eingabe]);
        $benutzer = $stmt->fetch();

        if ($benutzer && password_verify($passwort, $benutzer['passwort_hash'])) {
            anmelden($benutzer);
            // PRG Pattern: nach Login weiterleiten
            header('Location: /m307/index.php');
            exit;
        } else {
            $fehler['allgemein'] = 'Benutzername oder Passwort ist falsch.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anmeldung</title>
    <link rel="stylesheet" href="/m307/css/style.css">
</head>
<body>
    <div class="karte-wrapper">
        <div class="karte">
            <h1>Anmeldung</h1>

            <?php if (!empty($fehler['allgemein'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($fehler['allgemein']) ?></div>
            <?php endif; ?>

            <form id="login-formular" method="POST" action="/m307/login.php" novalidate>
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                <fieldset>
                    <legend class="sr-only">Anmeldedaten</legend>

                    <div class="formular-gruppe">
                        <label for="login-benutzer" class="sr-only">Benutzername oder E-Mail</label>
                        <input
                            type="text"
                            id="login-benutzer"
                            name="benutzer"
                            placeholder="Benutzername oder E-Mail"
                            value="<?= htmlspecialchars($_POST['benutzer'] ?? '') ?>"
                            autocomplete="username"
                            class="<?= isset($fehler['benutzer']) ? 'fehler' : '' ?>"
                        >
                        <?php if (isset($fehler['benutzer'])): ?>
                            <span class="fehler-text"><?= htmlspecialchars($fehler['benutzer']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="formular-gruppe">
                        <label for="login-passwort" class="sr-only">Passwort</label>
                        <input
                            type="password"
                            id="login-passwort"
                            name="passwort"
                            placeholder="Passwort"
                            autocomplete="current-password"
                            class="<?= isset($fehler['passwort']) ? 'fehler' : '' ?>"
                        >
                        <?php if (isset($fehler['passwort'])): ?>
                            <span class="fehler-text"><?= htmlspecialchars($fehler['passwort']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="formular-gruppe">
                        <div class="checkbox-gruppe">
                            <label>
                                <input type="checkbox" name="angemeldet_bleiben" value="1">
                                angemeldet bleiben
                            </label>
                        </div>
                    </div>
                </fieldset>

                <button type="submit" class="btn btn-primary">Login</button>
            </form>

            <p class="formular-link"><a href="/m307/login.php">Passwort vergessen?</a></p>
            <p class="formular-link"><a href="/m307/registrieren.php">Registrieren</a></p>
        </div>
    </div>
    <script src="/m307/js/validierung.js"></script>
</body>
</html>
