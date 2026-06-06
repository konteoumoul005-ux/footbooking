<?php
session_name('admin_session');
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin']) || $_SESSION['admin']['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit();
}

require_once("model/DBRepository.php");
require_once("send_email.php");

$db     = new DBRepository();
$id     = $_POST['id'] ?? null;
$statut = $_POST['statut'] ?? null;

// Suppression
if ($statut === 'supprimer') {
    $stmt = $db->getPDO()->prepare("DELETE FROM reservation WHERE id=:id");
    $stmt->execute(['id' => $id]);
    echo json_encode(['success' => true]);
    exit();
}

if (!$id || !$statut) {
    echo json_encode(['success' => false, 'error' => 'Données manquantes']);
    exit();
}

try {
    $stmt = $db->getPDO()->prepare("UPDATE reservation SET statut=:statut WHERE id=:id");
    $stmt->execute(['statut' => $statut, 'id' => $id]);

    if ($statut === 'confirmee') {
        $stmt2 = $db->getPDO()->prepare("UPDATE paiement SET statut='paye' WHERE reservation_id=:id");
        $stmt2->execute(['id' => $id]);

        $stmt3 = $db->getPDO()->prepare("
            SELECT r.*, u.nom, u.prenom, u.email, t.nom as terrain
            FROM reservation r
            JOIN utilisateur u ON r.utilisateur_id = u.id
            JOIN terrain t ON r.terrain_id = t.id
            WHERE r.id = :id
        ");
        $stmt3->execute(['id' => $id]);
        $reservation = $stmt3->fetch(PDO::FETCH_ASSOC);

        if ($reservation && !empty($reservation['email'])) {
            $nomComplet  = $reservation['prenom'] . ' ' . $reservation['nom'];
            $emailEnvoye = envoyerEmailConfirmation($reservation['email'], $nomComplet, $reservation);
            echo json_encode(['success' => true, 'email' => $emailEnvoye, 'destinataire' => $reservation['email']]);
        } else {
            echo json_encode(['success' => true, 'email' => false, 'error' => 'Email utilisateur manquant']);
        }
    } else {
        echo json_encode(['success' => true]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>