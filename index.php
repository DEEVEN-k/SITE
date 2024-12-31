<?php
session_start();
$erreur = ""; // Initialiser l'erreur

// Vérification si le formulaire a été soumis
if (isset($_POST['bouton'])) {
    // Vérifier si les champs sont remplis
    if (!empty($_POST['email']) && !empty($_POST['modp'])) {
        $email = trim($_POST['email']);
        $modp = trim($_POST['modp']);
        
        // Valider l'adresse email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreur = "Veuillez saisir une adresse email valide.";
        } else {
            // Paramètres de connexion à la base de données
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

            // Préparer la requête pour éviter les injections SQL
            $stmt = $con->prepare("SELECT modp FROM usr WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                
                // Vérification du mot de passe avec `password_verify`
                if (password_verify($modp, $row['modp'])) {
                    // L'utilisateur est authentifié
                    $_SESSION['email'] = $email;

                    // Régénérer l'ID de session pour éviter les attaques de fixation de session
                    session_regenerate_id(true);

                    // Rediriger l'utilisateur vers la page d'accueil ou une page spécifique
                    header("Location: form.php");
                    exit;
                } else {
                    $erreur = "Adresse email ou mot de passe invalide.";
                }
            } else {
                $erreur = "Adresse email ou mot de passe invalide.";
            }

            // Fermeture de la requête et de la connexion
            $stmt->close();
            $con->close();
        }
    } else {
        $erreur = "Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="ind.css">
</head>
<body>
    <section>
        <h1>Connexion</h1>
        <?php 
        if (!empty($erreur)) {
            echo "<p class='erreur'>" . htmlspecialchars($erreur) . "</p>";
        }
        ?>
        <form action="" method="POST">
            <label>Adresse Mail</label>
            <input type="email" name="email" >
            <label>Mot de Passe</label>
            <input type="password" name="modp" >
            <p class="oub"><a href="motdepasse_oublie.php">mot de passe oublié ?</a></p>
            <input type="submit" value="Valider" name="bouton">
            <p class="inscr">Vous n'avez pas de compte ? <a href="ins.php">Inscription</a></p>
        </form>
    </section>
</body>
</html>
