<?php
session_start();
require_once 'config.php';

// Vérifier si le panier existe et n'est pas vide
if (empty($_SESSION['panier'])) {
    header('Location: panier.php');
    exit;
}

// Calculer le total
$total = 0;
foreach ($_SESSION['panier'] as $item) {
    $total += $item['prix'] * $item['quantite'];
}

// Traitement du faux paiement
$paiement_effectue = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payer'])) {
    // Enregistrer le faux paiement
    $paiement_effectue = true;
    
    // Vider le panier après paiement
    unset($_SESSION['panier']);
    
    // Rediriger vers la confirmation après 3 secondes
    header("Refresh:3; url=confirmation.php");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement - E-Library</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }
        .payment-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 28px;
        }
        .total-amount {
            font-size: 24px;
            font-weight: bold;
            color: #27ae60;
            margin: 20px 0;
        }
        .payment-form {
            margin-top: 30px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
        input[type="text"]:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        .card-icons {
            display: flex;
            gap: 10px;
            margin: 15px 0;
            justify-content: center;
        }
        .card-icons img {
            height: 30px;
            opacity: 0.7;
        }
        .pay-btn {
            background: linear-gradient(45deg, #27ae60, #2ecc71);
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 18px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-weight: 600;
            transition: all 0.3s;
            margin-top: 20px;
        }
        .pay-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.4);
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="payment-card">
            <h1>Paiement sécurisé</h1>
            
            <?php if ($paiement_effectue): ?>
                <div class="success-message">
                    <p>Paiement effectué avec succès !</p>
                    <p>Vous allez être redirigé vers la page de confirmation...</p>
                </div>
            <?php else: ?>
                <p>Veuillez entrer vos informations de paiement</p>
                
                <div class="total-amount">
                    Total à payer : <?php echo number_format($total, 2); ?> €
                </div>
                
                <div class="card-icons">
                    <img src="https://cdn-icons-png.flaticon.com/512/196/196578.png" alt="Visa">
                    <img src="https://cdn-icons-png.flaticon.com/512/196/196561.png" alt="Mastercard">
                    <img src="https://cdn-icons-png.flaticon.com/512/196/196566.png" alt="American Express">
                </div>
                
                <form class="payment-form" method="POST">
                    <div class="form-group">
                        <label for="card-number">Numéro de carte</label>
                        <input type="text" id="card-number" placeholder="1234 5678 9012 3456" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="card-name">Nom sur la carte</label>
                        <input type="text" id="card-name" placeholder="Jean DUPONT" required>
                    </div>
                    
                    <div style="display: flex; gap: 15px;">
                        <div class="form-group" style="flex: 1;">
                            <label for="expiry-date">Date d'expiration</label>
                            <input type="text" id="expiry-date" placeholder="MM/AA" required>
                        </div>
                        
                        <div class="form-group" style="flex: 1;">
                            <label for="cvv">CVV</label>
                            <input type="text" id="cvv" placeholder="123" required>
                        </div>
                    </div>
                    
                    <button type="submit" name="payer" class="pay-btn">Payer maintenant</button>
                </form>
                
                <a href="panier.php" class="back-link">← Retour au panier</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>