<?php
session_start();

require_once __DIR__ . '/vendor/autoload.php';

use App\Database;

// Controllers
use App\Controller\UtilisateurController;
use App\Controller\ContactsController;
use App\Controller\ArticleController;
use App\Controller\BlogController;
use App\Controller\CommentaireController;


//2) INITIALISATION (DB, CSRF)
$db  = new Database();
$pdo = $db->getConnection();

// --- CSRF token ---
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}


// 3) ROUTE (page, step, id) + hygiène
$page = $_GET['page'] ?? 'accueil';
$step = $_GET['step'] ?? '';
$id   = isset($_GET['id']) ? (int) $_GET['id'] : null;

// Hygiène minimale : on garde lettres/chiffres/tirets
$page = strtolower(preg_replace('/[^a-z0-9\-]/', '', $page));
$step = strtolower(preg_replace('/[^a-z0-9\-]/', '', $step));

$meta = [
    'title'       => 'PlayZone',
    'description' => 'L’espace dédié à l’actualité et aux discussions sur le jeu vidéo.'
];
$view = '/assets/page/404error.php';

// 4) ROUTING GLOBAL (par page) : SEULEMENT LES CONTRÔLEURS

$result = null;

switch ($page) {
    case 'accueil':
        $articleCtrl = new ArticleController($pdo);
        $result = $articleCtrl->handleAccueil();
        break;

    case 'profilutilisateur':
        $userCtrl = new UtilisateurController($pdo);
        $result = $userCtrl->handleProfilUtilisateur();
        break;

    case 'article':
    case 'articleview':
        $articleCtrl = new ArticleController($pdo);
        $result = $articleCtrl->handleArticleView();
        break;

    case 'blog':
    case 'blogview':
        $blogCtrl = new BlogController($pdo);
        $result = $blogCtrl->handleBlogView();
        break;

    case 'actualites':
        $articleCtrl = new ArticleController($pdo);
        $result = $articleCtrl->handleActualites($step);
        if ($result === null) exit; // AJAX géré, sortie déjà effectuée
        break;

    case 'blogs':
        $blogCtrl = new BlogController($pdo);
        $result = $blogCtrl->handleBlogs($step);
        if ($result === null) exit; // AJAX géré, sortie déjà effectuée
        break;

    case 'connexion':
        $userCtrl = new UtilisateurController($pdo);
        $userCtrl->handleConnexion($step);
        $meta['title']       = 'Connexion | PlayZone';
        $meta['description'] = 'Connectez-vous ou créez un compte.';
        $view = '/app/View/Connexion.php';
        break;

    case 'parametres':
        $userCtrl = new UtilisateurController($pdo);
        $userCtrl->handleParametres();
        $meta['title']       = 'Paramètres | PlayZone';
        $meta['description'] = 'Paramétrez les informations de votre profil utilisateur.';
        $view = '/app/View/Parametres.php';
        break;

    case 'ajouterunarticle':
        $articleCtrl = new ArticleController($pdo);
        $articleCtrl->handleAddArticle();
        $meta['title']       = 'Ajouter un Article | PlayZone';
        $meta['description'] = 'Formulaire pour publier un article.';
        $view = '/app/View/AjouterUnArticle.php';
        break;

    case 'ajouterunblog':
        $blogCtrl = new BlogController($pdo);
        $blogCtrl->handleAddBlog();
        $meta['title']       = 'Ajouter un Blog | PlayZone';
        $meta['description'] = 'Formulaire pour publier un blog.';
        $view = '/app/View/AjouterUnBlog.php';
        break;

    case 'commentaire':
        $commentCtrl = new CommentaireController($pdo);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($step === 'ajouter') {
                $commentCtrl->add($_POST);
            } elseif ($step === 'supprimer') {
                $commentCtrl->delete($_POST);
            }
        }
        http_response_code(404);
        $meta['title']       = 'Page introuvable | PlayZone';
        $meta['description'] = 'La page que vous recherchez n\'existe pas ou a été déplacée.';
        $view = '/assets/page/404error.php';
        break;

    case 'contacts':
        $contactsCtrl = new ContactsController($pdo);
        $contactsCtrl->handle();
        $meta['title']       = 'Contact | PlayZone';
        $meta['description'] = 'Contactez l\'équipe PlayZone pour toute question ou suggestion.';
        $view = '/app/View/Contacts.php';
        break;

    // Administrateur    

    case 'adminutilisateur':
        $userCtrl = new UtilisateurController($pdo);
        $data = $userCtrl->handleAdmin();
        if ($data === null) break; // Redirection effectuée
        extract($data); // Extrait les variables pour la vue ($users)
        $meta['title']       = 'Administration | PlayZone';
        $meta['description'] = 'Gestion des utilisateurs.';
        $view = '/app/View/AdminUtilisateurs.php';
        break;

    case 'adminarticles':
        $articleCtrl = new ArticleController($pdo);
        $data = $articleCtrl->handleAdmin();
        if ($data === null) break; // Redirection effectuée
        extract($data); // Extrait les variables pour la vue ($articles)
        $meta['title']       = 'Administration | PlayZone';
        $meta['description'] = 'Gestion des articles.';
        $view = '/app/View/AdminArticles.php';
        break;

    case 'adminblogs':
        $blogCtrl = new BlogController($pdo);
        $data = $blogCtrl->handleAdmin();
        if ($data === null) break; // Redirection effectuée
        extract($data); // Extrait les variables pour la vue ($blogs)
        $meta['title']       = 'Administration | PlayZone';
        $meta['description'] = 'Gestion des blogs.';
        $view = '/app/View/AdminBlogs.php';
        break;

    case 'admincommentaires':
        $commentCtrl = new CommentaireController($pdo);
        $data = $commentCtrl->handleAdmin();
        if ($data === null) break; // Redirection effectuée
        extract($data); // Extrait les variables pour la vue ($comments)
        $meta['title']       = 'Administration | PlayZone';
        $meta['description'] = 'Gestion des commentaires.';
        $view = '/app/View/AdminCommentaires.php';
        break;

    case 'admincontacts':
        $contactsCtrl = new ContactsController($pdo);
        $data = $contactsCtrl->handleAdmin();
        if ($data === null) break; // Redirection effectuée
        extract($data); // Extrait les variables pour la vue ($contacts)
        $meta['title']       = 'Administration | PlayZone';
        $meta['description'] = 'Gestion des messages de contact.';
        $view = '/app/View/AdminContacts.php';
        break;

    default:
        http_response_code(404);
        $meta['title']       = 'Page introuvable | PlayZone';
        $meta['description'] = 'La page que vous recherchez n\'existe pas ou a été déplacée.';
        $view = '/assets/page/404error.php';
        break;
}

// Si un résultat a été retourné par un contrôleur, utiliser ses données
if ($result !== null) {
    $meta = $result['meta'];
    $view = $result['view'];
    // Extraire les données pour les vues
    if (isset($result['data'])) {
        extract($result['data']);
    }
}


// 6) SORTIE HTML

require __DIR__ . '/assets/templates/Head.php';
require __DIR__ . '/assets/templates/Menu.php';
require __DIR__ . $view;
require __DIR__ . '/assets/templates/Footer.php';
