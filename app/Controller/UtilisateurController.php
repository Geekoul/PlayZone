<?php
namespace App\Controller;

use App\Model\UtilisateurModel;
use App\Model\BlogModel;
use App\Helpers\ImageUtilisateur;
use PDO;

class UtilisateurController
{
    private UtilisateurModel $model;
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->model = new UtilisateurModel($pdo);
    }

    /** Inscription */
    public function register(array $post): void
    {
        $this->assertCsrf($post);

        $pseudo = trim((string)($post['pseudo'] ?? ''));
        $email  = strtolower(trim((string)($post['email'] ?? '')));
        $mdp    = (string)($post['mot_de_passe'] ?? '');

        // validations simples
        if (!preg_match('/^[a-z0-9_\-]{3,15}$/i', $pseudo)) {
            $this->flash('Pseudo invalide (3-15, lettres/chiffres/_/-).', 'error');
            $this->redirect('/connexion');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('Email invalide.', 'error');
            $this->redirect('/connexion');
        }
        if (strlen($mdp) < 5) {
            $this->flash('Mot de passe trop court (min. 5).', 'error');
            $this->redirect('/connexion');
        }

        // unicité
        if ($this->model->getByEmail($email)) {
            $this->flash('Cet email est déjà utilisé.', 'error');
            $this->redirect('/connexion');
        }
        if ($this->model->getByPseudo($pseudo)) {
            $this->flash('Ce pseudo est déjà pris.', 'error');
            $this->redirect('/connexion');
        }

        $hash = password_hash($mdp, PASSWORD_DEFAULT);
        $userId = $this->model->create($pseudo, $email, $hash);

        $_SESSION['user'] = [
            'id' => $userId,
            'pseudo' => $pseudo,
            'email' => $email,
            'est_administrateur' => 0,
            'profil_description' => null,
            'chemin_logo' => null,
        ];

        $this->flash('Compte créé, bienvenue !', 'success');
        $this->redirect('/accueil');
    }

    /** Connexion */
    public function login(array $post): void
    {
        $this->assertCsrf($post);

        $email = strtolower(trim((string)($post['email'] ?? '')));
        $mdp   = (string)($post['mot_de_passe'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('Email invalide.', 'error');
            $this->redirect('/connexion');
        }
        $user = $this->model->getByEmail($email);
        if (!$user || !password_verify($mdp, $user['mot_de_passe'])) {
            $this->flash('Identifiants incorrects.', 'error');
            $this->redirect('/connexion');
        }

        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'pseudo' => $user['pseudo'],
            'email' => $user['email'],
            'est_administrateur' => (int)$user['est_administrateur'],
            'profil_description' => $user['profil_description'], 
            'chemin_logo' => $user['chemin_logo'],             
        ];

        $this->flash('Connexion réussie.', 'success');
        $this->redirect('/accueil');
    }

    /** Déconnexion */
    public function logout(): void
    {
        unset($_SESSION['user']);
        $this->flash('Vous êtes déconnecté.', 'success');
        $this->redirect('/accueil');
    }

    // ---------- helpers ----------
    private function redirect(string $path): never
    {
        header('Location: '.$path);
        exit;
    }

    private function flash(string $msg, string $type='info'): void
    {
        $_SESSION['flash'][] = ['m'=>$msg,'t'=>$type];
    }

    private function assertCsrf(array $post): void
    {
        if (empty($_SESSION['csrf']) || empty($post['csrf']) || !hash_equals($_SESSION['csrf'], $post['csrf'])) {
            http_response_code(400);
            die('CSRF invalide.');
        }
    }

    // ---------- Parametres----------

        public function updateParams(array $post, array $files): void
    {
        if (empty($_SESSION['user'])) { $this->redirect('/connexion'); }
        $this->assertCsrf($post);

        $uid = (int)$_SESSION['user']['id'];
        $user = $this->model->getById($uid);
        if (!$user) { $this->flash('Utilisateur introuvable.', 'error'); $this->redirect('/connexion'); }

        // Suppression de compte
        if (isset($post['delete'])) {
            $this->model->deleteById($uid);
            unset($_SESSION['user']);
            $this->flash('Compte supprimé.', 'success');
            $this->redirect('/accueil');
        }

        // Préparation des champs à mettre à jour
        $toUpdate = [];

        // Pseudo
        if (isset($post['pseudo']) && $post['pseudo'] !== '' && $post['pseudo'] !== $user['pseudo']) {
            $pseudo = trim($post['pseudo']);
            if (!preg_match('/^[a-z0-9_\-]{3,15}$/i', $pseudo)) {
                $this->flash('Pseudo invalide (3-15, lettres/chiffres/_/-).', 'error');
                $this->redirect('/parametres');
            }
            // Unicité pseudo (optionnel)
            $exist = $this->model->getByPseudo($pseudo);
            if ($exist && (int)$exist['id'] !== $uid) {
                $this->flash('Ce pseudo est déjà pris.', 'error');
                $this->redirect('/parametres');
            }
            $toUpdate['pseudo'] = $pseudo;
            $_SESSION['user']['pseudo'] = $pseudo;
        }

        // Email
        if (isset($post['email']) && $post['email'] !== '' && $post['email'] !== $user['email']) {
            $email = strtolower(trim($post['email']));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->flash('Email invalide.', 'error');
                $this->redirect('/parametres');
            }
            $exist = $this->model->getByEmail($email);
            if ($exist && (int)$exist['id'] !== $uid) {
                $this->flash('Cet email est déjà utilisé.', 'error');
                $this->redirect('/parametres');
            }
            $toUpdate['email'] = $email;
            $_SESSION['user']['email'] = $email;
        }

        // Description (ajout, modif, suppression)
        if (array_key_exists('profil_description', $post)) {
            $desc = trim((string)$post['profil_description']);
            // chaîne vide = suppression → NULL en BDD
            $desc = ($desc === '') ? null : $desc;

            $old = $user['profil_description'] ?? null;
            if ($desc !== $old) {
                $toUpdate['profil_description'] = $desc;
                $_SESSION['user']['profil_description'] = $desc; // <-- ajouter cette ligne
            }
        }


        // Avatar (chemin_logo)
        if (!empty($files['chemin_logo']) && ($files['chemin_logo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $rel = \App\Helpers\ImageUtilisateur::saveCompressedWebp($files['chemin_logo'], $uid);
                $toUpdate['chemin_logo'] = $rel;
                $_SESSION['user']['chemin_logo'] = $rel;
            } catch (\RuntimeException $e) {
                $this->flash($e->getMessage(), 'error');
                $this->redirect('/parametres');
            }
        }


        // Changement de mot de passe
        // --- Changement de mot de passe (ancien + nouveau) ---
        $old = isset($post['old_password']) ? trim((string)$post['old_password']) : '';
        $new = isset($post['new_password']) ? trim((string)$post['new_password']) : '';

        if ($old !== '' || $new !== '') {
            if ($old === '' || $new === '') {
                $this->flash('Veuillez remplir les deux champs de mot de passe.', 'error');
                $this->redirect('/parametres');
            }

            // (Debug temporaire) Voir quelles clés POST arrivent
            // error_log('PARAMETRES POST KEYS: '.json_encode(array_keys($post)));

            if (!password_verify($old, $user['mot_de_passe'])) {
                // (Debug temporaire) Indiquer que la vérif échoue
                // error_log('PWD VERIFY: FAIL for user '.$uid);
                $this->flash('Mot de passe actuel incorrect.', 'error');
                $this->redirect('/parametres');
            }
            // error_log('PWD VERIFY: OK for user '.$uid);

            if (strlen($new) < 5) {
                $this->flash('Le nouveau mot de passe doit contenir au moins 5 caractères.', 'error');
                $this->redirect('/parametres');
            }

            $this->model->updatePassword($uid, password_hash($new, PASSWORD_DEFAULT));
            $this->flash('Mot de passe mis à jour avec succès.', 'success');
        }
    }

    public function ajaxCheckPassword(array $post): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (empty($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'Non authentifié']); exit;
        }

        // CSRF obligatoire
        if (empty($_SESSION['csrf']) || empty($post['csrf']) || !hash_equals($_SESSION['csrf'], $post['csrf'])) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'CSRF invalide']); exit;
        }

        $uid  = (int)$_SESSION['user']['id'];
        $user = $this->model->getById($uid);
        if (!$user) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Utilisateur introuvable']); exit;
        }

        $old = (string)($post['old_password'] ?? '');
        if ($old === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Veuillez saisir votre mot de passe actuel']); exit;
        }

        if (!password_verify($old, $user['mot_de_passe'])) {
            echo json_encode(['ok' => false, 'error' => 'Mot de passe actuel incorrect']); exit;
        }

        echo json_encode(['ok' => true]); exit;
    }

    public function adminUpdate(array $post): void
    {
        $this->assertCsrf($post);
        if (empty($_SESSION['user']['est_administrateur'])) {
            http_response_code(403);
            die('Accès refusé.');
        }

        $id = (int)($post['id'] ?? 0);
        if (!$id) return;

        // Suppression
        if (isset($post['delete'])) {
            $this->model->deleteById($id);
            $this->flash("Utilisateur #$id supprimé.", 'success');
            $this->redirect('/adminutilisateurs');
        }

        // Mise à jour
        $data = [];
        if (!empty($post['pseudo'])) $data['pseudo'] = trim($post['pseudo']);
        if (!empty($post['email']))  $data['email']  = trim($post['email']);
        if (isset($post['est_administrateur'])) {
            $data['est_administrateur'] = 1;
        } else {
            $data['est_administrateur'] = 0;
        }

        $this->model->updateProfile($id, $data);

        if (!empty($post['mot_de_passe'])) {
            $this->model->updatePassword($id, password_hash($post['mot_de_passe'], PASSWORD_DEFAULT));
        }

        $this->flash("Utilisateur #$id mis à jour.", 'success');
        $this->redirect('/adminutilisateurs');
    }

    /**
     * Gère la connexion/inscription (POST) ou prépare l'affichage (GET)
     */
    public function handleConnexion(string $step): void
    {
        // GET : déconnexion ?page=connexion&step=exit
        if ($step === 'exit') {
            $this->logout(); // redirige vers /accueil
            return;
        }

        // POST : login ou register (exécute le modèle)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
            if (isset($_POST['pseudo'])) {
                // Inscription
                $this->register($_POST);
            } else {
                // Connexion
                $this->login($_POST);
            }
            return; // register() et login() font déjà la redirection
        }

        // Sinon, rien à faire ici, la vue sera incluse par index.php
    }

    /**
     * Gère les paramètres utilisateur (POST) ou prépare l'affichage (GET)
     */
    public function handleParametres(): void
    {
        // Accès interdit si non connecté
        if (empty($_SESSION['user'])) {
            header('Location: /connexion');
            exit;
        }

        // POST : traitement des paramètres (exécute le modèle)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
            // 1) AJAX: vérif ancien mot de passe AVANT soumission
            $step = $_GET['step'] ?? '';
            if ($step === 'check-mdp') {
                $this->ajaxCheckPassword($_POST); // renvoie du JSON puis exit
                return;
            }
            // 2) Formulaire paramètres (pseudo, mail, mot de passe, avatar...)
            $this->updateParams($_POST, $_FILES);
            // updateParams() ne redirige pas toujours, donc on redirige ici
            header('Location: /parametres');
            exit;
        }

        // Sinon, rien à faire ici, la vue sera incluse par index.php
    }

    /**
     * Gère l'affichage de la page admin (GET) ou les actions admin (POST)
     * Retourne les données nécessaires pour la vue ou null si redirection
     */
    public function handleAdmin(): ?array
    {
        // Sécurité admin
        if (empty($_SESSION['user']['est_administrateur'])) {
            http_response_code(403);
            echo "<main class='box-bg'><h1>Accès refusé</h1></main>";
            return null;
        }

        // POST : mise à jour/suppression utilisateur (exécute le modèle)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
            $this->adminUpdate($_POST);
            return null; // adminUpdate() fait déjà la redirection
        }

        // GET : préparer les données pour la vue
        return ['users' => $this->model->getAll()];
    }

    /**
     * Gère l'affichage du profil utilisateur
     * Retourne les données nécessaires pour la vue
     */
    public function handleProfilUtilisateur(): array
    {
        $blogModel = new BlogModel($this->pdo);

        return [
            'meta' => [
                'title' => 'Profil | PlayZone',
                'description' => 'Page de profil de l\'utilisateur.'
            ],
            'view' => '/app/View/ProfilUtilisateur.php',
            'data' => [
                'userModel' => $this->model,
                'blogModel' => $blogModel
            ]
        ];
    }

}
