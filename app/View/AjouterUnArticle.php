<main id="page-ajouter-un-article">
<script src="/assets/js/article-compression-banniere.js" defer></script>
	
	<section id="titre">
		<h1>AJOUTER UN ARTICLE</h1>
	</section>

	<section id="ajouter-un-article" class="box-bg-formulaire formulaire">
		<form id="articleForm" action="/ajouterunarticle" method="post" enctype="multipart/form-data">
			<label for="titreArticle">Titre de l’article :</label>
			<input type="text" id="titreArticle" name="titreArticle" value="
			<?= isset($article) ? htmlspecialchars($article['article_titre'], ENT_QUOTES) : '' ?>" required />

			<label for="banniereArticle">Image principale de l’article :</label>
			<input type="file" id="banniereArticle" name="banniereArticle" accept="image/png, image/jpeg, image/webp" required/>

			<?php
        require __DIR__ . '/Partials/ArticleEditeurTexte.php';
      ?>

			<!-- Champ caché pour stocker le HTML complet de l’éditeur -->
			<input type="hidden" name="contenuArticle" id="contenuArticle" value="">

			<button class="form-bouton-envoyer" type="submit">
				Publier l’article
			</button>
		</form>
	</section>
</main>