<?php
require_once("model/Disponibilite.php");

class DisponibiliteController {

    public function liste() {
        $model = new Disponibilite();
        return $model->getAll();
    }

    public function ajouter() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $model = new Disponibilite();
            $data = [
                'terrain_id'  => $_POST['terrain_id'],
                'jour'        => $_POST['jour'],
                'heure_debut' => $_POST['heure_debut'],
                'heure_fin'   => $_POST['heure_fin'],
                'statut'      => 'disponible'
            ];
            $model->insert($data);
            header('Location: ListeDisponibilite');
            exit();
        }
    }

    public function supprimer() {
        if (isset($_GET['id'])) {
            $model = new Disponibilite();
            $model->softDelete($_GET['id']);
            header('Location: ListeDisponibilite');
            exit();
        }
    }

    public function restaurer() {
        if (isset($_GET['id'])) {
            $model = new Disponibilite();
            $model->restore($_GET['id']);
            header('Location: CorbeilleDisponibilite');
            exit();
        }
    }
}
?>