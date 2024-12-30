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
$produitAjoute = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_article'])) {
    $id_article = $_POST['id_article'];
    $nom_article = $_POST['nom_article'];
    $prix = (float)$_POST['prix'];
    $quantite = (int)$_POST['quantite'];

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
    <title>Acer aspire 15 corei3 'g ram ssd 256gb.</title>
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
    <h1>Acer aspire 15 corei3 8Go ram ssd 256gb</h1>
    
        <div class="pro-container">
            <div class="pro">
                <img src="e-commerce/Acer aspire 15 corei3 'g ram ssd 256gb.jpeg" alt="" style="width: 300px;">
                <div class="description">
                    <span>Ordinateur portable</span>
                    <div class="star"></div>
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star-half-alt"></i>
                   
                </div>
    <!-- Formulaire pour ajouter un produit au panier -->
    <form method="POST" action="">
        <input type="hidden" name="id_article" value="1">
        <input type="hidden" name="nom_article" value="Produit A">
        <input type="hidden" name="prix" value="750.85">
        <label>Quantité :</label>
        <input type="number" name="quantite" value="1" min="1">
        <button type="submit" class="normal" onclick="ouvrirModal()">Acheter</button>
    </form>

    <!-- Panier -->
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
     <section id="Newsletter" class="section-p1 section-m1" >
        <div class="newstext">
            <h4>Soumettez votre email pour etre informé des nouveautés</h4>
            <p>Nouveaux produits <span>Offres spéciales</span></p>
           
        </div>
        <div class="Form">
            <input type="email" placeholder="Entrez votre mail" required>
            <button class="normal">Soumettre</button>
        </div>
    </section>
    <footer class="section-p1" >
        <div class="col">
        
            <h4>Nous Contactez</h4>
            <p><strong>Adresse</strong> Avedji rond point Limosine</p>
            <p><strong>Tél</strong>(+228) 93527693 / 99136385 / 96512484</p>
            <p><strong>Heures</strong> 10:00 - 18:00, Lun - Dim</p>
            <div class="Follow">
                <h4>Nous suivre</h4>
                <div class="icon">
                   <a href=""><i class="fab fa-facebook-f"></i></a> 
                    <a href=""><i class="fab fa-twitter"></i> <i class="fab fa-instagram"></i> <i class="fab fa-pinterest-p"></i> <i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
        <div class="col">
            <h4>Nous</h4>
            <a href="#">A propos nous</a>
            <a href="#">Historique</a>   <a href="#">Confidentialité</a>   <a href="#">Politiques</a>  
        </div>
        <div class="col">
            <h4>Compte</h4>
            <a href="#">Se connecter</a>
            <a href="#"> Voir la carte</a>   <a href="#"> Mes listes de souhait</a>   <a href="#"> Suivre ma commande</a>   <a href="#">Aide</a> 
             </div>
       <div class="col Install">
            <h4>Télécharger </h4>
            <p>Depuis Appstore & PlayStore</p>
            <div class="row">
                <img src="e-commerce/app sto.jpg" alt="" width="140px">
                <img src="e-commerce/play.jpg" alt="" width="140px">
            </div>
            <p>Paiement sécurisé</p>
            <img src="e-commerce/pay.jpg" alt="" width="190px">
        </div>
        <div class="copyright">
            <p>&copy;Deeven 2024 | Tous droits réservés</p>
        </div>
    </footer>
    
    <script src="MTA.js"></script>
</body>
</html>
