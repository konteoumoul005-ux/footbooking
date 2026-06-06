<?php
require_once("model/DBRepository.php");

$db = new DBRepository();

// Suppression
if (isset($_POST['supprimer'])) {
    $stmt = $db->getPDO()->prepare("UPDATE disponibilite SET deleted_at = NOW() WHERE id = :id");
    $stmt->execute(['id' => $_POST['supprimer']]);
    header('Location: /FootBookingApp/ListeDisponibilite');
    exit();
}

// Modification
if (isset($_POST['modifier'])) {
    $stmt = $db->getPDO()->prepare("UPDATE disponibilite SET 
        terrain_id = :terrain_id,
        jour = :jour,
        heure_debut = :heure_debut,
        heure_fin = :heure_fin,
        statut = :statut
        WHERE id = :id");
    $stmt->execute([
        'terrain_id'  => $_POST['terrain_id'],
        'jour'        => $_POST['jour'],
        'heure_debut' => $_POST['heure_debut'],
        'heure_fin'   => $_POST['heure_fin'],
        'statut'      => $_POST['statut'],
        'id'          => $_POST['id']
    ]);
    header('Location: /FootBookingApp/ListeDisponibilite');
    exit();
}

try {
    $disponibilites = $db->query("
        SELECT d.*, t.nom as terrain 
        FROM disponibilite d
        JOIN terrain t ON d.terrain_id = t.id
        WHERE d.deleted_at IS NULL
        ORDER BY d.jour, d.heure_debut
    ")->fetchAll(PDO::FETCH_ASSOC);

    $terrains = $db->query("SELECT * FROM terrain WHERE deleted_at IS NULL")
                   ->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $disponibilites = [];
    $terrains = [];
}

$jours = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];
?>

<div id="content" class="content">
    <ol class="breadcrumb float-xl-right">
        <li class="breadcrumb-item">
            <a href="CorbeilleDisponibilite" class="btn btn-sm btn-dark">
                <i class="fa fa-trash"></i> Corbeille
            </a>
        </li>
        <li class="breadcrumb-item active">Disponibilités</li>
    </ol>
    <h1 class="page-header">Disponibilités <small>liste complète</small></h1>

    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Disponibilités des Terrains (<?= count($disponibilites) ?>)</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Terrain</th>
                            <th>Jour</th>
                            <th>Heure début</th>
                            <th>Heure fin</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($disponibilites)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Aucune disponibilité</td>
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
                                <?php if ($d['statut'] == 'disponible'): ?>
                                <span class="badge badge-success">Disponible</span>
                                <?php else: ?>
                                <span class="badge badge-danger">Occupé</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- Modifier -->
                                <button class="btn btn-sm btn-warning" data-toggle="modal"
                                    data-target="#modal-modifier-<?= $d['id'] ?>">
                                    <i class="fa fa-edit"></i> Modifier
                                </button>
                                <!-- Supprimer -->
                                <form method="POST" action="/FootBookingApp/ListeDisponibilite" style="display:inline;">
                                    <input type="hidden" name="supprimer" value="<?= $d['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Mettre en corbeille ?')">
                                        <i class="fa fa-trash"></i> Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Modal Modifier -->
                        <div class="modal fade" id="modal-modifier-<?= $d['id'] ?>" data-backdrop="static">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Modifier disponibilité</h4>
                                        <button type="button" class="close" data-dismiss="modal">×</button>
                                    </div>
                                    <form method="POST" action="/FootBookingApp/ListeDisponibilite">
                                        <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>Terrain</label>
                                                <select name="terrain_id" class="form-control" required>
                                                    <?php foreach ($terrains as $t): ?>
                                                    <option value="<?= $t['id'] ?>"
                                                        <?= $t['id'] == $d['terrain_id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($t['nom']) ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Jour</label>
                                                <select name="jour" class="form-control" required>
                                                    <?php foreach ($jours as $j): ?>
                                                    <option value="<?= $j ?>"
                                                        <?= $j == $d['jour'] ? 'selected' : '' ?>>
                                                        <?= $j ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Heure début</label>
                                                <input type="time" name="heure_debut" class="form-control"
                                                       value="<?= $d['heure_debut'] ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Heure fin</label>
                                                <input type="time" name="heure_fin" class="form-control"
                                                       value="<?= $d['heure_fin'] ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Statut</label>
                                                <select name="statut" class="form-control">
                                                    <option value="disponible" <?= $d['statut'] == 'disponible' ? 'selected' : '' ?>>Disponible</option>
                                                    <option value="occupe" <?= $d['statut'] == 'occupe' ? 'selected' : '' ?>>Occupé</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-danger" data-dismiss="modal">Annuler</button>
                                            <button type="submit" name="modifier" class="btn btn-warning">
                                                <i class="fa fa-save"></i> Enregistrer
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>