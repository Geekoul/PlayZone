<?php
namespace App\Controller;

use App\Model\BlogModel;
use App\Helpers\EditeurHelper;

class BlogController
{
    public function __construct(private BlogModel $model) {}

    /** Soumission du formulaire "Ajouter un blog" — route: ?page=ajouterunblog (POST) */
    public function submit(array $post, array $files): void
    {
        $titre   = trim((string)($post['titreBlog'] ?? ''));
        $contenu = (string)($post['contenuBlog'] ?? '');

        if ($titre === '' || trim(strip_tags($contenu)) === '') {
            $_SESSION['flash'][] = ['m' => 'Le titre et le contenu sont obligatoires.', 't' => 'error'];
            header('Location: ?page=ajouterunblog');
            exit;
        }

        $userId   = (int)($_SESSION['user']['id'] ?? 0);

        // slug unique
        $baseSlug = $this->slugify($titre);
        $slug     = $baseSlug;
        $i = 2;
        while ($this->model->slugExists($slug)) {
            $slug = $baseSlug . '-' . $i++;
        }

        // créer thread + blog
        $commentThreadId = $this->model->createEmptyComment();
        $blogId = $this->model->createBlog($userId, $commentThreadId, $titre, $slug);

        // dossier médias du blog
        $uploadDirWeb  = "/assets/images/imageBlogs/{$blogId}";
        $uploadDirDisk = rtrim($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 3), '/') . $uploadDirWeb;
        if (!is_dir($uploadDirDisk) && !mkdir($uploadDirDisk, 0775, true)) {
            $_SESSION['flash'][] = ['m' => "Impossible de créer le dossier du blog.", 't' => 'error'];
            header('Location: /blog/' . rawurlencode($slug));
            exit;
        }

        // bannière principale (optionnelle)
        if (!empty($files['banniereBlog']['tmp_name']) && ($files['banniereBlog']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $tmp = $files['banniereBlog']['tmp_name'];
            $bannerPathDisk = $uploadDirDisk . '/banner.webp';
            if (!move_uploaded_file($tmp, $bannerPathDisk)) {
                $_SESSION['flash'][] = ['m' => "Échec de l’enregistrement de la bannière.", 't' => 'error'];
                header('Location: /blog/' . rawurlencode($slug));
                exit;
            }
            $this->model->updateBanner($blogId, $uploadDirWeb . '/banner.webp');
        }

        // contenu + images base64 -> fichiers
        $contenu = EditeurHelper::stripAcImgDiv($contenu);

        $savedImagePaths = [];
        $processedHtml = preg_replace_callback(
            '#<img\s+[^>]*src=["\'](data:image/[^"\']+)["\']([^>]*)>#i',
            function(array $m) use ($uploadDirDisk, $uploadDirWeb, &$savedImagePaths) {
                $dataUri = $m[1];
                $attrs   = $m[2] ?? '';

                $alt = '';
                if (preg_match('#\salt=["\']([^"\']*)["\']#i', $attrs, $a)) {
                    $alt = trim($a[1]);
                }
                if ($alt === '') $alt = 'image';

                $parts = explode(',', $dataUri, 2);
                if (count($parts) !== 2) { return ''; }
                $b64 = $parts[1];
                $bin = base64_decode($b64, true);
                if ($bin === false) { return ''; }

                $safe = preg_replace('/[^A-Za-z0-9_\-]/', '_', $alt);
                if ($safe === '') $safe = 'image';
                $filename = $safe . '_' . substr(sha1($b64), 0, 8) . '.webp';

                file_put_contents($uploadDirDisk . '/' . $filename, $bin);
                $webPath = $uploadDirWeb . '/' . $filename;
                $savedImagePaths[] = $webPath;

                return '<img src="' . htmlspecialchars($webPath, ENT_QUOTES) . '" alt="' . htmlspecialchars($alt, ENT_QUOTES) . '">';
            },
            $contenu
        );

        // encapsuler images consécutives
        $processedHtml = preg_replace_callback(
            '/(?:\s*(<img[^>]+>)){2,}/i',
            function ($matches) {
                $imgs = trim($matches[0]);
                return "<div class=\"ac-img\">\n" . $imgs . "\n</div>";
            },
            $processedHtml
        );

        $csv = implode(';', array_unique($savedImagePaths));
        $this->model->updateContentImages($blogId, $processedHtml, $csv);

        $_SESSION['flash'][] = ['m' => 'Blog publié !', 't' => 'success'];
        header('Location: /blog/' . rawurlencode($slug));
        exit;
    }

    private function slugify(string $str): string
    {
        $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str);
        $str = strtolower($str);
        $str = preg_replace('/[^a-z0-9]+/','-', $str);
        $str = trim($str, '-');
        return $str ?: 'blog';
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
            $_SESSION['flash'][] = ['m' => 'ID blog manquant.', 't' => 'error'];
            header('Location: /adminblogs'); exit;
        }

        // Suppression
        if (isset($post['delete'])) {
            $this->model->delete($id);
            $_SESSION['flash'][] = ['m' => "Blog #$id supprimé.", 't' => 'success'];
            header('Location: /adminblogs'); exit;
        }

        // Mise à jour du titre
        if (isset($post['update'])) {
            $titre = trim((string)($post['blog_titre'] ?? ''));
            if ($titre === '') {
                $_SESSION['flash'][] = ['m' => 'Le titre ne peut pas être vide.', 't' => 'error'];
                header('Location: /adminblogs'); exit;
            }
            $this->model->adminUpdateTitle($id, $titre);
            $_SESSION['flash'][] = ['m' => "Blog #$id mis à jour.", 't' => 'success'];
            header('Location: /adminblogs'); exit;
        }

        header('Location: /adminblogs'); exit;
    }

}
