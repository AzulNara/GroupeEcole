<?php
require_once 'config.php';

// Vérifier si l'ID est présent
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id_livre = (int)$_GET['id'];

try {
    // Requête pour obtenir les détails du livre
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
        /* Reprenez le même style que index.php */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .book-detail { background: white; border-radius: 20px; padding: 40px; margin: 30px auto; max-width: 800px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .back-button { display: inline-block; margin-bottom: 20px; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 10px; transition: all 0.3s ease; }
        .back-button:hover { background: #764ba2; }
        .book-title { font-size: 2rem; margin-bottom: 15px; }
        .book-meta { margin-bottom: 20px; }
        .book-description { margin-top: 30px; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-button">← Retour à la liste</a>
        
        <div class="book-detail">
            <h1 class="book-title"><?php echo htmlspecialchars($livre['titre']); ?></h1>
            
            <div class="book-meta">
                <p><strong>Auteur(s):</strong> <?php echo htmlspecialchars($livre['noms_auteurs']); ?></p>
                <?php if (isset($livre['genre_nom'])): ?>
                    <p><strong>Genre:</strong> <?php echo htmlspecialchars($livre['genre_nom']); ?></p>
                <?php endif; ?>
                <?php if (isset($livre['annee_publication'])): ?>
                    <p><strong>Année:</strong> <?php echo $livre['annee_publication']; ?></p>
                <?php endif; ?>
                <?php if (isset($livre['isbn'])): ?>
                    <p><strong>ISBN:</strong> <?php echo htmlspecialchars($livre['isbn']); ?></p>
                <?php endif; ?>
                <?php if (isset($livre['prix'])): ?>
                    <p><strong>Prix:</strong> <?php echo number_format($livre['prix'], 2); ?> €</p>
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
</body>
</html>
