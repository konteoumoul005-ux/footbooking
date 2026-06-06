<?php
error_reporting(0);
if (session_status() === PHP_SESSION_NONE) {
    session_name('user_session');
    session_start();
}
require_once("model/DBRepository.php");
require_once("model/TerrainRepository.php");

if (!isset($_SESSION['user'])) {
    header('Location: /FootBookingApp/login.php');
    exit();
}

$db = new DBRepository();
$terrain_id = $_GET['terrain_id'] ?? $_SESSION['terrain_id_en_cours'] ?? null;
$terrain = null;

if ($terrain_id) {
    $stmt = $db->getPDO()->prepare("SELECT * FROM terrain WHERE id = :id");
    $stmt->execute(['id' => $terrain_id]);
    $terrain = $stmt->fetch(PDO::FETCH_ASSOC);
}

$avances = ['petit' => 5000, 'moitie' => 10000, 'grand' => 15000];
$success = false;
$error = '';
$reservation_id_created = null;

if (isset($_POST['reserver'])) {
    if (empty($_POST['type_terrain'])) { $error = "Veuillez sélectionner un type de terrain."; }
    elseif (empty($_POST['date']))        { $error = "Veuillez choisir une date."; }
    elseif (empty($_POST['heure_debut'])) { $error = "Veuillez choisir une heure de début."; }
    elseif (empty($_POST['heure_fin']))   { $error = "Veuillez choisir une heure de fin."; }
    elseif (empty($_POST['moyen_paiement'])) { $error = "Veuillez choisir un moyen de paiement."; }
    else {
        try {
            $montant = 0;
            if ($_POST['type_terrain'] == 'petit')  $montant = $terrain['prix_petit'];
            if ($_POST['type_terrain'] == 'moitie') $montant = $terrain['prix_moitie'];
            if ($_POST['type_terrain'] == 'grand')  $montant = $terrain['prix_grand'];
            $avance = $avances[$_POST['type_terrain']];

            $stmt = $db->getPDO()->prepare("INSERT INTO reservation
                (utilisateur_id, terrain_id, date_reservation, heure_debut, heure_fin, type_terrain, montant, statut)
                VALUES (:utilisateur_id, :terrain_id, :date_reservation, :heure_debut, :heure_fin, :type_terrain, :montant, 'en_attente')");
            $stmt->execute([
                'utilisateur_id'   => $_SESSION['user']['id'],
                'terrain_id'       => $terrain_id,
                'date_reservation' => $_POST['date'],
                'heure_debut'      => $_POST['heure_debut'],
                'heure_fin'        => $_POST['heure_fin'],
                'type_terrain'     => $_POST['type_terrain'],
                'montant'          => $montant
            ]);
            $reservation_id_created = $db->getPDO()->lastInsertId();

            $stmt2 = $db->getPDO()->prepare("INSERT INTO paiement
                (reservation_id, montant, date_paiement, methode, statut)
                VALUES (:reservation_id, :montant, NOW(), :methode, 'en_attente')");
            $stmt2->execute([
                'reservation_id' => $reservation_id_created,
                'montant'        => $avance,
                'methode'        => $_POST['moyen_paiement']
            ]);

            $_SESSION['reservation_en_cours'] = $reservation_id_created;
            $_SESSION['avance_en_cours']       = $avance;
            $_SESSION['moyen_en_cours']        = $_POST['moyen_paiement'];
            $_SESSION['type_terrain_en_cours'] = $_POST['type_terrain'];
            $_SESSION['montant_total_en_cours']= $montant;
            $_SESSION['terrain_id_en_cours'] = $terrain_id;

        } catch (Exception $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}

if (isset($_POST['confirmer_paiement'])) {
    $res_id = $_SESSION['reservation_en_cours'] ?? null;
    if ($res_id) {
        $stmt3 = $db->getPDO()->prepare("UPDATE reservation SET statut='en_attente_validation' WHERE id=:id");
        $stmt3->execute(['id' => $res_id]);
        $stmt4 = $db->getPDO()->prepare("UPDATE paiement SET statut='en_attente_validation' WHERE reservation_id=:id");
        $stmt4->execute(['id' => $res_id]);
        unset($_SESSION['reservation_en_cours'], $_SESSION['avance_en_cours'],
              $_SESSION['moyen_en_cours'], $_SESSION['type_terrain_en_cours'],
              $_SESSION['montant_total_en_cours'], $_SESSION['terrain_id_en_cours']);
        $success = true;
    }
}

$images = [
    1 => 'public/images/WhatsApp Image 2026-04-25 at 10.40.06.jpeg',
    2 => 'public/images/WhatsApp Image 2026-04-25 at 10.38.13.jpeg',
    3 => 'public/images/WhatsApp Image 2026-04-25 at 10.38.20.jpeg',
    4 => 'public/images/WhatsApp Image 2026-04-25 at 10.39.15.jpeg',
    5 => 'public/images/WhatsApp Image 2026-04-25 at 10.42.33.jpeg',
    6 => 'public/images/WhatsApp Image 2026-05-02 at 02.28.00.jpeg',
    7 => 'public/images/WhatsApp Image 2026-04-25 at 10.43.26.jpeg',
    8 => 'public/images/WhatsApp Image 2026-05-02 at 02.33.43.jpeg',
];
$img = $images[$terrain_id] ?? 'public/images/WhatsApp Image 2026-04-25 at 10.40.06.jpeg';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <title>FootBooking - Réserver</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@300;400;600;700;800&display=swap" rel="stylesheet" />
    <link href="public/templates/templateVitrine/assets/css/one-page-parallax/app.min.css" rel="stylesheet" />
    <link href="public/css/dark-theme.css" rel="stylesheet" />
    <style>
        body { background:#060a0e; padding-top:60px; font-family:'Barlow',sans-serif; color:#e8edf2; }

        /* ── Boîte principale ── */
        .reservation-box {
            background:#0c1117;
            border:1px solid rgba(0,255,136,0.12);
            border-radius:20px;
            box-shadow:0 30px 80px rgba(0,0,0,0.7);
            overflow:hidden;
            margin:40px auto;
            max-width:940px;
        }
        .terrain-header { position:relative; height:230px; }
        .terrain-header img { width:100%; height:100%; object-fit:cover; filter:brightness(0.6) grayscale(20%); }
        .terrain-header .overlay {
            position:absolute; bottom:0; left:0; right:0;
            background:linear-gradient(transparent, rgba(6,10,14,0.95));
            padding:22px 28px; color:#fff;
        }
        .terrain-header .overlay h2 { margin:0; font-family:'Bebas Neue',sans-serif; font-size:30px; letter-spacing:2px; color:#fff; }
        .terrain-header .overlay p  { margin:4px 0 0; color:#6b7c93; font-size:13px; }
        .form-section { padding:32px 36px; }

        /* ── Sélection type terrain ── */
        .prix-option {
            border:2px solid rgba(0,255,136,0.12);
            border-radius:12px; padding:14px 18px;
            cursor:pointer; transition:all .25s;
            margin-bottom:10px; display:flex;
            align-items:center; justify-content:space-between;
            background:#111820; color:#e8edf2;
        }
        .prix-option:hover { border-color:#00ff88; background:rgba(0,255,136,0.05); }
        .prix-option.selected { border-color:#00ff88; background:rgba(0,255,136,0.07); box-shadow:0 0 20px rgba(0,255,136,0.1); }
        .prix-option strong { color:#e8edf2; }
        .prix-option .badge-type {
            background:rgba(0,255,136,0.15); color:#00ff88;
            font-size:11px; font-weight:700;
            padding:3px 8px; border-radius:20px;
        }

        /* ── Boîte avance ── */
        .avance-box {
            background:linear-gradient(135deg, rgba(0,255,136,0.1), rgba(0,204,106,0.06));
            border:1px solid rgba(0,255,136,0.25);
            border-radius:14px; padding:18px 22px;
            color:#fff; text-align:center; margin:16px 0;
        }
        .avance-box h2 { font-family:'Bebas Neue',sans-serif; font-size:38px; letter-spacing:1px; color:#00ff88; text-shadow:0 0 20px rgba(0,255,136,0.3); margin:6px 0 2px; }
        .avance-box small { color:#6b7c93; font-size:12px; }
        .avance-box > div { font-size:11px; text-transform:uppercase; letter-spacing:1.5px; color:#6b7c93; }

        /* ── Section title ── */
        .section-title { font-weight:700; font-size:11px; color:#6b7c93; margin-bottom:12px; letter-spacing:1.5px; text-transform:uppercase; }

        /* ── Paiement options ── */
        .paiement-option {
            border:2px solid rgba(0,255,136,0.12);
            border-radius:14px; padding:14px 18px;
            cursor:pointer; transition:all .25s;
            margin-bottom:10px; display:flex; align-items:center;
            background:#111820;
        }
        .paiement-option:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,0,0,0.4); }
        .paiement-option.wave-opt    { border-color:rgba(29,161,242,0.3); }
        .paiement-option.wave-opt:hover, .paiement-option.wave-opt.selected { border-color:#1DA1F2; background:rgba(29,161,242,0.07); }
        .paiement-option.om-opt      { border-color:rgba(255,102,0,0.3); }
        .paiement-option.om-opt:hover, .paiement-option.om-opt.selected { border-color:#FF6600; background:rgba(255,102,0,0.07); }
        .paiement-option .pay-logo {
            width:48px; height:48px; border-radius:12px;
            display:flex; align-items:center; justify-content:center;
            font-weight:900; font-size:14px; color:#fff; margin-right:14px;
            flex-shrink:0;
        }
        .paiement-option .pay-name   { font-weight:700; font-size:15px; }
        .paiement-option .pay-sub    { font-size:12px; color:#6b7c93; }

        /* ── QR panels ── */
        .qr-panel {
            display:none;
            border-radius:16px; padding:24px;
            text-align:center; margin-top:16px;
            animation:fadeSlide .35s ease;
        }
        .qr-panel.wave-panel { border:2px solid rgba(29,161,242,0.4); background:rgba(29,161,242,0.06); }
        .qr-panel.om-panel   { border:2px solid rgba(255,102,0,0.4);  background:rgba(255,102,0,0.06); }
        @keyframes fadeSlide {
            from { opacity:0; transform:translateY(14px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .qr-panel img { width:190px; height:190px; object-fit:contain; border-radius:10px; margin:0 auto 14px; display:block; border:3px solid rgba(255,255,255,0.08); box-shadow:0 8px 30px rgba(0,0,0,0.5); }
        .qr-panel .qr-title { font-size:14px; font-weight:700; margin-bottom:6px; }
        .qr-panel .qr-amount { font-size:28px; font-weight:800; font-family:'Bebas Neue',sans-serif; letter-spacing:1px; }
        .qr-panel .qr-number { font-size:13px; margin-top:6px; color:#6b7c93; }

        /* ── Bouton continuer ── */
        .btn-continuer {
            background:#00ff88; color:#060a0e; border:none; border-radius:12px;
            padding:16px 30px; font-size:14px; font-weight:800;
            letter-spacing:1px; text-transform:uppercase;
            width:100%; cursor:pointer; transition:all .3s;
            box-shadow:0 0 24px rgba(0,255,136,0.25); margin-top:12px;
            font-family:'Barlow',sans-serif;
        }
        .btn-continuer:hover { background:#fff; box-shadow:0 0 40px rgba(0,255,136,0.4); transform:translateY(-2px); }

        /* ── Résumé ── */
        .resume-box {
            background:#111820; border-radius:14px;
            padding:18px 20px; margin-top:16px;
            border:1px solid rgba(0,255,136,0.12);
        }
        .resume-row { display:flex; justify-content:space-between; margin-bottom:8px; font-size:14px; border-bottom:1px solid rgba(255,255,255,0.04); padding-bottom:8px; }
        .resume-row:last-child { margin-bottom:0; border-bottom:none; padding-bottom:0; }
        .resume-row span { color:#6b7c93; }

        /* Form controls */
        .form-section label { font-size:11px; color:#6b7c93; font-weight:700; letter-spacing:1px; text-transform:uppercase; display:block; margin-bottom:7px; }
        .form-section .form-control, .form-section select.form-control {
            background:#111820; border:1px solid rgba(0,255,136,0.12);
            color:#e8edf2; border-radius:9px; padding:12px 16px; font-size:14px;
            font-family:'Barlow',sans-serif; transition:all .2s; width:100%;
        }
        .form-section .form-control:focus, .form-section select.form-control:focus {
            border-color:#00ff88; box-shadow:0 0 0 3px rgba(0,255,136,0.1); outline:none;
        }
        .form-section select.form-control option { background:#111820; color:#e8edf2; }

        /* ── Error ── */
        .alert-danger-dark {
            background:rgba(255,77,109,0.1); color:#ff4d6d;
            border:1px solid rgba(255,77,109,0.25);
            border-radius:10px; padding:14px 18px; margin-bottom:20px; font-size:14px;
        }

        /* ══════════════════════════════
           ÉTAPE 2 : QR CODE STEP
        ══════════════════════════════ */
        .step-pay-card {
            background:#0c1117; border:1px solid rgba(0,255,136,0.12);
            border-radius:20px; box-shadow:0 30px 80px rgba(0,0,0,0.7);
            max-width:560px; margin:40px auto; overflow:hidden;
        }
        .step-pay-header {
            background:linear-gradient(135deg, #0c1117, #16202c);
            border-bottom:1px solid rgba(0,255,136,0.12);
            padding:24px 28px; color:#fff; text-align:center;
        }
        .step-pay-header h4 { margin:0; font-family:'Bebas Neue',sans-serif; font-size:24px; letter-spacing:2px; color:#fff; }
        .step-pay-header p  { margin:6px 0 0; color:#6b7c93; font-size:13px; }
        .step-pay-body { padding:28px; }

        .pay-method-badge {
            display:inline-flex; align-items:center; gap:8px;
            border-radius:30px; padding:8px 18px;
            font-weight:700; font-size:13px; margin-bottom:20px;
        }
        .wave-badge { background:rgba(29,161,242,0.1); color:#1DA1F2; border:2px solid rgba(29,161,242,0.4); }
        .om-badge   { background:rgba(255,102,0,0.1);  color:#FF6600; border:2px solid rgba(255,102,0,0.4); }

        .qr-block-wave { border:2px solid rgba(29,161,242,0.35); border-radius:16px; background:rgba(29,161,242,0.06); padding:24px; text-align:center; margin-bottom:20px; }
        .qr-block-om   { border:2px solid rgba(255,102,0,0.35);  border-radius:16px; background:rgba(255,102,0,0.06);  padding:24px; text-align:center; margin-bottom:20px; }

        .step-qr-img { width:190px; height:190px; object-fit:contain; border-radius:10px; display:block; margin:0 auto 14px; border:3px solid rgba(255,255,255,0.06); box-shadow:0 8px 30px rgba(0,0,0,0.5); }

        .btn-app-wave { display:inline-block; background:#1DA1F2; color:#fff; border-radius:10px; padding:10px 24px; font-weight:800; text-decoration:none; font-size:13px; transition:all .3s; }
        .btn-app-wave:hover { background:#0d8de0; color:#fff; transform:translateY(-2px); box-shadow:0 8px 24px rgba(29,161,242,0.35); }
        .btn-app-om { display:inline-block; background:#FF6600; color:#fff; border-radius:10px; padding:10px 24px; font-weight:800; text-decoration:none; font-size:13px; transition:all .3s; }
        .btn-app-om:hover { background:#e05500; color:#fff; transform:translateY(-2px); box-shadow:0 8px 24px rgba(255,102,0,0.35); }

        .paid-box { background:rgba(245,166,35,0.08); border:1px solid rgba(245,166,35,0.3); border-radius:14px; padding:22px; text-align:center; }
        .paid-box p { color:#f5a623; margin-bottom:12px; font-weight:600; font-size:14px; }
        .paid-box .sub { color:#6b7c93; font-size:13px; margin-bottom:18px; font-weight:400; }
        .btn-jpaye {
            background:#00ff88; color:#060a0e; border:none; border-radius:12px;
            padding:16px 30px; font-size:14px; font-weight:800;
            letter-spacing:1px; text-transform:uppercase;
            width:100%; cursor:pointer; transition:all .3s;
            box-shadow:0 0 24px rgba(0,255,136,0.25);
            font-family:'Barlow',sans-serif;
        }
        .btn-jpaye:hover { background:#fff; box-shadow:0 0 40px rgba(0,255,136,0.4); }

        /* ══════════════════════════════
           PAGE CONFIRMATION FINALE
        ══════════════════════════════ */
        .confirm-page {
            min-height:calc(100vh - 120px);
            display:flex; align-items:center; justify-content:center;
            padding:40px 20px;
        }
        .confirm-card {
            background:#0c1117;
            border:1px solid rgba(0,255,136,0.15);
            border-radius:24px;
            box-shadow:0 40px 100px rgba(0,0,0,0.7), 0 0 60px rgba(0,255,136,0.04);
            max-width:520px; width:100%;
            text-align:center; padding:50px 40px;
            position:relative; overflow:hidden;
        }
        .confirm-card::before {
            content:'';
            position:absolute; top:0; left:0; right:0; height:3px;
            background:linear-gradient(90deg,#00ff88,#00ccff,#00ff88);
            background-size:200%;
            animation:shimmer 2.5s linear infinite;
        }
        @keyframes shimmer { to { background-position:200% 0; } }

        .confirm-icon-wrap {
            width:88px; height:88px;
            background:linear-gradient(135deg,rgba(0,255,136,0.2),rgba(0,204,106,0.1));
            border:2px solid rgba(0,255,136,0.4);
            border-radius:50%; display:flex; align-items:center;
            justify-content:center; margin:0 auto 28px;
            box-shadow:0 0 40px rgba(0,255,136,0.2);
            animation:popIn .5s cubic-bezier(.175,.885,.32,1.275);
        }
        @keyframes popIn { from{transform:scale(0);opacity:0;} to{transform:scale(1);opacity:1;} }
        .confirm-icon-wrap i { font-size:36px; color:#00ff88; }

        .confirm-card h2 { font-family:'Bebas Neue',sans-serif; font-size:32px; letter-spacing:2px; color:#fff; margin-bottom:10px; }
        .confirm-card .confirm-sub { color:#6b7c93; font-size:15px; margin-bottom:28px; line-height:1.7; }

        .status-pill {
            display:inline-flex; align-items:center; gap:8px;
            background:rgba(245,166,35,0.1); color:#f5a623;
            border:2px solid rgba(245,166,35,0.3); border-radius:30px;
            padding:10px 24px; font-weight:700; font-size:13px; letter-spacing:0.5px;
            margin-bottom:28px;
            animation:pulsePill 2s ease-in-out infinite;
        }
        @keyframes pulsePill {
            0%,100%{box-shadow:0 0 0 0 rgba(245,166,35,0.2);}
            50%{box-shadow:0 0 0 8px rgba(245,166,35,0);}
        }
        .status-pill .dot { width:8px; height:8px; border-radius:50%; background:#f5a623; animation:blink 1.4s ease infinite; }
        @keyframes blink { 0%,100%{opacity:1;} 50%{opacity:.2;} }

        .confirm-info-box {
            background:#111820; border-radius:14px;
            padding:18px 22px; margin-bottom:28px;
            text-align:left; border:1px solid rgba(0,255,136,0.1);
        }
        .confirm-info-row { display:flex; justify-content:space-between; padding:9px 0; font-size:14px; border-bottom:1px solid rgba(255,255,255,0.04); }
        .confirm-info-row:last-child { border-bottom:none; }
        .confirm-info-row .label { color:#6b7c93; }
        .confirm-info-row .val   { font-weight:700; color:#e8edf2; }

        .confirm-btns { display:flex; gap:12px; flex-wrap:wrap; }
        .confirm-btns a { flex:1; min-width:130px; padding:13px 20px; border-radius:12px; font-weight:700; font-size:13px; letter-spacing:0.5px; text-transform:uppercase; text-align:center; text-decoration:none; transition:all .25s; }
        .btn-primary-green { background:#00ff88; color:#060a0e; box-shadow:0 0 20px rgba(0,255,136,0.25); }
        .btn-primary-green:hover { background:#fff; color:#060a0e; box-shadow:0 0 40px rgba(0,255,136,0.4); transform:translateY(-2px); }
        .btn-outline-dark { background:transparent; color:#e8edf2; border:1px solid rgba(255,255,255,0.12); }
        .btn-outline-dark:hover { background:rgba(255,255,255,0.05); color:#e8edf2; transform:translateY(-2px); }
    </style>
</head>
<body>
<?php
$reservation_en_cours_save = $_SESSION["reservation_en_cours"] ?? null;
$avance_en_cours_save      = $_SESSION["avance_en_cours"] ?? null;
$moyen_en_cours_save       = $_SESSION["moyen_en_cours"] ?? null;
require_once("view/sections/vitrine/menu.php");
if ($reservation_en_cours_save) $_SESSION["reservation_en_cours"] = $reservation_en_cours_save;
if ($avance_en_cours_save)      $_SESSION["avance_en_cours"]      = $avance_en_cours_save;
if ($moyen_en_cours_save)       $_SESSION["moyen_en_cours"]       = $moyen_en_cours_save;
?>

<div class="container">

<?php if ($success): ?>
<!-- PAGE CONFIRMATION FINALE -->
<div class="confirm-page">
    <div class="confirm-card">
        <div class="confirm-icon-wrap">
            <i class="fa fa-check"></i>
        </div>
        <h2>Demande envoyée !</h2>
        <p class="confirm-sub">
            Votre réservation a bien été reçue.<br>
            Le paiement est en cours de vérification.
        </p>
        <div class="status-pill">
            <span class="dot"></span>
            En attente de validation
        </div>
        <div class="confirm-info-box">
            <div class="confirm-info-row">
                <span class="label"><i class="fa fa-user mr-1"></i> Client</span>
                <span class="val"><?= htmlspecialchars($_SESSION['user']['prenom'] . ' ' . $_SESSION['user']['nom']) ?></span>
            </div>
            <div class="confirm-info-row">
                <span class="label"><i class="fa fa-info-circle mr-1"></i> Statut</span>
                <span class="val" style="color:#f5a623;">⏳ En attente de validation</span>
            </div>
            <div class="confirm-info-row">
                <span class="label"><i class="fa fa-envelope mr-1"></i> Notification</span>
                <span class="val" style="color:#00ff88;">Par email dès confirmation</span>
            </div>
        </div>
        <p style="font-size:13px; color:#6b7c93; margin-bottom:28px; line-height:1.7;">
            Un administrateur va vérifier votre paiement et confirmer votre réservation.<br>
            Vous recevrez un email de confirmation automatique.
        </p>
        <div class="confirm-btns">
            <a href="terrains" class="btn-primary-green"><i class="fa fa-futbol mr-1"></i> Voir les terrains</a>
            <a href="monprofil" class="btn-outline-dark"><i class="fa fa-user mr-1"></i> Mon profil</a>
        </div>
    </div>
</div>

<?php elseif ($reservation_id_created): ?>
<!-- ÉTAPE 2 : QR CODE + CONFIRMER -->
<div class="step-pay-card">
    <div class="step-pay-header">
        <h4><i class="fa fa-mobile-alt mr-2"></i> Effectuez votre paiement</h4>
        <p>Scannez le QR code ou ouvrez votre app</p>
    </div>
    <div class="step-pay-body">

        <?php if ($_SESSION['moyen_en_cours'] === 'wave'): ?>
        <div class="text-center">
            <span class="pay-method-badge wave-badge">
                <span style="font-weight:900; font-size:16px;">W</span> Paiement Wave
            </span>
        </div>
        <div class="qr-block-wave">
            <p class="qr-title" style="color:#1DA1F2; font-weight:700; margin-bottom:14px;"><i class="fa fa-qrcode"></i> Scannez avec l'app Wave</p>
            <img src="public/images/wave_qr.png" alt="QR Code Wave" class="step-qr-img" />
            <div style="font-family:'Bebas Neue',sans-serif; font-size:32px; letter-spacing:1px; color:#1DA1F2; margin-bottom:6px;">
                <?= number_format($_SESSION['avance_en_cours'], 0, ',', ' ') ?> FCFA
            </div>
            <div style="font-size:13px; color:#6b7c93; margin-bottom:16px;">
                Numéro : <strong style="color:#1DA1F2;">77 565 02 03</strong>
            </div>
            <a href="https://www.wave.com/send/?phone=221775650203&amount=<?= $_SESSION['avance_en_cours'] ?>&memo=FootBooking+Reservation"
               class="btn-app-wave"><span style="font-weight:900; margin-right:6px;">W</span> Ouvrir l'app Wave →</a>
        </div>

        <?php else: ?>
        <div class="text-center">
            <span class="pay-method-badge om-badge">
                <span style="font-weight:900;">OM</span> Orange Money
            </span>
        </div>
        <div class="qr-block-om">
            <p class="qr-title" style="color:#FF6600; font-weight:700; margin-bottom:14px;"><i class="fa fa-qrcode"></i> Scannez avec Max-it / Orange Money</p>
            <img src="public/images/om_qr.jpeg" alt="QR Code Orange Money" class="step-qr-img" />
            <div style="font-family:'Bebas Neue',sans-serif; font-size:32px; letter-spacing:1px; color:#FF6600; margin-bottom:16px;">
                <?= number_format($_SESSION['avance_en_cours'], 0, ',', ' ') ?> FCFA
            </div>
            <a href="tel:*144*1*775650203*<?= $_SESSION['avance_en_cours'] ?>%23" class="btn-app-om">
                <span style="font-weight:900; margin-right:6px;">OM</span> Composer le code USSD →
            </a>
        </div>
        <?php endif; ?>

        <div class="paid-box">
            <p><i class="fa fa-check-circle mr-1"></i> Après avoir payé</p>
            <p class="sub">Revenez ici et cliquez sur le bouton ci-dessous pour valider votre réservation.</p>
            <form method="POST" action="reserver?terrain_id=<?= $_SESSION['terrain_id_en_cours'] ?? $terrain_id ?>">
                <button type="submit" name="confirmer_paiement" class="btn-jpaye">
                    <i class="fa fa-check mr-2"></i> J'ai payé — Confirmer ma réservation
                </button>
            </form>
        </div>

    </div>
</div>

<?php elseif ($terrain): ?>
<!-- ÉTAPE 1 : FORMULAIRE -->
<div class="reservation-box">
    <div class="terrain-header">
        <img src="<?= $img ?>" alt="<?= htmlspecialchars($terrain['nom']) ?>" />
        <div class="overlay">
            <h2><?= htmlspecialchars($terrain['nom']) ?></h2>
            <p><i class="fa fa-map-marker-alt"></i> <?= htmlspecialchars($terrain['localisation']) ?></p>
        </div>
    </div>

    <div class="form-section">
        <h4 class="mb-4" style="font-family:'Bebas Neue',sans-serif; font-size:24px; letter-spacing:2px; color:#e8edf2;">Réservez ce terrain</h4>

        <?php if ($error): ?>
        <div class="alert-danger-dark"><i class="fa fa-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="POST" id="form-reservation">
            <input type="hidden" id="type_terrain_hidden"   name="type_terrain"    value="">
            <input type="hidden" id="moyen_paiement_hidden" name="moyen_paiement"  value="">

            <div class="row">
                <!-- COLONNE GAUCHE -->
                <div class="col-lg-6">
                    <p class="section-title">1. Type de terrain</p>

                    <?php if (!empty($terrain['prix_petit'])): ?>
                    <div class="prix-option" onclick="selectType(this,'petit',<?= $avances['petit'] ?>,<?= $terrain['prix_petit'] ?>)">
                        <div>
                            <strong>Petit Terrain</strong>
                            <div style="font-size:12px; color:#6b7c93; margin-top:3px;">5 vs 5 · Avance : 5 000 FCFA</div>
                        </div>
                        <div style="text-align:right;">
                            <span class="badge-type">5v5</span>
                            <div style="font-weight:700; color:#00ff88; font-size:14px; margin-top:4px;"><?= number_format($terrain['prix_petit'],0,',',' ') ?> FCFA</div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($terrain['prix_moitie'])): ?>
                    <div class="prix-option" onclick="selectType(this,'moitie',<?= $avances['moitie'] ?>,<?= $terrain['prix_moitie'] ?>)">
                        <div>
                            <strong>Moitié de Terrain</strong>
                            <div style="font-size:12px; color:#6b7c93; margin-top:3px;">7 vs 7 · Avance : 10 000 FCFA</div>
                        </div>
                        <div style="text-align:right;">
                            <span class="badge-type" style="background:rgba(52,152,219,0.15); color:#5ba8e8;">7v7</span>
                            <div style="font-weight:700; color:#5ba8e8; font-size:14px; margin-top:4px;"><?= number_format($terrain['prix_moitie'],0,',',' ') ?> FCFA</div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($terrain['prix_grand'])): ?>
                    <div class="prix-option" onclick="selectType(this,'grand',<?= $avances['grand'] ?>,<?= $terrain['prix_grand'] ?>)">
                        <div>
                            <strong>Grand Terrain</strong>
                            <div style="font-size:12px; color:#6b7c93; margin-top:3px;">11 vs 11 · Avance : 15 000 FCFA</div>
                        </div>
                        <div style="text-align:right;">
                            <span class="badge-type" style="background:rgba(230,126,34,0.15); color:#e67e22;">11v11</span>
                            <div style="font-weight:700; color:#e67e22; font-size:14px; margin-top:4px;"><?= number_format($terrain['prix_grand'],0,',',' ') ?> FCFA</div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Avance dynamique -->
                    <div id="avance-box" style="display:none;" class="avance-box">
                        <div>Avance à payer maintenant</div>
                        <h2 id="avance-montant"></h2>
                        <small>Le reste sera réglé sur place</small>
                    </div>

                    <!-- Section paiement -->
                    <div id="section-paiement" style="display:none;" class="mt-3">
                        <p class="section-title">3. Moyen de paiement</p>

                        <div class="paiement-option wave-opt" onclick="selectPaiement(this,'wave')">
                            <div class="pay-logo" style="background:#1DA1F2;">W</div>
                            <div>
                                <div class="pay-name" style="color:#1DA1F2;">Wave</div>
                                <div class="pay-sub">Paiement mobile instantané</div>
                            </div>
                        </div>

                        <div id="qr-wave" class="qr-panel wave-panel">
                            <p class="qr-title" style="color:#1DA1F2;"><i class="fa fa-qrcode"></i> Scannez avec l'app Wave</p>
                            <img src="public/images/wave_qr.png" alt="QR Code Wave" />
                            <div class="qr-amount" id="wave-amount" style="color:#1DA1F2;"></div>
                            <div class="qr-number">Numéro : <strong style="color:#1DA1F2;">77 565 02 03</strong></div>
                            <div style="margin-top:12px;">
                                <a id="wave-app-link" href="#" class="btn-app-wave">
                                    <span style="font-weight:900; margin-right:4px;">W</span> Ouvrir Wave →
                                </a>
                            </div>
                        </div>

                        <div class="paiement-option om-opt" onclick="selectPaiement(this,'orange_money')">
                            <div class="pay-logo" style="background:#FF6600;">OM</div>
                            <div>
                                <div class="pay-name" style="color:#FF6600;">Orange Money</div>
                                <div class="pay-sub">Paiement mobile sécurisé</div>
                            </div>
                        </div>

                        <div id="qr-om" class="qr-panel om-panel">
                            <p class="qr-title" style="color:#FF6600;"><i class="fa fa-qrcode"></i> Scannez avec Max-it / Orange Money</p>
                            <img src="public/images/om_qr.jpeg" alt="QR Code Orange Money" />
                            <div class="qr-amount" id="om-amount" style="color:#FF6600;"></div>
                        </div>
                    </div>
                </div>

                <!-- COLONNE DROITE -->
                <div class="col-lg-6">
                    <p class="section-title">2. Date &amp; Horaires</p>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="date" class="form-control" min="<?= date('Y-m-d') ?>" value="<?= $_POST['date'] ?? '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Heure de début</label>
                        <select name="heure_debut" class="form-control" required>
                            <option value="">Choisir</option>
                            <?php for ($h = 8; $h <= 22; $h++): ?>
                            <option value="<?= sprintf('%02d:00',$h) ?>"><?= sprintf('%02d:00',$h) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Heure de fin</label>
                        <select name="heure_fin" class="form-control" required>
                            <option value="">Choisir</option>
                            <?php for ($h = 9; $h <= 23; $h++): ?>
                            <option value="<?= sprintf('%02d:00',$h) ?>"><?= sprintf('%02d:00',$h) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div id="resume-final" style="display:none;" class="resume-box">
                        <div style="font-weight:700; font-size:11px; color:#6b7c93; letter-spacing:1.5px; text-transform:uppercase; margin-bottom:12px;">
                            <i class="fa fa-receipt mr-1" style="color:#00ff88;"></i> Récapitulatif
                        </div>
                        <div class="resume-row"><span>Prix total</span><strong id="prix-total-affiche" style="color:#e8edf2;"></strong></div>
                        <div class="resume-row"><span style="color:#00ff88;">Avance maintenant</span><strong id="avance-affiche" style="color:#00ff88;"></strong></div>
                        <div class="resume-row"><span>Reste sur place</span><strong id="reste-affiche" style="color:#6b7c93;"></strong></div>
                    </div>
                </div>
            </div>

            <div id="boutons-paiement" style="display:none;" class="mt-4">
                <button type="submit" name="reserver" class="btn-continuer">
                    <i class="fa fa-arrow-right mr-2"></i> Continuer vers la confirmation
                </button>
            </div>
        </form>
    </div>
</div>

<?php else: ?>
<div class="alert-danger-dark mt-4">Terrain introuvable. <a href="terrains" style="color:#00ff88;">Retour aux terrains</a></div>
<?php endif; ?>

</div>

<?php require_once("view/sections/vitrine/footer.php"); ?>
<script src="public/templates/templateVitrine/assets/js/one-page-parallax/app.min.js"></script>
<script>
var currentAvance = 0, currentPrixTotal = 0, currentPaiement = null;
function fmt(n) { return new Intl.NumberFormat('fr-FR').format(n); }

function selectType(el, type, avance, prixTotal) {
    document.querySelectorAll('.prix-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('type_terrain_hidden').value = type;
    currentAvance = avance; currentPrixTotal = prixTotal;
    document.getElementById('avance-box').style.display = 'block';
    document.getElementById('avance-montant').textContent = fmt(avance) + ' FCFA';
    document.getElementById('section-paiement').style.display = 'block';
    document.getElementById('resume-final').style.display = 'block';
    document.getElementById('prix-total-affiche').textContent = fmt(prixTotal) + ' FCFA';
    document.getElementById('avance-affiche').textContent = fmt(avance) + ' FCFA';
    document.getElementById('reste-affiche').textContent = fmt(prixTotal - avance) + ' FCFA';
    document.getElementById('wave-amount').textContent = fmt(avance) + ' FCFA';
    document.getElementById('om-amount').textContent = fmt(avance) + ' FCFA';
    document.getElementById('wave-app-link').href = 'https://www.wave.com/send/?phone=221775650203&amount=' + avance + '&memo=FootBooking+Reservation';
    if (currentPaiement) updateQR(currentPaiement);
}

function selectPaiement(el, type) {
    document.querySelectorAll('.paiement-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('moyen_paiement_hidden').value = type;
    currentPaiement = type;
    updateQR(type);
    document.getElementById('boutons-paiement').style.display = 'block';
}

function updateQR(type) {
    var qrWave = document.getElementById('qr-wave');
    var qrOm   = document.getElementById('qr-om');
    qrWave.style.display = 'none'; qrOm.style.display = 'none';
    if (type === 'wave') {
        qrWave.style.display = 'block';
        qrWave.style.animation = 'none'; void qrWave.offsetWidth; qrWave.style.animation = '';
    } else {
        qrOm.style.display = 'block';
        qrOm.style.animation = 'none'; void qrOm.offsetWidth; qrOm.style.animation = '';
    }
}
</script>
</body>
</html>