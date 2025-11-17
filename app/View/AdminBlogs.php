<main id="page-admin" class="admin-container">
  <section id="titre">
		<h1>ADMIN - BLOGS</h1>
	</section>

  <nav class="admin-nav">
    <a href="/adminutilisateurs">Utilisateurs</a>
    <a href="/adminarticles">Articles</a>
    <a href="/adminblogs" class="active">Blogs</a>
    <a href="/admincommentaires">Commentaires</a>
    <a href="/admincontacts">Contacts</a>
  </nav>

  <section id="admin-blogs-liste" class="box-bg">
    <?php if (empty($blogs)): ?>
      <p>Aucun blog trouvé.</p>
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
          <?php foreach ($blogs as $b): ?>
            <tr>
              <form method="post" action="/adminblogs" class="form-admin-blog">
                <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?? '' ?>">
                <input type="hidden" name="id"   value="<?= (int)$b['id'] ?>">

                <td><?= (int)$b['id'] ?></td>

                <td title="id_utilisateur #<?= (int)($b['id_utilisateur'] ?? 0) ?>">
                  <?php
                    $pseudo = $b['auteur_pseudo'] ?? '—';
                    $pseudoSafe = htmlspecialchars($pseudo);
                    if (!empty($pseudo) && $pseudo !== '—') {
                      echo '<a href="/profil/'.rawurlencode($pseudoSafe).'">'.$pseudoSafe.'</a>';
                    } else {
                      echo $pseudoSafe;
                    }
                  ?>
                </td>

                <td><?= htmlspecialchars($b['blog_date_publication'] ?? '') ?></td>

                <td>
                  <input type="text"
                         name="blog_titre"
                         value="<?= htmlspecialchars($b['blog_titre'] ?? '') ?>"
                         required>
                </td>

                <td style="text-align:center;">
                  <button type="submit" name="update"  title="Enregistrer">💾</button>
                  <button type="submit" name="delete"  title="Supprimer"
                          onclick="return confirm('Supprimer ce blog ?');">🗑️</button>
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
