<?php
require_once("model/DBRepository.php");
require_once("model/TerrainRepository.php");

// Suppression douce
if (isset($_POST['supprimer'])) {
    $model = new Terrain();
    $model->softDelete($_POST['supprimer']);
    header('Location: /FootBookingApp/ListeTerrain');
    exit();
}

// Modification
if (isset($_POST['modifier'])) {
    $model = new Terrain();
    $data = [
        'id'           => $_POST['id'],
        'nom'          => $_POST['nom'],
        'localisation' => $_POST['localisation'],
        'prix_petit'   => $_POST['prix_petit'],
        'prix_moitie'  => $_POST['prix_moitie'],
        'prix_grand'   => $_POST['prix_grand']
    ];
    $model->update($data);
    header('Location: /FootBookingApp/ListeTerrain');
    exit();
}

$model = new Terrain();
$terrains = $model->getAll();
?>

<div id="content" class="content">
    <ol class="breadcrumb float-xl-right">
        <li class="breadcrumb-item">
            <a href="CorbeilleTerrain" class="btn btn-sm btn-dark">
                <i class="fa fa-trash"></i> Corbeille
            </a>
        </li>
        <li class="breadcrumb-item active">Terrains</li>
    </ol>
    <h1 class="page-header">Terrains <small>liste complète</small></h1>

    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Liste des Terrains (<?= count($terrains) ?>)</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Nom</th>
                            <th>Localisation</th>
                            <th>Petit Terrain</th>
                            <th>Moitié Terrain</th>
                            <th>Grand Terrain</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($terrains as $t): ?>
                        <tr>
                            <td><?= $t['id'] ?></td>
                            <td><strong><?= htmlspecialchars($t['nom']) ?></strong></td>
                            <td><?= htmlspecialchars($t['localisation']) ?></td>
                            <td><?= $t['prix_petit'] ? number_format($t['prix_petit'], 0, ',', ' ') . ' FCFA' : '-' ?></td>
                            <td><?= $t['prix_moitie'] ? number_format($t['prix_moitie'], 0, ',', ' ') . ' FCFA' : '-' ?></td>
                            <td><?= $t['prix_grand'] ? number_format($t['prix_grand'], 0, ',', ' ') . ' FCFA' : '-' ?></td>
                            <td>
                                <!-- Modifier -->
                                <button class="btn btn-sm btn-warning" data-toggle="modal"
                                    data-target="#modal-modifier-<?= $t['id'] ?>">
                                    <i class="fa fa-edit"></i> Modifier
                                </button>
                                <!-- Supprimer -->
                                <form method="POST" action="/FootBookingApp/ListeTerrain" style="display:inline;">
                                    <input type="hidden" name="supprimer" value="<?= $t['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Mettre en corbeille ?')">
                                        <i class="fa fa-trash"></i> Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Modal Modifier -->
                        <div class="modal fade" id="modal-modifier-<?= $t['id'] ?>" data-backdrop="static">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Modifier - <?= htmlspecialchars($t['nom']) ?></h4>
                                        <button type="button" class="close" data-dismiss="modal">×</button>
                                    </div>
                                    <form method="POST" action="/FootBookingApp/ListeTerrain">
                                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>Nom</label>
                                                <input type="text" name="nom" class="form-control"
                                                       value="<?= htmlspecialchars($t['nom']) ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Localisation</label>
                                                <input type="text" name="localisation" class="form-control"
                                                       value="<?= htmlspecialchars($t['localisation']) ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Prix Petit Terrain (FCFA)</label>
                                                <input type="number" name="prix_petit" class="form-control"
                                                       value="<?= $t['prix_petit'] ?>">
                                            </div>
                                            <div class="form-group">
                                                <label>Prix Moitié Terrain (FCFA)</label>
                                                <input type="number" name="prix_moitie" class="form-control"
                                                       value="<?= $t['prix_moitie'] ?>">
                                            </div>
                                            <div class="form-group">
                                                <label>Prix Grand Terrain (FCFA)</label>
                                                <input type="number" name="prix_grand" class="form-control"
                                                       value="<?= $t['prix_grand'] ?>">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-danger" data-dismiss="modal">
                                                Annuler
                                            </button>
                                            <button type="submit" name="modifier" class="btn btn-warning">
                                                <i class="fa fa-save"></i> Enregistrer
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>