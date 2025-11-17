<?php
namespace App\Controller;

use App\Model\ContactsModel;

class ContactsController
{
    private ContactsModel $model;

    public function __construct(ContactsModel $model)
    {
        $this->model = $model;
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
}
