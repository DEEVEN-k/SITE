<?php 

session_start();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue</title>
    <link rel="stylesheet" href="ind.css">
</head>
<body>
    <?php 
    echo " <p class='message'> Bienvenue " . $_SESSION['email'] . "</p>";
    ?>
</body>
</html>