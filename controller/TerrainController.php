<?php
require_once("model/Terrain.php");

class TerrainController {

    public function liste() {
        $model = new Terrain();
        return $model->getAll();
    }

    public function ajouter() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $model = new Terrain();
            $data = [
                'nom'          => $_POST['nom'],
                'localisation' => $_POST['localisation'],
                'type'         => $_POST['type'],
                'prix'         => $_POST['prix']
            ];
            $model->insert($data);
            header('Location: ListeTerrain');
            exit();
        }
    }

    public function supprimer() {
        if (isset($_GET['id'])) {
            $model = new Terrain();
            $model->softDelete($_GET['id']);
            header('Location: ListeTerrain');
            exit();
        }
    }

    public function restaurer() {
        if (isset($_GET['id'])) {
            $model = new Terrain();
            $model->restore($_GET['id']);
            header('Location: CorbeilleTerrain');
            exit();
        }
    }

    public function supprimerDefinitif() {
        if (isset($_GET['id'])) {
            $model = new Terrain();
            $model->delete($_GET['id']);
            header('Location: CorbeilleTerrain');
            exit();
        }
    }
}
?>