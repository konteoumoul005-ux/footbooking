<?php
session_name('admin_session');
session_start();
require_once("send_email.php");

$reservation = [
    'terrain'          => 'Terrain Test',
    'date_reservation' => date('Y-m-d'),
    'heure_debut'      => '10:00',
    'heure_fin'        => '11:00',
    'type_terrain'     => 'petit',
    'montant'          => 25000
];

$result = envoyerEmailConfirmation('footbooking0@gmail.com', 'Test User', $reservation);

if ($result) {
    echo '<h2 style="color:green;">✅ Email envoyé avec succès !</h2>';
} else {
    echo '<h2 style="color:red;">❌ Erreur envoi email</h2>';
    echo '<p>Vérifiez les logs Apache : <code>C:\xampp\apache\logs\error.log</code></p>';
}
?>