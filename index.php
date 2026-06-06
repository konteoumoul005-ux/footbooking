<?php ?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="utf-8" />
	<title>Accueil - FootBooking</title>
	<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
	<link href="public/templates/templateVitrine/assets/css/one-page-parallax/app.min.css" rel="stylesheet" />
	<link href="public/css/dark-theme.css" rel="stylesheet" />
	<style>
		html, body { padding: 0 !important; margin: 0 !important; }
		#page-container, #page-container.fade { opacity: 1 !important; }
	</style>
</head>
<body class="pace-done" data-spy="scroll" data-target="#header" data-offset="51">
	<div id="page-container" class="page-loaded show">
		<?php require_once("view/sections/vitrine/menu.php");?>
		<?php require_once("view/sections/vitrine/baniere.php");?>
		<?php require_once("view/sections/vitrine/terrain.php");?>
		<?php require_once("view/sections/vitrine/chiffrage.php");?>
		<?php require_once("view/sections/vitrine/info.php");?>
		<?php require_once("view/sections/vitrine/pricing.php");?>
		<?php require_once("view/sections/vitrine/temoignage.php");?>
		<?php require_once("view/sections/vitrine/contact.php");?>
		<?php require_once("view/sections/vitrine/footer.php");?>
	</div>
	<script src="public/templates/templateVitrine/assets/js/one-page-parallax/app.min.js"></script>
</body>
</html>