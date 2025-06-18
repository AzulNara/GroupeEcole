<?php
session_start(); // Ajouter pour gérer le panier
require_once 'config.php';

// Vérifier si l'ID est présent
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id_livre = (int)$_GET['id'];

// Traitement de l'ajout au panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    if (!isset($_SESSION['panier'])) {
        $_SESSION['panier'] = [];
    }
    
    if (isset($_SESSION['panier'][$id_livre])) {
        $_SESSION['panier'][$id_livre]['quantite']++;
    } else {
        $_SESSION['panier'][$id_livre] = [
            'titre' => $_POST['titre'],
            'prix' => $_POST['prix'],
            'quantite' => 1
        ];
    }
    
    $_SESSION['message'] = "Le livre a été ajouté au panier !";
}

try {
    // Requête pour obtenir les détails du livre (avec image)
    $sql = "SELECT l.*, 
                   GROUP_CONCAT(DISTINCT CONCAT(a.prenom, ' ', a.nom) SEPARATOR ', ') AS noms_auteurs,
                   g.intitule AS genre_nom 
            FROM livres l
            LEFT JOIN auteurs_livres al ON l.id_livre = al.id_livre
            LEFT JOIN auteurs a ON al.id_auteur = a.id_auteur
            LEFT JOIN genres g ON l.id_genre = g.id_genre
            WHERE l.id_livre = ?
            GROUP BY l.id_livre";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_livre]);
    $livre = $stmt->fetch();

    if (!$livre) {
        header('Location: index.php');
        exit;
    }

} catch (PDOException $e) {
    die("Erreur: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($livre['titre']); ?> - E-Library</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; position: relative; }
        
        /* Bouton panier dans le header */
        .header-buttons {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 10;
        }
        .cart-button {
            padding: 10px 16px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .cart-button:hover {
            background: #45a049;
            transform: translateY(-2px);
        }
        .cart-count {
            background: white;
            color: #4CAF50;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }
        
        .book-detail { 
            background: white; 
            border-radius: 20px; 
            padding: 40px; 
            margin: 30px auto; 
            max-width: 900px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 40px;
            align-items: start;
        }
        .back-button { 
            display: inline-block; 
            margin-bottom: 20px; 
            padding: 10px 20px; 
            background: #667eea; 
            color: white; 
            text-decoration: none; 
            border-radius: 10px; 
            transition: all 0.3s ease; 
        }
        .back-button:hover { background: #764ba2; }
        
        .book-image-large {
            width: 100%;
            max-width: 300px;
            height: auto;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            object-fit: cover;
        }
        
        .book-info-section {
            display: flex;
            flex-direction: column;
        }
        
        .book-title { 
            font-size: 2.2rem; 
            margin-bottom: 15px; 
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.3;
        }
        .book-meta { 
            margin-bottom: 30px; 
        }
        .book-meta p {
            margin-bottom: 12px;
            font-size: 1.1rem;
        }
        .book-meta strong {
            color: #667eea;
            font-weight: 600;
        }
        .book-description { 
            margin-top: 30px; 
            line-height: 1.6; 
            font-size: 1.1rem;
        }
        .book-description h3 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 1.4rem;
        }
        .book-genre-badge {
            display: inline-block;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            margin: 10px 0;
        }
        .book-price-large {
            font-size: 1.5rem;
            font-weight: 700;
            color: #28a745;
            margin: 15px 0;
        }
        
        /* Styles pour le bouton ajouter au panier */
        .add-to-cart-section {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 15px;
            border: 2px solid #e9ecef;
        }
        
        .add-to-cart-btn {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .add-to-cart-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.4);
        }
        
        .add-to-cart-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        @media (max-width: 768px) {
            .header-buttons {
                position: static;
                justify-content: center;
                margin-bottom: 20px;
            }
            .book-detail {
                grid-template-columns: 1fr;
                gap: 20px;
                padding: 20px;
            }
            .book-image-large {
                max-width: 250px;
                margin: 0 auto;
            }
            .book-title {
                font-size: 1.8rem;
            }
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
        
        <div class="book-detail">
            <div class="book-image-container">
                <?php 
                $imageUrl = $livre['image_url'] ?? '/placeholder.svg?height=400&width=300';
                ?>
                <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                     alt="Couverture de <?php echo htmlspecialchars($livre['titre']); ?>" 
                     class="book-image-large"
                     onerror="this.src='/placeholder.svg?height=400&width=300'">
            </div>
            
            <div class="book-info-section">
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="success-message">
                        ✅ <?php echo htmlspecialchars($_SESSION['message']); ?>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>
                
                <h1 class="book-title"><?php echo htmlspecialchars($livre['titre']); ?></h1>
                
                <div class="book-meta">
                    <p><strong>Auteur(s):</strong> <?php echo htmlspecialchars($livre['noms_auteurs']); ?></p>
                    
                    <?php if (isset($livre['genre_nom'])): ?>
                        <div class="book-genre-badge">
                            <?php echo htmlspecialchars($livre['genre_nom']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($livre['prix'])): ?>
                        <div class="book-price-large">💰 Prix: <?php echo number_format($livre['prix'], 2); ?> €</div>
                    <?php endif; ?>
                    
                    <?php if (isset($livre['annee_publication'])): ?>
                        <p><strong>Année de publication:</strong> <?php echo $livre['annee_publication']; ?></p>
                    <?php endif; ?>
                    
                    <?php if (isset($livre['isbn'])): ?>
                        <p><strong>ISBN:</strong> <?php echo htmlspecialchars($livre['isbn']); ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Section Ajouter au panier -->
                <?php if (isset($livre['prix']) && $livre['prix'] > 0): ?>
                <div class="add-to-cart-section">
                    <form method="POST">
                        <input type="hidden" name="action" value="add_to_cart">
                        <input type="hidden" name="titre" value="<?php echo htmlspecialchars($livre['titre']); ?>">
                        <input type="hidden" name="prix" value="<?php echo $livre['prix']; ?>">
                        
                        <button type="submit" class="add-to-cart-btn">
                            🛒 Ajouter au panier - <?php echo number_format($livre['prix'], 2); ?> €
                        </button>
                    </form>
                </div>
                <?php endif; ?>
                
                <?php if (isset($livre['description']) && !empty($livre['description'])): ?>
                    <div class="book-description">
                        <h3>Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($livre['description'])); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
