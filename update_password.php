<?php
require_once 'auth/auth.php';
$auth = new Auth();

// Vérifier si l'utilisateur est connecté
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

require_once 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Changer le mot de passe</h3>
                </div>
                <div class="card-body">
                    <?php if(isset($_GET['error'])): ?>
                        <div class="alert alert-danger">
                            <?php 
                            switch($_GET['error']) {
                                case 'current_password':
                                    echo "Le mot de passe actuel est incorrect.";
                                    break;
                                case 'password_mismatch':
                                    echo "Les nouveaux mots de passe ne correspondent pas.";
                                    break;
                                default:
                                    echo "Une erreur est survenue lors du changement de mot de passe.";
                            }
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if(isset($_GET['success'])): ?>
                        <div class="alert alert-success">
                            Le mot de passe a été changé avec succès.
                        </div>
                    <?php endif; ?>

                    <form action="auth/auth.php" method="POST">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="form-group">
                            <label for="current_password">Mot de passe actuel</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>

                        <div class="form-group">
                            <label for="new_password">Nouveau mot de passe</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Changer le mot de passe</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 