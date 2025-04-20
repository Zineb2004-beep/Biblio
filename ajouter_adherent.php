<?php
// Définir le chemin d'accès à la racine du projet
$root = __DIR__;

// Inclure le fichier de configuration de la base de données
require_once $root . '/config/database.php';

try {
    // Créer une nouvelle instance de la base de données
    $database = new Database();
    $db = $database->getConnection();

    // Les informations de l'adhérent à ajouter
    $nom = "John Doe"; // Changez ces valeurs selon vos besoins
    $email = "john.doe@example.com"; // Changez ces valeurs selon vos besoins
    $mot_de_passe = "1234"; // Changez ces valeurs selon vos besoins
    $role = "adherent";
    
    // Création du hash sécurisé pour le mot de passe
    $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

    // Requête d'insertion avec la date actuelle
    $query = "INSERT INTO utilisateurs (nom, email, mot_de_passe, role, date_creation) 
              VALUES (:nom, :email, :mot_de_passe, :role, NOW())";
    
    $stmt = $db->prepare($query);
    
    // Liaison des paramètres
    $stmt->bindParam(":nom", $nom);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":mot_de_passe", $hash);
    $stmt->bindParam(":role", $role);

    if($stmt->execute()) {
        echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin: 10px; border-radius: 5px;'>";
        echo "L'adhérent a été ajouté avec succès!<br>";
        echo "Nom: " . $nom . "<br>";
        echo "Email: " . $email . "<br>";
        echo "Rôle: " . $role . "<br>";
        echo "Mot de passe (non hashé): " . $mot_de_passe . "<br>";
        echo "Hash du mot de passe: " . $hash . "<br>";
        echo "<br>Vous pouvez maintenant vous connecter avec :<br>";
        echo "- Email: " . $email . "<br>";
        echo "- Mot de passe: " . $mot_de_passe;
        echo "</div>";
    } else {
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 10px; border-radius: 5px;'>";
        echo "Erreur lors de l'ajout de l'adhérent.";
        echo "</div>";
    }
} catch (PDOException $e) {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 10px; border-radius: 5px;'>";
    echo "Erreur de connexion : " . $e->getMessage();
    echo "</div>";
}
?> 