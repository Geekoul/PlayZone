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
        $host = $_ENV['DB_HOST'];        
        $dbname = $_ENV['DB_NAME']; 
        $charset = $_ENV['DB_CHARSET'];     
        $username = $_ENV['DB_USER'];     
        $password = $_ENV['DB_PASSWORD'];

        try {
            // Création d’un nouvel objet PDO avec DSN + identifiants
            $this->connection = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=$charset", $username, $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
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
