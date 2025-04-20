<?php
// Le mot de passe que vous voulez hasher
$mot_de_passe = "votre_mot_de_passe";

// Création du hash
$hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

// Affichage du hash
echo "Mot de passe original : " . $mot_de_passe . "\n";
echo "Hash du mot de passe : " . $hash . "\n";
?> 