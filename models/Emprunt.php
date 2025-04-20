<?php
class Emprunt {
    private $conn;
    private $table_name = "emprunts";

    public $id;
    public $livre_id;
    public $utilisateur_id;
    public $date_emprunt;
    public $date_retour_prevue;
    public $date_retour_effective;
    public $statut;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Créer un nouvel emprunt
    public function creer() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    livre_id = :livre_id,
                    utilisateur_id = :utilisateur_id,
                    date_retour_prevue = :date_retour_prevue,
                    statut = 'en_cours'";

        $stmt = $this->conn->prepare($query);

        // Nettoyer les données
        $this->livre_id = htmlspecialchars(strip_tags($this->livre_id));
        $this->utilisateur_id = htmlspecialchars(strip_tags($this->utilisateur_id));
        $this->date_retour_prevue = htmlspecialchars(strip_tags($this->date_retour_prevue));

        // Lier les paramètres
        $stmt->bindParam(":livre_id", $this->livre_id);
        $stmt->bindParam(":utilisateur_id", $this->utilisateur_id);
        $stmt->bindParam(":date_retour_prevue", $this->date_retour_prevue);

        if($stmt->execute()) {
            // Mettre à jour la quantité disponible du livre
            $this->mettreAJourQuantiteLivre(-1);
            return true;
        }
        return false;
    }

    // Retourner un livre
    public function retourner() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    date_retour_effective = NOW(),
                    statut = 'retourne'
                WHERE
                    id = :id";

        $stmt = $this->conn->prepare($query);
        
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            // Mettre à jour la quantité disponible du livre
            $this->mettreAJourQuantiteLivre(1);
            return true;
        }
        return false;
    }

    // Lire les emprunts d'un utilisateur
    public function lireParUtilisateur($utilisateur_id) {
        $query = "SELECT e.*, l.titre, l.auteur
                FROM " . $this->table_name . " e
                LEFT JOIN livres l ON e.livre_id = l.id
                WHERE e.utilisateur_id = ?
                ORDER BY e.date_emprunt DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $utilisateur_id);
        $stmt->execute();

        return $stmt;
    }

    // Lire les emprunts en cours
    public function lireEmpruntsEnCours() {
        $query = "SELECT e.*, l.titre, l.auteur, u.nom as nom_utilisateur
                FROM " . $this->table_name . " e
                LEFT JOIN livres l ON e.livre_id = l.id
                LEFT JOIN utilisateurs u ON e.utilisateur_id = u.id
                WHERE e.statut = 'en_cours'
                ORDER BY e.date_emprunt DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Mettre à jour la quantité disponible d'un livre
    private function mettreAJourQuantiteLivre($modification) {
        $query = "UPDATE livres
                SET quantite_disponible = quantite_disponible + :modification
                WHERE id = :livre_id";

        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":modification", $modification);
        $stmt->bindParam(":livre_id", $this->livre_id);
        
        $stmt->execute();
    }

    // Vérifier si un livre est disponible
    public function verifierDisponibilite($livre_id) {
        $query = "SELECT quantite_disponible
                FROM livres
                WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $livre_id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['quantite_disponible'] > 0;
        }
        return false;
    }
}
?> 