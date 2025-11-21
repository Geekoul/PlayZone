<?php
namespace App\Controller;

use App\Model\ContactsModel;
use PDO;

class ContactsController
{
    private ContactsModel $model;
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->model = new ContactsModel($pdo);
    }

    public function submit(array $post): void
    {
        // Nettoyage basique
        $email   = trim($post['contacts_email'] ?? '');
        $motif   = trim($post['contacts_motif'] ?? '');
        $message = trim($post['contacts_message'] ?? '');

        // Vérifications simples
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<script>alert('Veuillez entrer un email valide.');history.back();</script>";
            exit;
        }

        if ($motif === '' || $message === '') {
            echo "<script>alert('Merci de remplir tous les champs.');history.back();</script>";
            exit;
        }

        // Enregistrement
        $this->model->saveMessage($email, $motif, $message);

        // Confirmation
        echo "<script>alert('Votre message a bien été envoyé, merci !');window.location.href='/contacts';</script>";
        exit;
    }

    public function adminList(): void
    {
        if (empty($_SESSION['user']['est_administrateur'])) {
            http_response_code(403);
            echo "<main class='box-bg'><h1>Accès refusé</h1></main>";
            exit;
        }

        $contacts = $this->model->getAllForAdmin();
        require __DIR__ . '/../View/AdminContacts.php';
    }

    /**
     * Gère la soumission du formulaire (POST) ou prépare l'affichage (GET)
     */
    public function handle(): void
    {
        // Si POST, traiter le formulaire (exécute le modèle)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
            $this->submit($_POST);
            return; // submit() fait déjà la redirection/exit
        }

        // Sinon, rien à faire ici, la vue sera incluse par index.php
    }

    /**
     * Gère l'affichage de la page admin (GET) ou les actions admin (POST)
     * Retourne les données nécessaires pour la vue ou null si redirection
     */
    public function handleAdmin(): ?array
    {
        // Sécurité admin
        if (empty($_SESSION['user']['est_administrateur'])) {
            http_response_code(403);
            echo "<main class='box-bg'><h1>Accès refusé</h1></main>";
            return null;
        }

        // POST : actions admin (si un jour tu ajoutes des actions POST pour les contacts admin)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
            // À implémenter si nécessaire (marquer comme lu, supprimer, etc.)
        }

        // GET : préparer les données pour la vue
        return ['contacts' => $this->model->getAllForAdmin()];
    }
}
