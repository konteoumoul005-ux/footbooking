<?php
require_once("model/DBRepository.php");
require_once("model/UtilisateurRepository.php");

$model = new Utilisateur();
$db = new DBRepository();

// Récupérer depuis session admin ou user
$sessionUser = $_SESSION['admin'] ?? $_SESSION['user'] ?? null;

if (isset($sessionUser['id'])) {
    $user_id = $sessionUser['id'];
} elseif (isset($sessionUser['email'])) {
    $tmpUser = $model->findByEmail($sessionUser['email']);
    $user_id = $tmpUser['id'];
    $_SESSION['admin'] = $tmpUser;
} else {
    echo '<div class="alert alert-danger">Session expirée. <a href="/FootBookingApp/admin_login.php">Reconnectez-vous</a></div>';
    return;
}

$success = '';
$error   = '';

$success = '';
$error = '';

// Modifier les infos personnelles
if (isset($_POST['update_infos'])) {
    $nom      = htmlspecialchars(trim($_POST['nom']));
    $prenom   = htmlspecialchars(trim($_POST['prenom']));
    $email    = htmlspecialchars(trim($_POST['email']));
    $telephone = htmlspecialchars(trim($_POST['telephone']));

    try {
        $stmt = $db->getPDO()->prepare("UPDATE utilisateur SET nom=:nom, prenom=:prenom, email=:email, telephone=:telephone WHERE id=:id");
        $stmt->execute(['nom' => $nom, 'prenom' => $prenom, 'email' => $email, 'telephone' => $telephone, 'id' => $user_id]);
        // Mettre à jour la session
        $_SESSION['user']['nom']       = $nom;
        $_SESSION['user']['prenom']    = $prenom;
        $_SESSION['user']['email']     = $email;
        $_SESSION['user']['telephone'] = $telephone;
        $success = "Informations mises à jour avec succès !";
    } catch (Exception $e) {
        $error = "Erreur lors de la mise à jour.";
    }
}

// Modifier le mot de passe
if (isset($_POST['update_password'])) {
    $ancien = $_POST['ancien_mdp'];
    $nouveau = $_POST['nouveau_mdp'];
    $confirm = $_POST['confirm_mdp'];

    $userDB = $model->findByEmail($_SESSION['user']['email']);

    if (!password_verify($ancien, $userDB['mot_de_passe'])) {
        $error = "Ancien mot de passe incorrect.";
    } elseif ($nouveau !== $confirm) {
        $error = "Les nouveaux mots de passe ne correspondent pas.";
    } elseif (strlen($nouveau) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        try {
            $hash = password_hash($nouveau, PASSWORD_DEFAULT);
            $stmt = $db->getPDO()->prepare("UPDATE utilisateur SET mot_de_passe=:mdp WHERE id=:id");
            $stmt->execute(['mdp' => $hash, 'id' => $user_id]);
            $success = "Mot de passe modifié avec succès !";
        } catch (Exception $e) {
            $error = "Erreur lors du changement de mot de passe.";
        }
    }
}

// Upload photo de profil
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (in_array($_FILES['photo']['type'], $allowed)) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename = 'profil_' . $user_id . '.' . $ext;
        $dest = 'public/images/profils/' . $filename;

        if (!is_dir('public/images/profils/')) {
            mkdir('public/images/profils/', 0755, true);
        }

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
            $stmt = $db->getPDO()->prepare("UPDATE utilisateur SET photo=:photo WHERE id=:id");
            $stmt->execute(['photo' => $filename, 'id' => $user_id]);
            $_SESSION['user']['photo'] = $filename;
            $success = "Photo de profil mise à jour !";
        }
    } else {
        $error = "Format non autorisé. Utilisez JPG, PNG ou GIF.";
    }
}

// Recharger les infos depuis la DB
$stmt = $db->getPDO()->prepare("SELECT * FROM utilisateur WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$photo = isset($user['photo']) && $user['photo'] ? 'public/images/profils/' . $user['photo'] : 'public/templates/TemplateAdmin/assets/img/user/user-13.jpg';
?>

<div id="content" class="content">
    <ol class="breadcrumb float-xl-right">
        <li class="breadcrumb-item"><a href="javascript:;">Accueil</a></li>
        <li class="breadcrumb-item active">Mon Profil</li>
    </ol>
    <h1 class="page-header mb-4">Mon Profil <small>Gérer mes informations</small></h1>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa fa-check-circle mr-2"></i><?= $success ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fa fa-exclamation-circle mr-2"></i><?= $error ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <div class="row">

        <!-- Carte photo de profil -->
        <div class="col-lg-4">
            <div class="card text-center mb-4" style="border-radius:12px;">
                <div class="card-body p-4">
                    <img src="<?= htmlspecialchars($photo) ?>"
                         alt="Photo de profil"
                         id="preview-photo"
                         style="width:120px; height:120px; border-radius:50%; object-fit:cover; border:4px solid #1abc9c; margin-bottom:15px;" />

                    <h4 class="mb-0"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h4>
                    <p class="text-muted mb-3"><?= htmlspecialchars(ucfirst($user['role'])) ?></p>

                    <label for="photo" class="btn btn-outline-success btn-sm btn-block" style="cursor:pointer;">
                        <i class="fa fa-camera mr-1"></i> Changer la photo
                    </label>
                    <input type="file" name="photo" id="photo" accept="image/*" style="display:none;"
                           onchange="uploadPhoto(this)" />
                    <div id="photo-msg" style="margin-top:8px; font-size:13px;"></div>

                    <hr />
                    <div class="text-left" style="font-size:14px;">
                        <p class="mb-2"><i class="fa fa-envelope text-success mr-2"></i><?= htmlspecialchars($user['email']) ?></p>
                        <p class="mb-2"><i class="fa fa-phone text-success mr-2"></i><?= htmlspecialchars($user['telephone'] ?? 'Non renseigné') ?></p>
                        <p class="mb-0"><i class="fa fa-calendar text-success mr-2"></i>Inscrit le <?= date('d/m/Y', strtotime($user['created_at'])) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulaires -->
        <div class="col-lg-8">

            <!-- Modifier les infos -->
            <div class="card mb-4" style="border-radius:12px;">
                <div class="card-header" style="background:#f8f9fa; border-radius:12px 12px 0 0;">
                    <h5 class="mb-0"><i class="fa fa-user-edit text-success mr-2"></i>Modifier mes informations</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Nom</label>
                                    <input type="text" name="nom" class="form-control"
                                           value="<?= htmlspecialchars($user['nom']) ?>" required />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Prénom</label>
                                    <input type="text" name="prenom" class="form-control"
                                           value="<?= htmlspecialchars($user['prenom']) ?>" required />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Email</label>
                                    <input type="email" name="email" class="form-control"
                                           value="<?= htmlspecialchars($user['email']) ?>" required />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Téléphone</label>
                                    <input type="tel" name="telephone" class="form-control"
                                           value="<?= htmlspecialchars($user['telephone'] ?? '') ?>" />
                                </div>
                            </div>
                        </div>
                        <button type="submit" name="update_infos" class="btn btn-success">
                            <i class="fa fa-save mr-1"></i> Enregistrer les modifications
                        </button>
                    </form>
                </div>
            </div>

            <!-- Changer le mot de passe -->
            <div class="card mb-4" style="border-radius:12px;">
                <div class="card-header" style="background:#f8f9fa; border-radius:12px 12px 0 0;">
                    <h5 class="mb-0"><i class="fa fa-lock text-warning mr-2"></i>Changer le mot de passe</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="form-group">
                            <label class="font-weight-bold">Ancien mot de passe</label>
                            <input type="password" name="ancien_mdp" class="form-control"
                                   placeholder="Votre mot de passe actuel" required />
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Nouveau mot de passe</label>
                                    <input type="password" name="nouveau_mdp" class="form-control"
                                           placeholder="Minimum 6 caractères" required />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Confirmer le mot de passe</label>
                                    <input type="password" name="confirm_mdp" class="form-control"
                                           placeholder="Répétez le nouveau mot de passe" required />
                                </div>
                            </div>
                        </div>
                        <button type="submit" name="update_password" class="btn btn-warning text-white">
                            <i class="fa fa-key mr-1"></i> Changer le mot de passe
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function uploadPhoto(input) {
    if (!input.files || !input.files[0]) return;
    var reader = new FileReader();
    reader.onload = function(e) { document.getElementById("preview-photo").src = e.target.result; };
    reader.readAsDataURL(input.files[0]);
    var formData = new FormData();
    formData.append("photo", input.files[0]);
    var msg = document.getElementById("photo-msg");
    msg.innerHTML = "<span style="color:#888;"><i class="fa fa-spinner fa-spin"></i> Envoi...</span>";
    fetch("upload_photo.php", { method: "POST", body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) { msg.innerHTML = "<span style="color:green;"><i class="fa fa-check"></i> Photo mise à jour !</span>"; }
        else { msg.innerHTML = "<span style="color:red;">" + data.error + "</span>"; }
    })
    .catch(function() { msg.innerHTML = "<span style="color:red;">Erreur upload.</span>"; });
}
</script>