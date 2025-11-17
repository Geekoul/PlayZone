<?php
use App\Helpers\DateHelper;

// Charge 50 blogs au départ
$blogs = $blogModel->getLastBlogsPaged(50, 0);
?>

<main id="page-liste-blog">
  <script src="assets/js/ldp-responsive-container-jeu-equipe.js" defer></script>
  <script src="assets/js/ldp-container-equipe.js" defer></script>

  <section id="titre">
    <h1>Dernière publication des Blogs</h1>
  </section>

  <section id="i-liste-blog" class="box-bg" style="margin-top: 3rem;">
    <section class="box-bg" id="blogs-list">
      <?php if (empty($blogs)): ?>
        <p style="opacity:.7;">Aucun blog publié pour le moment.</p>
      <?php else: ?>
        <?php require __DIR__ . '/../../assets/templates/ListeBlog.php'; ?>
      <?php endif; ?>
    </section>
  </section>

  <?php if (!empty($blogs)): ?>
  <section id="container-bouton-voir-plus">
    <p class="bouton-voir-plus" role="button" tabindex="0">Voir plus...</p>
  </section>
  <?php endif; ?>
</main>

<?php if (!empty($blogs)): ?>
<?php if (!empty($blogs)): ?>
<script>
(function() {
  const listEl = document.getElementById('blogs-list');
  const btnEl  = document.querySelector('#container-bouton-voir-plus .bouton-voir-plus');
  if (!listEl || !btnEl) return;

  let offset = <?= count($blogs) ?>; // 50 affichés au départ
  const step = 10;
  let loading = false;

  async function loadMore() {
    if (loading) return;
    loading = true;
    btnEl.style.pointerEvents = 'none';
    btnEl.textContent = 'Chargement...';

    try {
      const res = await fetch(`/blogs/load?offset=${offset}&limit=${step}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      if (!res.ok) throw new Error('HTTP ' + res.status);

      const html = await res.text();
      // Rien de plus ? On cache le bouton
      if (!html.trim()) {
        btnEl.style.display = 'none';
        return;
      }

      // Injecte le fragment renvoyé (ListeBlog.php)
      const temp = document.createElement('div');
      temp.innerHTML = html;

      // On déplace les sections dans la liste
      const newItems = temp.querySelectorAll('.ldb-container-blog-user');
      newItems.forEach(el => listEl.appendChild(el));

      // Met à jour l'offset avec le NOMBRE RÉEL ajouté
      offset += newItems.length;

      // Si moins de "step" reçus, on suppose qu'on est au bout → cacher le bouton
      if (newItems.length < step) {
        btnEl.style.display = 'none';
      }
    } catch (e) {
      console.error(e);
      // (optionnel) message d'erreur utilisateur
    } finally {
      loading = false;
      btnEl.style.pointerEvents = '';
      btnEl.textContent = 'Voir plus...';
    }
  }

  const onClick = () => loadMore();
  btnEl.addEventListener('click', onClick);
  btnEl.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); onClick(); }
  });
})();
</script>
<?php endif; ?>
<?php endif; ?>
