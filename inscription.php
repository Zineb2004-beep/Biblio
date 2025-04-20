<?php
require_once 'config/database.php';

$success_message = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->getConnection();

    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $confirmer_mot_de_passe = $_POST['confirmer_mot_de_passe'] ?? '';

    // Validation
    if (empty($nom) || empty($email) || empty($mot_de_passe) || empty($confirmer_mot_de_passe)) {
        $error_message = "Tous les champs sont obligatoires.";
    } elseif ($mot_de_passe !== $confirmer_mot_de_passe) {
        $error_message = "Les mots de passe ne correspondent pas.";
    } else {
        try {
            // Vérifier si l'email existe déjà
            $query = "SELECT COUNT(*) FROM utilisateurs WHERE email = :email";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            
            if ($stmt->fetchColumn() > 0) {
                $error_message = "Cet email est déjà utilisé.";
            } else {
                // Hash du mot de passe
                $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
                
                // Insertion du nouvel utilisateur
                $query = "INSERT INTO utilisateurs (nom, email, mot_de_passe, role, date_creation) 
                         VALUES (:nom, :email, :mot_de_passe, 'adherent', NOW())";
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(":nom", $nom);
                $stmt->bindParam(":email", $email);
                $stmt->bindParam(":mot_de_passe", $hash);
                
                if ($stmt->execute()) {
                    $success_message = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
                    // Redirection après 2 secondes
                    header("refresh:2;url=connexionadherent.html");
                } else {
                    $error_message = "Erreur lors de l'inscription.";
                }
            }
        } catch (PDOException $e) {
            $error_message = "Erreur de connexion : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - BiblioTech</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background-image: url('images/TAB.png');
            background-size: cover;
            background-repeat: no-repeat;
            height: 100vh;
        }
        .inscription-container {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 30px;
            margin-top: 50px;
            max-width: 500px;
        }
        .form-group {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="inscription-container">
                    <h2 class="text-center mb-4">Inscription</h2>
                    
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success">
                            <?php echo $success_message; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger">
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="nom">Nom complet</label>
                            <input type="text" class="form-control" id="nom" name="nom" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="mot_de_passe">Mot de passe</label>
                            <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required>
                        </div>
                        <div class="form-group">
                            <label for="confirmer_mot_de_passe">Confirmer le mot de passe</label>
                            <input type="password" class="form-control" id="confirmer_mot_de_passe" name="confirmer_mot_de_passe" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">S'inscrire</button>
                    </form>
                    <p class="text-center mt-3">
                        Déjà inscrit ? <a href="connexionadherent.html">Connectez-vous</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html> 