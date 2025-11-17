<main id="page-admin" class="admin-container">
	<section id="titre">
		<h1>ADMIN - ARTICLES</h1>
	</section>
  <nav class="admin-nav">
    <a href="/adminutilisateurs">Utilisateurs</a>
    <a href="/adminarticles" class="active">Articles</a>
    <a href="/adminblogs">Blogs</a>
    <a href="/admincommentaires">Commentaires</a>
    <a href="/admincontacts">Contacts</a>
  </nav>

  <section id="admin-articles-liste" class="box-bg">
    <?php if (empty($articles)): ?>
      <p>Aucun article trouvé.</p>
    <?php else: ?>
      <div class="admin-table-wrapper">
        <table class="admin-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Auteur</th>
              <th>Date publication</th>
              <th>Titre</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($articles as $a): ?>
            <tr>
              <form method="post" action="/adminarticles" class="form-admin-article">
                <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?? '' ?>">
                <input type="hidden" name="id"   value="<?= (int)$a['id'] ?>">

                <td><?= (int)$a['id'] ?></td>

                <td title="id_utilisateur #<?= (int)($a['id_utilisateur'] ?? 0) ?>">
                  <?php
                    $pseudo = $a['auteur_pseudo'] ?? '—';
                    $pseudoSafe = htmlspecialchars($pseudo);
                    if (!empty($pseudo) && $pseudo !== '—') {
                      echo '<a href="/profil/'.rawurlencode($pseudoSafe).'">'.$pseudoSafe.'</a>';
                    } else {
                      echo $pseudoSafe;
                    }
                  ?>
                </td>

                <td><?= htmlspecialchars($a['article_date_publication'] ?? '') ?></td>

                <td>
                  <input type="text"
                         name="article_titre"
                         value="<?= htmlspecialchars($a['article_titre'] ?? '') ?>"
                         required>
                </td>

                <td style="text-align:center;">
                  <button type="submit" name="update"  title="Enregistrer">💾</button>
                  <button type="submit" name="delete"  title="Supprimer"
                          onclick="return confirm('Supprimer cet article ?');">🗑️</button>
                </td>
              </form>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</main>
