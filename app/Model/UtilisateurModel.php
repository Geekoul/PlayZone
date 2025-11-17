<?php
namespace App\Model;

use PDO;

class UtilisateurModel
{
    public function __construct(private PDO $pdo) {}

    public function getById(int $id): ?array
    {
        $q = $this->pdo->prepare('SELECT * FROM utilisateur WHERE id = :id LIMIT 1');
        $q->execute(['id'=>$id]);
        $u = $q->fetch();
        return $u ?: null;
    }

    public function getByEmail(string $email): ?array
    {
        $q = $this->pdo->prepare('SELECT * FROM utilisateur WHERE email = :email LIMIT 1');
        $q->execute(['email' => $email]);
        $u = $q->fetch();
        return $u ?: null;
    }

    public function getByPseudo(string $pseudo): ?array
    {
        $q = $this->pdo->prepare('SELECT * FROM utilisateur WHERE pseudo = :p LIMIT 1');
        $q->execute(['p' => $pseudo]);
        $u = $q->fetch();
        return $u ?: null;
    }

    public function create(string $pseudo, string $email, string $hash): int
    {
        $q = $this->pdo->prepare(
            'INSERT INTO utilisateur (pseudo, email, mot_de_passe, date_inscription, est_administrateur)
             VALUES (:pseudo, :email, :mdp, CURRENT_DATE, 0)'
        );
        $q->execute(['pseudo'=>$pseudo,'email'=>$email,'mdp'=>$hash]);
        return (int)$this->pdo->lastInsertId();
    }

    public function updateProfile(int $id, array $data): void
    {
        // Construit dynamiquement SET selon les clés présentes
        $fields = [];
        $params = ['id'=>$id];

        foreach (['pseudo','email','profil_description','chemin_logo'] as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "$col = :$col";
                $params[$col] = $data[$col];
            }
        }
        if (!$fields) return; // rien à mettre à jour

        $sql = 'UPDATE utilisateur SET '.implode(', ', $fields).' WHERE id = :id LIMIT 1';
        $q = $this->pdo->prepare($sql);
        $q->execute($params);
    }

    public function updatePassword(int $id, string $hash): void
    {
        $q = $this->pdo->prepare('UPDATE utilisateur SET mot_de_passe = :h WHERE id = :id LIMIT 1');
        $q->execute(['h'=>$hash,'id'=>$id]);
    }

    public function deleteById(int $id): void
    {
        $q = $this->pdo->prepare('DELETE FROM utilisateur WHERE id = :id LIMIT 1');
        $q->execute(['id'=>$id]);
    }

    public function getAll(): array
    {
        $sql = "SELECT id, pseudo, email, profil_description, chemin_logo, date_inscription, est_administrateur
                FROM utilisateur
                ORDER BY id ASC";
        $q = $this->pdo->query($sql);
        return $q->fetchAll(PDO::FETCH_ASSOC);
    }


}
