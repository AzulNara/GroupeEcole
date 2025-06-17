<?php
session_start();
require_once 'config.php';

// Vérifier si le panier n'est pas vide
if (empty($_SESSION['panier'])) {
    header('Location: panier.php');
    exit;
}

// Calculer le total
$total = 0;
foreach ($_SESSION['panier'] as $item) {
    $total += $item['prix'] * $item['quantite'];
}

// Si on vient directement de la page livre (acheter maintenant)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_livre'])) {
    $id_livre = (int)$_POST['id_livre'];
    
    // Vider le panier et ajouter seulement ce livre
    $_SESSION['panier'] = [
        $id_livre => [
            'titre' => $_POST['titre'],
            'prix' => (float)$_POST['prix'],
            'quantite' => 1
        ]
    ];
    $total = (float)$_POST['prix'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement - E-Library</title>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .payment-container { background: white; border-radius: 20px; padding: 40px; margin: 30px auto; max-width: 600px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .back-button { display: inline-block; margin-bottom: 20px; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 10px; }
        .total { font-size: 1.2rem; font-weight: bold; text-align: center; margin: 20px 0; }
        #payment-form { margin-top: 20px; }
        .stripe-btn { width: 100%; padding: 12px; background: #635bff; color: white; border: none; border-radius: 10px; font-size: 1rem; cursor: pointer; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="panier.php" class="back-button">← Retour au panier</a>
        
        <div class="payment-container">
            <h1>Paiement</h1>
            
            <div class="total">
                Total à payer: <?= number_format($total, 2) ?> €
            </div>
            
            <form id="payment-form">
                <div id="payment-element"></div>
                <button id="submit" class="stripe-btn">
                    <span id="button-text">Payer maintenant</span>
                    <span id="spinner" style="display:none;">Chargement...</span>
                </button>
                <div id="payment-message" class="hidden"></div>
            </form>
        </div>
    </div>

    <script>
        const stripe = Stripe('<?php echo STRIPE_PUBLISHABLE_KEY; ?>');
        
        let elements;
        
        initialize();
        checkStatus();
        
        document.querySelector("#payment-form").addEventListener("submit", handleSubmit);
        
        async function initialize() {
            const response = await fetch("create-payment-intent.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    amount: <?= $total * 100 ?>, // Stripe utilise les centimes
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
        
        async function checkStatus() {
            const clientSecret = new URLSearchParams(window.location.search).get(
                "payment_intent_client_secret"
            );
            
            if (!clientSecret) {
                return;
            }
            
            const { paymentIntent } = await stripe.retrievePaymentIntent(clientSecret);
            
            switch (paymentIntent.status) {
                case "succeeded":
                    showMessage("Paiement réussi!");
                    break;
                case "processing":
                    showMessage("Votre paiement est en cours.");
                    break;
                case "requires_payment_method":
                    showMessage("Votre paiement a échoué, veuillez réessayer.");
                    break;
                default:
                    showMessage("Une erreur est survenue.");
                    break;
            }
        }
        
        function showMessage(messageText) {
            const messageContainer = document.querySelector("#payment-message");
            messageContainer.textContent = messageText;
            messageContainer.style.display = "block";
        }
        
        function setLoading(isLoading) {
            const submitButton = document.querySelector("#submit");
            submitButton.disabled = isLoading;
            document.querySelector("#button-text").style.display = isLoading ? "none" : "inline";
            document.querySelector("#spinner").style.display = isLoading ? "inline" : "none";
        }
    </script>
</body>
</html>