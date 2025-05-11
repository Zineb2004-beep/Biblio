<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Utilisateur.php';

class Auth {
    private $db;
    private $utilisateur;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->utilisateur = new Utilisateur($this->db);
    }

    public function getDb() {
        return $this->db;
    }

    public function login($email, $password) {
        $this->utilisateur->email = $email;

        if ($this->utilisateur->emailExiste()) {
            if (password_verify($password, $this->utilisateur->mot_de_passe)) {
                $_SESSION['user_id'] = $this->utilisateur->id;
                $_SESSION['user_nom'] = $this->utilisateur->nom;
                $_SESSION['user_role'] = $this->utilisateur->role;
                return true;
            }
        }
        return false;
    }

    public function register($nom, $email, $password) {
        $this->utilisateur->nom = $nom;
        $this->utilisateur->email = $email;
        $this->utilisateur->mot_de_passe = $password;
        $this->utilisateur->role = 'utilisateur';

        if ($this->utilisateur->creer()) {
            return true;
        }
        return false;
    }

    public function logout() {
        session_unset();
        session_destroy();
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'nom' => $_SESSION['user_nom'],
                'role' => $_SESSION['user_role']
            ];
        }
        return null;
    }
}

// Traitement des requêtes d'authentification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth();
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'login':
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if ($auth->login($email, $password)) {
                header('Location: ../index.php');
                exit;
            } else {
                header('Location: ../login.php?error=invalid_credentials');
                exit;
            }
            break;

        case 'register':
            $nom = $_POST['nom'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if ($auth->register($nom, $email, $password)) {
                header('Location: ../login.php?success=registration_complete');
                exit;
            } else {
                header('Location: ../register.php?error=registration_failed');
                exit;
            }
            break;

        case 'logout':
            $auth->logout();
            header('Location: ../index.php');
            exit;
            break;
    }
}
?>