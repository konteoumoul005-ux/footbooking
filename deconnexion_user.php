<?php
// Déconnexion Utilisateur
session_name('user_session');
session_start();
session_destroy();

header('Location: /FootBookingApp/login.php');
exit();
?>