<?php
class Ressource {
    private $conn;

    // Le constructeur reçoit la connexion à la base de données
    public function __construct($db) {
        $this->conn = $db;
    }

    // Ajouter une ressource
    public function ajouter($nom, $montant, $source_id, $personne_id) {
        $stmt = $this->conn->prepare("
            INSERT INTO ressource (nom, montant, source_id, personne_id)
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$nom, $montant, $source_id, $personne_id]);
    }

    // Lister toutes les ressources d'un utilisateur
    public function lister($personne_id) {
        $stmt = $this->conn->prepare("
            SELECT * FROM ressource
            WHERE personne_id = ?
        ");
        $stmt->execute([$personne_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
