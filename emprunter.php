<?php
require_once 'config/database.php';
require_once 'models/Emprunt.php';
require_once 'models/Livre.php';
require_once 'auth/auth.php';

$auth = new Auth();

// Vérifier si l'utilisateur est connecté
if (!$auth->isLoggedIn()) {
    header('Location: login.php?error=not_logged_in');
    exit;
}

// Vérifier si l'ID du livre est fourni
if (!isset($_POST['livre_id']) || empty($_POST['livre_id'])) {
    header('Location: catalogue.php?error=missing_book');
    exit;
}

$database = new Database();
$db = $database->getConnection();
$emprunt = new Emprunt($db);
$livre = new Livre($db);

try {
    // Vérifier la disponibilité du livre
    if (!$emprunt->verifierDisponibilite($_POST['livre_id'])) {
        header('Location: catalogue.php?error=not_available');
        exit;
    }

    // Vérifier si l'utilisateur n'a pas déjà emprunté ce livre
    $stmt = $db->prepare("SELECT COUNT(*) FROM emprunts WHERE livre_id = ? AND utilisateur_id = ? AND statut = 'en_cours'");
    $stmt->execute([$_POST['livre_id'], $_SESSION['user_id']]);
    if ($stmt->fetchColumn() > 0) {
        header('Location: catalogue.php?error=already_borrowed');
        exit;
    }

    // Créer l'emprunt
    $emprunt->livre_id = $_POST['livre_id'];
    $emprunt->utilisateur_id = $_SESSION['user_id'];
    $emprunt->date_emprunt = date('Y-m-d');
    $emprunt->date_retour_prevue = date('Y-m-d', strtotime('+14 days')); // 2 semaines de prêt
    $emprunt->statut = 'en_cours';

    if ($emprunt->creer()) {
        header('Location: mes-emprunts.php?success=borrowed');
    } else {
        header('Location: catalogue.php?error=borrow_failed');
    }
} catch (Exception $e) {
    error_log("Erreur lors de l'emprunt du livre: " . $e->getMessage());
    header('Location: catalogue.php?error=system');
}
exit;
?> 