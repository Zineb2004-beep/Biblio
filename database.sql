-- Création de la base de données
CREATE DATABASE IF NOT EXISTS bibliotheque;
USE bibliotheque;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('admin', 'utilisateur') DEFAULT 'utilisateur',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table des catégories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL UNIQUE,
    description TEXT
);

-- Table des livres
CREATE TABLE IF NOT EXISTS livres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    auteur VARCHAR(100) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    categorie_id INT,
    quantite_disponible INT DEFAULT 1,
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categorie_id) REFERENCES categories(id)
);

-- Table des emprunts
CREATE TABLE IF NOT EXISTS emprunts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    livre_id INT,
    utilisateur_id INT,
    date_emprunt DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_retour_prevue DATETIME,
    date_retour_effective DATETIME,
    statut ENUM('en_cours', 'retourne', 'en_retard') DEFAULT 'en_cours',
    FOREIGN KEY (livre_id) REFERENCES livres(id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
);

-- Table des messages de contact
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('non_lu', 'lu', 'repondu') DEFAULT 'non_lu'
);

-- Insertion des catégories de base
INSERT INTO categories (nom, description) VALUES
('Roman', 'Romans et œuvres de fiction'),
('Policier', 'Romans policiers et thrillers'),
('Philosophie', 'Ouvrages philosophiques'),
('Poésie', 'Recueils de poèmes'),
('Fantastique', 'Œuvres de littérature fantastique');

-- Insertion d'un administrateur par défaut (mot de passe: admin123)
INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES
('Admin', 'admin@bibliotheque.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insertion de quelques livres d'exemple
INSERT INTO livres (titre, auteur, description, image_url, categorie_id) VALUES
('Étranger', 'Albert Camus', 'Un roman existentialiste qui explore la condition humaine', 'images/ÉTRANGER (L\') par Albert Camus couverture souple _ Indigo Chapters.jpeg', 1),
('La Métamorphose', 'Franz Kafka', 'Un homme se réveille transformé en insecte', 'images/La Métamorphose.jpeg', 1),
('Le Prince', 'Machiavel', 'Un traité de politique et de pouvoir', 'images/Le Prince - Machiavel.jpeg', 3),
('Les Contemplations', 'Victor Hugo', 'Un recueil de poèmes majeur', 'images/Les Contemplations - Victor Hugo - Livres I - VI.jpeg', 4),
('Le Horla', 'Guy de Maupassant', 'Une nouvelle fantastique', 'images/awareness.jpeg', 5); 