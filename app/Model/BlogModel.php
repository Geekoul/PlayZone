<?php
namespace App\Model;

use PDO;

class BlogModel
{
    public function __construct(private PDO $pdo) {}

    /** Crée un fil de commentaires vide et renvoie son ID */
    public function createEmptyComment(): int
    {
        $this->pdo->exec("INSERT INTO commentaire_thread () VALUES ()");
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Insère un nouveau blog minimal (sans contenu/images) et renvoie son ID.
     * Colonnes supposées: id_utilisateur, id_commentaire_thread, blog_titre, blog_slug,
     *   blog_banniere_img, blog_contenu, blog_contenu_img, blog_date_publication
     */
    public function createBlog(int $userId, int $commentThreadId, string $titre, string $slug): int
    {
        $sql = "INSERT INTO blog
                  (id_utilisateur, id_commentaire_thread, blog_titre, blog_slug,
                   blog_banniere_img, blog_contenu, blog_contenu_img, blog_date_publication)
                VALUES
                  (:uid, :cid, :titre, :slug, '', '', '', NOW())";
        $q = $this->pdo->prepare($sql);
        $q->execute([
            ':uid'   => $userId ?: null,
            ':cid'   => $commentThreadId,
            ':titre' => $titre,
            ':slug'  => $slug,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /** Met à jour le chemin de la bannière */
    public function updateBanner(int $blogId, string $path): void
    {
        $q = $this->pdo->prepare("UPDATE blog SET blog_banniere_img = :p WHERE id = :id LIMIT 1");
        $q->execute([':p' => $path, ':id' => $blogId]);
    }

    /** Met à jour le contenu HTML + la liste CSV des images */
    public function updateContentImages(int $blogId, string $html, string $csv): void
    {
        $q = $this->pdo->prepare("
            UPDATE blog
               SET blog_contenu = :c, blog_contenu_img = :imgs
             WHERE id = :id
             LIMIT 1
        ");
        $q->execute([
            ':c'   => $html,
            ':imgs'=> $csv,
            ':id'  => $blogId,
        ]);
    }

    /** Récupérer par id */
    public function get(int $id): ?array
    {
        $q = $this->pdo->prepare("SELECT * FROM blog WHERE id = ? LIMIT 1");
        $q->execute([$id]);
        $row = $q->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Slugs */
    public function slugExists(string $slug): bool
    {
        $q = $this->pdo->prepare("SELECT 1 FROM blog WHERE blog_slug = ? LIMIT 1");
        $q->execute([$slug]);
        return (bool)$q->fetchColumn();
    }

    public function getBySlug(string $slug): ?array
    {
        $q = $this->pdo->prepare("SELECT * FROM blog WHERE blog_slug = ? LIMIT 1");
        $q->execute([$slug]);
        $row = $q->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** (optionnel) Supprimer un blog */
    public function delete(int $id): void
    {
        $q = $this->pdo->prepare("DELETE FROM blog WHERE id = ? LIMIT 1");
        $q->execute([$id]);
    }

    public function getByUserId(int $userId): array
    {
        $sql = "SELECT 
                    b.id,
                    b.blog_titre             AS titre,
                    b.blog_slug              AS slug,
                    b.blog_banniere_img      AS banniere,
                    b.blog_date_publication  AS published_at,
                    COUNT(c.id)              AS nb_commentaires
                FROM blog b
                LEFT JOIN commentaire c
                ON c.id_commentaire_thread = b.id_commentaire_thread
                AND c.commentaire_supprimer = 0
                WHERE b.id_utilisateur = :uid
                GROUP BY b.id
                ORDER BY b.blog_date_publication DESC, b.id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getLastBlogs(int $limit = 20): array
    {
        $sql = "SELECT 
                    b.id,
                    b.blog_titre             AS titre,
                    b.blog_slug              AS slug,
                    b.blog_banniere_img      AS banniere,
                    b.blog_date_publication  AS published_at,
                    u.id                     AS id_utilisateur,
                    u.pseudo                 AS auteur_pseudo,
                    u.chemin_logo            AS auteur_logo,
                    COUNT(c.id)              AS nb_commentaires
                FROM blog b
                LEFT JOIN utilisateur u
                ON u.id = b.id_utilisateur
                LEFT JOIN commentaire c
                ON c.id_commentaire_thread = b.id_commentaire_thread
                AND c.commentaire_supprimer = 0
                GROUP BY b.id
                ORDER BY b.blog_date_publication DESC, b.id DESC
                LIMIT :limit";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getLastBlogsPaged(int $limit, int $offset = 0): array
    {
        $sql = "SELECT 
                    b.id,
                    b.blog_titre             AS titre,
                    b.blog_slug              AS slug,
                    b.blog_banniere_img      AS banniere,
                    b.blog_date_publication  AS published_at,
                    u.id                     AS id_utilisateur,
                    u.pseudo                 AS auteur_pseudo,
                    u.chemin_logo            AS auteur_logo,
                    COUNT(c.id)              AS nb_commentaires
                FROM blog b
                LEFT JOIN utilisateur u
                ON u.id = b.id_utilisateur
                LEFT JOIN commentaire c
                ON c.id_commentaire_thread = b.id_commentaire_thread
                AND c.commentaire_supprimer = 0
                GROUP BY b.id
                ORDER BY b.blog_date_publication DESC, b.id DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit',  $limit,  \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    public function getAllForAdmin(): array
    {
        $sql = "SELECT 
                    b.id,
                    b.id_utilisateur,
                    u.pseudo AS auteur_pseudo,
                    b.blog_date_publication,
                    b.blog_titre
                FROM blog b
                LEFT JOIN utilisateur u ON u.id = b.id_utilisateur
                ORDER BY b.id DESC";
        $q = $this->pdo->query($sql);
        return $q->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    public function adminUpdateTitle(int $id, string $titre): void
    {
        $q = $this->pdo->prepare("UPDATE blog SET blog_titre = :t WHERE id = :id LIMIT 1");
        $q->execute([':t' => $titre, ':id' => $id]);
    }

}
