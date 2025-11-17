<?php
namespace App\Controller;

use App\Model\CommentaireModel;

class CommentaireController
{
    public function __construct(private CommentaireModel $model) {}

    /** Ajout d’un commentaire : POST ?page=commentaire&step=ajouter */
    public function add(array $post): void
    {
        $this->assertAuth();
        $this->assertCsrf($post);

        $uid      = (int)$_SESSION['user']['id'];
        $threadId = (int)($post['thread_id'] ?? 0);
        $contenu  = trim((string)($post['contenu'] ?? ''));

        if ($threadId <= 0 || $contenu === '') {
            $_SESSION['flash'][] = ['m' => 'Commentaire vide.', 't' => 'error'];
            $this->back();
        }

        // Sécurité simple (éviter HTML arbitraire dans les coms)
        $contenu = strip_tags($contenu);

        $this->model->add($uid, $threadId, $contenu);
        $_SESSION['flash'][] = ['m' => 'Commentaire publié.', 't' => 'success'];

        $this->redirectRefererOr('/accueil');
    }

    /** Suppression : POST ?page=commentaire&step=supprimer */
    public function delete(array $post): void
    {
        $this->assertAuth();
        $this->assertCsrf($post);

        $cid = (int)($post['comment_id'] ?? 0);
        if ($cid <= 0) { $this->back(); }

        $row = $this->model->get($cid);
        if (!$row) {
            $_SESSION['flash'][] = ['m' => 'Commentaire introuvable.', 't' => 'error'];
            $this->back();
        }

        $isOwner = ((int)$row['id_utilisateur'] === (int)$_SESSION['user']['id']);
        $isAdmin = !empty($_SESSION['user']['est_administrateur']);

        if (!$isOwner && !$isAdmin) {
            http_response_code(403);
            $_SESSION['flash'][] = ['m' => 'Vous ne pouvez pas supprimer ce commentaire.', 't' => 'error'];
            $this->back();
        }

        $this->model->delete($cid);
        $_SESSION['flash'][] = ['m' => 'Commentaire supprimé.', 't' => 'success'];

        $this->redirectRefererOr('/accueil');
    }

    // ---------- helpers ----------
    private function assertAuth(): void
    {
        if (empty($_SESSION['user'])) {
            http_response_code(401);
            $_SESSION['flash'][] = ['m' => 'Connectez-vous pour commenter.', 't' => 'error'];
            header('Location: /connexion'); exit;
        }
    }

    private function assertCsrf(array $post): void
    {
        if (empty($_SESSION['csrf']) || empty($post['csrf']) || !hash_equals($_SESSION['csrf'], $post['csrf'])) {
            http_response_code(400);
            exit('CSRF invalide.');
        }
    }

    private function back(): never
    {
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/accueil')); exit;
    }

    private function redirectRefererOr(string $fallback): never
    {
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? $fallback)); exit;
    }

    public function adminUpdate(array $post): void
    {
        // CSRF + droits admin
        if (empty($_SESSION['csrf']) || empty($post['csrf']) || !hash_equals($_SESSION['csrf'], $post['csrf'])) {
            http_response_code(400);
            exit('CSRF invalide.');
        }
        if (empty($_SESSION['user']['est_administrateur'])) {
            http_response_code(403);
            exit('Accès refusé.');
        }

        $id = (int)($post['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['flash'][] = ['m' => 'ID commentaire manquant.', 't' => 'error'];
            header('Location: /admincommentaires'); exit;
        }

        // Suppression (soft delete)
        if (isset($post['delete'])) {
            $this->model->delete($id);
            $_SESSION['flash'][] = ['m' => "Commentaire #$id supprimé.", 't' => 'success'];
            header('Location: /admincommentaires'); exit;
        }

        // Mise à jour du contenu
        if (isset($post['update'])) {
            $contenu = (string)($post['commentaire_contenu'] ?? '');
            $this->model->adminUpdateContent($id, $contenu);
            $_SESSION['flash'][] = ['m' => "Commentaire #$id mis à jour.", 't' => 'success'];
            header('Location: /admincommentaires'); exit;
        }

        header('Location: /admincommentaires'); exit;
    }
}
