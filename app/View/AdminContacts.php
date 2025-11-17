<main id="page-admin" class="admin-container">
  <section id="titre">
		<h1>ADMIN - Contacts</h1>
	</section>

  <nav class="admin-nav">
    <a href="/adminutilisateurs">Utilisateurs</a>
    <a href="/adminarticles">Articles</a>
    <a href="/adminblogs">Blogs</a>
    <a href="/admincommentaires">Commentaires</a>
    <a href="/admincontacts" class="active">Contacts</a>
  </nav>

  <section id="admin-contacts-liste" class="box-bg">
    <?php if (empty($contacts)): ?>
      <p>Aucun message reçu.</p>
    <?php else: ?>
      <div class="admin-table-wrapper">
        <table class="admin-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Date d’envoi</th>
              <th>Email</th>
              <th>Motif</th>
              <th>Message</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($contacts as $c): ?>
            <tr>
              <td><?= (int)$c['id'] ?></td>
              <td><?= htmlspecialchars($c['date_envoi'] ?? '') ?></td>
              <td><a href="mailto:<?= htmlspecialchars($c['email']) ?>">
                <?= htmlspecialchars($c['email']) ?>
              </a></td>
              <td><?= htmlspecialchars($c['motif']) ?></td>
              <td style="max-width:400px; white-space:pre-wrap;">
                <?= nl2br(htmlspecialchars($c['message'])) ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</main>

