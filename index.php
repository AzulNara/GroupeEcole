<?php
require_once 'config.php';
require_once 'functions.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>E-Library</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<?php include 'templates/header.php'; ?>

<!-- Barre de recherche -->
<div class="search-bar">
    <form method="GET">
        <input type="text" name="q" placeholder="Rechercher un livre, un auteur...">
        <button type="submit">🔍</button>
    </form>
</div>

<!-- Filtres -->
<div class="filters">
    <form method="GET">
        <select name="genre">
            <option value="">Tous les genres</option>
            <?php
            $genres = $pdo->query("SELECT * FROM genres")->fetchAll();
            foreach ($genres as $genre) {
                echo "<option value='{$genre['id']}'>{$genre['nom']}</option>";
            }
            ?>
        </select>
        <button type="submit">Filtrer</button>
    </form>
</div>

<!-- Résultats -->
<div class="results">
    <h2>Résultats :</h2>
    <div class="books">
        <?php
        $query = "SELECT livres.*, auteurs.nom AS auteur 
                  FROM livres
                  JOIN auteurs_livres ON livres.id = auteurs_livres.id_livre
                  JOIN auteurs ON auteurs.id = auteurs_livres.id_auteur";

        if (!empty($_GET['q'])) {
            $q = '%' . $_GET['q'] . '%';
            $query .= " WHERE livres.titre LIKE ? OR auteurs.nom LIKE ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$q, $q]);
        } else {
            $stmt = $pdo->query($query);
        }

        while ($livre = $stmt->fetch()) {
            include 'templates/book_card.php';
        }
        ?>
    </div>
</div>

<?php include 'templates/footer.php'; ?>

</body>
</html>
