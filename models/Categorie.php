<?php
class Categorie {
    private $conn;
    private $table_name = "categories";

    public $id;
    public $nom;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function lireToutes() {
        $query = "SELECT id, nom FROM " . $this->table_name . " ORDER BY nom";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function lire($id) {
        $query = "SELECT id, nom FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt;
    }

    public function creer() {
        $query = "INSERT INTO " . $this->table_name . " (nom) VALUES (?)";
        $stmt = $this->conn->prepare($query);
        
        // Nettoyer les données
        $this->nom = htmlspecialchars(strip_tags($this->nom));
        
        $stmt->bindParam(1, $this->nom);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function mettreAJour() {
        $query = "UPDATE " . $this->table_name . " SET nom = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        // Nettoyer les données
        $this->nom = htmlspecialchars(strip_tags($this->nom));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        $stmt->bindParam(1, $this->nom);
        $stmt->bindParam(2, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

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