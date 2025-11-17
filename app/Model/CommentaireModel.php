<?php
namespace App\Model;

use PDO;

class CommentaireModel
{
    public function __construct(private PDO $pdo) {}

    /** Retourne les commentaires d’un fil (non supprimés), plus récents d’abord */
    public function listByThread(int $threadId): array
    {
        $sql = "SELECT 
                    c.id,
                    c.id_utilisateur,
                    c.id_commentaire_thread,
                    c.commentaire_contenu       AS contenu,
                    c.commentaire_date_publication AS date_publication,
                    u.pseudo,
                    u.chemin_logo
                FROM commentaire c
                LEFT JOIN utilisateur u ON u.id = c.id_utilisateur
                WHERE c.id_commentaire_thread = :tid
                  AND c.commentaire_supprimer = 0
                ORDER BY c.commentaire_date_publication DESC, c.id DESC";
        $q = $this->pdo->prepare($sql);
        $q->execute([':tid' => $threadId]);
        return $q->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** Ajoute un commentaire et renvoie son ID */
    public function add(int $userId, int $threadId, string $contenu): int
    {
        // la colonne est en VARCHAR(300) -> on tronque si besoin
        if (mb_strlen($contenu) > 300) {
            $contenu = mb_substr($contenu, 0, 300);
        }

        $q = $this->pdo->prepare("
            INSERT INTO commentaire
                (id_utilisateur, id_commentaire_thread, commentaire_contenu, commentaire_date_publication, commentaire_supprimer)
            VALUES
                (:uid, :tid, :contenu, NOW(), 0)
        ");
        $q->execute([
            ':uid'     => $userId ?: null,
            ':tid'     => $threadId,
            ':contenu' => $contenu,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /** Récupère un commentaire par id */
    public function get(int $id): ?array
    {
        $q = $this->pdo->prepare("SELECT * FROM commentaire WHERE id = ? LIMIT 1");
        $q->execute([$id]);
        $row = $q->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Soft delete: marque le commentaire comme supprimé */
    public function delete(int $id): void
    {
        $q = $this->pdo->prepare("UPDATE commentaire SET commentaire_supprimer = 1 WHERE id = ? LIMIT 1");
        $q->execute([$id]);
    }

    public function getAllForAdmin(): array
    {
        $sql = "SELECT
                    c.id,
                    c.id_commentaire_thread,
                    c.id_utilisateur,
                    c.commentaire_contenu,
                    c.commentaire_date_publication,
                    u.pseudo AS auteur_pseudo,
                    -- Déterminer le fil
                    COALESCE(b.blog_titre, a.article_titre) AS thread_titre,
                    COALESCE(b.blog_slug,  a.article_slug)  AS thread_slug,
                    CASE WHEN b.id IS NOT NULL THEN 'blog'
                        WHEN a.id IS NOT NULL THEN 'article'
                        ELSE 'thread' END AS thread_type
                FROM commentaire c
                LEFT JOIN utilisateur u ON u.id = c.id_utilisateur
                LEFT JOIN blog     b ON b.id_commentaire_thread = c.id_commentaire_thread
                LEFT JOIN article  a ON a.id_commentaire_thread = c.id_commentaire_thread
                WHERE c.commentaire_supprimer = 0
                ORDER BY c.commentaire_date_publication DESC, c.id DESC";
        $q = $this->pdo->query($sql);
        return $q->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /** MAJ du contenu (admin) */
    public function adminUpdateContent(int $id, string $contenu): void
    {
        if (mb_strlen($contenu) > 300) {
            $contenu = mb_substr($contenu, 0, 300);
        }
        // On neutralise le HTML pour l’admin aussi (même règle que create)
        $contenu = strip_tags($contenu);

        $q = $this->pdo->prepare("
            UPDATE commentaire
            SET commentaire_contenu = :c
            WHERE id = :id
            LIMIT 1
        ");
        $q->execute([':c' => $contenu, ':id' => $id]);
}

}
