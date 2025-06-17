<?php
session_start();
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
    <!-- Stripe JS -->
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .book-detail { background: white; border-radius: 20px; padding: 40px; margin: 30px auto; max-width: 800px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .back-button { display: inline-block; margin-bottom: 20px; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 10px; transition: all 0.3s ease; }
        .back-button:hover { background: #764ba2; }
        .book-title { font-size: 2rem; margin-bottom: 15px; }
        .book-meta { margin-bottom: 20px; }
        .book-description { margin-top: 30px; line-height: 1.6; }
        
        /* Styles pour les boutons d'action */
        .action-buttons {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .add-to-cart-btn, .buy-now-btn, .stripe-btn {
            padding: 12px 25px;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .add-to-cart-btn {
            background: linear-gradient(45deg, #667eea, #764ba2);
        }
        .buy-now-btn {
            background: linear-gradient(45deg, #4CAF50, #2E7D32);
        }
        .stripe-btn {
            background: #635bff;
            width: 100%;
            margin-top: 10px;
        }
        .add-to-cart-btn:hover, .buy-now-btn:hover, .stripe-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        /* Section Stripe */
        .stripe-section {
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        #payment-form {
            margin-top: 20px;
        }
        #payment-element {
            margin-bottom: 20px;
        }
        .hidden {
            display: none;
        }
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
            
            <!-- Section Boutons d'action -->
            <div class="action-buttons">
                <!-- Bouton Ajouter au panier -->
                <form action="panier.php" method="post">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id_livre" value="<?php echo $livre['id_livre']; ?>">
                    <button type="submit" class="add-to-cart-btn">🛒 Ajouter au panier</button>
                </form>
                
                <!-- Bouton Acheter maintenant -->
                <?php if (isset($livre['prix']) && $livre['prix'] > 0): ?>
                <form action="paiement.php" method="post">
                    <input type="hidden" name="direct_purchase" value="1">
                    <input type="hidden" name="id_livre" value="<?php echo $livre['id_livre']; ?>">
                    <input type="hidden" name="prix" value="<?php echo $livre['prix']; ?>">
                    <input type="hidden" name="titre" value="<?php echo htmlspecialchars($livre['titre']); ?>">
                    <button type="submit" class="buy-now-btn">💰 Acheter maintenant</button>
                </form>
                <?php endif; ?>
            </div>
            
            <!-- Section Paiement Stripe (optionnelle, peut être activée) -->
            <?php if (isset($livre['prix']) && $livre['prix'] > 0 && false): // Mettez "false" à "true" pour activer ?>
            <div class="stripe-section">
                <h3>Paiement sécurisé</h3>
                <form id="payment-form">
                    <div id="payment-element"></div>
                    <button id="submit" class="stripe-btn">
                        <span id="button-text">Payer <?php echo number_format($livre['prix'], 2); ?> € avec Stripe</span>
                        <span id="spinner" class="hidden">Chargement...</span>
                    </button>
                    <div id="payment-message" class="hidden"></div>
                </form>
            </div>

            <script>
                const stripe = Stripe('<?php echo STRIPE_PUBLISHABLE_KEY; ?>');
                let elements;
                
                initialize();
                
                document.querySelector("#payment-form").addEventListener("submit", handleSubmit);
                
                async function initialize() {
                    const response = await fetch("create-checkout-session.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({
                            id_livre: <?php echo $livre['id_livre']; ?>,
                            prix: <?php echo $livre['prix'] * 100; ?>,
                            titre: "<?php echo addslashes($livre['titre']); ?>",
                            currency: "eur"
                        }),
                    });
                    
                    const { clientSecret } = await response.json();
                    
                    elements = stripe.elements({ clientSecret });
                    const paymentElement = elements.create("payment");
                    paymentElement.mount("#payment-element");
                }
                
                async function handleSubmit(e) {
                    e.preventDefault();
                    setLoading(true);
                    
                    const { error } = await stripe.confirmPayment({
                        elements,
                        confirmParams: {
                            return_url: "http://votresite.com/confirmation.php",
                        },
                    });
                    
                    if (error) {
                        document.getElementById("payment-message").textContent = error.message;
                    }
                    
                    setLoading(false);
                }
                
                function setLoading(isLoading) {
                    const submitButton = document.querySelector("#submit");
                    submitButton.disabled = isLoading;
                    document.querySelector("#button-text").style.display = 
                        isLoading ? "none" : "inline";
                    document.querySelector("#spinner").style.display = 
                        isLoading ? "inline" : "none";
                }
            </script>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>