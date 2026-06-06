<?php 
session_name('user_session');
session_start(); 
?>
<div id="header" class="header navbar navbar-transparent navbar-fixed-top navbar-expand-lg">
    <div class="container">
        <!-- Logo -->
        <a href="home" class="navbar-brand">
            <span class="brand-logo"></span>
            <span class="brand-text">
                <span class="text-primary">Foot</span>Booking
            </span>
        </a>

        <!-- Bouton mobile -->
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#header-navbar">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>

        <!-- Liens menu -->
        <div class="collapse navbar-collapse" id="header-navbar">
            <ul class="nav navbar-nav navbar-right">
                <li class="nav-item">
                    <a class="nav-link active" href="#home" data-click="scroll-to-target">ACCUEIL</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#work" data-click="scroll-to-target">TERRAINS</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#milestone" data-click="scroll-to-target">CHIFFRES</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#service" data-click="scroll-to-target">INFOS</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#pricing" data-click="scroll-to-target">TARIF</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#client" data-click="scroll-to-target">TEMOIGNAGE</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#contact" data-click="scroll-to-target">CONTACT</a>
                </li>

                <?php if (isset($_SESSION['user'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                            <i class="fa fa-user-circle"></i>
                            <?= htmlspecialchars($_SESSION['user']['prenom'] . ' ' . $_SESSION['user']['nom']) ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="monprofil">
                                <i class="fa fa-user"></i> Mon Profil
                            </a>
                            <a class="dropdown-item" href="terrains">
                                <i class="fa fa-futbol"></i> Réserver un terrain
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="/FootBookingApp/deconnexion_user.php">
                                <i class="fa fa-sign-out-alt"></i> Déconnexion
                            </a>
                        </div>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php" style="border:1px solid #fff; padding:5px 15px; border-radius:20px; margin-left:5px;">
                            <i class="fa fa-sign-in-alt"></i> CONNEXION
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php" style="background:#1abc9c; color:#fff; padding:5px 15px; border-radius:20px; margin-left:5px;">
                            <i class="fa fa-user-plus"></i> S'INSCRIRE
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>