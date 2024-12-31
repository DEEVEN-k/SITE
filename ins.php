<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Vérifier si les champs nécessaires sont remplis
    if (!empty($_POST['email']) && !empty($_POST['modp']) && !empty($_POST['modp_confirm'])) {
        $email = trim($_POST['email']);
        $modp = trim($_POST['modp']);
        $modp_confirm = trim($_POST['modp_confirm']);
        $erreur = "";

        // Validation de l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreur = "Veuillez saisir une adresse email valide.";
        } elseif ($modp !== $modp_confirm) {
            $erreur = "Les mots de passe ne correspondent pas.";
        } else {
            $nom_serveur = "localhost";
            $utilisateur = "root";
            $mot_de_passe = "";
            $bd = "Siteweb_users";

            // Connexion à la base de données
            $con = new mysqli($nom_serveur, $utilisateur, $mot_de_passe, $bd);

            // Vérification de la connexion
            if ($con->connect_error) {
                die("Échec de la connexion : " . $con->connect_error);
            }

            // Vérifier si l'email existe déjà
            $stmt = $con->prepare("SELECT id FROM usr WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $erreur = "Un compte avec cet email existe déjà.";
            } else {
                // Hachage du mot de passe
                $hashed_password = password_hash($modp, PASSWORD_DEFAULT);

                // Insérer l'utilisateur dans la base de données
                $stmt = $con->prepare("INSERT INTO usr (email, modp) VALUES (?, ?)");
                $stmt->bind_param("ss", $email, $hashed_password);

                if ($stmt->execute()) {
                    header("Location: index.php");
                    exit;
                } else {
                    $erreur = "Une erreur est survenue lors de l'inscription.";
                }
            }

            // Fermeture des ressources
            $stmt->close();
            $con->close();
        }
    } else {
        $erreur = "Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="ind.css">
</head>
<body>
    <section>
        <h1>Inscription</h1>
        <?php 
        if (!empty($erreur)) {
            echo "<p class='erreur'>" . htmlspecialchars($erreur) . "</p>";
        }
        ?>
        <form action="" method="POST">
            <label>Adresse Mail</label>
            <input type="email" name="email" required>
            <label>Mot de Passe</label>
            <input type="password" name="modp" required>
            <label>Confirmez le Mot de Passe</label>
            <input type="password" name="modp_confirm" required>
            <input type="submit" value="S'inscrire">
        </form>
    </section>
</body>
</html>
