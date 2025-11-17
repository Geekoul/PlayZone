<main id="page-admin" class="admin-container">
  <section id="titre">
		<h1>ADMIN - COMMENTAIRES</h1>
	</section>

  <nav class="admin-nav">
    <a href="/adminutilisateurs">Utilisateurs</a>
    <a href="/adminarticles">Articles</a>
    <a href="/adminblogs">Blogs</a>
    <a href="/admincommentaires" class="active">Commentaires</a>
    <a href="/admincontacts">Contacts</a>
  </nav>

  <section id="admin-commentaires-liste" class="box-bg">
    <?php if (empty($comments)): ?>
      <p>Aucun commentaire trouvé.</p>
    <?php else: ?>
      <div class="admin-table-wrapper">
        <table class="admin-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Fil (Article/Blog)</th>
              <th>Auteur</th>
              <th>Date</th>
              <th>Contenu</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($comments as $c): ?>
            <tr>
              <form method="post" action="/admincommentaires" class="form-admin-commentaire">
                <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?? '' ?>">
                <input type="hidden" name="id"   value="<?= (int)$c['id'] ?>">

                <!-- ID -->
                <td><?= (int)$c['id'] ?></td>

                <!-- Fil -->
                <td>
                  <?php
                    $threadType = $c['thread_type'] ?? 'thread';
                    $threadTitre = htmlspecialchars($c['thread_titre'] ?? 'Fil inconnu');
                    $threadSlug  = $c['thread_slug']  ?? '';
                    if ($threadType === 'blog' && $threadSlug !== '') {
                      echo '<a href="/blog/'.htmlspecialchars($threadSlug).'" target="_blank">📝 Blog — '.$threadTitre.'</a>';
                    } elseif ($threadType === 'article' && $threadSlug !== '') {
                      echo '<a href="/article/'.htmlspecialchars($threadSlug).'" target="_blank">📰 Article — '.$threadTitre.'</a>';
                    } else {
                      echo $threadTitre;
                    }
                  ?>
                  <div style="opacity:.7;font-size:.85em;">thread #<?= (int)$c['id_commentaire_thread'] ?></div>
                </td>

                <!-- Auteur -->
                <td>
                  <?php
                    $pseudo = $c['auteur_pseudo'] ?? 'Anonyme';
                    $pseudoSafe = htmlspecialchars($pseudo);
                    if (!empty($pseudo) && $pseudo !== 'Anonyme') {
                      echo '<a href="/profil/'.rawurlencode($pseudo).'" target="_blank">'.$pseudoSafe.'</a>';
                    } else {
                      echo $pseudoSafe;
                    }
                  ?>
                  <div style="opacity:.7;font-size:.85em;">#<?= (int)($c['id_utilisateur'] ?? 0) ?></div>
                </td>

                <!-- Date -->
                <td><?= htmlspecialchars($c['commentaire_date_publication'] ?? '') ?></td>

                <!-- Contenu -->
                <td style="min-width:320px;">
                  <textarea name="commentaire_contenu" rows="2" maxlength="300"
                            placeholder="Contenu (max 300 caractères)"><?= htmlspecialchars($c['commentaire_contenu'] ?? '') ?></textarea>
                </td>

                <!-- Actions -->
                <td style="text-align:center;white-space:nowrap;">
                  <button type="submit" name="update"  title="Enregistrer">💾</button>
                  <button type="submit" name="delete"  title="Supprimer"
                          onclick="return confirm('Supprimer ce commentaire ?');">🗑️</button>
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
