<?php declare(strict_types=1);
/**
 * Vorlage bei error
 * Anpassen
 */

if( isset( $_GET[ 'error' ] ) ) {
  $error = $_GET[ 'error' ];
  $message = '<p>Error: ' . $error . '</p>';
} else {
  $message =
    '<p>Unbekannter Fehler</p>' .
    '<p><a:href="../index.php">Zurück</a:href>';
}

?><!DOCTYPE html>
<html lang="de">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../css/style.css">
  <title>Fehler</title>
</head>

<body>
  <h1>Fehler</h1>
  <?php echo $message ?>
</body>

</html>