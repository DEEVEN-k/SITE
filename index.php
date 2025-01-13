<?php
session_start();
$erreur = ""; // Initialisation de la variable pour afficher les erreurs

if (isset($_POST['bouton'])) {
    if (!empty($_POST['identifiant']) && !empty($_POST['modp'])) {
        $identifiant = trim($_POST['identifiant']);
        $modp = trim($_POST['modp']);

        // Connexion à la base de données
        $nom_serveur = "localhost";
        $utilisateur = "root";
        $mot_de_passe = "";
        $bd = "Siteweb_user";

        $con = new mysqli($nom_serveur, $utilisateur, $mot_de_passe, $bd);

        if ($con->connect_error) {
            die("Échec de la connexion : " . $con->connect_error);
        }

        // Vérification de l'identifiant
        if (filter_var($identifiant, FILTER_VALIDATE_EMAIL)) {
            $stmt = $con->prepare("SELECT modp FROM usr WHERE email = ?");
            $stmt->bind_param("s", $identifiant);
        } elseif (preg_match("/^[0-9]{10}$/", $identifiant)) {
            $stmt = $con->prepare("SELECT modp FROM usr WHERE telephone = ?");
            $stmt->bind_param("s", $identifiant);
        } else {
            $erreur = "Identifiant invalide (ni un email ni un numéro de téléphone valide).";
        }

        if (empty($erreur)) {
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if (password_verify($modp, $row['modp'])) {
                    $_SESSION['identifiant'] = $identifiant;
                    session_regenerate_id(true);
                    header("Location: Golden.html");
                    exit;
                } else {
                    $erreur = "Adresse email/téléphone ou mot de passe invalide.";
                }
            } else {
                $erreur = "Aucun utilisateur trouvé avec cet identifiant.";
            }

            $stmt->close();
        }

        $con->close();
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
    <link rel="stylesheet" href="style.css">
  
    
</head>
<body class="page">
    <section class="sct">
        <h1>Connexion</h1>
        <?php if (!empty($erreur)): ?>
            <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
        <?php endif; ?>
        
        <form  method="POST">
            <label>Adresse Mail ou Téléphone</label>
            <input type="text" name="identifiant" required>
            <label>Mot de Passe</label>
            <input  type="password" name="modp" required>
            <p><a href="javascript:void(0);" onclick="openModal('forgotPasswordModal', 'motdepasse_oublie.php')">Mot de passe oublié ?</a></p>
            <input type="submit" value="Valider" name="bouton">
            <div class="inscrir">
            <p>Pas encore de compte ? <a href="javascript:void(0);" onclick="openModal('registerModal', 'ins.php')">S'inscrire</a></p></div>
        </form>
    </section>

    <!-- Modales dynamiques -->
   

</body>
</html>
