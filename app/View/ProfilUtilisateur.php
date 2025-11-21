<?php
use App\Helpers\DateHelper;

// 1) Récupérer le pseudo dans l'URL
$pseudo = isset($_GET['pseudonyme']) ? trim((string)$_GET['pseudonyme']) : '';

// 2) Vérifier que le pseudo est fourni
if ($pseudo === '') {
    http_response_code(404);
    echo "<main class='box-bg'><h1>Profil introuvable</h1></main>";
    return;
}

// 3) Charger les infos de l’utilisateur
$userData = $userModel->getByPseudo($pseudo);
if (!$userData) {
    http_response_code(404);
    echo "<main class='box-bg'><h1>Profil introuvable</h1></main>";
    return;
}

// 4) Déterminer le chemin du logo (tolère BDD, pluriel et singulier)
$id       = (int)($userData['id'] ?? 0);
$candidates = [];

// a) Chemin depuis la BDD (si présent)
$dbPath = trim((string)($userData['chemin_logo'] ?? ''));
if ($dbPath !== '') {
    $dbPath = ($dbPath[0] === '/') ? $dbPath : '/'.$dbPath;
    $candidates[] = $dbPath;
}
// b) Chemins “construits”
$candidates[] = "/assets/images/logoUtilisateurs/{$id}/avatar.webp"; // pluriel (conforme à ton tree)
$candidates[] = "/assets/images/logoUtilisateur/{$id}/avatar.webp";  // singulier (fallback)

$logo    = '/assets/images/Profil_default.webp';
$docroot = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
foreach ($candidates as $webPath) {
    if ($webPath === '') { continue; }
    $abs = $docroot . $webPath;
    if ($docroot !== '' && is_file($abs)) { $logo = $webPath; break; }
}

// 5) Données prêtes pour l’affichage
$userPseudo  = (string)($userData['pseudo'] ?? $pseudo);
$description = (string)($userData['profil_description'] ?? '');
$description = trim($description) === '' ? "Aucune description pour le moment." : $description;

// 6) Charger tous les blogs créés par cet utilisateur
$blogs = $blogModel->getByUserId($id);
?>


<main id="page-utilisateur">
	<section id="pu-intro">
		<figure class="pu-user">
			<img class="pu-logo-user" src="<?= htmlspecialchars($logo) ?>" alt="Logo de <?= htmlspecialchars($userData['pseudo']) ?>" loading="lazy" width="200" height="200">
			<figcaption>
				<h1><?= htmlspecialchars($userData['pseudo']) ?></h1>
			</figcaption>
		</figure>
		<figure class="pu-signaler">
			<a href="/contacts" class="bouton-signaler">⚠️ Signaler !</a>
		</figure>
	</section>
	<section id="pu-description">
		<article class="pu-description box-card">
			<h2>Description de l'utilisateur :</h2>
			<p><?= nl2br(htmlspecialchars($description)) ?></p>
		</article>
	</section>

	<section id="utilisateur-informations">
		<section id="default" class="ldb-container-blog-user">
			<?php if (empty($blogs)): ?>
				<p style="opacity:.7;">Aucun blog publié pour le moment.</p>
			<?php else: ?>
				<?php require __DIR__ . '/../../assets/templates/ListeBlogProfil.php'; ?>
			<?php endif; ?>
		</section>
	</section>



</main>