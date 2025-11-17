<?php
session_start();
require_once __DIR__ . '/vendor/autoload.php';

use App\Database;
use App\Model\UtilisateurModel;
use App\Controller\UtilisateurController;
use App\Model\ContactsModel;
use App\Controller\ContactsController;
use App\Model\ArticleModel;
use App\Controller\ArticleController;
use App\Model\BlogModel;
use App\Controller\BlogController;
use App\Model\CommentaireModel;
use App\Controller\CommentaireController;



/* ============================================================
   1) Initialisation (DB, MVC, CSRF, route)
   ============================================================ */

// --- Connexion DB ---
$db  = new Database();
$pdo = $db->getConnection();

// --- MVC Auth ---
$userModel = new UtilisateurModel($pdo);
$userCtrl  = new UtilisateurController($userModel);

// --- MVC Contacts ---
$contactsModel = new ContactsModel($pdo);
$contactsCtrl  = new ContactsController($contactsModel);

// --- MVC Article et Blog ---
$articleModel = new ArticleModel($pdo);
$articleCtrl  = new ArticleController($articleModel);
$blogModel = new BlogModel($pdo);
$blogCtrl  = new BlogController($blogModel);

// --- MVC Commentaires ---
$commentModel = new CommentaireModel($pdo);
$commentCtrl  = new CommentaireController($commentModel);



// --- CSRF token ---
// Si aucun jeton CSRF n’existe encore dans la session,
// on en génère un nouveau pour sécuriser les formulaires (anti-requêtes forgées)
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32)); // Crée 64 caractères aléatoires uniques
}

// --- Paramètres de route (issus de la réécriture .htaccess) ---
$page = $_GET['page'] ?? 'accueil';
$step = $_GET['step'] ?? '';
$id   = isset($_GET['id']) ? (int) $_GET['id'] : null;

// Hygiène minimale : on garde lettres/chiffres/tirets
$page = strtolower(preg_replace('/[^a-z0-9\-]/', '', $page));
$step = strtolower(preg_replace('/[^a-z0-9\-]/', '', $step));

/* ============================================================
   2) Gestion des requêtes POST (aucun HTML envoyé avant)
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1) AJAX: vérif ancien mot de passe AVANT soumission
    if ($page === 'parametres' && $step === 'check-mdp') {
        $userCtrl->ajaxCheckPassword($_POST); // renvoie du JSON puis exit
    }

    // 2) Connexion / inscription
    if ($page === 'connexion') {
        if (isset($_POST['pseudo'])) { $userCtrl->register($_POST); }
        else { $userCtrl->login($_POST); }
    }

    // 3) Soumission du formulaire paramètres
    if ($page === 'parametres') {
        $userCtrl->updateParams($_POST, $_FILES);
    }

    // 4) Formulaire Contacts
    if ($page === 'contacts') {
        $contactsCtrl->submit($_POST);
    }

    // 5) Formulaire Ajouter un article + blog
    if ($page === 'ajouterunarticle') {
        $articleCtrl->submit($_POST, $_FILES);
    }

    if ($page === 'ajouterunblog') {
        $blogCtrl->submit($_POST, $_FILES);
    }

    // 6) Commentaires
    if ($page === 'commentaire') {
        if ($step === 'ajouter')   { $commentCtrl->add($_POST); }
        if ($step === 'supprimer') { $commentCtrl->delete($_POST); }
    }

    // 7) Admin Utilisateur (update/delete)
    if ($page === 'adminutilisateur') {
        $userCtrl->adminUpdate($_POST);
    }

    // 8) Admin Articles (update/delete)
    if ($page === 'adminarticles') {
        $articleCtrl->adminUpdate($_POST);
    }

    // 9) Admin Blogs (update/delete)
    if ($page === 'adminblogs') {
        $blogCtrl->adminUpdate($_POST);
    }

    // 10) Admin Commentaires (update/delete)
    if ($page === 'admincommentaires') {
        $commentCtrl->adminUpdate($_POST);
    }


}

/* ============================================================
   3) Actions GET "techniques" (ex: déconnexion)
   ============================================================ */
if ($page === 'connexion' && ($step === 'exit' || ($_GET['step'] ?? '') === 'exit')) {
    $userCtrl->logout(); // redirige vers /accueil
}

if ($page === 'parametres' && empty($_SESSION['user'])) {
    header('Location: /connexion'); exit;
}

// Charge 10 blogs supplémentaires en AJAX : /blogs/load?offset=50&limit=10
if ($page === 'blogs' && $step === 'load') {
    header('Content-Type: text/html; charset=utf-8');
    $limit  = max(1, min(50, (int)($_GET['limit']  ?? 10))); // sécurité
    $offset = max(0,          (int)($_GET['offset'] ?? 0));

    $blogs = $blogModel->getLastBlogsPaged($limit, $offset);

    // Rend uniquement le fragment HTML (la liste) et s'arrête
    require __DIR__ . '/assets/templates/ListeBlog.php';
    exit;
}

// /actualites/load?offset=50&limit=10 -> renvoie le fragment HTML
if ($page === 'actualites' && $step === 'load') {
    header('Content-Type: text/html; charset=utf-8');
    $limit  = max(1, min(50, (int)($_GET['limit']  ?? 10))); // sécurité
    $offset = max(0,          (int)($_GET['offset'] ?? 0));

    $articles = $articleModel->getLastArticlesPaged($limit, $offset);
    require __DIR__ . '/assets/templates/ListeActualite.php';
    exit;
}



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

    case 'actualites':
        $meta['title'] = 'Actualités | PlayZone';
        $meta['description'] = 'Toutes les dernières news et mises à jour de l’univers gaming.';
        $view = '/assets/page/Actualites.php';
        break;

    case 'blogs':
        $meta['title'] = 'Blogs | PlayZone';
        $meta['description'] = 'Lisez les articles et opinions des membres de la communauté.';
        $view = '/assets/page/Blogs.php';
        break;

    // ------------------
    // PAGES VUES / FORMULAIRES (app/View)
    // ------------------
    case 'contacts':
        $meta['title'] = 'Contact | PlayZone';
        $meta['description'] = 'Contactez l’équipe PlayZone pour toute question ou suggestion.';
        $view = '/app/View/Contacts.php';
        break;

    case 'connexion':
        $meta['title'] = 'Connexion | PlayZone';
        $meta['description'] = 'Connectez-vous ou créez un compte.';
        $view = '/app/View/Connexion.php';
        break;

    case 'parametres':
        $meta['title'] = 'Paramètres | PlayZone';
        $meta['description'] = 'Paramétrez les informations de votre profil utilisateur.';
        $view = '/app/View/Parametres.php';
        break;

    case 'profilutilisateur':
        $meta['title'] = 'Profil | PlayZone';
        $meta['description'] = 'Page de profil de l’utilisateur.';
        $view = '/app/View/ProfilUtilisateur.php';
        break;

    case 'article': // /article/{slug}
        $meta['title'] = 'Article | PlayZone';
        $meta['description'] = 'Page du contenu de l’article.';
        $view = '/app/View/ArticleView.php';
        break;

    case 'articleview':
        $meta['title'] = 'Article | PlayZone';
        $meta['description'] = 'Page du contenu de l’article.';
        $view = '/app/View/ArticleView.php';
        break;

    case 'blog': // /blog/{slug}
        $meta['title'] = 'Blog | PlayZone';
        $meta['description'] = 'Page du contenu du blog.';
        $view = '/app/View/BlogView.php';
        break;


    case 'blogview':
        $meta['title'] = 'Blog | PlayZone';
        $meta['description'] = 'Page du contenu du blog.';
        $view = '/app/View/BlogView.php';
        break;

    case 'ajouterunarticle':
        $meta['title'] = 'Ajouter un Article | PlayZone';
        $meta['description'] = 'Formulaire pour publier un article.';
				if (empty($_SESSION['user']['est_administrateur']))
					{
						http_response_code(403);
						exit('Accès refusé.');
					}
        $view = '/app/View/AjouterUnArticle.php';
        break;

    case 'ajouterunblog':
        $meta['title'] = 'Ajouter un Blog | PlayZone';
        $meta['description'] = 'Formulaire pour publier un blog.';
        $view = '/app/View/AjouterUnBlog.php';
        break;

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


    case 'adminarticles':
        $meta['title'] = 'Administration | PlayZone';
        $meta['description'] = 'Gestion des articles.';
        if (empty($_SESSION['user']['est_administrateur'])) {
            http_response_code(403);
            echo "<main class='box-bg'><h1>Accès refusé</h1></main>";
            break;
        }
        $articles = $articleModel->getAllForAdmin();
        $view = '/app/View/AdminArticles.php';
        break;


    case 'adminblogs':
        $meta['title'] = 'Administration | PlayZone';
        $meta['description'] = 'Gestion des blogs.';
        if (empty($_SESSION['user']['est_administrateur'])) {
            http_response_code(403);
            echo "<main class='box-bg'><h1>Accès refusé</h1></main>";
            break;
        }
        $blogs = $blogModel->getAllForAdmin();
        $view = '/app/View/AdminBlogs.php';
        break;


    case 'admincommentaires':
        $meta['title'] = 'Administration | PlayZone';
        $meta['description'] = 'Gestion des commentaires.';
        if (empty($_SESSION['user']['est_administrateur'])) {
            http_response_code(403);
            echo "<main class='box-bg'><h1>Accès refusé</h1></main>";
            break;
        }
        $comments = $commentModel->getAllForAdmin();
        $view = '/app/View/AdminCommentaires.php';
        break;

    case 'admincontacts':
    $meta['title'] = 'Administration | PlayZone';
    $meta['description'] = 'Gestion des messages de contact.';
    if (empty($_SESSION['user']['est_administrateur'])) {
        http_response_code(403);
        echo "<main class='box-bg'><h1>Accès refusé</h1></main>";
        break;
    }
    $contacts = $contactsModel->getAllForAdmin();
    $view = '/app/View/AdminContacts.php';
    break;



    default:
        http_response_code(404);
        $meta['title'] = 'Page introuvable | PlayZone';
        $meta['description'] = 'La page que vous recherchez n’existe pas ou a été déplacée.';
        $view = '/assets/page/404error.php';
        break;
}

/* ============================================================
   5) Sortie HTML (après la logique seulement)
   ============================================================ */
// Head/Menu ont besoin de $meta et $page
require __DIR__ . '/assets/templates/Head.php';
require __DIR__ . '/assets/templates/Menu.php';

// Vue principale
require __DIR__ . $view;

// Footer
require __DIR__ . '/assets/templates/Footer.php';
