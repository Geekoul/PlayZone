<?php foreach ($blogs as $blog): ?>
<?php
    $slug       = htmlspecialchars($blog['slug'] ?? '');
    $titre      = htmlspecialchars($blog['titre'] ?? 'Sans titre');
    $banner     = $blog['banniere'] ?: '/assets/images/Banniere_default.webp';
    if ($banner === '' || $banner[0] !== '/') $banner = '/'.$banner;
    $published  = \App\Helpers\DateHelper::formatFr($blog['published_at'] ?? '');
    $nbCommentaires = (int)($blog['nb_commentaires'] ?? 0);

    // Auteur
    $auteurPseudo = htmlspecialchars($blog['auteur_pseudo'] ?? 'Utilisateur inconnu');
    $auteurId     = (int)($blog['id_utilisateur'] ?? 0);
		$auteurLogo = trim((string)($blog['auteur_logo'] ?? ''));

		if ($auteurLogo === '') {
				// Aucun logo en BDD → chemin par défaut
				$auteurLogo = "/assets/images/logoUtilisateurs/{$auteurId}/avatar.webp";
				if (!is_file($_SERVER['DOCUMENT_ROOT'] . $auteurLogo)) {
						$auteurLogo = "/assets/images/Profil_default.webp";
				}
		} else {
				// Nettoyage du chemin depuis la BDD
				if ($auteurLogo[0] !== '/') $auteurLogo = '/' . $auteurLogo;

				// Vérifie que le fichier existe
				$absPath = $_SERVER['DOCUMENT_ROOT'] . $auteurLogo;
				if (!is_file($absPath)) {
						$auteurLogo = "/assets/images/Profil_default.webp";
				}
		}
?>

<section id="default" class="ldb-container-blog-user">
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
					<p>Commentaires (<?= $nbCommentaires ?>)</p>
				</div>
			</figcaption>
		</figure>
	</section>

	<section class="ldb-container-liste-user">
		<figure class="box-liste-blog card-liste-user">
			<a href="/profil/<?= urlencode($auteurPseudo) ?>" class="a-img-card-user">
				<img src="<?= htmlspecialchars($auteurLogo) ?>" alt="Profil de <?= $auteurPseudo ?>" loading="lazy" width="50" height="50">
			</a>
			<figcaption>
				<a class="limite-2lignes" href="/profil/<?= urlencode($auteurPseudo) ?>"><?= $auteurPseudo ?></a>
			</figcaption>
		</figure>
	</section>
</section>
<?php endforeach; ?>
