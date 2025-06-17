<?php
function afficherLivre($livre) {
    return "<li><strong>" . htmlspecialchars($livre['titre']) . "</strong> (" . htmlspecialchars($livre['genre']) . ")</li>";
}

function filtrerInput($str) {
    return htmlspecialchars(trim($str));
}
?>
