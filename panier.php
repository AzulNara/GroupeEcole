<?php
session_start();
require_once 'config.php';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' && isset($_POST['id_livre'])) {
            // Ajout au panier (existant)
            $id_livre = (int)$_POST['id_livre'];
            $stmt = $pdo->prepare("SELECT * FROM livres WHERE id_livre = ?");
            $stmt->execute([$id_livre]);
            $livre = $stmt->fetch();
            
            if ($livre) {
                if (!isset($_SESSION['panier'])) {
                    $_SESSION['panier'] = [];
                }
                
                if (isset($_SESSION['panier'][$id_livre])) {
                    $_SESSION['panier'][$id_livre]['quantite']++;
                } else {
                    $_SESSION['panier'][$id_livre] = [
                        'titre' => $livre['titre'],
                        'prix' => $livre['prix'],
                        'quantite' => 1
                    ];
                }
                
                $_SESSION['message'] = "Le livre a été ajouté au panier";
            }
        } elseif ($_POST['action'] === 'remove' && isset($_POST['id_livre'])) {
            // Suppression du panier
            $id_livre = (int)$_POST['id_livre'];
            if (isset($_SESSION['panier'][$id_livre])) {
                unset($_SESSION['panier'][$id_livre]);
                $_SESSION['message'] = "Le livre a été retiré du panier";
                
                // Si le panier est vide, le supprimer complètement
                if (empty($_SESSION['panier'])) {
                    unset($_SESSION['panier']);
                }
            }
        }
    }
}

// Récupérer les livres du panier avec leurs infos complètes
$livres_panier = [];
$total = 0;

if (isset($_SESSION['panier']) && !empty($_SESSION['panier'])) {
    foreach ($_SESSION['panier'] as $id_livre => $item) {
        $stmt = $pdo->prepare("SELECT l.*, 
                                      GROUP_CONCAT(DISTINCT CONCAT(a.prenom, ' ', a.nom) SEPARATOR ', ') AS noms_auteurs
                               FROM livres l
                               LEFT JOIN auteurs_livres al ON l.id_livre = al.id_livre
                               LEFT JOIN auteurs a ON al.id_auteur = a.id_auteur
                               WHERE l.id_livre = ?
                               GROUP BY l.id_livre");
        $stmt->execute([$id_livre]);
        $livre = $stmt->fetch();
        
        if ($livre) {
            $livre['quantite'] = $item['quantite'];
            $livre['sous_total'] = $livre['prix'] * $item['quantite'];
            $livres_panier[] = $livre;
            $total += $livre['sous_total'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Panier - E-Library</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; position: relative; }
        .panier-container { background: white; border-radius: 20px; padding: 40px; margin: 30px auto; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .back-button { display: inline-block; margin-bottom: 20px; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .total { font-size: 1.2rem; font-weight: bold; text-align: right; margin-top: 20px; }
        .checkout-btn { display: inline-block; padding: 12px 25px; background: #4CAF50; color: white; text-decoration: none; border-radius: 10px; margin-top: 20px; float: right; }
        .remove-btn { color: #e74c3c; text-decoration: none; font-weight: bold; }
        .message { padding: 10px; background: #4CAF50; color: white; border-radius: 5px; margin-bottom: 20px; }
        
        /* Style pour le bouton panier dans le header */
        .header-buttons {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
        }
        .cart-button {
            padding: 8px 15px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .cart-count {
            background: white;
            color: #4CAF50;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Bouton panier dans le header -->
        <div class="header-buttons">
            <a href="panier.php" class="cart-button">
                🛒 Panier
                <?php if (!empty($_SESSION['panier'])): ?>
                    <span class="cart-count"><?= count($_SESSION['panier']) ?></span>
                <?php endif; ?>
            </a>
        </div>
        
        <a href="index.php" class="back-button">← Retour à la liste</a>
        
        <div class="panier-container">
            <h1>Votre Panier</h1>
            
            <?php if (isset($_SESSION['message'])): ?>
                <div class="message"><?= $_SESSION['message'] ?></div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>
            
            <?php if (!empty($livres_panier)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Livre</th>
                            <th>Auteur(s)</th>
                            <th>Prix unitaire</th>
                            <th>Quantité</th>
                            <th>Sous-total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($livres_panier as $livre): ?>
                        <tr>
                            <td><?= htmlspecialchars($livre['titre']) ?></td>
                            <td><?= htmlspecialchars($livre['noms_auteurs']) ?></td>
                            <td><?= number_format($livre['prix'], 2) ?> €</td>
                            <td><?= $livre['quantite'] ?></td>
                            <td><?= number_format($livre['sous_total'], 2) ?> €</td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="id_livre" value="<?= $livre['id_livre'] ?>">
                                    <button type="submit" class="remove-btn" style="background:none; border:none; cursor:pointer;">✖ Retirer</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="total">
                    Total: <?= number_format($total, 2) ?> €
                </div>
                
                <a href="paiement.php" class="checkout-btn">Passer au paiement</a>
                <div style="clear: both;"></div>
            <?php else: ?>
                <p>Votre panier est vide.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>