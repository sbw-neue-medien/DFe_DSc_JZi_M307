<?php
// Registrierungsseite
// Autorin: DFe

require_once __DIR__ . '/includes/auth.php';

if (istAngemeldet()) {
    header('Location: /index.php');
    exit;
}

$fehler    = [];
$eingaben  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfPruefen();

    require_once __DIR__ . '/includes/db.php';

    $eingaben = [
        'email'                 => trim($_POST['email'] ?? ''),
        'benutzername'          => trim($_POST['benutzername'] ?? ''),
        'passwort'              => $_POST['passwort'] ?? '',
        'passwort_bestaetigung' => $_POST['passwort_bestaetigung'] ?? '',
    ];

    // Serverseitige Validierung (DFe: BE Validierung und Rueckmeldung)
    if (empty($eingaben['email']) || !filter_var($eingaben['email'], FILTER_VALIDATE_EMAIL)) {
        $fehler['email'] = 'Bitte eine gueltige E-Mail Adresse eingeben.';
    }

    if (empty($eingaben['benutzername'])) {
        $fehler['benutzername'] = 'Benutzername darf nicht leer sein.';
    } elseif (strlen($eingaben['benutzername']) < 3) {
        $fehler['benutzername'] = 'Benutzername muss mindestens 3 Zeichen lang sein.';
    }

    if (strlen($eingaben['passwort']) < 8) {
        $fehler['passwort'] = 'Passwort muss mindestens 8 Zeichen lang sein.';
    }

    if ($eingaben['passwort'] !== $eingaben['passwort_bestaetigung']) {
        $fehler['passwort_bestaetigung'] = 'Passwoerter stimmen nicht ueberein.';
    }

    if (empty($fehler)) {
        $pdo = getDbConnection();

        // Pruefen ob E-Mail oder Benutzername bereits existiert
        $stmt = $pdo->prepare('SELECT id FROM benutzer WHERE email = :email OR benutzername = :benutzername LIMIT 1');
        $stmt->execute(['email' => $eingaben['email'], 'benutzername' => $eingaben['benutzername']]);

        if ($stmt->fetch()) {
            $fehler['allgemein'] = 'E-Mail oder Benutzername wird bereits verwendet.';
        } else {
            $hash = password_hash($eingaben['passwort'], PASSWORD_BCRYPT);
            $insert = $pdo->prepare('INSERT INTO benutzer (benutzername, email, passwort_hash) VALUES (:benutzername, :email, :hash)');
            $insert->execute([
                'benutzername' => $eingaben['benutzername'],
                'email'        => $eingaben['email'],
                'hash'         => $hash,
            ]);

            // Nach erfolgreicher Registrierung einloggen
            $neuerBenutzer = [
                'id'           => (int) $pdo->lastInsertId(),
                'benutzername' => $eingaben['benutzername'],
                'email'        => $eingaben['email'],
            ];
            anmelden($neuerBenutzer);
            flashSetzen('erfolg', 'Willkommen ' . $eingaben['benutzername'] . '! Dein Konto wurde erstellt.');
            header('Location: /index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrieren</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="karte-wrapper">
        <div class="karte">
            <h1>Registrieren</h1>

            <?php if (!empty($fehler['allgemein'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($fehler['allgemein']) ?></div>
            <?php endif; ?>

            <form id="registrieren-formular" method="POST" action="/registrieren.php" novalidate>
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                <fieldset>
                    <legend class="sr-only">Kontodaten</legend>

                    <div class="formular-gruppe">
                        <label for="reg-email" class="sr-only">E-Mail</label>
                        <input
                            type="email"
                            id="reg-email"
                            name="email"
                            placeholder="E-Mail"
                            value="<?= htmlspecialchars($eingaben['email'] ?? '') ?>"
                            autocomplete="email"
                            class="<?= isset($fehler['email']) ? 'fehler' : '' ?>"
                        >
                        <?php if (isset($fehler['email'])): ?>
                            <span class="fehler-text"><?= htmlspecialchars($fehler['email']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="formular-gruppe">
                        <label for="reg-benutzername" class="sr-only">Benutzername</label>
                        <input
                            type="text"
                            id="reg-benutzername"
                            name="benutzername"
                            placeholder="Benutzername"
                            value="<?= htmlspecialchars($eingaben['benutzername'] ?? '') ?>"
                            autocomplete="username"
                            class="<?= isset($fehler['benutzername']) ? 'fehler' : '' ?>"
                        >
                        <?php if (isset($fehler['benutzername'])): ?>
                            <span class="fehler-text"><?= htmlspecialchars($fehler['benutzername']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="formular-gruppe">
                        <label for="reg-passwort" class="sr-only">Passwort</label>
                        <input
                            type="password"
                            id="reg-passwort"
                            name="passwort"
                            placeholder="Passwort"
                            autocomplete="new-password"
                            class="<?= isset($fehler['passwort']) ? 'fehler' : '' ?>"
                        >
                        <?php if (isset($fehler['passwort'])): ?>
                            <span class="fehler-text"><?= htmlspecialchars($fehler['passwort']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="formular-gruppe">
                        <label for="reg-passwort-bestaetigung" class="sr-only">Passwort bestätigen</label>
                        <input
                            type="password"
                            id="reg-passwort-bestaetigung"
                            name="passwort_bestaetigung"
                            placeholder="Passwort bestätigen"
                            autocomplete="new-password"
                            class="<?= isset($fehler['passwort_bestaetigung']) ? 'fehler' : '' ?>"
                        >
                        <?php if (isset($fehler['passwort_bestaetigung'])): ?>
                            <span class="fehler-text"><?= htmlspecialchars($fehler['passwort_bestaetigung']) ?></span>
                        <?php endif; ?>
                    </div>
                </fieldset>

                <button type="submit" class="btn btn-primary">Registrieren</button>
            </form>

            <p class="formular-link"><a href="/login.php">Anmelden</a></p>
        </div>
    </div>
    <script src="/js/validierung.js"></script>
</body>
</html>
