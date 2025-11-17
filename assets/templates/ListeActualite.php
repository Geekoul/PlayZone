<?php foreach ($articles as $a): ?>
<?php
    $slug   = htmlspecialchars($a['slug']  ?? '');
    $titre  = htmlspecialchars($a['titre'] ?? 'Sans titre');

    // Bannière
    $banner = trim((string)($a['banniere'] ?? ''));
    if ($banner === '' || $banner === '0') {
        $banner = '/assets/images/Banniere_default.webp';
    } else {
        if ($banner[0] !== '/') $banner = '/'.$banner;
    }

    // Date publication
    $publishedRaw = (string)($a['published_at'] ?? '');
    $dateRelatif  = \App\Helpers\DateHelper::relatif($publishedRaw);

    // Extrait texte depuis HTML
    $html = (string)($a['contenu_html'] ?? '');
    $txt  = trim(preg_replace('/\s+/u', ' ', strip_tags($html)));
    if (mb_strlen($txt) > 280) {
        $txt = mb_substr($txt, 0, 280) . '…';
    }
?>
<section id="dernieres-actualites-liste-container">
    <article class="da-liste box-card">
        <section class="da-img">
            <a href="/article/<?= $slug ?>">
                <img src="<?= htmlspecialchars($banner) ?>"
                     alt="Image principale de l'actualité"
                     loading="lazy" width="350" height="210">
            </a>
        </section>
        <section class="da-information-container">
            <section class="da-information">
                <a href="/article/<?= $slug ?>" class="limite-2lignes"><?= $titre ?></a>
                <p class="da-date-publication"><?= htmlspecialchars($dateRelatif) ?></p>
                <p class="limite-4lignes"><?= htmlspecialchars($txt) ?></p>
            </section>
            <div class="da-bouton">
                <a href="/article/<?= $slug ?>" class="bouton-lire-article">Lire l'article...</a>
            </div>
        </section>
    </article>
</section>
<?php endforeach; ?>
