<?php
session_start(); // Démarrage de la session pour stocker les informations utilisateur
require 'vendor/autoload.php'; // PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Génération d'un token CSRF si inexistant
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialiser PHPMailer
$mail = new PHPMailer(true);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? ''; // Identifier l'action (reset ou resend)
    $token = $_POST['token'] ?? '';
    $modp = trim($_POST['modp'] ?? '');
    $modp_confirm = trim($_POST['modp_confirm'] ?? '');
    $email = $_POST['email'] ?? ($_SESSION['email'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';

    $erreur = "";
    $ms = "";

    // Validation du token CSRF
    if (empty($csrf_token) || $csrf_token !== $_SESSION['csrf_token']) {
        $erreur = "Action non autorisée.";
        exit;
    }

    // Connexion à la base de données
    $con = new mysqli("localhost", "root", "", "Siteweb_users");
    if ($con->connect_error) {
        die("Échec de la connexion : " . $con->connect_error);
    }

    // Réinitialisation du mot de passe
    if ($action === 'reset') {
        if (empty($token) || empty($modp) || empty($modp_confirm)) {
            $erreur = "Veuillez remplir tous les champs.";
        } elseif ($modp !== $modp_confirm) {
            $erreur = "Les mots de passe ne correspondent pas.";
        } elseif (strlen($modp) < 8) {
            $erreur = "Le mot de passe doit contenir au moins 8 caractères.";
        } else {
            $stmt = $con->prepare("SELECT email FROM password_resets WHERE token = ? AND expire_at > NOW()");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $email = $result->fetch_assoc()['email'];
                $_SESSION['email'] = $email;

                $hashed_password = password_hash($modp, PASSWORD_DEFAULT);
                $stmt = $con->prepare("UPDATE usr SET modp = ? WHERE email = ?");
                $stmt->bind_param("ss", $hashed_password, $email);

                if ($stmt->execute()) {
                    $ms = "Votre mot de passe a été réinitialisé avec succès.";
                    // Supprimer le token utilisé
                    $stmt = $con->prepare("DELETE FROM password_resets WHERE email = ?");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                } else {
                    $erreur = "Échec de la réinitialisation du mot de passe.";
                }
            } else {
                $erreur = "Lien invalide ou expiré.";
            }
        }
    }

    // Renvoi du lien de réinitialisation
    if ($action === 'resend') {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreur = "Email introuvable ou invalide.";
        } else {
            $stmt = $con->prepare("SELECT id FROM usr WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $token = bin2hex(random_bytes(50));
                $expire_at = date("Y-m-d H:i:s", strtotime("+1 hour"));

                $stmt = $con->prepare(
                    "INSERT INTO password_resets (email, token, expire_at) VALUES (?, ?, ?) 
                     ON DUPLICATE KEY UPDATE token = ?, expire_at = ?"
                );
                $stmt->bind_param("sssss", $email, $token, $expire_at, $token, $expire_at);
                $stmt->execute();

                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'eyadomaleki@gmail.com'; // Votre adresse email
                    $mail->Password = 'neff djod rwrz clfy'; // Votre mot de passe
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('eyadomaleki@gmail.com', 'Eyadom ALEKI');
                    $mail->addAddress($email);
                    $mail->isHTML(true);
                    $mail->Subject = 'Nouveau lien de réinitialisation';
                    $mail->Body = "Cliquez sur ce lien pour réinitialiser votre mot de passe : 
                                  <a href='http://localhost:8080/SITE/reset_password.php?token=$token'>Réinitialiser</a>";

                    $mail->send();
                    $ms = "Un nouveau lien a été envoyé à votre adresse email.";
                } catch (Exception $e) {
                    $erreur = "Impossible d'envoyer l'email. Veuillez réessayer.";
                }
            } else {
                $erreur = "Cette adresse email n'existe pas.";
            }
        }
    }

    // Fermer la connexion
    if (isset($stmt)) {
        $stmt->close();
    }

    $con->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Réinitialisation du mot de passe</title>
</head>
<body class="page">
    <section>
        <h1>Réinitialiser le mot de passe</h1>

        <!-- Affichage des messages -->
        <?php if (!empty($erreur)): ?>
            <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
        <?php elseif (!empty($ms)): ?>
            <p class="erreur"><?= htmlspecialchars($ms) ?></p>

            <!-- Si réinitialisation réussie, afficher le bouton "Se connecter" -->
            <form action="index.php" method="get">
                <input type="submit" value="Se connecter" name="bouton">
            </form>

            <!-- Afficher le bouton de déconnexion -->
            <form action="logout.php" method="post">
                <input type="submit" value="Se déconnecter" name="bouton">
            </form>

        <?php else: ?>
            <!-- Formulaire principal -->
            <form action="" method="POST">
                <input type="hidden" name="action" value="reset">
                <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <label for="modp">Nouveau Mot de Passe</label>
                <input type="password" id="modp" name="modp" required>
                <label for="modp_confirm">Confirmez le Mot de Passe</label>
                <input type="password" id="modp_confirm" name="modp_confirm" required>
                <input 
    type="button" 
    value="Réinitialiser" 
    name="bouton" 
    id="resetButton" 
    onclick="openModal('resetModal', 'reset_password.php')">

            </form>

            <!-- Formulaire pour réenvoyer le lien -->
            <form action="" method="POST" style="margin-top: 20px;">
                <input type="hidden" name="action" value="resend">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="email" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>">
                <input type="submit" value="Renvoyer le lien" name="bouton">
            </form>
        <?php endif; ?>
    </section>
</body>
</html>
