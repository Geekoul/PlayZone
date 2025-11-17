<?php
// assets/templates/Commentaire.php
use App\Helpers\DateHelper;

// 1) État de connexion
$isLogged = !empty($_SESSION['user']);

// 2) Identifier le contexte (article ou blog) et récupérer le thread
$threadId = 0;

// slug/id depuis l'URL (compat route /page/step qui met le slug dans step)
$slug = $_GET['slug'] ?? ($_GET['step'] ?? '');
$slug = is_string($slug) ? trim($slug) : '';
$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// $page vient de index.php (routing)
$ctx = $page ?? '';

// Fonctions utilitaires locales
$loadArticle = function() use ($slug, $id, $articleModel) {
    if (!isset($articleModel)) return null;
    if ($slug !== '' && method_exists($articleModel, 'getBySlug')) {
        $a = $articleModel->getBySlug($slug);
        if ($a) return $a;
    }
    if ($id > 0 && method_exists($articleModel, 'get')) {
        return $articleModel->get($id);
    }
    return null;
};
$loadBlog = function() use ($slug, $id, $blogModel) {
    if (!isset($blogModel)) return null;
    if ($slug !== '' && method_exists($blogModel, 'getBySlug')) {
        $b = $blogModel->getBySlug($slug);
        if ($b) return $b;
    }
    if ($id > 0 && method_exists($blogModel, 'get')) {
        return $blogModel->get($id);
    }
    return null;
};

// Détermine threadId selon la page courante
if (($ctx === 'article' || $ctx === 'articleview') && isset($articleModel)) {
    $article = $loadArticle();
    if ($article && !empty($article['id_commentaire_thread'])) {
        $threadId = (int)$article['id_commentaire_thread'];
    }
} elseif (($ctx === 'blog' || $ctx === 'blogview') && isset($blogModel)) {
    $blog = $loadBlog();
    if ($blog && !empty($blog['id_commentaire_thread'])) {
        $threadId = (int)$blog['id_commentaire_thread'];
    }
}

// 3) Charger la liste des commentaires si on a un thread
$comments = [];
if ($threadId > 0 && isset($commentModel) && method_exists($commentModel, 'listByThread')) {
    $comments = $commentModel->listByThread($threadId);
}
?>
<section id="section-commentaires">
    <section class="commentaire-input-box">
        <h2>COMMENTAIRES :</h2>

        <?php if ($isLogged && $threadId > 0): ?>
            <form action="/commentaire/ajouter" method="post" class="commentaire-form">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                <input type="hidden" name="thread_id" value="<?= (int)$threadId ?>">
                <input type="text" name="contenu" class="commentaire-input" placeholder="Ajoutez un commentaire..." maxlength="500" required>
                <button class="commentaire-bouton-envoyer" type="submit">Ajouter un commentaire</button>
            </form>
        <?php else: ?>
            <a href="/connexion" class="commentaire-bouton-creer-compte" style="<?= $isLogged ? 'display:none' : '' ?>">
                Créer un compte pour commenter
            </a>
        <?php endif; ?>
    </section>

    <?php if ($threadId === 0): ?>
        <p style="opacity:.7; margin-top:.5rem;">Commentaires indisponibles (thread manquant).</p>
    <?php elseif (!$comments): ?>
        <p style="opacity:.7; margin-top:.5rem;">Aucun commentaire pour le moment.</p>
    <?php endif; ?>

    <?php foreach ($comments as $c): 
        $logo = $c['chemin_logo'] ?? '';
				// Si vide, tenter la session (au cas où) :
				if ($logo === '' && !empty($_SESSION['user']['chemin_logo']) && (int)$c['id_utilisateur'] === (int)($_SESSION['user']['id'] ?? 0)) {
						$logo = $_SESSION['user']['chemin_logo'];
				}

				// Normaliser le chemin web (ajoute "/" en tête si manquant)
				if (is_string($logo) && $logo !== '' && $logo[0] !== '/') {
						$logo = '/' . $logo;
				}

				// Si le fichier n’existe pas physiquement, fallback sur l’image par défaut
				$abs = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/') . ($logo ?: '');
				if ($logo === '' || !is_file($abs)) {
						$logo = '/assets/images/Profil_default.webp';
				}
        $pseudo = $c['pseudo'] ?: 'Utilisateur';
        $isOwner= $isLogged && ((int)$c['id_utilisateur'] === (int)($_SESSION['user']['id'] ?? 0));
        $isAdmin= $isLogged && !empty($_SESSION['user']['est_administrateur']);
        $canDelete = $isOwner || $isAdmin;
    ?>
    <article class="commentaire box-card-commentaire" data-id="<?= (int)$c['id'] ?>">
        <figure class="commentaire-profil-edition">
            <img class="commentaire-logo-utilisateur" src="<?= htmlspecialchars($logo) ?>" alt="Logo Utilisateur" loading="lazy" width="100" height="100">
            <figcaption class="commentaire-container-pseudo-edition">
                <div class="commentaire-pseudo">
                    <a href="/profilutilisateur"><?= htmlspecialchars($pseudo) ?></a>
                </div>
                <nav class="commentaire-actions">
                    <?php if ($canDelete): ?>
                        <form action="/commentaire/supprimer" method="post" style="display:inline" onsubmit="return confirm('Supprimer ce commentaire ?');">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                            <input type="hidden" name="comment_id" value="<?= (int)$c['id'] ?>">
                            <button class="commentaire-supprimer" type="submit" style="background:none;border:none;color:inherit;cursor:pointer;">Supprimer</button>
                        </form>
                    <?php endif; ?>
                    <a class="commentaire-signaler" href="/contacts">Signaler !</a>
                </nav>
            </figcaption>
        </figure>

        <article class="commentaire-texte">
            <p><?= nl2br(htmlspecialchars($c['contenu'])) ?></p>
        </article>
        <figure class="commentaire-date">
            <p>Publié <?= htmlspecialchars(DateHelper::relatif($c['date_publication'])) ?></p>
        </figure>
    </article>
    <?php endforeach; ?>
</section>
