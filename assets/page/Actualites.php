<?php
use App\Helpers\DateHelper;
// Charge 50 actualités au départ
$articles = $articleModel->getLastArticlesPaged(50, 0);
?>
<main id="page-liste-dernieres-actualites">
    <section id="titre">
        <h1>Dernières Actualités</h1>
    </section>

    <section id="actualites-wrapper">
        <div id="actualites-list">
            <?php if (empty($articles)): ?>
                <p style="opacity:.7;">Aucune actualité publiée pour le moment.</p>
            <?php else: ?>
                <?php require __DIR__ . '/../../assets/templates/ListeActualite.php'; ?>
            <?php endif; ?>
        </div>
    </section>

    <?php if (!empty($articles)): ?>
    <section id="container-bouton-voir-plus">
        <p class="bouton-voir-plus" role="button" tabindex="0">Voir plus...</p>
    </section>
    <?php endif; ?>
</main>

<?php if (!empty($articles)): ?>
<script>
  (function() {
    const listEl = document.getElementById('actualites-list');
    const btnEl  = document.querySelector('#container-bouton-voir-plus .bouton-voir-plus');
    if (!listEl || !btnEl) return;

    let offset = <?= count($articles) ?>; // 50 au départ
    const step = 10;
    let loading = false;

    async function loadMore() {
      if (loading) return;
      loading = true;
      btnEl.style.pointerEvents = 'none';
      btnEl.textContent = 'Chargement...';

      try {
        const res = await fetch(`/actualites/load?offset=${offset}&limit=${step}`, {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!res.ok) throw new Error('HTTP ' + res.status);

        const html = await res.text();
        if (!html.trim()) { // plus rien à charger
          btnEl.style.display = 'none';
          return;
        }
        const temp = document.createElement('div');
        temp.innerHTML = html;

        const newItems = temp.children; // chaque enfant = un <section ...> de la liste
        Array.from(newItems).forEach(el => listEl.appendChild(el));
        offset += newItems.length;

        if (newItems.length < step) { // dernière tranche
          btnEl.style.display = 'none';
        }
      } catch (e) {
        console.error(e);
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
