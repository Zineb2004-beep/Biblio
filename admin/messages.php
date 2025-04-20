<?php
require_once '../includes/header.php';
require_once '../config/database.php';

// Vérifier si l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Supprimer un message
if (isset($_POST['delete_message']) && isset($_POST['message_id'])) {
    try {
        $stmt = $db->prepare("DELETE FROM messages WHERE id = ?");
        $stmt->execute([intval($_POST['message_id'])]);
        header('Location: messages.php?success=deleted');
        exit;
    } catch(PDOException $e) {
        error_log("Erreur lors de la suppression du message: " . $e->getMessage());
        header('Location: messages.php?error=delete_failed');
        exit;
    }
}

// Marquer comme lu/non lu
if (isset($_POST['toggle_read']) && isset($_POST['message_id'])) {
    try {
        $stmt = $db->prepare("UPDATE messages SET lu = NOT lu WHERE id = ?");
        $stmt->execute([intval($_POST['message_id'])]);
        header('Location: messages.php?success=updated');
        exit;
    } catch(PDOException $e) {
        error_log("Erreur lors de la mise à jour du statut du message: " . $e->getMessage());
        header('Location: messages.php?error=update_failed');
        exit;
    }
}

// Récupérer tous les messages
try {
    $stmt = $db->query("SELECT * FROM messages ORDER BY date_envoi DESC");
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Erreur lors de la récupération des messages: " . $e->getMessage());
    $error = "Une erreur est survenue lors de la récupération des messages.";
}
?>

<div class="container my-5">
    <h2>Gestion des messages</h2>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <?php
            switch($_GET['success']) {
                case 'deleted':
                    echo "Le message a été supprimé avec succès.";
                    break;
                case 'updated':
                    echo "Le statut du message a été mis à jour.";
                    break;
            }
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <?php
            switch($_GET['error']) {
                case 'delete_failed':
                    echo "Une erreur est survenue lors de la suppression du message.";
                    break;
                case 'update_failed':
                    echo "Une erreur est survenue lors de la mise à jour du statut.";
                    break;
                default:
                    echo "Une erreur inattendue s'est produite.";
            }
            ?>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Sujet</th>
                    <th>Message</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $message): ?>
                    <tr class="<?php echo $message['lu'] ? '' : 'table-warning'; ?>">
                        <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($message['date_envoi']))); ?></td>
                        <td><?php echo htmlspecialchars($message['nom']); ?></td>
                        <td><?php echo htmlspecialchars($message['email']); ?></td>
                        <td><?php echo htmlspecialchars($message['sujet']); ?></td>
                        <td>
                            <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#messageModal<?php echo $message['id']; ?>">
                                Voir le message
                            </button>
                        </td>
                        <td><?php echo $message['lu'] ? 'Lu' : 'Non lu'; ?></td>
                        <td>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                <button type="submit" name="toggle_read" class="btn btn-sm btn-info">
                                    <?php echo $message['lu'] ? 'Marquer comme non lu' : 'Marquer comme lu'; ?>
                                </button>
                            </form>
                            <form method="post" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?');">
                                <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                <button type="submit" name="delete_message" class="btn btn-sm btn-danger">Supprimer</button>
                            </form>
                        </td>
                    </tr>

                    <!-- Modal pour afficher le message complet -->
                    <div class="modal fade" id="messageModal<?php echo $message['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Message de <?php echo htmlspecialchars($message['nom']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Sujet :</strong> <?php echo htmlspecialchars($message['sujet']); ?></p>
                                    <p><strong>Message :</strong></p>
                                    <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                    <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" class="btn btn-primary">
                                        Répondre par email
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 