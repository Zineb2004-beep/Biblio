<?php
require_once 'config/database.php';
require_once 'models/Emprunt.php';
require_once 'auth/auth.php';
require_once 'includes/header.php';

$auth = new Auth();

// Vérifier si l'utilisateur est connecté
if (!$auth->isLoggedIn()) {
    header('Location: login.php?error=not_logged_in');
    exit;
}

$database = new Database();
$db = $database->getConnection();
$emprunt = new Emprunt($db);

// Récupérer les emprunts de l'utilisateur
$emprunts = $emprunt->lireParUtilisateur($_SESSION['user_id']);
?>

<div class="container mt-4">
    <h2>Mes Emprunts</h2>

    <?php if (isset($_GET['success']) && $_GET['success'] == 'returned'): ?>
        <div class="alert alert-success">Le livre a été retourné avec succès.</div>
    <?php endif; ?>

    <?php if (isset($_GET['success']) && $_GET['success'] == 'borrowed'): ?>
        <div class="alert alert-success">Le livre a été emprunté avec succès.</div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <?php
            switch($_GET['error']) {
                case 'not_found':
                    echo "L'emprunt spécifié n'a pas été trouvé.";
                    break;
                case 'return_failed':
                    echo "Une erreur est survenue lors du retour du livre.";
                    break;
                default:
                    echo "Une erreur est survenue.";
            }
            ?>
        </div>
    <?php endif; ?>

    <?php if (empty($emprunts)): ?>
        <div class="alert alert-info">Vous n'avez aucun emprunt en cours.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($emprunts as $emprunt): ?>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($emprunt['titre']); ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted">
                                Par <?php echo htmlspecialchars($emprunt['auteur']); ?>
                            </h6>
                            <p class="card-text">
                                <strong>Date d'emprunt:</strong> <?php echo date('d/m/Y', strtotime($emprunt['date_emprunt'])); ?><br>
                                <strong>Date de retour prévue:</strong> <?php echo date('d/m/Y', strtotime($emprunt['date_retour_prevue'])); ?><br>
                                <strong>Statut:</strong> 
                                <?php if ($emprunt['statut'] == 'en_cours'): ?>
                                    <span class="badge bg-primary">En cours</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Retourné</span>
                                <?php endif; ?>
                            </p>
                            <?php if ($emprunt['statut'] == 'en_cours'): ?>
                                <form action="retourner.php" method="post" style="display: inline;">
                                    <input type="hidden" name="emprunt_id" value="<?php echo $emprunt['id']; ?>">
                                    <button type="submit" class="btn btn-success" onclick="return confirm('Êtes-vous sûr de vouloir retourner ce livre ?')">
                                        Retourner le livre
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
}

.badge {
    font-size: 0.9em;
    padding: 0.5em 1em;
}

.btn-success {
    margin-top: 1rem;
}
</style>

<?php require_once 'includes/footer.php'; ?> 