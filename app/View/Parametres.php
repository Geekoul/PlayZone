<?php
if (empty($_SESSION['user'])) { header('Location: /connexion'); exit; }
$u = $_SESSION['user']; // Option rapide. Idéalement: recharger depuis la BDD si tu veux du fresh.
?>


<main id="page-parametres">
	<script src="/assets/js/params-mot-de-passe-verif.js"></script>
  <script src="/assets/js/params-utilisateur-image-compression.js"></script>

  <section id="titre">
    <h1>PARAMÈTRES</h1>
  </section>

  <section id="parametres-utilisateur" class="box-bg-formulaire formulaire">
    <form method="post" action="/parametres" enctype="multipart/form-data" autocomplete="off">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'] ?? '') ?>">

      <h2>Paramètres utilisateur</h2>

      <label for="utilisateur_pseudo">Changer le pseudo :</label>
      <input type="text" id="utilisateur_pseudo" name="pseudo" value="<?= htmlspecialchars($u['pseudo'] ?? '') ?>">

      <label for="utilisateur_email">Changer l'e-mail :</label>
      <input type="email" id="utilisateur_email" name="email" value="<?= htmlspecialchars($u['email'] ?? '') ?>">

      <label for="utilisateur_logo">Photo du profil :</label>
      <?php if (!empty($u['chemin_logo'])): ?>
        <div style="margin: 8px 0;">
          <img src="<?= htmlspecialchars($u['chemin_logo']) ?>" alt="Avatar" width="100" height="100" style="border-radius:50%;object-fit:cover">
        </div>
      <?php endif; ?>
      <input type="file" id="utilisateur_logo" name="chemin_logo" accept="image/png, image/jpeg, image/webp">
      <p style="font-size:12px;opacity:.8">Max 3 Mo. L’image sera redimensionnée en 300×300 et compressée en .webp.</p>

      <label for="utilisateur_description">Description du profil :</label>
      <textarea id="utilisateur_description" name="profil_description" rows="4"><?= htmlspecialchars((string)($u['profil_description'] ?? '')) ?></textarea>


      <h4>Modifier le mot de passe</h4>

      <label for="utilisateur_ancien_mdp">Mot de passe actuel :</label>
			<input type="password" id="utilisateur_ancien_mdp" name="old_password">

			<label for="utilisateur_nouveau_mdp">Nouveau mot de passe :</label>
			<input type="password" id="utilisateur_nouveau_mdp" name="new_password">

      <hr>

      <button class="form-bouton-envoyer" type="submit" name="update" onclick="return confirm('Confirmer les modifications de vos paramètres ?')">
        Appliquer les changements
      </button>

      <button class="form-bouton-supprimer" type="submit" name="delete" onclick="return confirm('Confirmer la suppression de votre compte ?')">
        /!\ SUPPRIMER SON COMPTE /!\
      </button>
    </form>
  </section>
</main>
