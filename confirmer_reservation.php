<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /FootBookingApp/login.php');
    exit();
}

require_once("model/DBRepository.php");
require_once("send_email.php");

$db = new DBRepository();
$reservation_id = $_POST['reservation_id'] ?? null;

if ($reservation_id) {
    // Confirmer la réservation
    $stmt = $db->getPDO()->prepare("UPDATE reservation SET statut='confirme' WHERE id=:id");
    $stmt->execute(['id' => $reservation_id]);
    $stmt2 = $db->getPDO()->prepare("UPDATE paiement SET statut='paye' WHERE reservation_id=:id");
    $stmt2->execute(['id' => $reservation_id]);

    // Récupérer les infos pour l'email
    $stmt3 = $db->getPDO()->prepare("
        SELECT r.*, u.nom, u.prenom, u.email, t.nom as terrain
        FROM reservation r
        JOIN utilisateur u ON r.utilisateur_id = u.id
        JOIN terrain t ON r.terrain_id = t.id
        WHERE r.id = :id
    ");
    $stmt3->execute(['id' => $reservation_id]);
    $reservation = $stmt3->fetch(PDO::FETCH_ASSOC);

    if ($reservation) {
        $nomComplet = $reservation['prenom'] . ' ' . $reservation['nom'];
        envoyerEmailConfirmation($reservation['email'], $nomComplet, $reservation);
    }
}

header('Location: /FootBookingApp/ListeReservation');
exit();
?>