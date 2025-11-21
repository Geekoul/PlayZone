<?php
namespace App\Model;

use PDO;

class ArticleModel
{
    public function __construct(private PDO $pdo) {}

    public function createEmptyComment(): int
    {
        $this->pdo->exec("INSERT INTO commentaire_thread () VALUES ()");
        return (int)$this->pdo->lastInsertId();
    }

    public function createArticle(int $userId, int $commentThreadId, string $titre, string $slug): int
		{
				$sql = "INSERT INTO article
									(id_utilisateur, id_commentaire_thread, article_titre, article_slug,
									article_banniere_img, article_contenu, article_contenu_img, article_date_publication)
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


    public function updateBanner(int $articleId, string $path): void
    {
        $q = $this->pdo->prepare("UPDATE article SET article_banniere_img = :p WHERE id = :id LIMIT 1");
        $q->execute([':p' => $path, ':id' => $articleId]);
    }

    public function updateContentImages(int $articleId, string $html, string $csv): void
    {
        $q = $this->pdo->prepare("
            UPDATE article
               SET article_contenu = :c, article_contenu_img = :imgs
             WHERE id = :id
             LIMIT 1
        ");
        $q->execute([
            ':c'   => $html,
            ':imgs'=> $csv,
            ':id'  => $articleId,
        ]);
    }

    public function get(int $id): ?array
    {
        $q = $this->pdo->prepare("SELECT * FROM article WHERE id = ? LIMIT 1");
        $q->execute([$id]);
        $row = $q->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function delete(int $id): void
    {
        $q = $this->pdo->prepare("DELETE FROM article WHERE id = ? LIMIT 1");
        $q->execute([$id]);
    }

    public function slugExists(string $slug): bool 
    {
            $q = $this->pdo->prepare("SELECT 1 FROM article WHERE article_slug = ? LIMIT 1");
            $q->execute([$slug]);
            // fetchColumn() récupère la première colonne de la première ligne.
            // Si une ligne existe, fetchColumn() renverra "1", sinon false.
            // Le cast (bool) transforme cela en true (slug existant) ou false (slug libre).
            return (bool)$q->fetchColumn();
    }

    public function getBySlug(string $slug): ?array 
    {
            $q = $this->pdo->prepare("SELECT * FROM article WHERE article_slug = ? LIMIT 1");
            $q->execute([$slug]);
            $row = $q->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
    }

    public function getLastArticlesPaged(int $limit, int $offset = 0): array
    {
        $sql = "SELECT
                    a.id,
                    a.article_titre            AS titre,
                    a.article_slug             AS slug,
                    a.article_banniere_img     AS banniere,
                    a.article_contenu          AS contenu_html,
                    a.article_date_publication AS published_at
                FROM article a
                ORDER BY a.article_date_publication DESC, a.id DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit',  $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    public function getAllForAdmin(): array
    {
        $sql = "SELECT 
                    a.id,
                    a.id_utilisateur,
                    u.pseudo AS auteur_pseudo,
                    a.article_date_publication,
                    a.article_titre
                FROM article a
                LEFT JOIN utilisateur u ON u.id = a.id_utilisateur
                ORDER BY a.id DESC";
        $q = $this->pdo->query($sql);
        return $q->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    public function adminUpdateTitle(int $id, string $titre): void
    {
        $q = $this->pdo->prepare("UPDATE article SET article_titre = :t WHERE id = :id LIMIT 1");
        $q->execute([':t' => $titre, ':id' => $id]);
    }



}
