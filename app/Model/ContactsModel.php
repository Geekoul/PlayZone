<?php
namespace App\Model;

use PDO;

class ContactsModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function saveMessage(string $email, string $motif, string $message): void
    {
        $sql = "INSERT INTO contacts (email, motif, message, date_envoi)
                VALUES (:email, :motif, :message, NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':email' => $email,
            ':motif' => $motif,
            ':message' => $message,
        ]);
    }

    public function getAllForAdmin(): array
    {
        $sql = "SELECT id, date_envoi, email, motif, message
                FROM contacts
                ORDER BY date_envoi DESC, id DESC";
        $q = $this->pdo->query($sql);
        return $q->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}
