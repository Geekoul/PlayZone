<?php
use App\Helpers\DateHelper;

// Récupère les 20 derniers blogs
$blogs = $blogModel->getLastBlogs(20);
?>

<section id="i-liste-blog" class="box-bg" style="margin-top: 3rem;">
	<h2 class="titre-section">Blogs des utilisateurs</h2>

	<?php if (empty($blogs)): ?>
		<p style="opacity:.7;">Aucun blog publié pour le moment.</p>
	<?php else: ?>
		<?php require __DIR__ . '/../../assets/templates/listeBlogAccueil.php'; ?>
	<?php endif; ?>

	<section class="voir-plus">
		<a href="/blogs">Voir plus...</a>
	</section>
</section>
