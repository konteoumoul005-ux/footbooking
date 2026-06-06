<?php
require_once("model/DBRepository.php");

class Paiement extends DBRepository {

    public function getAll() {
        $stmt = $this->db->query("SELECT p.*, u.nom, u.prenom, t.nom as terrain 
            FROM paiement p
            JOIN reservation r ON p.reservation_id = r.id
            JOIN utilisateur u ON r.utilisateur_id = u.id
            JOIN terrain t ON r.terrain_id = t.id
            WHERE p.supprime = 0");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert($data) {
        $stmt = $this->db->prepare("INSERT INTO paiement (reservation_id, montant, moyen, statut, date) VALUES (:reservation_id, :montant, :moyen, :statut, :date)");
        $stmt->execute($data);
    }

    public function softDelete($id) {
        $stmt = $this->db->prepare("UPDATE paiement SET supprime = 1 WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function restore($id) {
        $stmt = $this->db->prepare("UPDATE paiement SET supprime = 0 WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
}
?>