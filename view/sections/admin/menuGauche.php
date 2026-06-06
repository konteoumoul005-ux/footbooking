<div id="sidebar" class="sidebar">
    <div data-scrollbar="true" data-height="100%">
        <ul class="nav">
            <li class="nav-profile">
                <a href="javascript:;" data-toggle="nav-profile">
                    <div class="cover with-shadow"></div>
                    <div class="image">
                        <?php
                            $photo = $_SESSION['user']['photo'] ?? 'public/templates/TemplateAdmin/assets/img/user/user-13.jpg';
                            $prenom = $_SESSION['user']['prenom'] ?? '';
                            $nom = $_SESSION['user']['nom'] ?? 'Admin';
                            $nomComplet = trim($prenom . ' ' . $nom);
                        ?>
                        <img src="<?= htmlspecialchars($photo) ?>" alt="" />
                    </div>
                    <div class="info">
                        <b class="caret pull-right"></b>
                        <?= htmlspecialchars($nomComplet) ?>
                        <small><?= htmlspecialchars($_SESSION['user']['role'] ?? 'Administrateur') ?></small>
                    </div>
                </a>
            </li>
            <li>
                <ul class="nav nav-profile">
                    <li><a href="Profil"><i class="fa fa-cog"></i> Mon Profil</a></li>
                    <li><a href="Deconnexion"><i class="fa fa-sign-out-alt"></i> Déconnexion</a></li>
                </ul>
            </li>
        </ul>

```
    <ul class="nav">
        <li class="nav-header">Navigation</li>
        <li class="has-sub active">
            <a href="Dashboard"><i class="fa fa-th-large"></i><span>Dashboard</span></a>
        </li>
        <li class="has-sub">
            <a href="ListeUtilisateur"><i class="fa fa-users"></i><span>Utilisateur</span></a>
        </li>
        <li class="has-sub">
            <a href="ListeTerrain"><i class="fa fa-futbol"></i><span>Terrain</span></a>
        </li>
        <li class="has-sub">
            <a href="ListeReservation"><i class="fa fa-calendar-check"></i><span>Reservation</span></a>
        </li>
        <li class="has-sub">
            <a href="Profil"><i class="fa fa-user"></i><span>Profil</span></a>
        </li>
        <li class="has-sub">
            <a href="ListePaiement"><i class="fa fa-credit-card"></i><span>Paiement</span></a>
        </li>
        <li class="has-sub">
            <a href="ListeDisponibilite"><i class="fa fa-calendar-alt"></i><span>Disponibilité</span></a>
        </li>
        <li>
            <a href="Deconnexion"><i class="fa fa-sign-out-alt"></i><span>Déconnexion</span></a>
        </li>
        <li>
            <a href="javascript:;" class="sidebar-minify-btn" data-click="sidebar-minify">
                <i class="fa fa-angle-double-left"></i>
            </a>
        </li>
    </ul>
</div>
```

</div>
<div class="sidebar-bg"></div>