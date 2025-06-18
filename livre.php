<?php
require_once 'config.php';

// Vérifier si l'ID est présent
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id_livre = (int)$_GET['id'];

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
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
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
        
        @media (max-width: 768px) {
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
