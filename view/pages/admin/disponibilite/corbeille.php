<?php
require_once("model/DBRepository.php");

$db = new DBRepository();

// Restaurer
if (isset($_POST['restaurer'])) {
    $stmt = $db->getPDO()->prepare("UPDATE disponibilite SET deleted_at = NULL WHERE id = :id");
    $stmt->execute(['id' => $_POST['restaurer']]);
    header('Location: /FootBookingApp/CorbeilleDisponibilite');
    exit();
}

// Supprimer définitivement
if (isset($_POST['supprimer_def'])) {
    $stmt = $db->getPDO()->prepare("DELETE FROM disponibilite WHERE id = :id");
    $stmt->execute(['id' => $_POST['supprimer_def']]);
    header('Location: /FootBookingApp/CorbeilleDisponibilite');
    exit();
}

try {
    $disponibilites = $db->query("
        SELECT d.*, t.nom as terrain 
        FROM disponibilite d
        JOIN terrain t ON d.terrain_id = t.id
        WHERE d.deleted_at IS NOT NULL
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $disponibilites = [];
}
?>

<div id="content" class="content">
    <ol class="breadcrumb float-xl-right">
        <li class="breadcrumb-item">
            <a href="/FootBookingApp/ListeDisponibilite" class="btn btn-sm btn-success">
                <i class="fa fa-arrow-left"></i> Retour
            </a>
        </li>
        <li class="breadcrumb-item active">Corbeille Disponibilités</li>
    </ol>
    <h1 class="page-header">Corbeille <small>disponibilités supprimées</small></h1>

    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Disponibilités supprimées (<?= count($disponibilites) ?>)</h4>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Terrain</th>
                        <th>Jour</th>
                        <th>Heure début</th>
                        <th>Heure fin</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($disponibilites)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">Corbeille vide</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($disponibilites as $d): ?>
                    <tr>
                        <td><?= $d['id'] ?></td>
                        <td><?= htmlspecialchars($d['terrain']) ?></td>
                        <td><?= htmlspecialchars($d['jour']) ?></td>
                        <td><?= $d['heure_debut'] ?></td>
                        <td><?= $d['heure_fin'] ?></td>
                        <td>
                            <form method="POST" action="/FootBookingApp/CorbeilleDisponibilite" style="display:inline;">
                                <input type="hidden" name="restaurer" value="<?= $d['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="fa fa-undo"></i> Restaurer
                                </button>
                            </form>
                            <form method="POST" action="/FootBookingApp/CorbeilleDisponibilite" style="display:inline;">
                                <input type="hidden" name="supprimer_def" value="<?= $d['id'] ?>">
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