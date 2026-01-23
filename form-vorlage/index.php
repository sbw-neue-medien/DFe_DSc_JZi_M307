<?php declare(strict_types=1);
/**
 * Formularausgangslage
 */
require_once '../config.php';
require_once '../include/utils.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../assets/styles/main.css">
  <title>Formularname</title>
</head>
<body>
  <h1>Formularname</h1>
  <?php if (isset($_GET['error'])) :?>
    <p>Es ist ein Fehler aufgetreten: <?php echo htmlspecialchars($_GET['error']);?></p>
  <?php endif;?>
  <form action="save.php" method="post" enctype="multipart/form>
  </form>
</body>
</html>