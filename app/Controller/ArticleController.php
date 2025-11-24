<?php
namespace App\Controller; // Déclare l'espace de noms du contrôleur Article

use App\Model\ArticleModel;    // Importe le modèle ArticleModel pour les opérations BDD liées aux articles
use App\Model\BlogModel;
use App\Model\CommentaireModel;
use App\Model\UtilisateurModel;
use App\Helpers\EditeurHelper; // Importe un helper pour traiter le HTML de l'éditeur (images, div ac-img, etc.)
use PDO;

class ArticleController
{
    private ArticleModel $model;
    private PDO $pdo;

    // Constructeur : injection du PDO pour créer les modèles nécessaires
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->model = new ArticleModel($pdo);
    }

    /**
     * Soumission du formulaire "Ajouter un article"
     */
    public function submit(array $post, array $files): void
    {
        $titre   = trim((string)($post['titreArticle'] ?? ''));
        $contenu = (string)($post['contenuArticle'] ?? '');

        if ($titre === '' || trim(strip_tags($contenu)) === '') {
            $_SESSION['flash'][] = ['m' => 'Le titre et le contenu sont obligatoires.', 't' => 'error'];
            header('Location: ?page=ajouterunarticle');
            exit;
        }

        $userId = (int)($_SESSION['user']['id'] ?? 0);
        $baseSlug = $this->slugify($titre);
        // Initialise le slug courant avec le slug de base
        $slug = $baseSlug;
        // Compteur pour suffixer le slug en cas de doublon (ex: -2, -3, ...)
        $i = 2;
        // Tant qu’un article avec ce slug existe déjà en BDD...
        while ($this->model->slugExists($slug)) {
            // Ajoute un suffixe numérique pour obtenir un slug unique
            $slug = $baseSlug . '-' . $i++;
        }

        $commentThreadId = $this->model->createEmptyComment();
        $articleId = $this->model->createArticle($userId, $commentThreadId, $titre, $slug);
        $uploadDirWeb  = "/assets/images/imageActualites/{$articleId}";
        $uploadDirDisk = rtrim($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 3), '/') . $uploadDirWeb;

        // Si le dossier n’existe pas et qu’on n’arrive pas à le créer...
        if (!is_dir($uploadDirDisk) && !mkdir($uploadDirDisk, 0775, true)) {
            $_SESSION['flash'][] = ['m' => "Impossible de créer le dossier de l’article.", 't' => 'error'];
            // Redirige vers la page de l’article (même si le dossier n’existe pas, on évite de rester sur le POST)
            header('Location: /article/' . rawurlencode($slug));
            exit;
        }

        // 2) Bannière principale (optionnelle)

        // Vérifie si un fichier bannière a été uploadé et qu’il n’y a pas d’erreur d’upload
        if (!empty($files['banniereArticle']['tmp_name']) && ($files['banniereArticle']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            // Chemin temporaire du fichier uploadé
            $tmp = $files['banniereArticle']['tmp_name'];
            // Chemin disque final de la bannière en .webp
            $bannerPathDisk = $uploadDirDisk . '/banner.webp';

            // Déplace le fichier uploadé dans le dossier définitif
            if (!move_uploaded_file($tmp, $bannerPathDisk)) {
                // Message d’erreur si le déplacement échoue
                $_SESSION['flash'][] = ['m' => "Échec de l’enregistrement de la bannière.", 't' => 'error'];
                // Redirige vers la page de l’article
                header('Location: /article/' . rawurlencode($slug));
                exit;
            }

            // Met à jour en BDD le chemin web de la bannière associée à l’article
            $this->model->updateBanner($articleId, $uploadDirWeb . '/banner.webp');
        }

        // 3) Contenu HTML :
        // Supprime les anciens wrappers <div class="ac-img"> autour des images dans le contenu
        $contenu = EditeurHelper::stripAcImgDiv($contenu);
        $savedImagePaths = [];
        // Première passe : trouver toutes les balises <img> avec src en Data URI (base64)
        $processedHtml = preg_replace_callback(
            '#<img\s+[^>]*src=["\'](data:image/[^"\']+)["\']([^>]*)>#i',
            function(array $m) use ($uploadDirDisk, $uploadDirWeb, &$savedImagePaths) {
                // $m[1] = Data URI complète, ex: data:image/png;base64,xxx...
                $dataUri = $m[1];
                // $m[2] = les autres attributs après src (alt, class, etc.)
                $attrs   = $m[2] ?? '';

                // Récupère l’attribut alt s’il est présent
                $alt = '';
                if (preg_match('#\salt=["\']([^"\']*)["\']#i', $attrs, $a)) {
                    $alt = trim($a[1]); // Nettoie la valeur de alt
                }
                // Si aucun alt, on met une valeur par défaut
                if ($alt === '') $alt = 'image';

                // Découpe la Data URI en deux parties "meta" et "base64"
                $parts = explode(',', $dataUri, 2);
                // Si on n’a pas exactement 2 parties, ce n’est pas valide → on supprime l’image
                if (count($parts) !== 2) { return ''; }
                // Partie base64 (après la virgule)
                $b64 = $parts[1];
                // Décode la base64 en binaire
                $bin = base64_decode($b64, true);
                // Si le décodage échoue, on supprime l’image
                if ($bin === false) { return ''; }

                // Nettoie le alt pour en faire un nom de fichier safe (remplace les caractères non autorisés)
                $safe = preg_replace('/[^A-Za-z0-9_\-]/', '_', $alt);
                // Si le résultat est vide, on met "image"
                if ($safe === '') $safe = 'image';

                // Ajoute un hash basé sur la chaîne base64 pour éviter les collisions simples
                $filename = $safe . '_' . substr(sha1($b64), 0, 8) . '.webp';

                // Écrit le fichier image décodé sur le disque dans le dossier de l’article
                file_put_contents($uploadDirDisk . '/' . $filename, $bin);
                // Chemin web (URL relative) de l’image enregistrée
                $webPath = $uploadDirWeb . '/' . $filename;
                // Ajoute ce chemin à la liste des images sauvegardées
                $savedImagePaths[] = $webPath;

                // Retourne une balise <img> propre avec src vers le fichier et alt échappé
                return '<img src="' . htmlspecialchars($webPath, ENT_QUOTES) . '" alt="' . htmlspecialchars($alt, ENT_QUOTES) . '">';
            },
            $contenu // Texte HTML d’origine à traiter
        );

        // Deuxième passe : encapsuler les images consécutives dans un <div class="ac-img">
        $processedHtml = preg_replace_callback(
            // Regex qui repère 2 ou plus balises <img> qui se suivent (avec espaces éventuels)
            '/(?:\s*(<img[^>]+>)){2,}/i',
            function ($matches) {
                // $matches[0] contient tout le bloc de <img> consécutives
                $imgs = trim($matches[0]); // Supprime espaces au début/fin
                // Retourne ce bloc entouré par un <div class="ac-img"> pour mise en forme
                return "<div class=\"ac-img\">\n" . $imgs . "\n</div>";
            },
            $processedHtml // HTML déjà traité à l’étape précédente
        );

        // Transforme la liste des chemins d’images en chaîne CSV séparée par des ;
        $csv = implode(';', array_unique($savedImagePaths));

        // 4) Sauvegarder contenu + csv images
        // Met à jour l’article en BDD avec le contenu HTML final + la liste des images
        $this->model->updateContentImages($articleId, $processedHtml, $csv);

        // Ajoute un message flash de succès
        $_SESSION['flash'][] = ['m' => 'Article publié !', 't' => 'success'];

        // Redirige vers la page publique de l’article, basée sur le slug
        header('Location: /article/' . rawurlencode($slug));
        exit; // Stoppe le script après la redirection
    }

    // Méthode privée pour transformer une chaîne en "slug" URL-friendly
    private function slugify(string $str): string 
    {
            // Convertit les caractères accentués en ASCII (é → e, etc.)
            $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
            // Met tout en minuscules
            $str = strtolower($str);
            // Remplace toute séquence de caractères non alphanumériques par un tiret
            $str = preg_replace('/[^a-z0-9]+/','-', $str);
            // Supprime les tirets au début et à la fin
            $str = trim($str, '-');
            // Retourne le slug ou "article" si la chaîne est vide
            return $str ?: 'article';
    }

    // Méthode pour gérer les actions admin sur un article (update titre ou suppression)
    public function adminUpdate(array $post): void
    {
        // Vérifie le token CSRF : doit exister en session et dans le POST, et correspondre
        if (empty($_SESSION['csrf']) || empty($post['csrf']) || !hash_equals($_SESSION['csrf'], $post['csrf'])) {
            http_response_code(400);   // Renvoie le code HTTP 400 (Bad Request)
            die('CSRF invalide.');     // Stoppe le script avec un message d'erreur
        }

        // Vérifie que l'utilisateur est administrateur
        if (empty($_SESSION['user']['est_administrateur'])) {
            http_response_code(403);   // Renvoie le code 403 (Forbidden)
            die('Accès refusé.');      // Stoppe le script avec un message d'erreur
        }

        // Récupère l'ID de l'article à partir du POST
        $id = (int)($post['id'] ?? 0);
        // Si l'ID est invalide ou manquant...
        if ($id <= 0) {
            // Ajoute un message d'erreur
            $_SESSION['flash'][] = ['m' => 'ID article manquant.', 't' => 'error'];
            // Redirige vers la page d'administration des articles
            header('Location: /adminarticles'); exit;
        }

        // Si le formulaire demande une suppression (bouton "delete")
        if (isset($post['delete'])) {
            // Supprime l'article en BDD
            $this->model->delete($id);
            // Message flash de confirmation
            $_SESSION['flash'][] = ['m' => "Article #$id supprimé.", 't' => 'success'];
            // Redirige vers la liste admin des articles
            header('Location: /adminarticles'); exit;
        }

        // Si le formulaire demande une mise à jour du titre (bouton "update")
        if (isset($post['update'])) {
            // Récupère et nettoie le nouveau titre
            $titre = trim((string)($post['article_titre'] ?? ''));
            // Si le titre est vide, erreur
            if ($titre === '') {
                $_SESSION['flash'][] = ['m' => 'Le titre ne peut pas être vide.', 't' => 'error'];
                header('Location: /adminarticles'); exit;
            }
            // Met à jour le titre de l'article en BDD
            $this->model->adminUpdateTitle($id, $titre);
            // Message de succès
            $_SESSION['flash'][] = ['m' => "Article #$id mis à jour.", 't' => 'success'];
            // Redirection vers la liste admin
            header('Location: /adminarticles'); exit;
        }

        // Si aucune action reconnue, on retourne simplement vers la liste admin
        header('Location: /adminarticles'); exit;
    }

    /**
     * Gère la soumission du formulaire (POST) ou prépare l'affichage (GET)
     */
    public function handleAddArticle(): void
    {
        // Sécurité : réservé admin
        if (empty($_SESSION['user']['est_administrateur'])) {
            http_response_code(403);
            exit('Accès refusé.');
        }

        // Si POST, traiter le formulaire (exécute le modèle)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
            $this->submit($_POST, $_FILES);
            return; // submit() fait déjà la redirection
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

        // Si POST, traiter les actions admin (exécute le modèle)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
            $this->adminUpdate($_POST);
            return null; // adminUpdate() fait déjà la redirection
        }

        // Sinon, préparer les données pour la vue
        return ['articles' => $this->model->getAllForAdmin()];
    }

    /**
     * Gère la page d'accueil
     * Retourne les données nécessaires pour la vue
     */
    public function handleAccueil(): array
    {
        $blogModel = new BlogModel($this->pdo);
        $commentModel = new CommentaireModel($this->pdo);
        $userModel = new UtilisateurModel($this->pdo);

        return [
            'meta' => [
                'title' => 'Accueil | PlayZone',
                'description' => 'Découvrez les dernières actualités du jeu vidéo sur PlayZone.'
            ],
            'view' => '/assets/page/Accueil.php',
            'data' => [
                'articleModel' => $this->model,
                'blogModel' => $blogModel,
                'commentModel' => $commentModel,
                'userModel' => $userModel
            ]
        ];
    }

    /**
     * Gère la page des actualités
     * Retourne les données nécessaires pour la vue ou gère l'AJAX
     */
    public function handleActualites(string $step): ?array
    {
        // AJAX : /actualites?step=load&offset=..&limit=..
        if ($step === 'load') {
            header('Content-Type: text/html; charset=utf-8');

            $limit  = max(1, min(50, (int)($_GET['limit']  ?? 10))); // sécurité
            $offset = max(0,          (int)($_GET['offset'] ?? 0));

            $articles = $this->model->getLastArticlesPaged($limit, $offset);

            require __DIR__ . '/../../assets/templates/ListeActualite.php';
            exit;
        }

        return [
            'meta' => [
                'title' => 'Actualités | PlayZone',
                'description' => 'Toutes les dernières news et mises à jour de l\'univers gaming.'
            ],
            'view' => '/assets/page/Actualites.php',
            'data' => [
                'articleModel' => $this->model
            ]
        ];
    }

    /**
     * Gère l'affichage d'un article
     * Retourne les données nécessaires pour la vue
     */
    public function handleArticleView(): array
    {
        $blogModel = new BlogModel($this->pdo);
        $commentModel = new CommentaireModel($this->pdo);
        $userModel = new UtilisateurModel($this->pdo);

        return [
            'meta' => [
                'title' => 'Article | PlayZone',
                'description' => 'Page du contenu de l\'article.'
            ],
            'view' => '/app/View/ArticleView.php',
            'data' => [
                'articleModel' => $this->model,
                'blogModel' => $blogModel,
                'commentModel' => $commentModel,
                'userModel' => $userModel
            ]
        ];
    }

}
