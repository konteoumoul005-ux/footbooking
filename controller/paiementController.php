<?php
require_once("model/Paiement.php");

class PaiementController {

    public function liste() {
        $model = new Paiement();
        return $model->getAll();
    }

    public function ajouter() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $model = new Paiement();
            $data = [
                'reservation_id' => $_POST['reservation_id'],
                'montant'        => $_POST['montant'],
                'moyen'          => $_POST['moyen'],
                'statut'         => 'en attente',
                'date'           => date('Y-m-d H:i:s')
            ];
            $model->insert($data);
            header('Location: ListePaiement');
            exit();
        }
    }

    public function supprimer() {
        if (isset($_GET['id'])) {
            $model = new Paiement();
            $model->softDelete($_GET['id']);
            header('Location: ListePaiement');
            exit();
        }
    }

    public function restaurer() {
        if (isset($_GET['id'])) {
            $model = new Paiement();
            $model->restore($_GET['id']);
            header('Location: CorbeillePaiement');
            exit();
        }
    }
}
?>