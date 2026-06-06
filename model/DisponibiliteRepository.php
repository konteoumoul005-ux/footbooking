<?php
require_once("model/DBRepository.php");

class Disponibilite extends DBRepository {

    public function getAll() {
        $stmt = $this->db->query("SELECT d.*, t.nom as terrain 
            FROM disponibilite d
            JOIN terrain t ON d.terrain_id = t.id
            WHERE d.supprime = 0");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert($data) {
        $stmt = $this->db->prepare("INSERT INTO disponibilite (terrain_id, jour, heure_debut, heure_fin, statut) VALUES (:terrain_id, :jour, :heure_debut, :heure_fin, :statut)");
        $stmt->execute($data);
    }

    public function softDelete($id) {
        $stmt = $this->db->prepare("UPDATE disponibilite SET supprime = 1 WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function restore($id) {
        $stmt = $this->db->prepare("UPDATE disponibilite SET supprime = 0 WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
}
?>