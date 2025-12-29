-- Script d'initialisation de la base de données
-- Ce script sera exécuté automatiquement par Ansible

GRANT ALL PRIVILEGES ON webdb.* TO 'webuser'@'%' IDENTIFIED BY 'Tigrou007';
FLUSH PRIVILEGES;

-- Création de la table des produits (exemple)
CREATE TABLE IF NOT EXISTS produits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    prix DECIMAL(10,2),
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertion de données de démonstration
INSERT INTO produits (nom, description, prix) VALUES
    ('Laptop Pro', 'Ordinateur portable haute performance', 1299.99),
    ('Souris Ergonomique', 'Souris sans fil ergonomique', 49.99),
    ('Clavier Mécanique', 'Clavier gaming RGB', 129.99),
    ('Écran 27 pouces', 'Moniteur 4K IPS', 399.99),
    ('Webcam HD', 'Caméra 1080p avec micro', 79.99)
ON DUPLICATE KEY UPDATE nom=nom;

-- Création de la table des informations système
CREATE TABLE IF NOT EXISTS system_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cle VARCHAR(50) NOT NULL UNIQUE,
    valeur VARCHAR(255),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertion d'informations système
INSERT INTO system_info (cle, valeur) VALUES
    ('version', '1.0.0'),
    ('projet', 'TI331 - Automatisation Ansible'),
    ('auteur', 'Etudiant'),
    ('status', 'Production')
ON DUPLICATE KEY UPDATE valeur=VALUES(valeur);
