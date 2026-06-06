<?php
$page = basename($_SERVER['REQUEST_URI']);

switch($page) {
    case 'ListeUtilisateur':
        require_once("view/pages/admin/utilisateur/liste.php");
        break;
    case 'CorbeilleUtilisateur':
        require_once("view/pages/admin/utilisateur/corbeille.php");
        break;
    case 'ListeTerrain':
        require_once("view/pages/admin/terrain/liste.php");
        break;
    case 'CorbeilleTerrain':
        require_once("view/pages/admin/terrain/corbeille.php");
        break;
    case 'ListeReservation':
        require_once("view/pages/admin/reservation/liste.php");
        break;
    case 'CorbeilleReservation':
        require_once("view/pages/admin/reservation/corbeille.php");
        break;
    case 'ListePaiement':
        require_once("view/pages/admin/paiement/liste.php");
        break;
    case 'CorbeillePaiement':
        require_once("view/pages/admin/paiement/corbeille.php");
        break;
    case 'ListeDisponibilite':
        require_once("view/pages/admin/disponibilite/liste.php");
        break;
    case 'CorbeilleDisponibilite':
        require_once("view/pages/admin/disponibilite/corbeille.php");
        break;
    case 'Profil':
        require_once("view/pages/admin/profil/profil.php");
        break;
    default:
        require_once("model/DBRepository.php");
        $db = new DBRepository();

        try {
            $totalUtilisateurs = $db->query("SELECT COUNT(*) as total FROM utilisateur WHERE deleted_at IS NULL")->fetch()['total'];
            $totalTerrains = $db->query("SELECT COUNT(*) as total FROM terrain WHERE deleted_at IS NULL")->fetch()['total'];
            $totalReservations = $db->query("SELECT COUNT(*) as total FROM reservation")->fetch()['total'];
            $totalArgent = $db->query("SELECT SUM(montant) as total FROM paiement")->fetch()['total'];
            $totalArgent = $totalArgent ?? 0;

            try {
                $reservations = $db->query("
                    SELECT r.*, u.nom, u.prenom, t.nom as terrain
                    FROM reservation r 
                    JOIN utilisateur u ON r.utilisateur_id = u.id 
                    JOIN terrain t ON r.terrain_id = t.id
                    ORDER BY r.id DESC LIMIT 5")->fetchAll();
            } catch (Exception $e) {
                $reservations = [];
            }

            try {
                $events = $db->query("
                    SELECT r.*, u.nom, u.prenom, t.nom as terrain
                    FROM reservation r
                    JOIN utilisateur u ON r.utilisateur_id = u.id
                    JOIN terrain t ON r.terrain_id = t.id")->fetchAll();
            } catch (Exception $e) {
                $events = [];
            }

        } catch (Exception $e) {
            $totalUtilisateurs = 0;
            $totalTerrains = 0;
            $totalReservations = 0;
            $totalArgent = 0;
            $reservations = [];
            $events = [];
        }
?>

<div id="content" class="content">
    <ol class="breadcrumb float-xl-right">
        <li class="breadcrumb-item"><a href="javascript:;">Accueil</a></li>
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>
    <h1 class="page-header mb-3">Tableau de bord <small>FootBooking</small></h1>

    <!-- Cartes statistiques -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card text-white mb-3" style="background:#1abc9c; border-radius:12px;">
                <div class="card-body text-center p-4">
                    <i class="fa fa-users fa-3x mb-2"></i>
                    <h2 class="mb-0"><?= $totalUtilisateurs ?></h2>
                    <p class="mb-0">Utilisateurs</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card text-white mb-3" style="background:#2c3e50; border-radius:12px;">
                <div class="card-body text-center p-4">
                    <i class="fa fa-futbol fa-3x mb-2"></i>
                    <h2 class="mb-0"><?= $totalTerrains ?></h2>
                    <p class="mb-0">Terrains</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card text-white mb-3" style="background:#e67e22; border-radius:12px;">
                <div class="card-body text-center p-4">
                    <i class="fa fa-calendar-check fa-3x mb-2"></i>
                    <h2 class="mb-0"><?= $totalReservations ?></h2>
                    <p class="mb-0">Réservations</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card text-white mb-3" style="background:#27ae60; border-radius:12px;">
                <div class="card-body text-center p-4">
                    <i class="fa fa-money-bill-wave fa-3x mb-2"></i>
                    <h2 class="mb-0"><?= number_format($totalArgent, 0, ',', ' ') ?> FCFA</h2>
                    <p class="mb-0">Revenus Total</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendrier et Réservations -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fa fa-calendar-alt text-primary mr-2"></i>Calendrier des Réservations
                    </h4>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fa fa-clock text-warning mr-2"></i>Dernières Réservations
                    </h4>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php if (empty($reservations)): ?>
                            <li class="list-group-item text-center text-muted">
                                Aucune réservation
                            </li>
                        <?php else: ?>
                            <?php foreach ($reservations as $r): ?>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong><?= htmlspecialchars($r['prenom'] . ' ' . $r['nom']) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($r['terrain']) ?></small>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-success">
                                            <?= isset($r['montant']) ? number_format($r['montant'], 0, ',', ' ') . ' FCFA' : 'N/A' ?>
                                        </span>
                                    </div>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FullCalendar -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/fr.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        events: [
            <?php foreach ($events as $e): ?>
            {
                title: '<?= addslashes(($e['prenom'] ?? '') . ' ' . ($e['nom'] ?? '') . ' - ' . ($e['terrain'] ?? '')) ?>',
                start: '<?= isset($e['date_reservation']) ? $e['date_reservation'] : (isset($e['date']) ? $e['date'] : '') ?>',
                color: '#1abc9c'
            },
            <?php endforeach; ?>
        ],
        height: 500
    });
    calendar.render();
});
</script>

<?php
        break;
}
?>