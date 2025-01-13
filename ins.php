<?php

require 'vendor/autoload.php'; // Pour utiliser libphonenumber (si installé via Composer)

use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;

$phoneUtil = PhoneNumberUtil::getInstance();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $modp = trim($_POST['modp'] ?? '');
    $modp_confirm = trim($_POST['modp_confirm'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $full_telephone = trim($_POST['full_telephone'] ?? ''); // Téléphone complet
    $age_verifie = isset($_POST['age_verifie']); // Case à cocher pour l'âge
    $accepte_conditions = isset($_POST['accepte_conditions']); // Case à cocher pour les conditions

    $erreur = "";
    $ms = "";

    // Validation des champs
    if (empty($nom)) {
        $erreur = "Veuillez entrer votre nom.";
    } elseif (empty($prenom)) {
        $erreur = "Veuillez entrer votre prénom.";
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = "Veuillez entrer une adresse email valide.";
    } elseif (empty($modp) || strlen($modp) < 8) {
        $erreur = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif ($modp !== $modp_confirm) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } elseif (empty($telephone) || !preg_match("/^[0-7]{8}$/", $telephone)) {
        $erreur = "Le numéro de téléphone doit contenir exactement 10 chiffres.";
    } elseif (!$age_verifie) {
        $erreur = "Vous devez confirmer que vous avez 18 ans ou plus.";
    } elseif (!$accepte_conditions) {
        $erreur = "Vous devez accepter nos règles de confidentialité et politique.";
    }

    try {
        $parsedNumber = $phoneUtil->parse($full_telephone, null); // null si le pays est inclus dans le numéro
        if (!$phoneUtil->isValidNumber($parsedNumber)) {
            $erreur = "Le numéro de téléphone est invalide.";
        }
    } catch (\libphonenumber\NumberParseException $e) {
        $erreur = "Le numéro de téléphone est invalide.";
    }

    // Enregistrement dans la base de données si aucune erreur
    if (empty($erreur)) {
        $con = new mysqli("localhost", "root", "", "Siteweb_users");

        if ($con->connect_error) {
            die("Échec de la connexion : " . $con->connect_error);
        }

        // Vérifier si l'email existe déjà
        $stmt = $con->prepare("SELECT id FROM usr WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $erreur = "Cette adresse email est déjà utilisée.";
        } else {
            $hashed_password = password_hash($modp, PASSWORD_DEFAULT);
            $created_at = date("Y-m-d H:i:s"); // Date et heure actuelles

            // Insertion de l'utilisateur dans la base de données
            $stmt = $con->prepare("INSERT INTO usr (nom, prenom, email, modp, telephone, created_at) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $nom, $prenom, $email, $hashed_password, $full_telephone, $created_at);

            if ($stmt->execute()) {
                $ms = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            } else {
                $erreur = "Une erreur est survenue lors de l'inscription.";
            }
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
    <title>Inscription</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
</head>
<body class="page">

<section>
    <h1>Inscription</h1>

    <!-- Affichage des messages -->
    <?php if (!empty($erreur)): ?>
        <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
    <?php elseif (!empty($ms)): ?>
        <p class="success"><?= htmlspecialchars($ms) ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <label for="nom">Nom</label>
        <input type="text" id="nom" name="nom" placeholder="Entrez votre nom" required>

        <label for="prenom">Prénom</label>
        <input type="text" id="prenom" name="prenom" placeholder="Entrez votre prénom" required>

        <label for="email">Adresse Email</label>
        <input type="email" id="email" name="email" placeholder="Entrez votre email" required>

        <label for="modp">Mot de Passe</label>
        <div class="password-container">
            <input type="password" id="modp" name="modp" placeholder="Entrez votre mot de passe" required>
            <button type="button" class="toggle-password" data-target="modp">
                <i class="fas fa-eye"></i>
            </button>
        </div>

        <label for="modp_confirm">Confirmez le Mot de Passe</label>
        <div class="password-container">
            <input type="password" id="modp_confirm" name="modp_confirm" placeholder="Confirmez votre mot de passe" required>
            <button type="button" class="toggle-password" data-target="modp_confirm">
                <i class="fas fa-eye"></i>
            </button>
        </div>

        <label for="telephone">Numéro de téléphone</label>
        <input type="tel" id="telephone" name="telephone" required>
        <input type="hidden" name="full_telephone" id="full_telephone">

        <div class="V">
            <label for="age_verifie">
                <input type="checkbox" id="age_verifie" name="age_verifie"> J'ai 18 ans ou plus
            </label>
        </div>

        <div class="V">
            <label for="accepte_conditions">
                <input type="checkbox" id="accepte_conditions" name="accepte_conditions"> 
                J'accepte les <a href="javascript:void(0);" onclick="openModal('privacyModal', 'politique_confidentialite.html')">règles de confidentialité</a> et les 
                <a href="javascript:void(0);" onclick="openModal('termsModal', 'conditions_utilisation.html')">conditions d'utilisation</a>
            </label>
        </div>

        <input type="submit" value="S'inscrire">
    </form>
</section>

<!-- Modales -->
<div id="privacyModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <!-- Le contenu sera chargé ici -->
    </div>
</div>

<div id="termsModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <!-- Le contenu sera chargé ici -->
    </div>
</div>


<script>
    // Fonction pour ouvrir la modale
    function openModal(modalId, contentUrl) {
        const modal = document.getElementById(modalId);
        const modalContent = modal.querySelector(".modal-content");

        // Charger le contenu dans la modale
        fetch(contentUrl)
            .then(response => response.text())
            .then(content => {
                modalContent.innerHTML = content;
                modal.style.display = "block"; // Afficher la modale
            })
            .catch(() => {
                modalContent.innerHTML = "Erreur de chargement du contenu.";
                modal.style.display = "block";
            });
    }

    // Fermer la modale lorsqu'on clique sur le bouton de fermeture
    document.querySelectorAll(".close").forEach(button => {
        button.addEventListener("click", function() {
            this.closest(".modal").style.display = "none";
        });
    });

    const input = document.querySelector("#telephone");

const iti = window.intlTelInput(input, {
    initialCountry: "TG",  // Le code pays pour le Togo
    separateDialCode: true, // Ajoute l'indicatif séparément
    geoIpLookup: function (callback) {
        fetch('https://ipapi.co/json/')
            .then(response => response.json())
            .then(data => callback(data.country_code))
            .catch(() => callback("TG"));  // Utilisation du Togo si la géolocalisation échoue
    },
    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
});

// Validation lors de la soumission du formulaire
document.querySelector("form").addEventListener("submit", function (e) {
    if (!iti.isValidNumber()) {
        e.preventDefault();
        alert("Veuillez entrer un numéro de téléphone valide.");
    } else {
        const fullNumber = iti.getNumber();
        document.querySelector("#full_telephone").value = fullNumber;
    }
});

// Écoute des changements de pays
iti.getInstance().on("countrychange", function () {
    const countryData = iti.getSelectedCountryData();
    const countryCode = countryData.dialCode;
    const countryName = countryData.name;

    // Ajuster la validation en fonction du pays
    const phoneLength = iti.getNumberType();
    if (phoneLength !== 0) {
        const phonePattern = new RegExp(`^\\d{${phoneLength}}$`);
        // Ajouter une validation supplémentaire si nécessaire, comme afficher une alerte si la longueur est incorrecte
    }
});


    // Toggle mot de passe
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const passwordInput = document.getElementById(targetId);
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
</script>

</body>
</html>
