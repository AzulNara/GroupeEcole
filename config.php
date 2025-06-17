<?php
// Configuration de la base de données
$host = '10.96.16.82';
$db = 'librairie';
$user = 'colin';
$pass = '';
$port = '3306';
$charset = 'utf8mb4';

// Construction du DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset;port=$port";

// Options PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Création de la connexion PDO
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>