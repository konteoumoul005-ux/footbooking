<?php
require_once("model/DBRepository.php");
require_once("model/UtilisateurRepository.php");

// Restaurer
if (isset($_POST['restaurer'])) {
    $model = new Utilisateur();
    $model->restore($_POST['restaurer']);
    header('Location: /FootBookingApp/CorbeilleUtilisateur');
    exit();
}

// Supprimer définitivement
if (isset($_POST['supprimer_def'])) {
    $model = new Utilisateur();
    $model->delete($_POST['supprimer_def']);
    header('Location: /FootBookingApp/CorbeilleUtilisateur');
    exit();
}

$model = new Utilisateur();
$utilisateurs = $model->getCorbeille();
?>

<div id="content" class="content">
    <ol class="breadcrumb float-xl-right">
        <li class="breadcrumb-item">
            <a href="/FootBookingApp/ListeUtilisateur" class="btn btn-sm btn-success">
                <i class="fa fa-arrow-left"></i> Retour à la liste
            </a>
        </li>
        <li class="breadcrumb-item active">Corbeille</li>
    </ol>
    <h1 class="page-header">Corbeille <small>utilisateurs supprimés</small></h1>

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">
                Utilisateurs supprimés (<?= count($utilisateurs) ?>)
            </h4>
        </div>
        <div class="panel-body">
            <table class="table table-striped table-bordered table-td-valign-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($utilisateurs)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            Corbeille vide
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($utilisateurs as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['nom']) ?></td>
                        <td><?= htmlspecialchars($u['prenom']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['telephone']) ?></td>
                        <td>
                            <form method="POST" action="/FootBookingApp/CorbeilleUtilisateur" style="display:inline;">
                                <input type="hidden" name="restaurer" value="<?= $u['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="fa fa-undo"></i> Restaurer
                                </button>
                            </form>
                            <form method="POST" action="/FootBookingApp/CorbeilleUtilisateur" style="display:inline;">
                                <input type="hidden" name="supprimer_def" value="<?= $u['id'] ?>">
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