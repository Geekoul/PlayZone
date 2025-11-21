<?
/* ============================================================
   4) Routing GET -> choix de la vue + metas
   ============================================================ */
$meta = [
    'title' => 'PlayZone',
    'description' => 'L’espace dédié à l’actualité et aux discussions sur le jeu vidéo.'
];
// $meta (title, description) pour Head.php
$view = '/assets/page/404error.php'; // valeur par défaut (sécurité)
// $view (chemin du fichier de vue à inclure)

switch ($page) {
    // ------------------
    // PAGES ACCUEIL/LISTES (assets/page)
    // ------------------
    case 'accueil':
        $meta['title'] = 'Accueil | PlayZone';
        $meta['description'] = 'Découvrez les dernières actualités du jeu vidéo sur PlayZone.';
        $view = '/assets/page/Accueil.php';
        break;

    .....
    // ------------------
    // PAGES ADMIN (app/View)
    // ------------------
    case 'adminutilisateurs':
        $meta['title'] = 'Administration | PlayZone';
        $meta['description'] = 'Gestion des utilisateurs.';

        // 🔒 Sécurité
        if (empty($_SESSION['user']['est_administrateur'])) {
            http_response_code(403);
            echo "<main class='box-bg'><h1>Accès refusé</h1></main>";
            break;
        }

        // ✅ Charger la liste des utilisateurs
        $users = $userModel->getAll();

        $view = '/app/View/AdminUtilisateurs.php';
        break;
    }