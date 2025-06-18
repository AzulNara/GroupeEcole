<?php
require_once 'config.php';
require_once 'auth.php'; // Inclure le système d'authentification

// Traitement de la recherche
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$genre = isset($_GET['genre']) ? trim($_GET['genre']) : '';

try {
    // Requête principale pour les livres (ajout de image_url)
    $sql = "SELECT l.*, 
                   GROUP_CONCAT(DISTINCT CONCAT(a.prenom, ' ', a.nom) SEPARATOR ', ') AS noms_auteurs,
                   g.intitule AS genre_nom 
            FROM livres l
            LEFT JOIN auteurs_livres al ON l.id_livre = al.id_livre
            LEFT JOIN auteurs a ON al.id_auteur = a.id_auteur
            LEFT JOIN genres g ON l.id_genre = g.id_genre";

    $conditions = [];
    $params = [];

    // Ajout des conditions de recherche
    if (!empty($search)) {
        $conditions[] = "(l.titre LIKE :search OR a.nom LIKE :search OR a.prenom LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }

    if (!empty($genre)) {
        $conditions[] = "g.intitule = :genre";
        $params[':genre'] = $genre;
    }

    // Finalisation de la requête
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }
    $sql .= " GROUP BY l.id_livre ORDER BY l.titre ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $livres = $stmt->fetchAll();
    
    // Récupération des genres pour le menu déroulant
    $genresStmt = $pdo->query("SELECT intitule FROM genres ORDER BY intitule");
    $genres = $genresStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Statistiques
    $statsStmt = $pdo->query("SELECT COUNT(*) as total_livres FROM livres");
    $totalLivres = $statsStmt->fetch()['total_livres'];
    
    $statsStmt = $pdo->query("SELECT COUNT(*) as total_auteurs FROM auteurs");
    $totalAuteurs = $statsStmt->fetch()['total_auteurs'];
    
} catch (PDOException $e) {
    $error = "Erreur lors de la recherche : " . $e->getMessage();
    $livres = [];
    $genres = [];
    $totalLivres = 0;
    $totalAuteurs = 0;
}

// Obtenir l'utilisateur connecté
$currentUser = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📚 E-Library</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        header { background: rgba(255, 255, 255, 0.95); border-radius: 20px; padding: 30px; text-align: center; margin-bottom: 30px; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1); position: relative; }
        .auth-buttons { 
            position: absolute; 
            top: 20px; 
            right: 20px; 
            display: flex; 
            gap: 10px; 
            align-items: center; 
            z-index: 10;
        }
        .auth-btn { 
            padding: 10px 16px; 
            text-decoration: none; 
            border-radius: 8px; 
            font-weight: 600; 
            font-size: 0.9rem; 
            transition: all 0.3s ease;
            cursor: pointer;
            display: inline-block;
            border: none;
            text-align: center;
        }
        .login-btn { 
            background: linear-gradient(45deg, #667eea, #764ba2); 
            color: white !important; 
        }
        .register-btn { 
            background: rgba(255, 255, 255, 0.9); 
            color: #333 !important; 
            border: 2px solid #667eea; 
        }
        .logout-btn { 
            background: linear-gradient(45deg, #dc3545, #c82333); 
            color: white !important; 
        }
        .auth-btn:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2); 
            text-decoration: none;
        }
        .user-welcome { 
            color: #667eea; 
            font-weight: 600; 
            margin-right: 10px; 
            font-size: 0.9rem; 
        }
        .logo { font-size: 4rem; margin-bottom: 10px; animation: bounce 2s infinite; }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        h1 { font-size: 2.5rem; background: linear-gradient(45deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin-bottom: 10px; }
        .subtitle { color: #666; font-size: 1.1rem; }
        .search-section { background: rgba(255, 255, 255, 0.95); border-radius: 20px; padding: 30px; margin-bottom: 30px; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1); }
        .search-form { display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; align-items: center; }
        .search-input, .genre-select { padding: 15px 20px; border: 2px solid #e0e0e0; border-radius: 15px; font-size: 1rem; transition: all 0.3s ease; background: white; }
        .search-input { flex: 1; min-width: 250px; }
        .search-input:focus, .genre-select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 20px rgba(102, 126, 234, 0.2); transform: translateY(-2px); }
        .search-btn { padding: 15px 30px; background: linear-gradient(45deg, #667eea, #764ba2); color: white; border: none; border-radius: 15px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
        .search-btn:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4); }
        .results-section { background: rgba(255, 255, 255, 0.95); border-radius: 20px; padding: 30px; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1); }
        .results-title { font-size: 1.8rem; margin-bottom: 25px; color: #333; display: flex; align-items: center; gap: 10px; }
        .books-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px; }
        .book-link { text-decoration: none; color: inherit; display: block; }
        .book-card { 
            background: white; 
            border-radius: 15px; 
            padding: 0; 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); 
            transition: all 0.3s ease; 
            border: 2px solid transparent; 
            position: relative; 
            overflow: hidden; 
            cursor: pointer;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .book-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(45deg, #667eea, #764ba2); z-index: 1; }
        .book-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15); border-color: #667eea; }
        
        .book-image { 
            width: 100%; 
            height: 250px; 
            object-fit: cover; 
            border-radius: 15px 15px 0 0;
            background: #f8f9fa;
        }
        
        .book-content { 
            padding: 20px; 
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .book-title { font-size: 1.3rem; font-weight: 700; color: #333; margin-bottom: 10px; line-height: 1.4; }
        .book-author { color: #667eea; font-weight: 600; margin-bottom: 8px; font-size: 1.1rem; }
        .book-genre { display: inline-block; background: linear-gradient(45deg, #667eea, #764ba2); color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.9rem; font-weight: 500; margin-bottom: 15px; }
        .book-price { font-size: 1.2rem; font-weight: 700; color: #28a745; margin-bottom: 10px; }
        .book-info { color: #666; font-size: 0.9rem; margin-bottom: 5px; }
        .no-results { text-align: center; padding: 60px 20px; color: #666; }
        .no-results-icon { font-size: 4rem; margin-bottom: 20px; opacity: 0.5; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #f5c6cb; }
        .stats { display: flex; justify-content: center; gap: 30px; margin-bottom: 20px; flex-wrap: wrap; }
        .stat-item { text-align: center; padding: 15px 25px; background: rgba(255, 255, 255, 0.2); border-radius: 15px; }
        .stat-number { font-size: 2rem; font-weight: 700; color: white; display: block; }
        .stat-label { color: rgba(255, 255, 255, 0.9); font-size: 0.9rem; }
        footer { text-align: center; padding: 30px; color: rgba(255, 255, 255, 0.8); margin-top: 40px; }
        @media (max-width: 768px) {
            .auth-buttons { 
                position: static; 
                justify-content: center; 
                margin-bottom: 20px; 
                flex-wrap: wrap;
            }
            .search-form { flex-direction: column; }
            .search-input { min-width: 100%; }
            .books-grid { grid-template-columns: 1fr; }
            .stats { flex-direction: column; gap: 15px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="auth-buttons">
                <?php if (isLoggedIn()): ?>
                    <span class="user-welcome">
                        👋 Bonjour, <?php echo htmlspecialchars($currentUser['prenom'] ?: $currentUser['username']); ?>
                    </span>
                    <a href="logout.php" class="auth-btn logout-btn">🚪 Déconnexion</a>
                <?php else: ?>
                    <a href="login.php" class="auth-btn login-btn">🔐 Se connecter</a>
                    <a href="register.php" class="auth-btn register-btn">📝 S'inscrire</a>
                <?php endif; ?>
            </div>
            
            <div class="logo">📚</div>
            <h1>E-Library</h1>
            <p class="subtitle">Découvrez votre prochaine lecture</p>
        </header>

        <div class="stats">
            <div class="stat-item">
                <span class="stat-number"><?php echo $totalLivres ?? 0; ?></span>
                <span class="stat-label">Livres total</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo $totalAuteurs ?? 0; ?></span>
                <span class="stat-label">Auteurs</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo count($livres); ?></span>
                <span class="stat-label">Résultats</span>
            </div>
        </div>

        <div class="search-section">
            <form class="search-form" method="GET">
                <input type="text" 
                       class="search-input" 
                       name="search" 
                       value="<?php echo htmlspecialchars($search); ?>"
                       placeholder="🔍 Rechercher un livre, un auteur...">
                
                <?php if (!empty($genres)): ?>
                <select class="genre-select" name="genre">
                    <option value="">Tous les genres</option>
                    <?php foreach ($genres as $g): ?>
                        <option value="<?php echo htmlspecialchars($g); ?>" 
                                <?php echo ($genre === $g) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($g); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
                
                <button type="submit" class="search-btn">
                    🔍 Rechercher
                </button>
            </form>
        </div>

        <div class="results-section">
            <h2 class="results-title">
                <span>📋</span>
                <span>
                    <?php 
                    if (!empty($search) || !empty($genre)) {
                        echo "Résultats de recherche (" . count($livres) . ")";
                    } else {
                        echo "Tous les livres (" . count($livres) . ")";
                    }
                    ?>
                </span>
            </h2>
            
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (empty($livres)): ?>
                <div class="no-results">
                    <div class="no-results-icon">📚</div>
                    <h3>Aucun résultat trouvé</h3>
                    <p>Essayez avec d'autres mots-clés ou changez le filtre de genre.</p>
                </div>
            <?php else: ?>
                <div class="books-grid">
                    <?php foreach ($livres as $livre): ?>
                        <a href="livre.php?id=<?php echo $livre['id_livre']; ?>" class="book-link">
                            <div class="book-card">
                                <?php 
                                $imageUrl = $livre['image_url'] ?? '/placeholder.svg?height=250&width=200';
                                ?>
                                <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                                     alt="Couverture de <?php echo htmlspecialchars($livre['titre'] ?? 'Livre'); ?>" 
                                     class="book-image"
                                     onerror="this.src='/placeholder.svg?height=250&width=200'">
                                
                                <div class="book-content">
                                    <h3 class="book-title">
                                        <?php echo htmlspecialchars($livre['titre'] ?? 'Titre non disponible'); ?>
                                    </h3>
                                    
                                    <p class="book-author">
                                        par <?php echo htmlspecialchars($livre['noms_auteurs'] ?? 'Auteur inconnu'); ?>
                                    </p>
                                    
                                    <?php if (isset($livre['genre_nom'])): ?>
                                        <div class="book-genre">
                                            <?php echo htmlspecialchars($livre['genre_nom']); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($livre['prix'])): ?>
                                        <div class="book-price">💰 Prix: <?php echo number_format($livre['prix'], 2); ?> €</div>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($livre['annee_publication'])): ?>
                                        <div class="book-info">📅 <?php echo $livre['annee_publication']; ?></div>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($livre['isbn'])): ?>
                                        <div class="book-info">📖 ISBN: <?php echo htmlspecialchars($livre['isbn']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 E-Library. Tous droits réservés. 📚✨</p>
    </footer>
</body>
</html>
