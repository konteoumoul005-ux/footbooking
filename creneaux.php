<?php
session_start();
require_once("model/DBRepository.php");
require_once("model/TerrainRepository.php");

$db = new DBRepository();
$model = new Terrain();
$terrains = $model->getAll();

// On récupère juste les plages horaires réservées (sans nom ni infos perso)
$plages = [];
try {
    $result = $db->query("
        SELECT terrain_id, date_reservation, heure_debut, heure_fin
        FROM reservation
        WHERE statut != 'annulee'
    ")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($result as $r) {
        $plages[$r['terrain_id']][] = [
            'date'  => $r['date_reservation'],
            'debut' => substr($r['heure_debut'], 0, 5),
            'fin'   => substr($r['heure_fin'], 0, 5),
        ];
    }
} catch (Exception $e) {
    $plages = [];
}

$horaires = ['08:00','09:00','10:00','11:00','12:00','13:00','14:00',
             '15:00','16:00','17:00','18:00','19:00','20:00','21:00','22:00'];

// Date filtrée (aujourd'hui par défaut)
$dateFiltre = $_GET['date'] ?? date('Y-m-d');

// Fonction : est-ce qu'un créneau est occupé pour un terrain à une date ?
function estOccupe($plages, $terrain_id, $date, $heure) {
    if (!isset($plages[$terrain_id])) return false;
    foreach ($plages[$terrain_id] as $p) {
        if ($p['date'] === $date && $heure >= $p['debut'] && $heure < $p['fin']) {
            return true;
        }
    }
    return false;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <title>FootBooking - Créneaux</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@300;400;600;700;800&display=swap" rel="stylesheet" />
    <link href="public/templates/templateVitrine/assets/css/one-page-parallax/app.min.css" rel="stylesheet" />
    <link href="public/css/dark-theme.css" rel="stylesheet" />
    <style>
        body { background:#060a0e; padding-top:60px; font-family:'Barlow',sans-serif; color:#e8edf2; }

        .page-header-section {
            background: linear-gradient(rgba(6,10,14,0.8), rgba(6,10,14,0.8)), url('public/images/bg-home.jpg');
            background-size:cover; background-position:center;
            padding:70px 0 40px; text-align:center;
            border-bottom:1px solid rgba(0,255,136,0.12); margin-bottom:40px;
        }
        .page-header-section h1 {
            font-family:'Bebas Neue',sans-serif; font-size:56px;
            letter-spacing:3px; color:#fff; margin-bottom:10px;
        }
        .page-header-section p { color:rgba(232,237,242,0.6); font-size:15px; }

        .filter-bar {
            background:#0c1117; border:1px solid rgba(0,255,136,0.12);
            border-radius:14px; padding:20px 24px; margin-bottom:28px;
        }
        .filter-bar label {
            font-size:11px; font-weight:700; letter-spacing:1.5px;
            text-transform:uppercase; color:#6b7c93; display:block; margin-bottom:7px;
        }
        .filter-bar .form-control, .filter-bar select.form-control {
            background:#111820; border:1px solid rgba(0,255,136,0.12);
            color:#e8edf2; border-radius:9px; padding:11px 14px;
            font-size:14px; font-family:'Barlow',sans-serif; transition:all .2s;
        }
        .filter-bar .form-control:focus { border-color:#00ff88; box-shadow:0 0 0 3px rgba(0,255,136,0.1); outline:none; }
        .filter-bar select option { background:#111820; }
        .btn-reserver-filter {
            display:block; width:100%;
            background:#00ff88; color:#060a0e; border:none;
            padding:12px; border-radius:9px; font-weight:800; font-size:12px;
            letter-spacing:1.5px; text-transform:uppercase; text-align:center;
            text-decoration:none; transition:all .3s; box-shadow:0 0 20px rgba(0,255,136,0.2);
        }
        .btn-reserver-filter:hover { background:#fff; color:#060a0e; }

        /* Légende */
        .legende {
            display:flex; gap:20px; margin-bottom:24px; flex-wrap:wrap;
        }
        .legende-item { display:flex; align-items:center; gap:8px; font-size:13px; color:#6b7c93; }
        .legende-dot { width:14px; height:14px; border-radius:4px; flex-shrink:0; }
        .dot-libre  { background:rgba(0,255,136,0.2); border:1px solid rgba(0,255,136,0.4); }
        .dot-occupe { background:rgba(255,77,109,0.2); border:1px solid rgba(255,77,109,0.4); }

        /* Terrain card */
        .terrain-section {
            background:#0c1117; border:1px solid rgba(0,255,136,0.12);
            border-radius:14px; margin-bottom:24px; overflow:hidden;
        }
        .terrain-title {
            background:#111820; border-bottom:1px solid rgba(0,255,136,0.1);
            padding:16px 22px; display:flex; justify-content:space-between; align-items:center;
        }
        .terrain-title h5 {
            margin:0; font-family:'Barlow',sans-serif; font-weight:800; font-size:16px; color:#e8edf2;
        }
        .terrain-title small { color:#6b7c93; font-size:12px; }
        .btn-reserver-small {
            background:rgba(0,255,136,0.1); color:#00ff88;
            border:1px solid rgba(0,255,136,0.3); padding:7px 16px;
            border-radius:8px; font-size:12px; font-weight:700;
            text-decoration:none; transition:all .25s; white-space:nowrap;
        }
        .btn-reserver-small:hover { background:#00ff88; color:#060a0e; }

        .creneaux-body { padding:20px 22px; }

        /* Grille créneaux */
        .slot-grid {
            display:grid;
            grid-template-columns:repeat(auto-fill, minmax(100px,1fr));
            gap:10px;
        }
        .slot-item {
            border-radius:10px; padding:12px 8px; text-align:center;
            transition:all .25s; cursor:default;
        }
        .slot-item.libre {
            background:rgba(0,255,136,0.07);
            border:1px solid rgba(0,255,136,0.25);
        }
        .slot-item.libre:hover {
            background:rgba(0,255,136,0.14);
            border-color:#00ff88;
            transform:translateY(-2px);
        }
        .slot-item.occupe {
            background:rgba(255,77,109,0.07);
            border:1px solid rgba(255,77,109,0.25);
            cursor:not-allowed;
        }
        .slot-heure {
            font-weight:800; font-size:15px; margin-bottom:4px;
        }
        .slot-item.libre  .slot-heure { color:#00ff88; }
        .slot-item.occupe .slot-heure { color:#ff4d6d; }
        .slot-label { font-size:11px; letter-spacing:.5px; text-transform:uppercase; }
        .slot-item.libre  .slot-label { color:#6b7c93; }
        .slot-item.occupe .slot-label { color:rgba(255,77,109,0.7); }
    </style>
</head>
<body>
<?php require_once("view/sections/vitrine/menu.php"); ?>

<div class="page-header-section">
    <h1>Créneaux Disponibles</h1>
    <p>Consultez les horaires libres et occupés pour chaque terrain</p>
</div>

<div class="container mb-5">

    <div class="filter-bar">
        <div class="row align-items-center">
            <div class="col-md-4 mb-3 mb-md-0">
                <label>Filtrer par date</label>
                <input type="date" id="filtre-date" class="form-control"
                       value="<?= htmlspecialchars($dateFiltre) ?>"
                       onchange="filtrerDate(this.value)">
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <label>Filtrer par terrain</label>
                <select id="filtre-terrain" class="form-control" onchange="filtrerTerrain(this.value)">
                    <option value="">Tous les terrains</option>
                    <?php foreach ($terrains as $t): ?>
                    <option value="terrain-<?= $t['id'] ?>">
                        <?= htmlspecialchars($t['nom']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label>&nbsp;</label>
                <a href="terrains" class="btn-reserver-filter">
                    <i class="fa fa-futbol mr-1"></i> Réserver un terrain
                </a>
            </div>
        </div>
    </div>

    <div class="legende">
        <div class="legende-item"><div class="legende-dot dot-libre"></div> Disponible</div>
        <div class="legende-item"><div class="legende-dot dot-occupe"></div> Occupé</div>
    </div>

    <?php foreach ($terrains as $t): ?>
    <div class="terrain-section terrain-<?= $t['id'] ?>">
        <div class="terrain-title">
            <div>
                <h5><?= htmlspecialchars($t['nom']) ?></h5>
                <small><i class="fa fa-map-marker-alt mr-1"></i><?= htmlspecialchars($t['localisation']) ?></small>
            </div>
            <a href="reserver?terrain_id=<?= $t['id'] ?>" class="btn-reserver-small">
                <i class="fa fa-calendar-plus mr-1"></i> Réserver
            </a>
        </div>
        <div class="creneaux-body">
            <div class="slot-grid">
                <?php foreach ($horaires as $h):
                    $occupe = estOccupe($plages, $t['id'], $dateFiltre, $h);
                ?>
                <div class="slot-item <?= $occupe ? 'occupe' : 'libre' ?>">
                    <div class="slot-heure"><?= $h ?></div>
                    <div class="slot-label"><?= $occupe ? 'Occupé' : 'Libre' ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

</div>

<?php require_once("view/sections/vitrine/footer.php"); ?>
<script src="public/templates/templateVitrine/assets/js/one-page-parallax/app.min.js"></script>
<script>
function filtrerTerrain(val) {
    document.querySelectorAll('.terrain-section').forEach(el => {
        el.style.display = (!val || el.classList.contains(val)) ? 'block' : 'none';
    });
}
function filtrerDate(val) {
    window.location.href = 'creneaux?date=' + val;
}
</script>
</body>
</html>