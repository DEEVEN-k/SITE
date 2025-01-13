<?php
session_start();

// Vérifier si une session est active avant de la détruire
if (session_status() === PHP_SESSION_ACTIVE) {
    // Supprimer toutes les variables de session
    $_SESSION = array();

    // Si un cookie de session est utilisé, le supprimer
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Détruire la session
    session_destroy();
}

// Rediriger vers la page de connexion
header("Location: Golden.html");
exit;
?>
