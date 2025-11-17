-- PlayZone - Schéma de base
-- MySQL 8+ / MariaDB (InnoDB, utf8mb4)

CREATE DATABASE IF NOT EXISTS playzone
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE playzone;

-- =====================================================
-- 1) UTILISATEUR
-- =====================================================
CREATE TABLE IF NOT EXISTS utilisateur (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  pseudo            VARCHAR(15)  NOT NULL,
  email             VARCHAR(255) NOT NULL,
  mot_de_passe      VARCHAR(255) NOT NULL,
  date_inscription  DATE         NOT NULL DEFAULT (CURRENT_DATE),
  profil_description TEXT        NULL,
  chemin_logo       TEXT         NULL,
  est_administrateur TINYINT(1)  NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_utilisateur_pseudo (pseudo),
  UNIQUE KEY uq_utilisateur_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 2) FILS DE COMMENTAIRES (thread)
-- =====================================================
CREATE TABLE IF NOT EXISTS commentaire_thread (
  id                       INT UNSIGNED NOT NULL AUTO_INCREMENT,
  thread_date_publication  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 3) ARTICLE
-- =====================================================
CREATE TABLE IF NOT EXISTS article (
  id                         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_utilisateur             INT UNSIGNED NULL,
  id_commentaire_thread      INT UNSIGNED NULL,
  article_titre              TEXT        NOT NULL,
  article_banniere_img       TEXT        NULL,
  article_contenu            LONGTEXT    NOT NULL,
  article_contenu_img        TEXT        NULL,
  article_date_publication   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_article_utilisateur (id_utilisateur),
  KEY idx_article_thread (id_commentaire_thread),
  CONSTRAINT fk_article_utilisateur
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_article_thread
    FOREIGN KEY (id_commentaire_thread) REFERENCES commentaire_thread(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 4) BLOG
-- =====================================================
CREATE TABLE IF NOT EXISTS blog (
  id                         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_utilisateur             INT UNSIGNED NULL,
  id_commentaire_thread      INT UNSIGNED NULL,
  blog_titre                 TEXT        NOT NULL,
  blog_banniere_img          TEXT        NULL,
  blog_contenu               LONGTEXT    NOT NULL,
  blog_contenu_img           TEXT        NULL,
  blog_date_publication      DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_blog_utilisateur (id_utilisateur),
  KEY idx_blog_thread (id_commentaire_thread),
  CONSTRAINT fk_blog_utilisateur
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_blog_thread
    FOREIGN KEY (id_commentaire_thread) REFERENCES commentaire_thread(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 5) COMMENTAIRE
-- =====================================================
CREATE TABLE IF NOT EXISTS commentaire (
  id                          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_commentaire_thread       INT UNSIGNED NULL,
  id_utilisateur              INT UNSIGNED NULL,
  commentaire_contenu         VARCHAR(300) NOT NULL,
  commentaire_date_publication DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  commentaire_supprimer       TINYINT(1)   NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_commentaire_thread (id_commentaire_thread),
  KEY idx_commentaire_utilisateur (id_utilisateur),
  CONSTRAINT fk_commentaire_thread
    FOREIGN KEY (id_commentaire_thread) REFERENCES commentaire_thread(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_commentaire_utilisateur
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 6) CONTACTS
-- =====================================================
CREATE TABLE contacts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL,
  motif VARCHAR(190) NOT NULL,
  message TEXT NOT NULL,
  date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 7) Petites données d'exemple (optionnel)
-- =====================================================
-- INSERT INTO utilisateur (pseudo, email, mot_de_passe, est_administrateur)
-- VALUES ('admin', 'admin@playzone.com', '<hash_bcrypt>', 1);
