<?php
use App\Helpers\DateHelper;

/* 1) Récup slug : priorité à ?slug=..., sinon fallback sur ?step=... à cause des règles génériques */
$slug = isset($_GET['slug']) ? trim((string)$_GET['slug']) : '';
if ($slug === '' && isset($_GET['step'])) {
    $slug = trim((string)$_GET['step']); // quand la règle /page/step a capturé article/SLUG
}

/* 2) Si slug manquant → message simple et sortie */
if ($slug === '') {
    echo "<main class='box-bg'><h1>Article introuvable</h1></main>";
    return;
}

/* 3) Charger l’article */
$article = $articleModel->getBySlug($slug);
if (!$article) {
    echo "<main class='box-bg'><h1>Article introuvable</h1></main>";
    return;
}

/* 4) Préparer données d’affichage */
$titre        = (string)($article['article_titre'] ?? 'Sans titre');
$datePubRaw   = (string)($article['article_date_publication'] ?? '');
$datePubTxt   = $datePubRaw ? ('Publié : ' . DateHelper::formatFr($datePubRaw)) : 'Publié :';

$banner = trim((string)($article['article_banniere_img'] ?? ''));
if ($banner === '' || $banner === '0') { $banner = '/assets/images/Banniere_default.webp'; }
elseif ($banner[0] !== '/') { $banner = '/'.$banner; }

$contenuHtml = (string)($article['article_contenu'] ?? '');

/* 5) Droits d’actions */
$uidSession = (int)($_SESSION['user']['id'] ?? 0);
$isAdmin    = !empty($_SESSION['user']['est_administrateur']);
$isAuthor   = (int)($article['id_utilisateur'] ?? 0) === $uidSession;
$canManage  = $isAdmin || $isAuthor;

/* 6) Auteur pour CarteUtilisateur.php */
$authorId     = (int)($article['id_utilisateur'] ?? 0);
$authorRow    = $authorId ? $userModel->getById($authorId) : null;
$authorPseudo = $authorRow['pseudo'] ?? 'Utilisateur';
$authorLink   = '/profil/' . rawurlencode($authorPseudo);

/* logo auteur robuste */
$authorLogoCandidates = [];
$dbLogo = trim((string)($authorRow['chemin_logo'] ?? ''));
if ($dbLogo !== '') { $authorLogoCandidates[] = ($dbLogo[0] === '/' ? $dbLogo : '/'.$dbLogo); }
$authorLogoCandidates[] = "/assets/images/logoUtilisateurs/{$authorId}/avatar.webp";

$authorLogo = '/assets/images/Profil_default.webp';
$docroot = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
foreach ($authorLogoCandidates as $p) {
    if ($docroot && $p !== '' && is_file($docroot.$p)) { $authorLogo = $p; break; }
}

/* 7) Variables consommées par assets/templates/CarteUtilisateur.php */
$__carte_user_pseudo = $authorPseudo;
$__carte_user_logo   = $authorLogo;
$__carte_user_href   = $authorLink;
?>


<main id="page-article">
  <section id="article-intro">
    <section class="article-titre">
      <h1><?= htmlspecialchars($titre) ?></h1>
      <p class="date-publication"><?= htmlspecialchars($datePubTxt) ?></p>

      <?php if ($canManage): ?>
      <div class="article-actions">
        <!-- lien vers le formulaire d’ajout (comme demandé) -->
        <a href="/ajouterunarticle" class="article-modifier">Modifier</a>
        <!-- (suppression à brancher plus tard si besoin) -->
        <a href="#" class="article-supprimer" onclick="return confirm('Confirmer la suppression de l’article ?');">Supprimer l'article</a>
      </div>
      <?php endif; ?>
    </section>

    <section class="article-redacteur">
      <?php require_once("assets/templates/CarteUtilisateur.php"); ?>
    </section>
  </section>

  <article id="article-contenu-box" class="box-bg">
    <section class="a-container-img-principal">
      <img class="a-img-principal"
           src="<?= htmlspecialchars($banner) ?>"
           alt="Image principale de l'article"
           loading="lazy" width="100%" max-height="600">
    </section>
    <section class="article-contenu">
      <?= $contenuHtml /* HTML volontairement brut, déjà filtré à la création */ ?>
    </section>
  </article>
  <?php require_once("assets/templates/Commentaire.php"); ?>
</main>
