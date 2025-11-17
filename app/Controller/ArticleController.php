<?php
namespace App\Controller;

use App\Model\ArticleModel;
use App\Helpers\EditeurHelper;

class ArticleController
{
    public function __construct(private ArticleModel $model) {}

    /**
     * Soumission du formulaire "Ajouter un article"
     * Route: ?page=ajouterUnArticle (POST)
     */
    public function submit(array $post, array $files): void
    {
        // (Optionnel) sécurité minimale: exiger un titre & contenu
        $titre   = trim((string)($post['titreArticle'] ?? ''));
        $contenu = (string)($post['contenuArticle'] ?? '');

        if ($titre === '' || trim(strip_tags($contenu)) === '') {
            $_SESSION['flash'][] = ['m' => 'Le titre et le contenu sont obligatoires.', 't' => 'error'];
            header('Location: ?page=ajouterunarticle');
            exit;
        }

        // Auteur = user connecté (ton index bloque déjà l’accès aux non-admins)
        $userId = (int)($_SESSION['user']['id'] ?? 0);
				$baseSlug = $this->slugify($titre);
				$slug = $baseSlug;
				$i = 2;
				while ($this->model->slugExists($slug)) {
						$slug = $baseSlug . '-' . $i++;
				}

				$commentThreadId = $this->model->createEmptyComment();
				$articleId = $this->model->createArticle($userId, $commentThreadId, $titre, $slug);

        // Préparer le dossier de stockage des médias de l’article
        $uploadDirWeb  = "/assets/images/imageActualites/{$articleId}";
        $uploadDirDisk = rtrim($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 3), '/') . $uploadDirWeb;
        if (!is_dir($uploadDirDisk) && !mkdir($uploadDirDisk, 0775, true)) {
            $_SESSION['flash'][] = ['m' => "Impossible de créer le dossier de l’article.", 't' => 'error'];
						header('Location: /article/' . rawurlencode($slug));
						exit;
        }

        // 2) Bannière principale (optionnelle)
        if (!empty($files['banniereArticle']['tmp_name']) && ($files['banniereArticle']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $tmp = $files['banniereArticle']['tmp_name'];
            $bannerPathDisk = $uploadDirDisk . '/banner.webp';
            if (!move_uploaded_file($tmp, $bannerPathDisk)) {
                $_SESSION['flash'][] = ['m' => "Échec de l’enregistrement de la bannière.", 't' => 'error'];
                header('Location: /article/' . rawurlencode($slug));
								exit;
            }
            $this->model->updateBanner($articleId, $uploadDirWeb . '/banner.webp');
        }

        // 3) Contenu HTML : 
        // - nettoyer les anciennes <div class="ac-img">...
        // - extraire/convertir les <img src="data:image/..."> en fichiers .webp
        // - encapsuler les images consécutives dans <div class="ac-img">...</div>
        $contenu = EditeurHelper::stripAcImgDiv($contenu);

        $savedImagePaths = [];
        $processedHtml = preg_replace_callback(
            '#<img\s+[^>]*src=["\'](data:image/[^"\']+)["\']([^>]*)>#i',
            function(array $m) use ($uploadDirDisk, $uploadDirWeb, &$savedImagePaths) {
                $dataUri = $m[1];
                $attrs   = $m[2] ?? '';

                // Récup alt si présent
                $alt = '';
                if (preg_match('#\salt=["\']([^"\']*)["\']#i', $attrs, $a)) {
                    $alt = trim($a[1]);
                }
                if ($alt === '') $alt = 'image';

                // Décoder la Data URI
                $parts = explode(',', $dataUri, 2);
                if (count($parts) !== 2) { return ''; }
                $b64 = $parts[1];
                $bin = base64_decode($b64, true);
                if ($bin === false) { return ''; }

                // Nom de fichier safe
                $safe = preg_replace('/[^A-Za-z0-9_\-]/', '_', $alt);
                if ($safe === '') $safe = 'image';
                // éviter collisions simples
                $filename = $safe . '_' . substr(sha1($b64), 0, 8) . '.webp';

                // Écrire sur disque
                file_put_contents($uploadDirDisk . '/' . $filename, $bin);
                $webPath = $uploadDirWeb . '/' . $filename;
                $savedImagePaths[] = $webPath;

                // Remplacer le <img ...> par un <img src="/chemin" alt="...">
                return '<img src="' . htmlspecialchars($webPath, ENT_QUOTES) . '" alt="' . htmlspecialchars($alt, ENT_QUOTES) . '">';
            },
            $contenu
        );

        // Encapsuler les images consécutives
        $processedHtml = preg_replace_callback(
            '/(?:\s*(<img[^>]+>)){2,}/i',
            function ($matches) {
                $imgs = trim($matches[0]);
                return "<div class=\"ac-img\">\n" . $imgs . "\n</div>";
            },
            $processedHtml
        );

        $csv = implode(';', array_unique($savedImagePaths));

        // 4) Sauvegarder contenu + csv images
        $this->model->updateContentImages($articleId, $processedHtml, $csv);

        $_SESSION['flash'][] = ['m' => 'Article publié !', 't' => 'success'];
        // Redirige vers la page de lecture (ta route s’appelle "articleview")
        header('Location: /article/' . rawurlencode($slug));
				exit;
    }

    private function slugify(string $str): string 
    {
            $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
            $str = strtolower($str);
            $str = preg_replace('/[^a-z0-9]+/','-', $str);
            $str = trim($str, '-');
            return $str ?: 'article';
    }

    public function adminUpdate(array $post): void
    {
        // CSRF + droits admin
        if (empty($_SESSION['csrf']) || empty($post['csrf']) || !hash_equals($_SESSION['csrf'], $post['csrf'])) {
            http_response_code(400);
            die('CSRF invalide.');
        }
        if (empty($_SESSION['user']['est_administrateur'])) {
            http_response_code(403);
            die('Accès refusé.');
        }

        $id = (int)($post['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['flash'][] = ['m' => 'ID article manquant.', 't' => 'error'];
            header('Location: /adminarticles'); exit;
        }

        // Suppression
        if (isset($post['delete'])) {
            $this->model->delete($id);
            $_SESSION['flash'][] = ['m' => "Article #$id supprimé.", 't' => 'success'];
            header('Location: /adminarticles'); exit;
        }

        // Mise à jour du titre
        if (isset($post['update'])) {
            $titre = trim((string)($post['article_titre'] ?? ''));
            if ($titre === '') {
                $_SESSION['flash'][] = ['m' => 'Le titre ne peut pas être vide.', 't' => 'error'];
                header('Location: /adminarticles'); exit;
            }
            $this->model->adminUpdateTitle($id, $titre);
            $_SESSION['flash'][] = ['m' => "Article #$id mis à jour.", 't' => 'success'];
            header('Location: /adminarticles'); exit;
        }

        header('Location: /adminarticles'); exit;
    }

}
