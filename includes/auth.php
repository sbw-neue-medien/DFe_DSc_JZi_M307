<?php
// Hilfsfunktionen fuer Session-Management und Authentifizierung

session_start();

function istAngemeldet(): bool {
    return isset($_SESSION['benutzer_id']);
}

function requireLogin(): void {
    if (!istAngemeldet()) {
        header('Location: /m307/login.php');
        exit;
    }
}

function aktuellerBenutzer(): array|null {
    if (!istAngemeldet()) {
        return null;
    }
    return [
        'id'           => $_SESSION['benutzer_id'],
        'benutzername' => $_SESSION['benutzername'],
        'email'        => $_SESSION['email'],
    ];
}

function anmelden(array $benutzer): void {
    $_SESSION['benutzer_id']   = $benutzer['id'];
    $_SESSION['benutzername']  = $benutzer['benutzername'];
    $_SESSION['email']         = $benutzer['email'];
}

function abmelden(): void {
    session_unset();
    session_destroy();
}

// CSRF Token generieren und pruefen
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfPruefen(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Ungueltige Anfrage.');
    }
}

// Flash Nachrichten fuer Rueckmeldungen
function flashSetzen(string $typ, string $nachricht): void {
    $_SESSION['flash'][] = ['typ' => $typ, 'nachricht' => $nachricht];
}

function flashHolen(): array {
    $nachrichten = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $nachrichten;
}

function flashAnzeigen(): void {
    foreach (flashHolen() as $flash) {
        $klasse = match($flash['typ']) {
            'erfolg'  => 'alert-success',
            'fehler'  => 'alert-danger',
            'warnung' => 'alert-warning',
            default   => 'alert-info',
        };
        echo '<div class="alert ' . $klasse . '">' . htmlspecialchars($flash['nachricht']) . '</div>';
    }
}
