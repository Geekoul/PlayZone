<?php
namespace App;

use PDO;
use PDOException;

class Database
{
    // Propriété privée qui contiendra l'objet PDO (connexion à la BDD)
    private PDO $connection;

    public function __construct()
    {
        // Paramètres de connexion à la base de données
        $host = "mysql";          // Nom d’hôte (souvent "localhost" ou nom du service dans Docker)
        $dbname = "bdd_playzone"; // Nom de la base
        $charset = "utf8mb4";     // Encodage recommandé
        $username = "root";       // Nom d'utilisateur MySQL
        $password = "root";       // Mot de passe MySQL

        try {
            // Création d’un nouvel objet PDO avec DSN + identifiants
            $this->connection = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=$charset", $username, $password,
                [
                    // Active le mode Exception pour faciliter le debug
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    // Définit le mode de récupération des données par défaut
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            // Force le fuseau horaire directement côté MySQL
            $this->connection->exec("SET time_zone = 'Europe/Paris'");
        } catch (PDOException $e) {
            // En cas d'erreur de connexion, on stoppe tout et on affiche un message
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }

    // Méthode publique permettant de récupérer l'objet PDO
    public function getConnection(): PDO
    {
        return $this->connection;
    }
}
