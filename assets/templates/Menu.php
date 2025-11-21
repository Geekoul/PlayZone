<header id="en-tete">
    <a href="/accueil">
        <img src="/assets/images/Logo_PlayZone.svg" alt="Logo PlayZone" loading="lazy" width="200" height="200">
    </a>

    <button class="menu-burger" title="menu burger">
        <span></span><span></span><span></span>
    </button>

    <nav>
        <!-- Liens toujours visibles -->
        <a href="/actualites">ACTUALITÉS</a>
        <a href="/blogs">BLOGS</a>
        <a href="/contacts">CONTACTS</a>
        <p class="nav-separation">|</p>

        <?php if (empty($_SESSION['user'])): ?>
            <!-- 🔸 Utilisateur non connecté -->
            <div class="nav-connexion">
                <img src="/assets/images/Silhouette.svg" alt="Silhouette">
                <a href="/connexion">Connexion</a>
            </div>

        <?php else: ?>
            <!-- 🔹 Utilisateur connecté -->
            <a href="/ajouterunblog">Ajouter un Blog</a>
            <a href="/profil/<?= urlencode($_SESSION['user']['pseudo']) ?>">Profil</a>
            <a href="/parametres">Paramètres</a>

            <?php if (!empty($_SESSION['user']['est_administrateur']) && $_SESSION['user']['est_administrateur'] == 1): ?>
                <!-- 🛠️ Lien Admin visible uniquement pour les administrateurs -->
                <a href="/ajouterunarticle">Ajouter un Article</a>
                <a href="?page=adminutilisateur">🛠️ Admin</a>
            <?php endif; ?>

            <!-- 🚪 Lien Déconnexion -->
            <a href="/connexion/exit">Déconnexion</a>
        <?php endif; ?>
    </nav>
</header>
