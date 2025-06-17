<?php
require_once 'auth.php';

// Si déjà connecté, rediriger vers l'accueil
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    
    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs obligatoires';
    } elseif ($password !== $confirm_password) {
        $error = 'Les mots de passe ne correspondent pas';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide';
    } else {
        $result = registerUser($username, $email, $password, $nom, $prenom);
        if ($result['success']) {
            $success = $result['message'] . ' Vous pouvez maintenant vous connecter.';
        } else {
            $error = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - E-Library</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        header { background: rgba(255, 255, 255, 0.95); border-radius: 20px; padding: 30px; text-align: center; margin-bottom: 30px; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1); }
        .logo { font-size: 4rem; margin-bottom: 10px; animation: bounce 2s infinite; }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        h1 { font-size: 2.5rem; background: linear-gradient(45deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin-bottom: 10px; }
        .subtitle { color: #666; font-size: 1.1rem; }
        .auth-form { background: rgba(255, 255, 255, 0.95); border-radius: 20px; padding: 40px; max-width: 500px; margin: 0 auto; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        .form-group input { width: 100%; padding: 15px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 1rem; transition: all 0.3s ease; }
        .form-group input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 20px rgba(102, 126, 234, 0.2); }
        .btn { width: 100%; padding: 15px; background: linear-gradient(45deg, #667eea, #764ba2); color: white; border: none; border-radius: 10px; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; margin-bottom: 15px; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4); }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #f5c6cb; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #c3e6cb; }
        .auth-links { text-align: center; margin-top: 20px; }
        .auth-links a { color: #667eea; text-decoration: none; font-weight: 600; }
        .auth-links a:hover { text-decoration: underline; }
        .back-link { display: inline-block; margin-bottom: 20px; padding: 10px 20px; background: rgba(255, 255, 255, 0.2); color: white; text-decoration: none; border-radius: 10px; transition: all 0.3s ease; }
        .back-link:hover { background: rgba(255, 255, 255, 0.3); }
        .form-row { display: flex; gap: 15px; }
        .form-row .form-group { flex: 1; }
        footer { text-align: center; padding: 30px; color: rgba(255, 255, 255, 0.8); margin-top: 40px; }
        .required { color: #e74c3c; }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">← Retour à l'accueil</a>
        
        <header>
            <div class="logo">📚</div>
            <h1>E-Library</h1>
            <p class="subtitle">Créer votre compte</p>
        </header>

        <div class="auth-form">
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur <span class="required">*</span></label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Adresse email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" 
                               value="<?php echo htmlspecialchars($_POST['prenom'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" 
                               value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe <span class="required">*</span></label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe <span class="required">*</span></label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                </div>

                <button type="submit" class="btn">📝 Créer mon compte</button>
            </form>

            <div class="auth-links">
                <p>Déjà un compte ? <a href="login.php">Se connecter</a></p>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 E-Library. Tous droits réservés. 📚✨</p>
    </footer>
</body>
</html>
