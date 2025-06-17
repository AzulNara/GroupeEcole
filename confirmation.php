<?php
session_start();
require_once 'config.php';
require_once 'vendor/autoload.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Vérifier la session Stripe
$payment_intent_id = $_GET['payment_intent'] ?? null;

try {
    if ($payment_intent_id) {
        $payment_intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
        
        if ($payment_intent->status === 'succeeded') {
            // Enregistrer la commande en base de données
            // ...
            
            // Vider le panier
            unset($_SESSION['panier']);
            
            $message = "Paiement réussi! Merci pour votre achat.";
        } else {
            $message = "Votre paiement n'a pas abouti.";
        }
    } else {
        $message = "Aucune information de paiement.";
    }
} catch (Exception $e) {
    $message = "Erreur lors de la vérification du paiement: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation - E-Library</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .confirmation-container { background: white; border-radius: 20px; padding: 40px; margin: 30px auto; max-width: 600px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; }
        .home-button { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 10px; }
        .success { color: #4CAF50; font-size: 1.2rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="confirmation-container">
            <h1>Confirmation de commande</h1>
            
            <p class="success"><?= htmlspecialchars($message) ?></p>
            
            <?php if ($payment_intent_id): ?>
            <p>Référence de paiement: <?= htmlspecialchars($payment_intent_id) ?></p>
            <?php endif; ?>
            
            <a href="index.php" class="home-button">Retour à l'accueil</a>
        </div>
    </div>
</body>
</html>