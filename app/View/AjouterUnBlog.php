<main id="page-ajouter-un-blog">
<script src="/assets/js/blog-compression-banniere.js" defer></script>

	<section id="titre">
		<h1>AJOUTER UN BLOG</h1>
	</section>

	<section id="ajouter-un-blog" class="box-bg-formulaire formulaire">
		<form id="blogForm" action="/ajouterunblog" method="post" enctype="multipart/form-data">
			<label for="titreBlog">Titre du blog :</label>
			<input type="text" id="titreBlog" name="titreBlog" value="<?= isset($Blog) ? htmlspecialchars($Blog['Blog_titre'], ENT_QUOTES) : '' ?>" required />

			<label for="banniereBlog">Image principale du blog :</label>
			<input type="file" id="banniereBlog" name="banniereBlog" accept="image/png, image/jpeg, image/webp" required/>

			<?php
        require __DIR__ . '/Partials/BlogEditeurTexte.php';
      ?>

			<!-- Champ caché pour stocker le HTML complet de l’éditeur -->
			<input type="hidden" name="contenuBlog" id="contenuBlog" value="">

			<button class="form-bouton-envoyer" type="submit">
				Publier le Blog
			</button>
		</form>
	</section>
</main>