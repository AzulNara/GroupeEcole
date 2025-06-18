<?php
session_start();
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation - E-Library</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            text-align: center;
        }
        .confirmation-box {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 90%;
        }
        h1 {
            color: #27ae60;
            margin-bottom: 20px;
        }
        p {
            margin-bottom: 30px;
            font-size: 18px;
        }
        .icon {
            font-size: 60px;
            margin-bottom: 20px;
            color: #27ae60;
        }
        .home-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        .home-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="confirmation-box">
        <div class="icon">✓</div>
        <h1>Paiement confirmé !</h1>
        <p>Merci pour votre achat. Votre commande a été enregistrée avec succès.</p>
        <p>Un email de confirmation vous a été envoyé.</p>
        <a href="index.php" class="home-btn">Retour à l'accueil</a>
    </div>
</body>
</html>