<?php
// Inclure le système d'authentification
require_once 'auth.php';
$currentUser = getCurrentUser();
?>

<header>
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div style="text-align: center; flex: 1;">
            <div class="logo">📚</div>
            <h1>E-Library</h1>
            <p class="subtitle">Découvrez votre prochaine lecture</p>
        </div>
        
        <div style="display: flex; gap: 10px; align-items: center;">
            <?php if (isLoggedIn()): ?>
                <div style="text-align: right; margin-right: 15px;">
                    <span style="color: #667eea; font-weight: 600;">
                        👋 Bonjour, <?php echo htmlspecialchars($currentUser['prenom'] ?: $currentUser['username']); ?>
                    </span>
                </div>
                <a href="logout.php" 
                   style="padding: 10px 20px; background: linear-gradient(45deg, #dc3545, #c82333); color: white; text-decoration: none; border-radius: 10px; font-weight: 600; transition: all 0.3s ease;"
                   onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(220, 53, 69, 0.4)'"
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    🚪 Déconnexion
                </a>
            <?php else: ?>
                <a href="login.php" 
                   style="padding: 10px 20px; background: linear-gradient(45deg, #667eea, #764ba2); color: white; text-decoration: none; border-radius: 10px; font-weight: 600; transition: all 0.3s ease; margin-right: 10px;"
                   onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(102, 126, 234, 0.4)'"
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                    🔐 Se connecter
                </a>
                <a href="register.php" 
                   style="padding: 10px 20px; background: rgba(255, 255, 255, 0.2); color: #333; text-decoration: none; border-radius: 10px; font-weight: 600; border: 2px solid #667eea; transition: all 0.3s ease;"
                   onmouseover="this.style.background='#667eea'; this.style.color='white'; this.style.transform='translateY(-2px)'"
                   onmouseout="this.style.background='rgba(255, 255, 255, 0.2)'; this.style.color='#333'; this.style.transform='translateY(0)'">
                    📝 S'inscrire
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>
