<div class="book">
    <img src="assets/images/<?php echo $livre['image']; ?>" alt="couverture">
    <div class="info">
        <h3><?php echo htmlspecialchars($livre['titre']); ?></h3>
        <p>Auteur : <?php echo htmlspecialchars($livre['auteur']); ?></p>
        <p>Prix : <?php echo number_format($livre['prix'], 2); ?> €</p>
    </div>
</div>
