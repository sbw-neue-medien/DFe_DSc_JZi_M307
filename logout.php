<?php
// Abmeldung
require_once __DIR__ . '/includes/auth.php';

abmelden();
header('Location: /login.php');
exit;
