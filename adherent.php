<?php
require_once 'auth/auth.php';
require_once 'config/database.php';

// Initialiser l'objet Auth
$auth = new Auth();

// Vérifier si l'utilisateur est connecté
if (!$auth->isLoggedIn()) {
    header('Location: login.php?error=' . urlencode('Vous devez être connecté pour accéder à cette page'));
    exit();
}

// Initialiser la connexion à la base de données
$db = $auth->getDb();
if (!$db) {
    die("Erreur : Impossible de se connecter à la base de données.");
}

// Récupérer les informations de l'utilisateur connecté
$currentUser = $auth->getCurrentUser();

// Définir la section à afficher
$section = isset($_GET['section']) ? $_GET['section'] : 'dashboard';

// Charger les données nécessaires
function getBorrowedBooks($db, $userId) {
    try {
        $query = "SELECT e.id, l.titre, l.auteur, e.date_emprunt, e.date_retour_prevue 
                  FROM emprunts e 
                  JOIN livres l ON e.livre_id = l.id 
                  WHERE e.utilisateur_id = :userId AND (e.date_retour_effective IS NULL OR e.date_retour_effective = '')";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Erreur lors de la récupération des livres empruntés : " . $e->getMessage());
    }
}

function getCatalogue($db) {
    try {
        $query = "SELECT id, titre, auteur, quantite_disponible FROM livres";
        $stmt = $db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Erreur lors de la récupération du catalogue : " . $e->getMessage());
    }
}

function getBorrowHistory($db, $userId) {
    try {
        $query = "SELECT e.id, l.titre, l.auteur, e.date_emprunt, e.date_retour_effective 
                  FROM emprunts e 
                  JOIN livres l ON e.livre_id = l.id 
                  WHERE e.utilisateur_id = :userId AND e.date_retour_effective IS NOT NULL";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Erreur lors de la récupération de l'historique des emprunts : " . $e->getMessage());
    }
}

function getStatistics($db, $userId) {
    try {
        // Nombre total de livres empruntés par l'utilisateur
        $query1 = "SELECT COUNT(*) AS total_emprunts FROM emprunts WHERE utilisateur_id = :userId";
        $stmt1 = $db->prepare($query1);
        $stmt1->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt1->execute();
        $totalEmprunts = $stmt1->fetch(PDO::FETCH_ASSOC)['total_emprunts'];

        // Nombre de livres disponibles dans le catalogue
        $query2 = "SELECT COUNT(*) AS total_livres FROM livres WHERE quantite_disponible > 0";
        $stmt2 = $db->prepare($query2);
        $stmt2->execute();
        $totalLivres = $stmt2->fetch(PDO::FETCH_ASSOC)['total_livres'];

        // Nombre de livres actuellement empruntés
        $query3 = "SELECT COUNT(*) AS emprunts_actuels FROM emprunts WHERE utilisateur_id = :userId AND date_retour_effective IS NULL";
        $stmt3 = $db->prepare($query3);
        $stmt3->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt3->execute();
        $empruntsActuels = $stmt3->fetch(PDO::FETCH_ASSOC)['emprunts_actuels'];

        return [
            'total_emprunts' => $totalEmprunts,
            'total_livres' => $totalLivres,
            'emprunts_actuels' => $empruntsActuels,
        ];
    } catch (PDOException $e) {
        die("Erreur lors de la récupération des statistiques : " . $e->getMessage());
    }
}

// Charger les données en fonction de la section
$borrowedBooks = [];
$catalogue = [];
$borrowHistory = [];
$statistics = [];
if ($section === 'borrowed') {
    $borrowedBooks = getBorrowedBooks($db, $currentUser['id']);
} elseif ($section === 'catalogue') {
    $catalogue = getCatalogue($db);
} elseif ($section === 'history') {
    $borrowHistory = getBorrowHistory($db, $currentUser['id']);
} elseif ($section === 'dashboard') {
    $statistics = getStatistics($db, $currentUser['id']);
}

// Gestion de l'emprunt d'un livre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['emprunter_livre_id'])) {
    $livreId = $_POST['emprunter_livre_id'];
    try {
        // Vérifier si le livre est disponible
        $query = "SELECT quantite_disponible FROM livres WHERE id = :livreId";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':livreId', $livreId, PDO::PARAM_INT);
        $stmt->execute();
        $livre = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($livre && $livre['quantite_disponible'] > 0) {
            // Ajouter un emprunt
            $query = "INSERT INTO emprunts (utilisateur_id, livre_id, date_emprunt, date_retour_prevue) 
                      VALUES (:userId, :livreId, NOW(), DATE_ADD(NOW(), INTERVAL 14 DAY))";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':userId', $currentUser['id'], PDO::PARAM_INT);
            $stmt->bindParam(':livreId', $livreId, PDO::PARAM_INT);
            $stmt->execute();

            // Mettre à jour la quantité disponible
            $query = "UPDATE livres SET quantite_disponible = quantite_disponible - 1 WHERE id = :livreId";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':livreId', $livreId, PDO::PARAM_INT);
            $stmt->execute();

            header('Location: adherent.php?section=catalogue&success=emprunt');
            exit();
        } else {
            header('Location: adherent.php?section=catalogue&error=indisponible');
            exit();
        }
    } catch (PDOException $e) {
        die("Erreur lors de l'emprunt du livre : " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Adhérent</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        /* Styles généraux */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #AF9284;
            color: #0F090B;
        }
        .container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: #362828;
            color: white;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.5em;
        }
        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }
        .sidebar ul li {
            margin-bottom: 15px;
        }
        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .sidebar ul li a:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        .main-content {
            flex: 1;
            padding: 20px;
            background-color: #F5ECE6;
        }
        h1 {
            color: #362828;
            margin-bottom: 20px;
        }

        /* Styles pour les statistiques */
        .stats {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            flex: 1;
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card h3 {
            margin: 0;
            color: #362828;
            font-size: 1.2em;
        }
        .stat-card p {
            font-size: 2em;
            margin: 10px 0;
            font-weight: bold;
            color: #AF9284;
        }

        /* Styles pour les tableaux */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #362828;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }

        /* Styles pour les boutons */
        button {
            background-color: #362828;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #AF9284;
        }

        /* Messages de succès et d'erreur */
        .success {
            color: green;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .error {
            color: red;
            font-weight: bold;
            margin-bottom: 20px;
        }

        /* Styles pour les états */
        span {
            font-weight: bold;
        }
        span[style*="color: red"] {
            background-color: #f8d7da;
            padding: 5px 10px;
            border-radius: 5px;
        }
        span[style*="color: green"] {
            background-color: #d4edda;
            padding: 5px 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="sidebar">
        <h2>Menu Adhérent</h2>
        <ul>
            <li><a href="?section=dashboard"><i class="fas fa-tachometer-alt"></i> Tableau de Bord</a></li>
            <li><a href="?section=borrowed"><i class="fas fa-book"></i> Livres Empruntés</a></li>
            <li><a href="?section=catalogue"><i class="fas fa-list"></i> Catalogue</a></li>
            <li><a href="?section=history"><i class="fas fa-history"></i> Historique</a></li>
            <li><a href="../login.php?action=logout"><i class="fas fa-sign-out-alt"></i> Se Déconnecter</a></li>
        </ul>
    </div>
    <div class="main-content">
        <?php if ($section === 'dashboard'): ?>
            <h1>Bienvenue <?php echo htmlspecialchars($currentUser['nom']); ?> dans votre espace adhérent!</h1>
            <div class="stats">
                <div class="stat-card">
                    <h3>Total des emprunts</h3>
                    <p><?php echo $statistics['total_emprunts']; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Livres disponibles</h3>
                    <p><?php echo $statistics['total_livres']; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Emprunts en cours</h3>
                    <p><?php echo $statistics['emprunts_actuels']; ?></p>
                </div>
            </div>
        <?php elseif ($section === 'borrowed'): ?>
            <h1>Vos Livres Empruntés</h1>
            <?php if (empty($borrowedBooks)): ?>
                <p>Aucun livre emprunté pour le moment.</p>
            <?php else: ?>
                <table>
                    <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Auteur</th>
                        <th>Date d'emprunt</th>
                        <th>Date de retour prévue</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($borrowedBooks as $book): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($book['titre']); ?></td>
                            <td><?php echo htmlspecialchars($book['auteur']); ?></td>
                            <td><?php echo htmlspecialchars($book['date_emprunt']); ?></td>
                            <td><?php echo htmlspecialchars($book['date_retour_prevue']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php elseif ($section === 'catalogue'): ?>
            <h1>Catalogue de la Bibliothèque</h1>
            <?php if (isset($_GET['success']) && $_GET['success'] === 'emprunt'): ?>
                <p class="success">Livre emprunté avec succès !</p>
            <?php elseif (isset($_GET['error']) && $_GET['error'] === 'indisponible'): ?>
                <p class="error">Ce livre n'est pas disponible.</p>
            <?php endif; ?>
            <table>
                <thead>
                <tr>
                    <th>Titre</th>
                    <th>Auteur</th>
                    <th>Disponibilité</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($catalogue as $book): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($book['titre']); ?></td>
                        <td><?php echo htmlspecialchars($book['auteur']); ?></td>
                        <td><?php echo $book['quantite_disponible'] > 0 ? 'Disponible' : 'Indisponible'; ?></td>
                        <td>
                            <?php if ($book['quantite_disponible'] > 0): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="emprunter_livre_id" value="<?php echo $book['id']; ?>">
                                    <button type="submit">Emprunter</button>
                                </form>
                            <?php else: ?>
                                Indisponible
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($section === 'history'): ?>
            <h1>Historique des Emprunts</h1>
            <?php if (empty($borrowHistory)): ?>
                <p>Aucun historique d'emprunt trouvé.</p>
            <?php else: ?>
                <table>
                    <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Auteur</th>
                        <th>Date d'emprunt</th>
                        <th>Date de retour</th>
                        <th>Durée (jours)</th>
                        <th>État</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($borrowHistory as $book): ?>
                        <?php
                        // Calculer la durée de l'emprunt
                        $dateEmprunt = new DateTime($book['date_emprunt']);
                        $dateRetour = new DateTime($book['date_retour_effective']);
                        $interval = $dateEmprunt->diff($dateRetour);
                        $duree = $interval->days;

                        // Déterminer l'état en fonction de la durée
                        $etat = $duree > 3 
                            ? '<span style="color: red;">En retard</span>' 
                            : '<span style="color: green;">Retourné à temps</span>';
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($book['titre']); ?></td>
                            <td><?php echo htmlspecialchars($book['auteur']); ?></td>
                            <td><?php echo htmlspecialchars($book['date_emprunt']); ?></td>
                            <td><?php echo htmlspecialchars($book['date_retour_effective']); ?></td>
                            <td><?php echo $duree; ?></td>
                            <td><?php echo $etat; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
``` 

