-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : mysql
-- Généré le : lun. 10 nov. 2025 à 15:13
-- Version du serveur : 8.0.42
-- Version de PHP : 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `bdd_playzone`
--

-- --------------------------------------------------------

--
-- Structure de la table `article`
--

CREATE TABLE `article` (
  `id` int UNSIGNED NOT NULL,
  `id_utilisateur` int UNSIGNED DEFAULT NULL,
  `id_commentaire_thread` int UNSIGNED DEFAULT NULL,
  `article_titre` text COLLATE utf8mb4_general_ci NOT NULL,
  `article_slug` varchar(190) COLLATE utf8mb4_general_ci NOT NULL,
  `article_banniere_img` text COLLATE utf8mb4_general_ci,
  `article_contenu` longtext COLLATE utf8mb4_general_ci NOT NULL,
  `article_contenu_img` text COLLATE utf8mb4_general_ci,
  `article_date_publication` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `article`
--

INSERT INTO `article` (`id`, `id_utilisateur`, `id_commentaire_thread`, `article_titre`, `article_slug`, `article_banniere_img`, `article_contenu`, `article_contenu_img`, `article_date_publication`) VALUES
(1, 1, 2, 'Blabla et blablacar sont HS ? O_o', '', '/assets/images/imageActualites/1/banner.webp', '<p>Commencez à écrire…</p><p><div class=\"ac-img\">\n<img src=\"/assets/images/imageActualites/1/Final_Fantasy_VII__2__png_74752083.webp\" alt=\"Final_Fantasy_VII (2).png\"><img src=\"/assets/images/imageActualites/1/Final_Fantasy_VII__3__png_659b3d23.webp\" alt=\"Final_Fantasy_VII (3).png\">\n</div><br></p>', '/assets/images/imageActualites/1/Final_Fantasy_VII__2__png_74752083.webp;/assets/images/imageActualites/1/Final_Fantasy_VII__3__png_659b3d23.webp', '2025-11-06 18:06:55'),
(2, 1, 3, 'Blabla et blablacar sont HS ? O_o', 'blabla-et-blablacar-sont-hs-o-o', '/assets/images/imageActualites/2/banner.webp', '<p>Commencez à écrire…<br><div class=\"ac-img\">\n<img src=\"/assets/images/imageActualites/2/Final_Fantasy_VII__2__png_74752083.webp\" alt=\"Final_Fantasy_VII (2).png\"><img src=\"/assets/images/imageActualites/2/Final_Fantasy_VII__4__png_b9ec480c.webp\" alt=\"Final_Fantasy_VII (4).png\">\n</div><br></p>', '/assets/images/imageActualites/2/Final_Fantasy_VII__2__png_74752083.webp;/assets/images/imageActualites/2/Final_Fantasy_VII__4__png_b9ec480c.webp', '2025-11-06 18:34:23'),
(5, 1, 19, 'Nouvelle article tst', 'nouvelle-article-tst', '/assets/images/imageActualites/5/banner.webp', '<p>Commencez à écrire…<br><br>blkalba</p>', '', '2025-11-08 17:10:17');

-- --------------------------------------------------------

--
-- Structure de la table `blog`
--

CREATE TABLE `blog` (
  `id` int UNSIGNED NOT NULL,
  `id_utilisateur` int UNSIGNED DEFAULT NULL,
  `id_commentaire_thread` int UNSIGNED DEFAULT NULL,
  `blog_titre` text COLLATE utf8mb4_general_ci NOT NULL,
  `blog_slug` varchar(190) COLLATE utf8mb4_general_ci NOT NULL,
  `blog_banniere_img` text COLLATE utf8mb4_general_ci,
  `blog_contenu` longtext COLLATE utf8mb4_general_ci NOT NULL,
  `blog_contenu_img` text COLLATE utf8mb4_general_ci,
  `blog_date_publication` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `blog`
--

INSERT INTO `blog` (`id`, `id_utilisateur`, `id_commentaire_thread`, `blog_titre`, `blog_slug`, `blog_banniere_img`, `blog_contenu`, `blog_contenu_img`, `blog_date_publication`) VALUES
(3, 1, 18, 'Je suis un blog 2', 'je-suis-un-blog-2', '/assets/images/imageBlogs/3/banner.webp', '<p>Commencez à écrire…</p>', '', '2025-11-07 16:11:44');

-- --------------------------------------------------------

--
-- Structure de la table `commentaire`
--

CREATE TABLE `commentaire` (
  `id` int UNSIGNED NOT NULL,
  `id_commentaire_thread` int UNSIGNED DEFAULT NULL,
  `id_utilisateur` int UNSIGNED DEFAULT NULL,
  `commentaire_contenu` varchar(300) COLLATE utf8mb4_general_ci NOT NULL,
  `commentaire_date_publication` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `commentaire_supprimer` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commentaire`
--

INSERT INTO `commentaire` (`id`, `id_commentaire_thread`, `id_utilisateur`, `commentaire_contenu`, `commentaire_date_publication`, `commentaire_supprimer`) VALUES
(1, 18, 1, 'je suis un commentaire blog 2', '2025-11-07 16:32:15', 0),
(2, 18, 1, 'je retest commentaire blog', '2025-11-07 16:41:21', 0),
(3, 6, 1, 'je suis commentaire article 2', '2025-11-07 16:45:15', 0);

-- --------------------------------------------------------

--
-- Structure de la table `commentaire_thread`
--

CREATE TABLE `commentaire_thread` (
  `id` int UNSIGNED NOT NULL,
  `thread_date_publication` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commentaire_thread`
--

INSERT INTO `commentaire_thread` (`id`, `thread_date_publication`) VALUES
(1, '2025-11-06 18:02:13'),
(2, '2025-11-06 18:06:55'),
(3, '2025-11-06 18:34:23'),
(4, '2025-11-06 19:23:56'),
(5, '2025-11-07 15:38:06'),
(6, '2025-11-07 15:38:50'),
(7, '2025-11-07 15:38:50'),
(8, '2025-11-07 15:39:43'),
(9, '2025-11-07 15:40:03'),
(10, '2025-11-07 15:49:07'),
(11, '2025-11-07 15:50:24'),
(12, '2025-11-07 15:50:25'),
(13, '2025-11-07 15:51:33'),
(14, '2025-11-07 15:59:00'),
(15, '2025-11-07 15:59:08'),
(16, '2025-11-07 15:59:17'),
(17, '2025-11-07 15:59:17'),
(18, '2025-11-07 16:11:44'),
(19, '2025-11-08 17:10:17');

-- --------------------------------------------------------

--
-- Structure de la table `contacts`
--

CREATE TABLE `contacts` (
  `id` int NOT NULL,
  `email` varchar(190) NOT NULL,
  `motif` varchar(190) NOT NULL,
  `message` text NOT NULL,
  `date_envoi` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `contacts`
--

INSERT INTO `contacts` (`id`, `email`, `motif`, `message`, `date_envoi`) VALUES
(1, 'bobo@gmails.com', 'Je suis le Sujet', 'J\'ai oublié mon mot de passe', '2025-11-06 16:40:51');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id` int UNSIGNED NOT NULL,
  `pseudo` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `mot_de_passe` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `date_inscription` date NOT NULL DEFAULT (curdate()),
  `profil_description` text COLLATE utf8mb4_general_ci,
  `chemin_logo` text COLLATE utf8mb4_general_ci,
  `est_administrateur` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `pseudo`, `email`, `mot_de_passe`, `date_inscription`, `profil_description`, `chemin_logo`, `est_administrateur`) VALUES
(1, 'Geekoul', 'geekoulsb@hotmail.com', '$2y$12$GYfbADKfoEy.JJ4WYSdj8.tvVnI9b2U5pAsa/bvwL6ychtD/PV7Ui', '2025-11-05', 'blabla', NULL, 1),
(3, 'letest2', 'letest2@gmail.com', '$2y$12$IrZPchSZttVomnIb/F3ju.Ra7UKnbvoABGEl.8akVGRMZiC4CGnAu', '2025-11-10', NULL, NULL, 0),
(4, 'Admin', 'admin@gmail.com', '$2y$12$4WbkIzjD.UbQvqo7uRVFWeRzbUKpJBYmgtllscqtuE0ANsOoIV4xW', '2025-11-10', NULL, NULL, 1);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `article`
--
ALTER TABLE `article`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `article_slug` (`article_slug`),
  ADD KEY `idx_article_utilisateur` (`id_utilisateur`),
  ADD KEY `idx_article_thread` (`id_commentaire_thread`);

--
-- Index pour la table `blog`
--
ALTER TABLE `blog`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `blog_slug` (`blog_slug`),
  ADD KEY `idx_blog_utilisateur` (`id_utilisateur`),
  ADD KEY `idx_blog_thread` (`id_commentaire_thread`);

--
-- Index pour la table `commentaire`
--
ALTER TABLE `commentaire`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_commentaire_thread` (`id_commentaire_thread`),
  ADD KEY `idx_commentaire_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `commentaire_thread`
--
ALTER TABLE `commentaire_thread`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_utilisateur_pseudo` (`pseudo`),
  ADD UNIQUE KEY `uq_utilisateur_email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `article`
--
ALTER TABLE `article`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `blog`
--
ALTER TABLE `blog`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `commentaire`
--
ALTER TABLE `commentaire`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `commentaire_thread`
--
ALTER TABLE `commentaire_thread`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT pour la table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `article`
--
ALTER TABLE `article`
  ADD CONSTRAINT `fk_article_thread` FOREIGN KEY (`id_commentaire_thread`) REFERENCES `commentaire_thread` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_article_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `blog`
--
ALTER TABLE `blog`
  ADD CONSTRAINT `fk_blog_thread` FOREIGN KEY (`id_commentaire_thread`) REFERENCES `commentaire_thread` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_blog_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `commentaire`
--
ALTER TABLE `commentaire`
  ADD CONSTRAINT `fk_commentaire_thread` FOREIGN KEY (`id_commentaire_thread`) REFERENCES `commentaire_thread` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_commentaire_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
