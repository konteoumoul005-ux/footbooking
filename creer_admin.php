<?php
require_once("model/DBRepository.php");

$db = new DBRepository();

$nom       = "Kebe";
$prenom    = "Admin";
$email     = "hechkb@gmail.com";
$password  = "M.kebe85";
$telephone = "77 565 02 03";

$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $check = $db->getPDO()->prepare("SELECT id FROM utilisateur WHERE email = :email");
    $check->execute(['email' => $email]);
    
    if ($check->fetch()) {
        $stmt = $db->getPDO()->prepare("UPDATE utilisateur SET mot_de_passe=:mdp, role='admin' WHERE email=:email");
        $stmt->execute(['mdp' => $hash, 'email' => $email]);
        echo "<h2 style='color:green; font-family:sans-serif;'>✅ Compte admin mis à jour !</h2>";
    } else {
        $stmt = $db->getPDO()->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, telephone, role, created_at) VALUES (:nom, :prenom, :email, :mot_de_passe, :telephone, 'admin', NOW())");
        $stmt->execute(['nom' => $nom, 'prenom' => $prenom, 'email' => $email, 'mot_de_passe' => $hash, 'telephone' => $telephone]);
        echo "<h2 style='color:green; font-family:sans-serif;'>✅ Compte admin créé !</h2>";
    }
    echo "<p style='font-family:sans-serif;'>Email : <b>$email</b><br>Mot de passe : <b>$password</b></p>";
    echo "<p style='font-family:sans-serif;'><a href='admin_login.php'>👉 Aller à la connexion admin</a></p>";
    echo "<p style='color:red; font-family:sans-serif;'><b>⚠️ Supprimez ce fichier après utilisation !</b></p>";
} catch (Exception $e) {
    echo "<h2 style='color:red; font-family:sans-serif;'>❌ Erreur : " . $e->getMessage() . "</h2>";
}
?>