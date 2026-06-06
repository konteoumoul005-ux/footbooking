<?php
session_name('admin_session');
session_start();

require_once("model/DBRepository.php");
require_once("model/UtilisateurRepository.php");

$error = '';

if (isset($_POST['connexion'])) {
    $email    = $_POST['email'];
    $password = $_POST['password'];

    $model = new Utilisateur();
    $user  = $model->findByEmail($email);

    if ($user && password_verify($password, $user['mot_de_passe'])) {
        if ($user['role'] === 'admin') {
            $_SESSION['admin'] = $user;
            header('Location: /FootBookingApp/admin.php');
            exit();
        } else {
            $error = "Accès refusé. Ce compte n'est pas administrateur.";
        }
    } else {
        $error = "Email ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <title>FootBooking - Connexion Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Open Sans', sans-serif;
            background: linear-gradient(135deg, #1e2a3a, #2d3a4a);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo h2 { font-size: 26px; font-weight: 700; color: #1e2a3a; }
        .login-logo h2 span { color: #1abc9c; }
        .login-logo p { color: #888; font-size: 13px; margin-top: 5px; }
        .badge-admin {
            display: inline-block;
            background: #1e2a3a;
            color: #1abc9c;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            margin-top: 8px;
            letter-spacing: 1px;
        }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-weight: 600; font-size: 13px; color: #444; margin-bottom: 6px; }
        .form-control {
            width: 100%; border: 1px solid #ddd; border-radius: 8px;
            padding: 11px 15px; font-size: 14px; font-family: inherit;
            transition: border 0.2s;
        }
        .form-control:focus { outline: none; border-color: #1abc9c; box-shadow: 0 0 0 3px rgba(26,188,156,0.15); }
        .btn-login {
            width: 100%; padding: 13px; background: #1abc9c; color: #fff;
            border: none; border-radius: 8px; font-weight: 700; font-size: 15px;
            cursor: pointer; transition: background 0.2s; margin-top: 5px;
        }
        .btn-login:hover { background: #16a085; }
        .alert-error {
            background: #ffe0e0; color: #c0392b; padding: 10px 15px;
            border-radius: 8px; margin-bottom: 15px; font-size: 13px;
        }
        .back-link { text-align: center; margin-top: 20px; }
        .back-link a { color: #888; font-size: 13px; text-decoration: none; }
        .back-link a:hover { color: #1abc9c; }
    </style>
</head>
<body>
<div class="login-box">
    <div class="login-logo">
        <h2><span>Foot</span>Booking</h2>
        <p>Espace administrateur</p>
        <span class="badge-admin">ADMIN</span>
    </div>

    <?php if ($error): ?>
    <div class="alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label><i class="fas fa-envelope" style="color:#1abc9c;"></i> Email administrateur</label>
            <input type="email" name="email" class="form-control" placeholder="admin@footbooking.com" required />
        </div>
        <div class="form-group">
            <label><i class="fas fa-lock" style="color:#1abc9c;"></i> Mot de passe</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required />
        </div>
        <button type="submit" name="connexion" class="btn-login">
            <i class="fas fa-sign-in-alt"></i> Accéder au Dashboard
        </button>
    </form>

    <div class="back-link">
        <a href="/FootBookingApp/home"><i class="fas fa-arrow-left"></i> Retour au site</a>
    </div>
</div>
</body>
</html>