<?php
class Livre {
    private $conn;
    private $table_name = "livres";

    public $id;
    public $titre;
    public $auteur;
    public $description;
    public $image_url;
    public $categorie_id;
    public $quantite_disponible;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lire tous les livres
    public function lireTous() {
        $query = "SELECT l.*, c.nom as categorie_nom 
                 FROM " . $this->table_name . " l
                 LEFT JOIN categories c ON l.categorie_id = c.id
                 ORDER BY l.date_ajout DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Lire les livres par catégorie
    public function lireParCategorie($categorie_id) {
        $query = "SELECT l.*, c.nom as categorie_nom 
                 FROM " . $this->table_name . " l
                 LEFT JOIN categories c ON l.categorie_id = c.id
                 WHERE l.categorie_id = ?
                 ORDER BY l.date_ajout DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $categorie_id);
        $stmt->execute();
        
        return $stmt;
    }

    // Créer un nouveau livre
    public function creer() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    titre = :titre,
                    auteur = :auteur,
                    description = :description,
                    image_url = :image_url,
                    categorie_id = :categorie_id,
                    quantite_disponible = :quantite_disponible";

        $stmt = $this->conn->prepare($query);

        // Nettoyer les données
        $this->titre = htmlspecialchars(strip_tags($this->titre));
        $this->auteur = htmlspecialchars(strip_tags($this->auteur));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));
        $this->categorie_id = htmlspecialchars(strip_tags($this->categorie_id));
        $this->quantite_disponible = htmlspecialchars(strip_tags($this->quantite_disponible));

        // Lier les paramètres
        $stmt->bindParam(":titre", $this->titre);
        $stmt->bindParam(":auteur", $this->auteur);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":categorie_id", $this->categorie_id);
        $stmt->bindParam(":quantite_disponible", $this->quantite_disponible);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Mettre à jour un livre
    public function mettreAJour() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    titre = :titre,
                    auteur = :auteur,
                    description = :description,
                    image_url = :image_url,
                    categorie_id = :categorie_id,
                    quantite_disponible = :quantite_disponible
                WHERE
                    id = :id";

        $stmt = $this->conn->prepare($query);

        // Nettoyer les données
        $this->titre = htmlspecialchars(strip_tags($this->titre));
        $this->auteur = htmlspecialchars(strip_tags($this->auteur));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));
        $this->categorie_id = htmlspecialchars(strip_tags($this->categorie_id));
        $this->quantite_disponible = htmlspecialchars(strip_tags($this->quantite_disponible));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Lier les paramètres
        $stmt->bindParam(":titre", $this->titre);
        $stmt->bindParam(":auteur", $this->auteur);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":categorie_id", $this->categorie_id);
        $stmt->bindParam(":quantite_disponible", $this->quantite_disponible);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Supprimer un livre
    public function supprimer() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?> 