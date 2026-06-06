<?php
require_once("model/Reservation.php");

class ReservationController {

    public function liste() {
        $model = new Reservation();
        return $model->getAll();
    }

    public function ajouter() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $model = new Reservation();
            $data = [
                'utilisateur_id' => $_POST['utilisateur_id'],
                'terrain_id'     => $_POST['terrain_id'],
                'date'           => $_POST['date'],
                'heure_debut'    => $_POST['heure_debut'],
                'heure_fin'      => $_POST['heure_fin'],
                'montant'        => $_POST['montant'],
                'statut'         => 'en attente'
            ];
            $model->insert($data);
            header('Location: ListeReservation');
            exit();
        }
    }

    public function supprimer() {
        if (isset($_GET['id'])) {
            $model = new Reservation();
            $model->softDelete($_GET['id']);
            header('Location: ListeReservation');
            exit();
        }
    }

    public function restaurer() {
        if (isset($_GET['id'])) {
            $model = new Reservation();
            $model->restore($_GET['id']);
            header('Location: CorbeilleReservation');
            exit();
        }
    }
}
?>