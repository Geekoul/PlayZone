<?php
use App\Helpers\DateHelper;

/* 1) Slug depuis /blog/{slug}; fallback sur ?step=... si la règle générique a capturé */
$slug = isset($_GET['slug']) ? trim((string)$_GET['slug']) : '';
if ($slug === '' && isset($_GET['step'])) {
    $slug = trim((string)$_GET['step']);
}

/* 2) Si slug manquant → message simple et sortie */
if ($slug === '') {
    echo "<main class='box-bg'><h1>Blog introuvable</h1></main>";
    return;
}

/* 3) Charger le blog */
$blog = $blogModel->getBySlug($slug);
if (!$blog) {
    echo "<main class='box-bg'><h1>Blog introuvable</h1></main>";
    return;
}

/* 4) Variables d'affichage */
$titre        = (string)($blog['blog_titre'] ?? 'Sans titre');
$datePubRaw   = (string)($blog['blog_date_publication'] ?? '');
$datePubTxt   = $datePubRaw ? ('Publié : ' . DateHelper::formatFr($datePubRaw)) : 'Publié :';

$banner = trim((string)($blog['blog_banniere_img'] ?? ''));
if ($banner === '' || $banner === '0') $banner = '/assets/images/Banniere_default.webp';
elseif ($banner[0] !== '/') $banner = '/'.$banner;

$contenuHtml = (string)($blog['blog_contenu'] ?? '');

/* 5) Droits d'actions */
$uidSession = (int)($_SESSION['user']['id'] ?? 0);
$isAdmin    = !empty($_SESSION['user']['est_administrateur']);
$isAuthor   = (int)($blog['id_utilisateur'] ?? 0) === $uidSession;
$canManage  = $isAdmin || $isAuthor;

/* 6) Auteur pour CarteUtilisateur.php */
$authorId     = (int)($blog['id_utilisateur'] ?? 0);
$authorRow    = $authorId ? $userModel->getById($authorId) : null;
$authorPseudo = $authorRow['pseudo'] ?? 'Utilisateur';
$authorLink   = '/profil/' . rawurlencode($authorPseudo);

/* Logo auteur robuste */
$authorLogoCandidates = [];
$dbLogo = trim((string)($authorRow['chemin_logo'] ?? ''));
if ($dbLogo !== '') $authorLogoCandidates[] = ($dbLogo[0] === '/' ? $dbLogo : '/'.$dbLogo);
$authorLogoCandidates[] = "/assets/images/logoUtilisateurs/{$authorId}/avatar.webp";

$authorLogo = '/assets/images/Profil_default.webp';
$docroot = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
foreach ($authorLogoCandidates as $p) {
    if ($docroot && $p !== '' && is_file($docroot.$p)) { $authorLogo = $p; break; }
}

/* 7) Variables pour CarteUtilisateur.php */
$__carte_user_pseudo = $authorPseudo;
$__carte_user_logo   = $authorLogo;
$__carte_user_href   = $authorLink;
?>


<main id="page-blog">
	<section id="blog-intro">
		<section class="blog-titre">
			<h1><?= htmlspecialchars($titre) ?></h1>
			<p class="date-publication">
				<?= htmlspecialchars($datePubTxt) ?>
			</p>
			<?php if (!empty($canManage)): ?>
			<div class="blog-actions">
				<a href="/ajouterunblog" class="blog-modifier">Modifier</a>
				<a href="#" class="blog-supprimer" onclick="return confirm('Confirmer la suppression de l’blog ?');">Supprimer l'blog</a>
			</div>
			<?php endif; ?>
		</section>

		<section class="blog-redacteur">
			<?php
				require_once("assets/templates/CarteUtilisateur.php");
			?>
		</section>
	</section>

	<article id="blog-contenu-box" class="box-bg">
		<section class="a-container-img-principal">
			<img class="a-img-principal"
					src="<?= htmlspecialchars($banner) ?>" alt="Image principale de l'blog" loading="lazy" width="100%" max-height="600">
		</section>
		<section class="blog-contenu">
			<?= $contenuHtml ?>
		</section>
	</article>

	<?php require_once("assets/templates/Commentaire.php"); ?>
</main>

