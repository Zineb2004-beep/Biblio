<?php
class Utilisateur {
    private $conn;
    private $table_name = "utilisateurs";

    public $id;
    public $nom;
    public $email;
    public $mot_de_passe;
    public $role;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Créer un nouvel utilisateur
    public function creer() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    nom = :nom,
                    email = :email,
                    mot_de_passe = :mot_de_passe,
                    role = :role";

        $stmt = $this->conn->prepare($query);

        // Nettoyer les données
        $this->nom = htmlspecialchars(strip_tags($this->nom));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->role = htmlspecialchars(strip_tags($this->role));

        // Hasher le mot de passe
        $this->mot_de_passe = password_hash($this->mot_de_passe, PASSWORD_DEFAULT);

        // Lier les paramètres
        $stmt->bindParam(":nom", $this->nom);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":mot_de_passe", $this->mot_de_passe);
        $stmt->bindParam(":role", $this->role);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Vérifier si un email existe
    public function emailExiste() {
        $query = "SELECT id, nom, mot_de_passe, role
                FROM " . $this->table_name . "
                WHERE email = ?
                LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->nom = $row['nom'];
            $this->mot_de_passe = $row['mot_de_passe'];
            $this->role = $row['role'];
            return true;
        }
        return false;
    }

    // Mettre à jour un utilisateur
    public function mettreAJour() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    nom = :nom,
                    email = :email,
                    role = :role
                WHERE
                    id = :id";

        $stmt = $this->conn->prepare($query);

        // Nettoyer les données
        $this->nom = htmlspecialchars(strip_tags($this->nom));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Lier les paramètres
        $stmt->bindParam(":nom", $this->nom);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Changer le mot de passe
    public function changerMotDePasse($nouveau_mot_de_passe) {
        $query = "UPDATE " . $this->table_name . "
                SET
                    mot_de_passe = :mot_de_passe
                WHERE
                    id = :id";

        $stmt = $this->conn->prepare($query);

        // Hasher le nouveau mot de passe
        $nouveau_mot_de_passe = password_hash($nouveau_mot_de_passe, PASSWORD_DEFAULT);

        // Lier les paramètres
        $stmt->bindParam(":mot_de_passe", $nouveau_mot_de_passe);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?> 