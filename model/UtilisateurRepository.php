<?php
require_once("model/DBRepository.php");

class Utilisateur extends DBRepository {

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM utilisateur WHERE deleted_at IS NULL");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCorbeille() {
        $stmt = $this->db->query("SELECT * FROM utilisateur WHERE deleted_at IS NOT NULL");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO utilisateur 
            (nom, prenom, email, mot_de_passe, telephone, role, date_inscription, created_at) 
            VALUES 
            (:nom, :prenom, :email, :mot_de_passe, :telephone, :role, NOW(), NOW())"
        );
        $stmt->execute($data);
    }

    public function softDelete($id) {
        $stmt = $this->db->prepare("UPDATE utilisateur SET deleted_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function restore($id) {
        $stmt = $this->db->prepare("UPDATE utilisateur SET deleted_at = NULL WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM utilisateur WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM utilisateur WHERE email = :email AND deleted_at IS NULL");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>