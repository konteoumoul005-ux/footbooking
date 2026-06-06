<?php
session_name('admin_session');
session_start();

if (!isset($_SESSION['admin']) || $_SESSION['admin']['role'] !== 'admin') {
    header('Location: /FootBookingApp/admin_login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<?php require_once("view/sections/admin/head.php");?>
<body>
<div id="page-container" class="fade page-sidebar-fixed page-header-fixed show">
    <?php require_once("view/sections/admin/menuHaut.php");?>
    <?php require_once("view/sections/admin/menuGauche.php");?>
    <?php require_once("view/sections/admin/baseContent.php");?>
    <a href="javascript:;" class="btn btn-icon btn-circle btn-success btn-scroll-to-top fade" data-click="scroll-top">
        <i class="fa fa-angle-up"></i>
    </a>
</div>
<?php require_once("view/sections/admin/script.php");?>
</body>
</html>