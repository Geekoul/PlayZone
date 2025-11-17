<?php
namespace App\Helpers;

class ImageUtilisateur
{
    public static function saveCompressedWebp(array $file, int $userId, string $publicBase = '/assets/images/logoUtilisateurs'): string
    {
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new \RuntimeException('Aucun fichier téléchargé.');
        }

        // Vérifie le type MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if ($mime !== 'image/webp') {
            throw new \RuntimeException('Format non supporté : veuillez envoyer une image WebP.');
        }

        // Dossier absolu de destination
        $absBase = rtrim($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 3), '/')
                 . $publicBase;
        $userDir = $absBase . '/' . $userId;

        if (!is_dir($userDir) && !mkdir($userDir, 0775, true)) {
            throw new \RuntimeException("Impossible de créer le dossier de destination : $userDir");
        }

        // Nom et chemins
        $absPath = $userDir . '/avatar.webp';
        $relPath = rtrim($publicBase, '/') . '/' . $userId . '/avatar.webp';

        // Déplace le fichier
        if (!move_uploaded_file($file['tmp_name'], $absPath)) {
            throw new \RuntimeException('Erreur lors de l’enregistrement du fichier sur le serveur.');
        }

        return $relPath; // à sauvegarder dans utilisateur.chemin_logo
    }
}
