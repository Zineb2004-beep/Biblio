<?php
require_once 'config/database.php';
require_once 'models/Emprunt.php';
require_once 'auth/auth.php';

$auth = new Auth();

// Vérifier si l'utilisateur est connecté
if (!$auth->isLoggedIn()) {
    header('Location: login.php?error=not_logged_in');
    exit;
}

// Vérifier si l'ID de l'emprunt est fourni
if (!isset($_POST['emprunt_id']) || empty($_POST['emprunt_id'])) {
    header('Location: mes-emprunts.php?error=missing_id');
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $emprunt = new Emprunt($db);
    
    // Définir l'ID de l'emprunt
    $emprunt->id = $_POST['emprunt_id'];
    
    // Vérifier que l'emprunt existe et appartient à l'utilisateur connecté
    $stmt = $db->prepare("SELECT utilisateur_id FROM emprunts WHERE id = ? AND statut = 'en_cours'");
    $stmt->execute([$emprunt->id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        header('Location: mes-emprunts.php?error=not_found');
        exit;
    }
    
    if ($result['utilisateur_id'] != $_SESSION['user_id']) {
        header('Location: mes-emprunts.php?error=unauthorized');
        exit;
    }
    
    // Retourner le livre
    if ($emprunt->retourner()) {
        header('Location: mes-emprunts.php?success=returned');
    } else {
        header('Location: mes-emprunts.php?error=return_failed');
    }
    
} catch(PDOException $e) {
    error_log("Erreur lors du retour du livre: " . $e->getMessage());
    header('Location: mes-emprunts.php?error=return_failed');
}
exit;
?> 