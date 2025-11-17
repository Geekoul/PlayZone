<?php foreach ($blogs as $blog): ?>
<?php
    $slug       = htmlspecialchars($blog['slug'] ?? '');
    $titre      = htmlspecialchars($blog['titre'] ?? 'Sans titre');
    $banner     = $blog['banniere'] ?: '/assets/images/Banniere_default.webp';
    if ($banner === '' || $banner[0] !== '/') $banner = '/'.$banner;
    $published  = \App\Helpers\DateHelper::formatFr($blog['published_at'] ?? '');
    $nbCommentaires = isset($blog['nb_commentaires']) ? (int)$blog['nb_commentaires'] : 0;
?>
<section class="box-liste-blog ldb-card-blog">
    <a href="/blog/<?= $slug ?>" class="img-card-blog">
        <img src="<?= htmlspecialchars($banner) ?>" alt="Bannière du blog" loading="lazy">
    </a>
    <figure class="card-blog-info">
        <figcaption class="ldb-container-information">
            <div class="ldb-container-titre">
                <a class="limite-2lignes" href="/blog/<?= $slug ?>"><?= $titre ?></a>
            </div>
            <div class="ldb-container-publier-commentaire">
                <p>Publié le <?= $published ?></p>
                <p>Commentaires (<?= htmlspecialchars((string)$nbCommentaires) ?>)</p>
            </div>
        </figcaption>
    </figure>
</section>
<?php endforeach; ?>
