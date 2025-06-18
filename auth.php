<?php
require_once 'config.php';

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Vérifier si l'utilisateur est connecté
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Obtenir les informations de l'utilisateur connecté
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return $_SESSION['user_data'] ?? null;
}

/**
 * Connecter un utilisateur
 */
function loginUser($username, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND actif = 1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Mettre à jour la dernière connexion
            $updateStmt = $pdo->prepare("UPDATE users SET derniere_connexion = NOW() WHERE id_user = ?");
            $updateStmt->execute([$user['id_user']]);
            
            // Créer la session
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['user_data'] = [
                'id' => $user['id_user'],
                'username' => $user['username'],
                'email' => $user['email'],
                'nom' => $user['nom'],
                'prenom' => $user['prenom']
            ];
            
            return true;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("Erreur de connexion: " . $e->getMessage());
        return false;
    }
}

/**
 * Inscrire un nouvel utilisateur
 */
function registerUser($username, $email, $password, $nom = '', $prenom = '') {
    global $pdo;
    
    try {
        // Vérifier si l'utilisateur existe déjà
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $checkStmt->execute([$username, $email]);
        
        if ($checkStmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Nom d\'utilisateur ou email déjà utilisé'];
        }
        
        // Hasher le mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insérer le nouvel utilisateur
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, nom, prenom) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $hashedPassword, $nom, $prenom]);
        
        return ['success' => true, 'message' => 'Compte créé avec succès'];
        
    } catch (PDOException $e) {
        error_log("Erreur d'inscription: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de la création du compte'];
    }
}

/**
 * Déconnecter l'utilisateur
 */
function logoutUser() {
    session_destroy();
    header('Location: index.php');
    exit;
}

/**
 * Rediriger vers la page de connexion si non connecté
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}
?>
