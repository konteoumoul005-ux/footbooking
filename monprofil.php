<?php
session_name('user_session');
session_start();
require_once("model/DBRepository.php");
require_once("model/UtilisateurRepository.php");

if (!isset($_SESSION['user'])) {
    header('Location: /FootBookingApp/login.php');
    exit();
}

if ($_SESSION['user']['role'] === 'admin') {
    header('Location: /FootBookingApp/Profil');
    exit();
}

$db    = new DBRepository();
$model = new Utilisateur();
$user  = $_SESSION['user'];

if (isset($user['id'])) {
    $user_id = $user['id'];
} else {
    $tmpUser = $model->findByEmail($user['email']);
    $user_id = $tmpUser['id'];
    $_SESSION['user'] = $tmpUser;
    $user = $tmpUser;
}

$success = '';
$error   = '';

// Modifier les infos
if (isset($_POST['modifier'])) {
    $nom       = htmlspecialchars(trim($_POST['nom']));
    $prenom    = htmlspecialchars(trim($_POST['prenom']));
    $email     = htmlspecialchars(trim($_POST['email']));
    $telephone = htmlspecialchars(trim($_POST['telephone']));

    $photo = $user['photo'] ?? '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($_FILES['photo']['type'], $allowed)) {
            $ext      = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $filename = 'profil_' . $user_id . '.' . $ext;
            $dest     = 'public/images/profils/' . $filename;
            if (!is_dir('public/images/profils/')) mkdir('public/images/profils/', 0755, true);
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
                $photo = $filename;
            }
        }
    }

    $motDePasse = $user['mot_de_passe'];
    if (!empty($_POST['password'])) {
        if (strlen($_POST['password']) < 6) {
            $error = "Le mot de passe doit contenir au moins 6 caractères.";
        } else {
            $motDePasse = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }
    }

    if (!$error) {
        try {
            $stmt = $db->getPDO()->prepare("UPDATE utilisateur SET nom=:nom, prenom=:prenom, email=:email, telephone=:telephone, mot_de_passe=:mot_de_passe, photo=:photo WHERE id=:id");
            $stmt->execute([
                'nom'          => $nom,
                'prenom'       => $prenom,
                'email'        => $email,
                'telephone'    => $telephone,
                'mot_de_passe' => $motDePasse,
                'photo'        => $photo,
                'id'           => $user_id
            ]);
            $_SESSION['user']['nom']       = $nom;
            $_SESSION['user']['prenom']    = $prenom;
            $_SESSION['user']['email']     = $email;
            $_SESSION['user']['telephone'] = $telephone;
            $_SESSION['user']['photo']     = $photo;
            $user    = $_SESSION['user'];
            $success = "Profil mis à jour avec succès !";
        } catch (Exception $e) {
            $error = "Erreur lors de la mise à jour.";
        }
    }
}

// Historique des réservations
try {
    $stmtRes = $db->getPDO()->prepare("
        SELECT r.*, t.nom as terrain_nom, t.localisation,
               p.methode as moyen_paiement, p.statut as statut_paiement, p.montant as avance_payee
        FROM reservation r
        JOIN terrain t ON r.terrain_id = t.id
        LEFT JOIN paiement p ON p.reservation_id = r.id
        WHERE r.utilisateur_id = :uid
        ORDER BY r.date_reservation DESC, r.id DESC
    ");
    $stmtRes->execute(['uid' => $user_id]);
    $reservations = $stmtRes->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $reservations = [];
}

$nomComplet = trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''));
$photoSrc   = !empty($user['photo'])
    ? '/FootBookingApp/public/images/profils/' . $user['photo']
    : 'https://ui-avatars.com/api/?name=' . urlencode($nomComplet) . '&background=00ff88&color=060a0e&size=128&bold=true';

$typeLabels  = ['petit' => '5v5 · Petit', 'moitie' => '7v7 · Moitié', 'grand' => '11v11 · Grand'];
$statutBadge = [
    'en_attente'            => ['color' => '#f5a623', 'bg' => 'rgba(245,166,35,0.12)',  'border' => 'rgba(245,166,35,0.3)',   'label' => '⏳ En attente'],
    'en_attente_validation' => ['color' => '#5ba8e8', 'bg' => 'rgba(91,168,232,0.12)',  'border' => 'rgba(91,168,232,0.3)',   'label' => '🔍 Paiement à vérifier'],
    'confirmee'             => ['color' => '#00ff88', 'bg' => 'rgba(0,255,136,0.1)',    'border' => 'rgba(0,255,136,0.3)',    'label' => '✅ Confirmée'],
    'confirme'              => ['color' => '#00ff88', 'bg' => 'rgba(0,255,136,0.1)',    'border' => 'rgba(0,255,136,0.3)',    'label' => '✅ Confirmée'],
    'annulee'               => ['color' => '#ff4d6d', 'bg' => 'rgba(255,77,109,0.1)',   'border' => 'rgba(255,77,109,0.3)',   'label' => '❌ Annulée'],
];

$totalReservations = count($reservations);
$totalConfirmees   = count(array_filter($reservations, fn($r) => in_array($r['statut'], ['confirmee','confirme'])));
$totalDepense      = array_sum(array_column(array_filter($reservations, fn($r) => in_array($r['statut'], ['confirmee','confirme'])), 'montant'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <title>Mon Profil - FootBooking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@300;400;600;700;800&display=swap" rel="stylesheet" />
    <link href="public/templates/templateVitrine/assets/css/one-page-parallax/app.min.css" rel="stylesheet" />
    <link href="public/css/dark-theme.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #060a0e;
            background-image:
                radial-gradient(ellipse 70% 50% at 0% 100%, rgba(0,255,136,0.04) 0%, transparent 60%),
                radial-gradient(ellipse 40% 40% at 100% 0%, rgba(0,180,255,0.03) 0%, transparent 60%);
            min-height: 100vh;
            font-family: 'Barlow', sans-serif;
            color: #e8edf2;
            padding-top: 70px;
        }

        /* ── Wrapper ── */
        .profil-wrapper { max-width: 1080px; margin: 0 auto; padding: 40px 20px 80px; }

        /* ── Page header ── */
        .page-header-row {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 32px; flex-wrap: wrap; gap: 12px;
        }
        .page-header-row h1 {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 36px; letter-spacing: 3px; color: #fff;
        }
        .page-header-row h1 span { color: #00ff88; }
        .breadcrumb-row { color: #6b7c93; font-size: 13px; }
        .breadcrumb-row a { color: #00ff88; text-decoration: none; }

        /* ── Alerts ── */
        .alert { padding: 14px 18px; border-radius: 10px; margin-bottom: 24px; font-size: 14px; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: rgba(0,255,136,0.1); color: #00ff88; border: 1px solid rgba(0,255,136,0.3); }
        .alert-danger  { background: rgba(255,77,109,0.1); color: #ff4d6d; border: 1px solid rgba(255,77,109,0.3); }

        /* ── Grid layout ── */
        .main-grid { display: grid; grid-template-columns: 300px 1fr; gap: 24px; }
        @media (max-width: 768px) { .main-grid { grid-template-columns: 1fr; } }

        /* ── Card ── */
        .card {
            background: #0c1117;
            border: 1px solid rgba(0,255,136,0.1);
            border-radius: 16px;
            overflow: hidden;
        }
        .card-header-bar {
            padding: 16px 22px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            font-weight: 700; font-size: 13px; color: #e8edf2;
            display: flex; align-items: center; gap: 8px;
            text-transform: uppercase; letter-spacing: 1px;
        }
        .card-header-bar i { color: #00ff88; }
        .card-body { padding: 22px; }

        /* ── Avatar card ── */
        .avatar-top {
            background: linear-gradient(135deg, #0d1f15, #111820);
            border-bottom: 1px solid rgba(0,255,136,0.1);
            padding: 32px 20px 24px;
            text-align: center;
            position: relative;
        }
        .avatar-ring {
            width: 110px; height: 110px; border-radius: 50%;
            border: 3px solid rgba(0,255,136,0.4);
            box-shadow: 0 0 30px rgba(0,255,136,0.15);
            margin: 0 auto 8px;
            position: relative; overflow: hidden; cursor: pointer;
        }
        .avatar-ring img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .avatar-ring .avatar-overlay {
            position: absolute; inset: 0;
            background: rgba(0,255,136,0.25);
            display: flex; align-items: center; justify-content: center;
            opacity: 0; transition: opacity .25s;
        }
        .avatar-ring:hover .avatar-overlay { opacity: 1; }
        .avatar-ring .avatar-overlay i { color: #fff; font-size: 22px; }
        .avatar-name { font-family: 'Bebas Neue', sans-serif; font-size: 22px; letter-spacing: 2px; color: #fff; margin-top: 10px; }
        .badge-role {
            display: inline-block; background: rgba(0,255,136,0.15);
            color: #00ff88; border: 1px solid rgba(0,255,136,0.3);
            padding: 3px 14px; border-radius: 20px; font-size: 11px;
            font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-top: 8px;
        }

        .avatar-info { padding: 20px 22px; }
        .info-row {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 0; border-bottom: 1px solid rgba(255,255,255,0.04);
            font-size: 13px; color: #9aacbc;
        }
        .info-row:last-child { border-bottom: none; }
        .info-row i { color: #00ff88; width: 16px; flex-shrink: 0; }
        .info-row strong { color: #e8edf2; margin-left: auto; text-align: right; font-size: 13px; }

        /* ── Stats mini ── */
        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; padding: 16px 22px; border-top: 1px solid rgba(255,255,255,0.05); }
        .stat-mini { text-align: center; }
        .stat-mini .val { font-family: 'Bebas Neue', sans-serif; font-size: 24px; color: #00ff88; }
        .stat-mini .lbl { font-size: 10px; color: #6b7c93; text-transform: uppercase; letter-spacing: 1px; }

        /* ── Form ── */
        .form-2col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        @media (max-width: 500px) { .form-2col { grid-template-columns: 1fr; } }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 11px; font-weight: 700; color: #6b7c93; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 7px; }
        .form-control {
            width: 100%; background: #111820;
            border: 1px solid rgba(0,255,136,0.12);
            color: #e8edf2; border-radius: 9px;
            padding: 11px 14px; font-size: 14px;
            font-family: 'Barlow', sans-serif; transition: all .2s;
        }
        .form-control:focus { border-color: #00ff88; box-shadow: 0 0 0 3px rgba(0,255,136,0.1); outline: none; }
        .divider { border: none; border-top: 1px solid rgba(255,255,255,0.06); margin: 18px 0; }
        .btn-save {
            width: 100%; padding: 14px; background: #00ff88; color: #060a0e;
            border: none; border-radius: 10px; font-weight: 800; font-size: 14px;
            letter-spacing: 1px; text-transform: uppercase; cursor: pointer;
            transition: all .3s; box-shadow: 0 0 20px rgba(0,255,136,0.2);
            font-family: 'Barlow', sans-serif; margin-top: 6px;
        }
        .btn-save:hover { background: #fff; box-shadow: 0 0 40px rgba(0,255,136,0.4); transform: translateY(-2px); }

        /* ── Historique ── */
        .historique-section { margin-top: 28px; }
        .historique-section .section-title {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 26px; letter-spacing: 2px; color: #fff;
            margin-bottom: 16px;
        }
        .historique-section .section-title span { color: #00ff88; }

        /* Reservation card */
        .res-card {
            background: #0c1117;
            border: 1px solid rgba(0,255,136,0.08);
            border-radius: 14px;
            padding: 18px 20px;
            margin-bottom: 14px;
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 16px;
            align-items: start;
            transition: border-color .2s;
        }
        .res-card:hover { border-color: rgba(0,255,136,0.2); }

        .res-icon {
            width: 48px; height: 48px; border-radius: 12px;
            background: rgba(0,255,136,0.1); border: 1px solid rgba(0,255,136,0.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; color: #00ff88; flex-shrink: 0;
        }

        .res-body .res-terrain { font-weight: 700; font-size: 15px; color: #e8edf2; margin-bottom: 4px; }
        .res-body .res-loc { font-size: 12px; color: #6b7c93; margin-bottom: 8px; }
        .res-body .res-meta { display: flex; flex-wrap: wrap; gap: 10px; }
        .res-meta-item { font-size: 12px; color: #9aacbc; display: flex; align-items: center; gap: 5px; }
        .res-meta-item i { color: #00ff88; font-size: 11px; }

        .res-right { text-align: right; flex-shrink: 0; }
        .res-montant { font-family: 'Bebas Neue', sans-serif; font-size: 20px; color: #00ff88; letter-spacing: 1px; }
        .res-avance  { font-size: 11px; color: #6b7c93; margin-top: 2px; }

        .statut-pill {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 12px; border-radius: 20px;
            font-size: 11px; font-weight: 700; letter-spacing: 0.5px;
            margin-top: 8px; white-space: nowrap;
        }
        .statut-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }

        .empty-state {
            text-align: center; padding: 50px 20px;
            background: #0c1117; border: 1px dashed rgba(0,255,136,0.15);
            border-radius: 14px;
        }
        .empty-state i { font-size: 40px; color: rgba(0,255,136,0.3); margin-bottom: 14px; }
        .empty-state p { color: #6b7c93; font-size: 14px; }
        .empty-state a {
            display: inline-block; margin-top: 14px; padding: 10px 24px;
            background: #00ff88; color: #060a0e; border-radius: 8px;
            font-weight: 700; font-size: 13px; text-decoration: none;
            transition: all .25s;
        }
        .empty-state a:hover { background: #fff; }

        /* Filtres statuts */
        .filter-tabs { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 18px; }
        .filter-tab {
            padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 700;
            border: 1px solid rgba(255,255,255,0.1); background: transparent;
            color: #9aacbc; cursor: pointer; transition: all .2s;
        }
        .filter-tab:hover, .filter-tab.active { background: rgba(0,255,136,0.1); border-color: rgba(0,255,136,0.3); color: #00ff88; }
    </style>
</head>
<body>
<?php require_once("view/sections/vitrine/menu.php"); ?>

<div class="profil-wrapper">

    <!-- Header -->
    <div class="page-header-row">
        <div>
            <div class="breadcrumb-row"><a href="index.php">Accueil</a> / Mon Profil</div>
            <h1>Mon <span>Profil</span></h1>
        </div>
        <a href="terrains" style="background:rgba(0,255,136,0.1); color:#00ff88; border:1px solid rgba(0,255,136,0.3); padding:10px 22px; border-radius:10px; text-decoration:none; font-weight:700; font-size:13px; letter-spacing:0.5px;">
            <i class="fa fa-futbol mr-1"></i> Réserver un terrain
        </a>
    </div>

    <!-- Alerts -->
    <?php if ($success): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i><?= $error ?></div>
    <?php endif; ?>

    <!-- Main grid -->
    <form method="POST" enctype="multipart/form-data">
    <div class="main-grid">

        <!-- ── Colonne gauche : Avatar + Infos ── -->
        <div>
            <div class="card">
                <!-- Avatar -->
                <div class="avatar-top">
                    <label for="photo-input" style="cursor:pointer;">
                        <div class="avatar-ring">
                            <img id="preview-photo" src="<?= htmlspecialchars($photoSrc) ?>" alt="Photo" />
                            <div class="avatar-overlay"><i class="fas fa-camera"></i></div>
                        </div>
                    </label>
                    <input type="file" id="photo-input" name="photo" accept="image/*" style="display:none;" onchange="previewPhoto(this)" />
                    <div class="avatar-name"><?= htmlspecialchars($nomComplet) ?></div>
                    <span class="badge-role"><i class="fas fa-user mr-1"></i> Utilisateur</span>
                </div>

                <!-- Infos -->
                <div class="avatar-info">
                    <div class="info-row"><i class="fas fa-envelope"></i> Email <strong><?= htmlspecialchars($user['email'] ?? '-') ?></strong></div>
                    <div class="info-row"><i class="fas fa-phone"></i> Téléphone <strong><?= htmlspecialchars($user['telephone'] ?? 'Non renseigné') ?></strong></div>
                    <div class="info-row"><i class="fas fa-calendar-alt"></i> Inscrit le <strong><?= isset($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : '-' ?></strong></div>
                </div>

                <!-- Stats mini -->
                <div class="stats-row">
                    <div class="stat-mini">
                        <div class="val"><?= $totalReservations ?></div>
                        <div class="lbl">Réservations</div>
                    </div>
                    <div class="stat-mini">
                        <div class="val"><?= $totalConfirmees ?></div>
                        <div class="lbl">Confirmées</div>
                    </div>
                    <div class="stat-mini">
                        <div class="val" style="font-size:16px;"><?= number_format($totalDepense/1000, 0) ?>k</div>
                        <div class="lbl">FCFA dépensés</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Colonne droite : Formulaire ── -->
        <div class="card">
            <div class="card-header-bar">
                <i class="fas fa-user-edit"></i> Modifier mes informations
            </div>
            <div class="card-body">
                <div class="form-2col">
                    <div class="form-group">
                        <label>Prénom</label>
                        <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($user['prenom'] ?? '') ?>" required />
                    </div>
                    <div class="form-group">
                        <label>Nom</label>
                        <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($user['nom'] ?? '') ?>" required />
                    </div>
                </div>
                <div class="form-group">
                    <label>Adresse email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required />
                </div>
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="tel" name="telephone" class="form-control" value="<?= htmlspecialchars($user['telephone'] ?? '') ?>" placeholder="Ex: 77 000 00 00" />
                </div>
                <hr class="divider" />
                <div class="form-group">
                    <label><i class="fas fa-lock" style="color:#e67e22; margin-right:5px;"></i> Nouveau mot de passe</label>
                    <input type="password" name="password" class="form-control" placeholder="Laisser vide pour ne pas modifier" />
                </div>
                <button type="submit" name="modifier" class="btn-save">
                    <i class="fas fa-save mr-2"></i> Enregistrer les modifications
                </button>
            </div>
        </div>

    </div>
    </form>

    <!-- ══════════════════════════════
         HISTORIQUE DES RÉSERVATIONS
    ══════════════════════════════ -->
    <div class="historique-section">
        <div class="section-title">Historique des <span>Réservations</span></div>

        <?php if (empty($reservations)): ?>
        <div class="empty-state">
            <i class="fa fa-calendar-times"></i>
            <p>Vous n'avez pas encore de réservation.</p>
            <a href="terrains"><i class="fa fa-futbol mr-1"></i> Réserver un terrain</a>
        </div>
        <?php else: ?>

        <!-- Filtres -->
        <div class="filter-tabs">
            <button class="filter-tab active" onclick="filtrer(this,'all')">Toutes (<?= $totalReservations ?>)</button>
            <button class="filter-tab" onclick="filtrer(this,'confirmee')">✅ Confirmées</button>
            <button class="filter-tab" onclick="filtrer(this,'en_attente_validation')">🔍 À vérifier</button>
            <button class="filter-tab" onclick="filtrer(this,'en_attente')">⏳ En attente</button>
            <button class="filter-tab" onclick="filtrer(this,'annulee')">❌ Annulées</button>
        </div>

        <div id="res-list">
        <?php foreach ($reservations as $r):
            $s   = $r['statut'];
            $sb  = $statutBadge[$s] ?? ['color'=>'#9aacbc','bg'=>'rgba(150,150,150,0.1)','border'=>'rgba(150,150,150,0.2)','label'=>ucfirst($s)];
            $tp  = $typeLabels[$r['type_terrain']] ?? $r['type_terrain'];
            $mp  = ['wave'=>'Wave','orange_money'=>'Orange Money'][$r['moyen_paiement']] ?? ($r['moyen_paiement'] ?? '-');
            $statut_filter = in_array($s, ['confirmee','confirme']) ? 'confirmee' : $s;
        ?>
        <div class="res-card" data-statut="<?= $statut_filter ?>">
            <div class="res-icon"><i class="fa fa-futbol"></i></div>
            <div class="res-body">
                <div class="res-terrain"><?= htmlspecialchars($r['terrain_nom']) ?></div>
                <div class="res-loc"><i class="fa fa-map-marker-alt" style="color:#00ff88;margin-right:4px;"></i><?= htmlspecialchars($r['localisation']) ?></div>
                <div class="res-meta">
                    <span class="res-meta-item"><i class="fa fa-calendar-alt"></i><?= date('d/m/Y', strtotime($r['date_reservation'])) ?></span>
                    <span class="res-meta-item"><i class="fa fa-clock"></i><?= $r['heure_debut'] ?> → <?= $r['heure_fin'] ?? '?' ?></span>
                    <span class="res-meta-item"><i class="fa fa-layer-group"></i><?= $tp ?></span>
                    <span class="res-meta-item"><i class="fa fa-mobile-alt"></i><?= $mp ?></span>
                </div>
            </div>
            <div class="res-right">
                <div class="res-montant"><?= number_format($r['montant'], 0, ',', ' ') ?> <span style="font-size:13px;color:#6b7c93;">FCFA</span></div>
                <?php if (!empty($r['avance_payee'])): ?>
                <div class="res-avance">Avance : <?= number_format($r['avance_payee'], 0, ',', ' ') ?> FCFA</div>
                <?php endif; ?>
                <div>
                    <span class="statut-pill" style="color:<?= $sb['color'] ?>;background:<?= $sb['bg'] ?>;border:1px solid <?= $sb['border'] ?>;">
                        <span class="statut-dot" style="background:<?= $sb['color'] ?>;"></span>
                        <?= $sb['label'] ?>
                    </span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        </div>

        <?php endif; ?>
    </div>

</div><!-- /profil-wrapper -->

<?php require_once("view/sections/vitrine/footer.php"); ?>
<script src="public/templates/templateVitrine/assets/js/one-page-parallax/app.min.js"></script>
<script>
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-photo').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function filtrer(btn, statut) {
    document.querySelectorAll('.filter-tab').forEach(function(b) { b.classList.remove('active'); });
    btn.classList.add('active');
    document.querySelectorAll('.res-card').forEach(function(card) {
        if (statut === 'all' || card.dataset.statut === statut) {
            card.style.display = 'grid';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>
</body>
</html>