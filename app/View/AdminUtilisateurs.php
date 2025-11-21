<main id="page-admin" class="admin-container">
	<section id="titre">
		<h1>ADMIN - UTILISATEURS</h1>
	</section>

	<nav class="admin-nav">
    <a href="?page=adminutilisateur">Utilisateurs</a>
    <a href="?page=adminarticles">Articles</a>
    <a href="?page=adminblogs">Blogs</a>
    <a href="?page=admincommentaires">Commentaires</a>
    <a href="?page=admincontacts">Contacts</a>
	</nav>

	<section id="admin-utilisateurs-liste">
		<?php if (empty($users)): ?>
			<p>Aucun utilisateur trouvé.</p>
		<?php else: ?>
			<div class="admin-table-wrapper" class="box-bg">
				<table class="admin-table">
					<thead>
						<tr>
							<th>ID</th>
							<th>Date inscription</th>
							<th>Logo</th>
							<th>Pseudo</th>
							<th>Email</th>
							<th>Mot de passe</th>
							<th>Description</th>
							<th>Admin</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($users as $u): 
							$logo = $u['chemin_logo'] ?: "/assets/images/logoUtilisateurs/{$u['id']}/avatar.webp";
							if (!is_file($_SERVER['DOCUMENT_ROOT'] . $logo)) {
								$logo = "/assets/images/Profil_default.webp";
							}
						?>
						<tr>
							<form method="post" action="/adminutilisateurs" class="form-admin-utilisateur">
								<input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?? '' ?>">
								<input type="hidden" name="id" value="<?= (int)$u['id'] ?>">

								<td><?= (int)$u['id'] ?></td>
								<td><?= htmlspecialchars($u['date_inscription']) ?></td>

								<td>
									<img src="<?= htmlspecialchars($logo) ?>" 
										 alt="Logo de <?= htmlspecialchars($u['pseudo']) ?>" 
										 width="50" height="50">
								</td>

								<td>
									<input type="text" 
										   name="pseudo" 
										   value="<?= htmlspecialchars($u['pseudo'] ?? '') ?>" 
										   required>
								</td>

								<td>
									<input type="email" 
										   name="email" 
										   value="<?= htmlspecialchars($u['email'] ?? '') ?>" 
										   required>
								</td>

								<td>
									<input type="password" 
										   name="mot_de_passe" 
										   placeholder="••••••">
								</td>

								<td>
									<textarea name="profil_description" rows="2"><?= htmlspecialchars($u['profil_description'] ?? '') ?></textarea>
								</td>

								<td style="text-align:center;">
									<input type="checkbox" 
										   name="est_administrateur" 
										   value="1" 
										   <?= !empty($u['est_administrateur']) ? 'checked' : '' ?>>
								</td>

								<td style="text-align:center;">
									<button type="submit" name="update" title="Enregistrer">💾</button>
									<button type="submit" name="delete" title="Supprimer" onclick="return confirm('Supprimer cet utilisateur ?');">🗑️</button>
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
