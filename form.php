<?php
session_start();

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['email'])) {
    // Si l'utilisateur n'est pas connecté, on le redirige vers la page de connexion
    header("Location: index.php");
    exit;
}

// Initialisation du panier si ce n'est pas déjà fait
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
$produitAjoute = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_article'])) {
    $id_article = $_POST['id_article'];
    $nom_article = $_POST['nom_article'];
    $prix = (float)$_POST['prix'];
    $quantite = (int)$_POST['quantite'];

    // Ajouter ou mettre à jour l'article dans le panier
    if (isset($_SESSION['panier'][$id_article])) {
        $_SESSION['panier'][$id_article]['quantite'] += $quantite;
    } else {
        $_SESSION['panier'][$id_article] = [
            'nom' => $nom_article,
            'prix' => $prix,
            'quantite' => $quantite,
        ];
    }
    $produitAjoute = true;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="MTA.css">
    <title>Acer Aspire 15 Core i3 8Go RAM SSD 256GB</title>
    <style>
        .normal {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            font-size: 16px;
            border-radius: 5px;
            text-align: center;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .normal:hover {
            background-color: #0056b3;
        }

        /* Styles pour la modale */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            width: 300px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <h1>Acer Aspire 15 Core i3 8Go RAM SSD 256GB</h1>
    
    <div class="pro-container">
        <div class="pro">
            <img src="e-commerce/Acer aspire 15 corei3 'g ram ssd 256gb.jpeg" alt="Acer Aspire" style="width: 300px;">
            <div class="description">
                <span>Ordinateur portable</span>
                <div class="star">
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star-half-alt"></i>
                </div>
            </div>

            <!-- Formulaire pour ajouter un produit au panier -->
            <form method="POST" action="">
                <input type="hidden" name="id_article" value="1">
                <input type="hidden" name="nom_article" value="Acer Aspire 15 Core i3 8Go RAM SSD 256GB">
                <input type="hidden" name="prix" value="750.85">
                <label>Quantité :</label>
                <input type="number" name="quantite" value="1" min="1">
                <button type="submit" class="normal">Acheter</button>
            </form>
        </div>
    </div>

    <!-- Affichage du panier -->
    <div class="panier">
        <?php if (!empty($_SESSION['panier'])): ?>
            <ul>
                <?php foreach ($_SESSION['panier'] as $id => $article): ?>
                    <li>
                        <?= htmlspecialchars($article['nom']) ?> - 
                        Prix: <?= number_format($article['prix'], 2) ?> € - 
                        Quantité: <?= $article['quantite'] ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p><strong>Total: <?= number_format(calculerTotal(), 2) ?> €</strong></p>
        <?php else: ?>
            <p>Votre panier est vide.</p>
        <?php endif; ?>
    </div>

    <!-- Fenêtre modale -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="fermerModal()">&times;</span>
            <p>Produit ajouté au panier avec succès !</p>
        </div>
    </div>

    <script>
        // Afficher la modale
        function ouvrirModal() {
            var modal = document.getElementById('modal');
            modal.style.display = 'flex';
        }

        // Fermer la modale
        function fermerModal() {
            var modal = document.getElementById('modal');
            modal.style.display = 'none';
        }

        // Ouvrir la modale automatiquement après un ajout
        <?php if ($produitAjoute): ?>
        document.addEventListener('DOMContentLoaded', function() {
            ouvrirModal();
        });
        <?php endif; ?>
    </script>
</body>
</html>
