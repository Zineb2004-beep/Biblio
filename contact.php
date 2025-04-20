<?php
require_once 'includes/header.php';
require_once 'config/database.php';

$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $nom = htmlspecialchars($_POST['nom']);
        $email = htmlspecialchars($_POST['email']);
        $sujet = htmlspecialchars($_POST['sujet']);
        $message = htmlspecialchars($_POST['message']);
        
        $stmt = $db->prepare("INSERT INTO messages (nom, email, sujet, message, date_envoi) VALUES (?, ?, ?, ?, NOW())");
        if ($stmt->execute([$nom, $email, $sujet, $message])) {
            header('Location: contact.php?success=sent');
            exit;
        } else {
            header('Location: contact.php?error=failed');
            exit;
        }
    } catch(PDOException $e) {
        error_log("Erreur lors de l'envoi du message: " . $e->getMessage());
        header('Location: contact.php?error=system');
        exit;
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">Contactez-nous</h2>
                </div>
                <div class="card-body">
                    <?php if ($success === 'sent'): ?>
                        <div class="alert alert-success">
                            Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <?php
                            switch($error) {
                                case 'failed':
                                    echo "Une erreur est survenue lors de l'envoi du message. Veuillez réessayer.";
                                    break;
                                case 'system':
                                    echo "Une erreur système est survenue. Veuillez réessayer plus tard.";
                                    break;
                                default:
                                    echo "Une erreur inattendue s'est produite.";
                            }
                            ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="contact.php">
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom complet</label>
                            <input type="text" class="form-control" id="nom" name="nom" required 
                                value="<?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Adresse email</label>
                            <input type="email" class="form-control" id="email" name="email" required
                                value="<?php echo isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label for="sujet" class="form-label">Sujet</label>
                            <input type="text" class="form-control" id="sujet" name="sujet" required>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Envoyer le message</button>
                    </form>

                    <div class="mt-4">
                        <h3>Informations de contact</h3>
                        <p><i class="fas fa-map-marker-alt"></i> 123 Rue de la Bibliothèque, 75000 Paris</p>
                        <p><i class="fas fa-phone"></i> +33 1 23 45 67 89</p>
                        <p><i class="fas fa-envelope"></i> contact@bibliotheque.fr</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 