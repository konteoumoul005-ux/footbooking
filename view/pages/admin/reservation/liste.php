<?php
require_once("model/DBRepository.php");

$db = new DBRepository();
try {
    $reservations = $db->query("
        SELECT r.*, u.nom, u.prenom, u.telephone, u.email, t.nom as terrain, t.localisation
        FROM reservation r
        JOIN utilisateur u ON r.utilisateur_id = u.id
        JOIN terrain t ON r.terrain_id = t.id
        ORDER BY r.date_reservation DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $reservations = [];
}
?>

<div id="content" class="content">
    <ol class="breadcrumb float-xl-right">
        <li class="breadcrumb-item"><a href="Dashboard">Accueil</a></li>
        <li class="breadcrumb-item active">Réservations</li>
    </ol>
    <h1 class="page-header">Réservations <small>liste complète</small></h1>

    <div id="alert-box" style="display:none;" class="alert alert-success alert-dismissible fade show">
        <i class="fa fa-envelope mr-2"></i> <span id="alert-msg"></span>
        <button type="button" class="close" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>

    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Liste des Réservations (<?= count($reservations) ?>)</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Client</th>
                            <th>Téléphone</th>
                            <th>Terrain</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Début</th>
                            <th>Fin</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reservations)): ?>
                        <tr><td colspan="11" class="text-center text-muted">Aucune réservation</td></tr>
                        <?php else: ?>
                        <?php foreach ($reservations as $r): ?>
                        <tr id="row-<?= $r['id'] ?>">
                            <td><?= $r['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($r['prenom'] . ' ' . $r['nom']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($r['email']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($r['telephone']) ?></td>
                            <td><?= htmlspecialchars($r['terrain']) ?><br><small class="text-muted"><?= htmlspecialchars($r['localisation']) ?></small></td>
                            <td><?php $types = ['petit'=>'Petit','moitie'=>'Moitié','grand'=>'Grand']; echo $types[$r['type_terrain']] ?? '-'; ?></td>
                            <td><?= date('d/m/Y', strtotime($r['date_reservation'])) ?></td>
                            <td><?= $r['heure_debut'] ?></td>
                            <td><?= $r['heure_fin'] ?? '-' ?></td>
                            <td><strong><?= number_format($r['montant'], 0, ',', ' ') ?> FCFA</strong></td>
                            <td id="statut-<?= $r['id'] ?>">
                                <?php
                                $badges = ['en_attente'=>'badge-warning','en_attente_validation'=>'badge-info','confirmee'=>'badge-success','confirme'=>'badge-success','annulee'=>'badge-danger'];
                                $labels = ['en_attente'=>'En attente','en_attente_validation'=>'⏳ Paiement à vérifier','confirmee'=>'✅ Confirmée','confirme'=>'✅ Confirmée','annulee'=>'Annulée'];
                                echo '<span class="badge '.($badges[$r['statut']]??'badge-secondary').'">'.($labels[$r['statut']]??$r['statut']).'</span>';
                                ?>
                            </td>
                            <td id="actions-<?= $r['id'] ?>">
                                <?php if ($r['statut'] !== 'confirmee' && $r['statut'] !== 'confirme'): ?>
                                <button onclick="confirmerReservation(<?= $r['id'] ?>, '<?= htmlspecialchars($r['prenom']) ?>')" class="btn btn-sm btn-success mb-1">
                                    <i class="fa fa-check"></i> Confirmer
                                </button>
                                <?php endif; ?>
                                <?php if ($r['statut'] !== 'annulee'): ?>
                                <button onclick="changerStatut(<?= $r['id'] ?>, 'annulee')" class="btn btn-sm btn-warning mb-1">
                                    <i class="fa fa-times"></i>
                                </button>
                                <?php endif; ?>
                                <button onclick="supprimerReservation(<?= $r['id'] ?>)" class="btn btn-sm btn-danger mb-1">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function showAlert(msg, type) {
    var box = document.getElementById('alert-box');
    box.className = 'alert alert-' + type + ' alert-dismissible fade show';
    document.getElementById('alert-msg').textContent = msg;
    box.style.display = 'block';
    setTimeout(function() { box.style.display = 'none'; }, 5000);
}

function confirmerReservation(id, prenom) {
    if (!confirm('Confirmer la réservation de ' + prenom + ' et lui envoyer un email ?')) return;
    var btn = document.querySelector('#actions-' + id + ' .btn-success');
    if (btn) { btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>'; btn.disabled = true; }
    var fd = new FormData();
    fd.append('id', id); fd.append('statut', 'confirmee');
    fetch('/FootBookingApp/action_reservation.php', {method:'POST', body:fd})
    .then(function(r){return r.json();})
    .then(function(data){
        if (data.success) {
            document.getElementById('statut-'+id).innerHTML = '<span class="badge badge-success">✅ Confirmée</span>';
            if (btn) btn.remove();
            showAlert(data.email ? '✅ Réservation confirmée et email envoyé !' : '✅ Confirmée (email non envoyé)', data.email ? 'success' : 'warning');
        } else {
            showAlert('Erreur : ' + (data.error||'inconnue'), 'danger');
            if (btn) { btn.innerHTML = '<i class="fa fa-check"></i> Confirmer'; btn.disabled = false; }
        }
    }).catch(function(){ showAlert('Erreur réseau', 'danger'); });
}

function changerStatut(id, statut) {
    var fd = new FormData(); fd.append('id', id); fd.append('statut', statut);
    fetch('/FootBookingApp/action_reservation.php', {method:'POST', body:fd})
    .then(function(r){return r.json();})
    .then(function(data){
        if (data.success) {
            document.getElementById('statut-'+id).innerHTML = '<span class="badge badge-danger">Annulée</span>';
            showAlert('Réservation annulée.', 'warning');
        }
    });
}

function supprimerReservation(id) {
    if (!confirm('Supprimer définitivement cette réservation ?')) return;
    var fd = new FormData(); fd.append('id', id); fd.append('statut', 'supprimer');
    fetch('/FootBookingApp/action_reservation.php', {method:'POST', body:fd})
    .then(function(r){return r.json();})
    .then(function(data){
        if (data.success) { var row = document.getElementById('row-'+id); if(row) row.remove(); showAlert('Réservation supprimée.', 'danger'); }
    });
}
</script>