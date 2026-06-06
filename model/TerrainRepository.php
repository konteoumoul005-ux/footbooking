<?php
require_once("model/DBRepository.php");

class Terrain extends DBRepository {

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM terrain WHERE deleted_at IS NULL");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCorbeille() {
        $stmt = $this->db->query("SELECT * FROM terrain WHERE deleted_at IS NOT NULL");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO terrain (nom, localisation, prix_petit, prix_moitie, prix_grand) 
             VALUES (:nom, :localisation, :prix_petit, :prix_moitie, :prix_grand)"
        );
        $stmt->execute($data);
    }

    public function softDelete($id) {
        $stmt = $this->db->prepare("UPDATE terrain SET deleted_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function restore($id) {
        $stmt = $this->db->prepare("UPDATE terrain SET deleted_at = NULL WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM terrain WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function update($data) {
        $stmt = $this->db->prepare(
            "UPDATE terrain SET 
            nom = :nom, 
            localisation = :localisation,
            prix_petit = :prix_petit,
            prix_moitie = :prix_moitie,
            prix_grand = :prix_grand
            WHERE id = :id"
        );
        $stmt->execute($data);
    }
}
?>