<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Charge automatiquement les classes nécessaires

$ms = ""; // Message générique pour afficher le résultat
$error = "";   // Message en cas d'erreur

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $error = "Veuillez entrer votre adresse email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Veuillez saisir une adresse email valide.";
    } else {
        // Connexion à la base de données
        $db_host = "localhost";
        $db_user = "root";
        $db_password = "";
        $db_name = "Siteweb_users";

        $con = new mysqli($db_host, $db_user, $db_password, $db_name);

        if ($con->connect_error) {
            die("Erreur de connexion à la base de données : " . $con->connect_error);
        }

        // Vérifier si l'email existe dans la base
        $stmt = $con->prepare("SELECT id FROM usr WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Générer un token unique et définir une expiration
            $token = bin2hex(random_bytes(50));
            $expire_at = date("Y-m-d H:i:s", strtotime("+1 hour"));

            // Insérer ou mettre à jour le token dans la table `password_resets`
            $stmt = $con->prepare(
                "INSERT INTO password_resets (email, token, expire_at) VALUES (?, ?, ?) 
                 ON DUPLICATE KEY UPDATE token = ?, expire_at = ?"
            );
            $stmt->bind_param("sssss", $email, $token, $expire_at, $token, $expire_at);
            $stmt->execute();

            // Générer le lien de réinitialisation
            $reset_link = "http://localhost:8080/SITE/reset_password.php?token=" . $token;

            // Envoi de l'email avec PHPMailer
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ];
                $mail->Username = 'eyadomaleki@gmail.com'; // Votre email
                $mail->Password = 'neff djod rwrz clfy'; // Votre mot de passe ou app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Configuration de l'email
                $mail->setFrom('eyadomaleki@gmail.com', 'Eyadom ALEKI');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Réinitialisation de votre mot de passe';
                $mail->Body = "Cliquez sur ce lien pour réinitialiser votre mot de passe : 
                    <a href='$reset_link'>$reset_link</a>";

                $mail->send();
                $ms = "Si un compte est associé à cette adresse, un email de réinitialisation a été envoyé.";
            } catch (Exception $e) {
                $error = "L'envoi de l'email a échoué. Veuillez réessayer.";
            }
        } else {
            // Message générique pour éviter les attaques par enumeration
            $ms = "Si un compte est associé à cette adresse, un email de réinitialisation a été envoyé.";
        }

        // Fermer la connexion
        $stmt->close();
        $con->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié</title>
    <link rel="stylesheet" href="ind.css">
</head>
<body>
    <section>
        <h1>Mot de passe oublié</h1>

        <!-- Affichage des erreurs -->
        <?php if (!empty($error)) : ?>
            <p class="erreur"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <!-- Affichage des messages -->
        <?php if (!empty($ms)) : ?>
            <p class="erreur"><?= htmlspecialchars($ms) ?></p>
        <?php endif; ?>

        <!-- Formulaire -->
        <form action="" method="POST">
            <label for="email">Adresse Email</label>
            <input type="email" id="email" name="email"  placeholder="Entrez votre email">
            <input type="submit" name="bouton" value="Envoyer le lien de réinitialisation">
        </form>
    </section>
</body>
</html>
