<?php
require_once("model/DBRepository.php");
require_once("model/UtilisateurRepository.php");

// Traitement ajout
if (isset($_POST['ajouter'])) {
    $model = new Utilisateur();
    $data = [
        'nom'          => $_POST['nom'],
        'prenom'       => $_POST['prenom'],
        'email'        => $_POST['email'],
        'mot_de_passe' => password_hash($_POST['password'], PASSWORD_DEFAULT),
        'telephone'    => $_POST['telephone'],
        'role'         => 'client'
    ];
    $model->insert($data);
    header('Location: /FootBookingApp/ListeUtilisateur');
    exit();
}

// Suppression douce
if (isset($_POST['supprimer'])) {
    $model = new Utilisateur();
    $model->softDelete($_POST['supprimer']);
    header('Location: /FootBookingApp/ListeUtilisateur');
    exit();
}

$model = new Utilisateur();
$utilisateurs = $model->getAll();
?>

<div id="content" class="content">
    <ol class="breadcrumb float-xl-right">
        <li class="breadcrumb-item">
            <a href="#modal-add-utilisateur" class="btn btn-sm btn-success" data-toggle="modal">
                <i class="fa fa-plus"></i> Ajouter
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="CorbeilleUtilisateur" class="btn btn-sm btn-dark">
                <i class="fa fa-trash"></i> Corbeille
            </a>
        </li>
        <li class="breadcrumb-item active">Utilisateurs</li>
    </ol>
    <h1 class="page-header">Utilisateurs</h1>

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">
                Liste des utilisateurs (<?= count($utilisateurs) ?>)
            </h4>
            <div class="panel-heading-btn">
                <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand">
                    <i class="fa fa-expand"></i>
                </a>
                <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-success" data-click="panel-reload">
                    <i class="fa fa-redo"></i>
                </a>
                <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-warning" data-click="panel-collapse">
                    <i class="fa fa-minus"></i>
                </a>
            </div>
        </div>
        <div class="panel-body">
            <table id="data-table-default" class="table table-striped table-bordered table-td-valign-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Rôle</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($utilisateurs)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            Aucun utilisateur pour le moment
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($utilisateurs as $u): ?>
                    <tr>
                        <td class="f-w-600"><?= $u['id'] ?></td>
                        <td>
                            <img src="public/templates/TemplateAdmin/assets/img/user/user-1.jpg"
                                 class="img-rounded"
                                 style="height:35px; width:35px; object-fit:cover;" />
                        </td>
                        <td><?= htmlspecialchars($u['nom']) ?></td>
                        <td><?= htmlspecialchars($u['prenom']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['telephone']) ?></td>
                        <td>
                            <span class="badge <?= $u['role'] == 'admin' ? 'badge-danger' : 'badge-success' ?>">
                                <?= $u['role'] ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" action="/FootBookingApp/ListeUtilisateur" style="display:inline;">
                                <input type="hidden" name="supprimer" value="<?= $u['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Mettre en corbeille ?')">
                                    <i class="fa fa-trash"></i>
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

<!-- Modal Ajouter Utilisateur -->
<div class="modal fade" id="modal-add-utilisateur" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Ajouter un utilisateur</h4>
                <button type="button" class="close" data-dismiss="modal">×</button>
            </div>
            <form method="POST" action="/FootBookingApp/ListeUtilisateur">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nom</label>
                        <input type="text" name="nom" class="form-control"
                               placeholder="Entrer le nom" required>
                    </div>
                    <div class="form-group">
                        <label>Prénom</label>
                        <input type="text" name="prenom" class="form-control"
                               placeholder="Entrer le prénom" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control"
                               placeholder="Entrer l'email" required>
                    </div>
                    <div class="form-group">
                        <label>Mot de passe</label>
                        <input type="password" name="password" class="form-control"
                               placeholder="Entrer le mot de passe" required>
                    </div>
                    <div class="form-group">
                        <label>Téléphone</label>
                        <input type="tel" name="telephone" class="form-control"
                               placeholder="Entrer le numéro" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                        Annuler
                    </button>
                    <button type="submit" name="ajouter" class="btn btn-success">
                        <i class="fa fa-plus"></i> Ajouter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>