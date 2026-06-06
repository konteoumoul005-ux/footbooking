<?php
require_once("model/DBRepository.php");
require_once("model/TerrainRepository.php");

// Restaurer
if (isset($_POST['restaurer'])) {
    $model = new Terrain();
    $model->restore($_POST['restaurer']);
    header('Location: /FootBookingApp/CorbeilleTerrain');
    exit();
}

// Supprimer définitivement
if (isset($_POST['supprimer_def'])) {
    $model = new Terrain();
    $model->delete($_POST['supprimer_def']);
    header('Location: /FootBookingApp/CorbeilleTerrain');
    exit();
}

$model = new Terrain();
$terrains = $model->getCorbeille();
?>

<div id="content" class="content">
    <ol class="breadcrumb float-xl-right">
        <li class="breadcrumb-item">
            <a href="/FootBookingApp/ListeTerrain" class="btn btn-sm btn-success">
                <i class="fa fa-arrow-left"></i> Retour
            </a>
        </li>
        <li class="breadcrumb-item active">Corbeille Terrains</li>
    </ol>
    <h1 class="page-header">Corbeille <small>terrains supprimés</small></h1>

    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Terrains supprimés (<?= count($terrains) ?>)</h4>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Localisation</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($terrains)): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">Corbeille vide</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($terrains as $t): ?>
                    <tr>
                        <td><?= $t['id'] ?></td>
                        <td><?= htmlspecialchars($t['nom']) ?></td>
                        <td><?= htmlspecialchars($t['localisation']) ?></td>
                        <td>
                            <form method="POST" action="/FootBookingApp/CorbeilleTerrain" style="display:inline;">
                                <input type="hidden" name="restaurer" value="<?= $t['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="fa fa-undo"></i> Restaurer
                                </button>
                            </form>
                            <form method="POST" action="/FootBookingApp/CorbeilleTerrain" style="display:inline;">
                                <input type="hidden" name="supprimer_def" value="<?= $t['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Supprimer définitivement ?')">
                                    <i class="fa fa-times"></i> Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>