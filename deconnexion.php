<?php
// Déconnexion Admin
session_name('admin_session');
session_start();
session_destroy();

// Déconnexion Utilisateur
session_name('user_session');
session_start();
session_destroy();

header('Location: /FootBookingApp/admin_login.php');
exit();
?>