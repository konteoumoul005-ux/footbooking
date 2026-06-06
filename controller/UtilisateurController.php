public function ajouter() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // DEBUG - à supprimer après
        var_dump($_POST);
        die();
        
        $model = new Utilisateur();
        $data = [
            'nom'           => $_POST['nom'],
            'prenom'        => $_POST['prenom'],
            'email'         => $_POST['email'],
            'mot_de_passe'  => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'telephone'     => $_POST['telephone']
        ];
        $model->insert($data);
        header('Location: ListeUtilisateur');
        exit();
    }
}