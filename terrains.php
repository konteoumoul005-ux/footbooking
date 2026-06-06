<?php
session_start();
require_once("model/DBRepository.php");
require_once("model/TerrainRepository.php");

$model = new Terrain();
$terrains = $model->getAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <title>FootBooking - Nos Terrains</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <link href="public/templates/templateVitrine/assets/css/one-page-parallax/app.min.css" rel="stylesheet" />
    <link href="public/css/dark-theme.css" rel="stylesheet" />
    <style>
        body { background: #060a0e; padding-top: 60px; }
        .terrain-card {
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid rgba(0,255,136,0.12);
            background: #111820;
            margin-bottom: 25px;
            transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s;
        }
        .terrain-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 60px rgba(0,0,0,0.6), 0 0 30px rgba(0,255,136,0.08);
            border-color: rgba(0,255,136,0.3);
        }
        .terrain-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            filter: brightness(0.8) grayscale(15%);
            transition: filter 0.35s;
        }
        .terrain-card:hover img { filter: brightness(1) grayscale(0%); }
        .terrain-card .card-body { padding: 20px; }
        .terrain-card h4 { font-family: 'Barlow', sans-serif; font-weight: 800; color: #e8edf2; font-size: 16px; }
        .terrain-card p { color: #6b7c93; font-size: 13px; }
        .prix-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            margin: 3px 2px;
            letter-spacing: 0.3px;
        }
        .btn-reserver {
            background: #00ff88;
            color: #060a0e;
            border: none;
            padding: 11px 25px;
            border-radius: 8px;
            font-weight: 800;
            font-size: 12px;
            letter-spacing: 1px;
            text-transform: uppercase;
            width: 100%;
            margin-top: 12px;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
            text-align: center;
            box-shadow: 0 0 18px rgba(0,255,136,0.2);
        }
        .btn-reserver:hover { background: #fff; color: #060a0e; box-shadow: 0 0 30px rgba(0,255,136,0.4); }
        .page-header-section {
            background: linear-gradient(rgba(6,10,14,0.75), rgba(6,10,14,0.75)),
                        url('public/images/bg-home.jpg');
            background-size: cover;
            background-position: center;
            padding: 80px 0 40px;
            text-align: center;
            color: #fff;
            margin-bottom: 40px;
            border-bottom: 1px solid rgba(0,255,136,0.12);
        }
        .page-header-section h1 { font-family: 'Bebas Neue', sans-serif; font-size: 56px; letter-spacing: 3px; }
        .page-header-section p { color: rgba(232,237,242,0.6); font-size: 16px; }
        /* Fix prix badge colors in dark mode */
        span.prix-badge[style*="#e8f8f5"] { background:#0d2b20 !important; color:#00ff88 !important; }
        span.prix-badge[style*="#eaf0fb"] { background:#0d1a2b !important; color:#5ba8e8 !important; }
        span.prix-badge[style*="#fef9e7"] { background:#2b200d !important; color:#f5a623 !important; }
    </style>
</head>
<body>
<?php require_once("view/sections/vitrine/menu.php"); ?>

<div class="page-header-section">
    <h1 style="font-size:2.5rem; font-weight:800;">Nos Terrains</h1>
    <p style="font-size:1.1rem; color:rgba(255,255,255,0.8);">
        Choisissez votre terrain et réservez en quelques clics
    </p>
</div>

<div class="container mb-5">
    <div class="row">
        <?php 
        $images = [
            'public/images/WhatsApp Image 2026-04-25 at 10.40.06.jpeg',
            'public/images/WhatsApp Image 2026-04-25 at 10.38.13.jpeg',
            'public/images/WhatsApp Image 2026-04-25 at 10.38.20.jpeg',
            'public/images/WhatsApp Image 2026-04-25 at 10.39.15.jpeg',
            'public/images/WhatsApp Image 2026-04-25 at 10.42.33.jpeg',
            'public/images/WhatsApp Image 2026-05-02 at 02.28.00.jpeg',
            'public/images/WhatsApp Image 2026-04-25 at 10.43.26.jpeg',
            'public/images/WhatsApp Image 2026-05-02 at 02.33.43.jpeg',
        ];
        $i = 0;
        foreach ($terrains as $t): 
            $img = $images[$i % count($images)];
            $i++;
        ?>
        <div class="col-lg-3 col-md-6">
            <div class="terrain-card">
                <img src="<?= $img ?>" alt="<?= htmlspecialchars($t['nom']) ?>" />
                <div class="card-body">
                    <h4><?= htmlspecialchars($t['nom']) ?></h4>
                    <p style="color:#888; font-size:13px;">
                        <i class="fa fa-map-marker-alt"></i> 
                        <?= htmlspecialchars($t['localisation']) ?>
                    </p>
                    <div>
                        <?php if ($t['prix_petit']): ?>
                        <span class="prix-badge" style="background:#e8f8f5; color:#1abc9c;">
                            Petit : <?= number_format($t['prix_petit'], 0, ',', ' ') ?> FCFA
                        </span>
                        <?php endif; ?>
                        <?php if ($t['prix_moitie']): ?>
                        <span class="prix-badge" style="background:#eaf0fb; color:#2980b9;">
                            Moitié : <?= number_format($t['prix_moitie'], 0, ',', ' ') ?> FCFA
                        </span>
                        <?php endif; ?>
                        <?php if ($t['prix_grand']): ?>
                        <span class="prix-badge" style="background:#fef9e7; color:#f39c12;">
                            Grand : <?= number_format($t['prix_grand'], 0, ',', ' ') ?> FCFA
                        </span>
                        <?php endif; ?>
                    </div>
                    <a href="reserver?terrain_id=<?= $t['id'] ?>" class="btn-reserver">
                        <i class="fa fa-calendar-check"></i> Réserver
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once("view/sections/vitrine/footer.php"); ?>
<script src="public/templates/templateVitrine/assets/js/one-page-parallax/app.min.js"></script>
</body>
</html>