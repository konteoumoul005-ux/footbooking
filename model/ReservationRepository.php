<?php
require_once("model/DBRepository.php");

class Reservation extends DBRepository {

    public function getAll() {
        $stmt = $this->db->query("SELECT r.*, u.nom, u.prenom, t.nom as terrain 
            FROM reservation r 
            JOIN utilisateur u ON r.utilisateur_id = u.id 
            JOIN terrain t ON r.terrain_id = t.id
            WHERE r.supprime = 0");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert($data) {
        $stmt = $this->db->prepare("INSERT INTO reservation (utilisateur_id, terrain_id, date, heure_debut, heure_fin, montant, statut) VALUES (:utilisateur_id, :terrain_id, :date, :heure_debut, :heure_fin, :montant, :statut)");
        $stmt->execute($data);
    }

    public function softDelete($id) {
        $stmt = $this->db->prepare("UPDATE reservation SET supprime = 1 WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function restore($id) {
        $stmt = $this->db->prepare("UPDATE reservation SET supprime = 0 WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
}
?>