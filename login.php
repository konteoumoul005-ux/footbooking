<?php
session_name('user_session');
session_start();

require_once("model/DBRepository.php");
require_once("model/UtilisateurRepository.php");

$error   = '';
$success = '';

if (isset($_POST['connexion'])) {
    $model = new Utilisateur();
    $user  = $model->findByEmail($_POST['email']);
    if ($user && password_verify($_POST['password'], $user['mot_de_passe'])) {
        if ($user['role'] === 'admin') {
            $error = "Ce compte est administrateur. Utilisez l'espace admin.";
        } else {
            $_SESSION['user'] = $user;
            header('Location: /FootBookingApp/home');
            exit();
        }
    } else {
        $error = "Email ou mot de passe incorrect.";
    }
}

if (isset($_POST['inscription'])) {
    $model = new Utilisateur();
    if ($model->findByEmail($_POST['email_inscription'])) {
        $error = "Cet email est déjà utilisé.";
    } else {
        $model->insert([
            'nom'          => $_POST['nom'],
            'prenom'       => $_POST['prenom'],
            'email'        => $_POST['email_inscription'],
            'mot_de_passe' => password_hash($_POST['password_inscription'], PASSWORD_DEFAULT),
            'telephone'    => $_POST['telephone'],
            'role'         => 'user'
        ]);
        $success = "Compte créé avec succès ! Vous pouvez vous connecter.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <title>FootBooking - Connexion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <link href="public/templates/templateVitrine/assets/css/one-page-parallax/app.min.css" rel="stylesheet" />
    <link href="public/css/dark-theme.css" rel="stylesheet" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@300;400;600;700;800&display=swap');
        * { box-sizing: border-box; }
        body {
            background: #060a0e;
            background-image:
                radial-gradient(ellipse 60% 50% at 10% 80%, rgba(0,255,136,0.05) 0%, transparent 60%),
                radial-gradient(ellipse 40% 40% at 90% 20%, rgba(0,180,255,0.03) 0%, transparent 60%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Barlow', sans-serif;
            position: relative;
            overflow: hidden;
        }
        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");
            pointer-events: none; z-index: 0;
        }
        .auth-box {
            background: rgba(17,24,32,0.98);
            border: 1px solid rgba(0,255,136,0.15);
            border-radius: 18px;
            padding: 44px 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 40px 100px rgba(0,0,0,0.7), 0 0 60px rgba(0,255,136,0.04);
            position: relative;
            z-index: 1;
        }
        .auth-logo { text-align: center; margin-bottom: 32px; }
        .auth-logo h2 {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 36px;
            letter-spacing: 3px;
            color: #e8edf2;
            margin: 0 0 6px;
        }
        .auth-logo h2 span { color: #00ff88; text-shadow: 0 0 20px rgba(0,255,136,0.4); }
        .auth-logo p { color: #6b7c93; font-size: 13px; margin: 0; }
        .auth-tabs { display: flex; margin-bottom: 28px; border-bottom: 1px solid rgba(0,255,136,0.1); }
        .auth-tab {
            flex: 1; text-align: center; padding: 12px; cursor: pointer;
            font-weight: 700; font-size: 12px; letter-spacing: 1.5px; text-transform: uppercase;
            color: #3d4f62; border-bottom: 2px solid transparent; margin-bottom: -1px; transition: all 0.2s;
        }
        .auth-tab.active { color: #00ff88; border-bottom-color: #00ff88; }
        .auth-form { display: none; }
        .auth-form.active { display: block; }
        .form-group { margin-bottom: 16px; }
        .form-group label { font-weight: 600; color: #6b7c93; font-size: 11px; letter-spacing: 1px; text-transform: uppercase; display: block; margin-bottom: 7px; }
        .form-control {
            border-radius: 9px; border: 1px solid rgba(0,255,136,0.12);
            background: #0c1117; color: #e8edf2;
            padding: 12px 16px; font-size: 14px; width: 100%;
            font-family: 'Barlow', sans-serif; transition: all 0.2s;
        }
        .form-control:focus { border-color: #00ff88; box-shadow: 0 0 0 3px rgba(0,255,136,0.1); outline: none; background: #0c1117; color: #e8edf2; }
        .form-control::placeholder { color: #3d4f62; }
        .btn-auth {
            width: 100%; padding: 14px; background: #00ff88; color: #060a0e;
            border: none; border-radius: 9px; font-weight: 800; font-size: 13px;
            letter-spacing: 1.5px; text-transform: uppercase; cursor: pointer; margin-top: 12px;
            transition: all 0.3s; box-shadow: 0 0 24px rgba(0,255,136,0.25);
            font-family: 'Barlow', sans-serif;
        }
        .btn-auth:hover { background: #fff; box-shadow: 0 0 40px rgba(0,255,136,0.4); }
        .alert-error { background: rgba(255,77,109,0.1); color: #ff4d6d; border: 1px solid rgba(255,77,109,0.2); padding: 12px 16px; border-radius: 9px; margin-bottom: 16px; font-size: 13px; }
        .alert-success { background: rgba(0,255,136,0.08); color: #00ff88; border: 1px solid rgba(0,255,136,0.2); padding: 12px 16px; border-radius: 9px; margin-bottom: 16px; font-size: 13px; }
        a { color: #6b7c93; font-size: 13px; text-decoration: none; transition: color 0.2s; }
        a:hover { color: #00ff88; }
    </style>
</head>
<body>
<div class="auth-box">
    <div class="auth-logo">
        <h2><span>Foot</span>Booking</h2>
        <p style="color:#888; font-size:13px;">Réservez votre terrain de foot</p>
    </div>

    <div class="auth-tabs">
        <div class="auth-tab active" onclick="showTab('connexion', this)">Connexion</div>
        <div class="auth-tab" onclick="showTab('inscription', this)">Inscription</div>
    </div>

    <?php if ($error): ?><div class="alert-error"><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert-success"><?= $success ?></div><?php endif; ?>

    <div id="form-connexion" class="auth-form active">
        <form method="POST">
            <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" required /></div>
            <div class="form-group"><label>Mot de passe</label><input type="password" name="password" class="form-control" required /></div>
            <button type="submit" name="connexion" class="btn-auth">Se connecter</button>
        </form>
    </div>

    <div id="form-inscription" class="auth-form">
        <form method="POST">
            <div class="form-group"><label>Nom</label><input type="text" name="nom" class="form-control" required /></div>
            <div class="form-group"><label>Prénom</label><input type="text" name="prenom" class="form-control" required /></div>
            <div class="form-group"><label>Email</label><input type="email" name="email_inscription" class="form-control" required /></div>
            <div class="form-group"><label>Téléphone</label><input type="tel" name="telephone" class="form-control" required /></div>
            <div class="form-group"><label>Mot de passe</label><input type="password" name="password_inscription" class="form-control" required /></div>
            <button type="submit" name="inscription" class="btn-auth">Créer mon compte</button>
        </form>
    </div>

    <div style="text-align:center; margin-top:20px;">
        <a href="home">← Retour à l'accueil</a>
    </div>
    <div style="text-align:center; margin-top:10px; padding-top:10px; border-top:1px solid #eee;">
        <a href="/FootBookingApp/admin_login.php" style="font-size:11px; letter-spacing:1px;">Espace administrateur</a>
    </div>
</div>
<script>
function showTab(tab, el) {
    document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
    document.getElementById('form-' + tab).classList.add('active');
    el.classList.add('active');
}
</script>
</body>
</html>