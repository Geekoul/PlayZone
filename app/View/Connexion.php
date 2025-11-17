<?php
// Flash messages
if (!empty($_SESSION['flash'])): ?>
  <div class="flash-container">
    <?php foreach ($_SESSION['flash'] as $f): ?>
      <p class="flash <?= htmlspecialchars($f['t']) ?>"><?= htmlspecialchars($f['m']) ?></p>
    <?php endforeach; unset($_SESSION['flash']); ?>
  </div>
<?php endif; ?>

<main id="page-connexion">
  <section id="titre">
    <h1>CONNEXION</h1>
  </section>

  <!-- Formulaire de connexion -->
  <section class="box-bg connexion-inscirption-formulaire">
    <form method="post" action="/connexion" autocomplete="off">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'] ?? '') ?>">
      <label class="c-label-1" for="email_connexion">E-Mail</label>
      <input type="email" id="email_connexion" name="email" required>

      <label for="mot_de_passe_connexion">Mot de passe</label>
      <input type="password" id="mot_de_passe_connexion" name="mot_de_passe" required>

      <a href="/contacts" class="c-mdp">Mot de passe oublié</a>

      <button class="c-bouton" type="submit">Connexion</button>
    </form>
  </section>

  <!-- Formulaire d'inscription -->
  <section class="box-bg connexion-inscirption-formulaire">
    <h2>CRÉER UN COMPTE</h2>
    <form method="post" action="/connexion" autocomplete="off">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'] ?? '') ?>">
      <label for="pseudo_inscription">Pseudo</label>
      <input type="text" id="pseudo_inscription" name="pseudo" required>

      <label for="email_inscription">E-Mail</label>
      <input type="email" id="email_inscription" name="email" required>

      <label for="mot_de_passe_inscription">Mot de passe</label>
      <input type="password" id="mot_de_passe_inscription" name="mot_de_passe" required>

      <button class="c-bouton" type="submit">Créer un compte</button>
    </form>
  </section>
</main>
