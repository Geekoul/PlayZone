<?php
$pseudo = $__carte_user_pseudo ?? 'Utilisateur';
$logo   = $__carte_user_logo   ?? '/assets/images/Profil_default.webp';
$href   = $__carte_user_href   ?? '/profilutilisateur';
?>
<section class="carte-utilisateur-container">
  <section class="cu-chef-de-projet">
    <p class="cu-gras">Rédigé par :</p>
    <section class="cu-container-utilisateur">
      <figure class="box-card-utilisateur">
        <img src="<?= htmlspecialchars($logo) ?>"
             alt="logo de <?= htmlspecialchars($pseudo) ?>"
             loading="lazy" width="60" height="60">
        <figcaption>
          <a href="<?= htmlspecialchars($href) ?>" class="limite-3lignes"><?= htmlspecialchars($pseudo) ?></a>
        </figcaption>
      </figure>
    </section>
  </section>
</section>
