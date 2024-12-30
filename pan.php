<?php
session_start();

// Initialisation du panier
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Fonction pour calculer le total du panier
function calculerTotal() {
    $total = 0;
    foreach ($_SESSION['panier'] as $article) {
        $total += $article['prix'] * $article['quantite'];
    }
    return $total;
}
// Traitement de l'ajout au panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_article'])) {
    $id_article = $_POST['id_article'];
    $nom_article = $_POST['nom_article'];
    $prix = (float)$_POST['prix'];
    $quantite = (int)$_POST['quantite'];

    // Vérifier si l'article existe déjà dans le panier
    if (isset($_SESSION['panier'][$id_article])) {
        $_SESSION['panier'][$id_article]['quantite'] += $quantite;
    } else {
        $_SESSION['panier'][$id_article] = [
            'nom' => $nom_article,
            'prix' => $prix,
            'quantite' => $quantite,
        ];
    }

    $message = "Produit ajouté au panier avec succès.";
}
?>