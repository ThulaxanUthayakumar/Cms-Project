-- ============================================================
-- Prism CMS - MySQL Schema
-- Run this file once to set up the database
-- ============================================================

CREATE DATABASE IF NOT EXISTS cms_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cms_db;

-- Users
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('admin','editor','viewer') NOT NULL DEFAULT 'editor',
    avatar      VARCHAR(255) DEFAULT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories
CREATE TABLE IF NOT EXISTS categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    slug        VARCHAR(120) NOT NULL UNIQUE,
    description TEXT,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Posts
CREATE TABLE IF NOT EXISTS posts (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(255) NOT NULL,
    slug         VARCHAR(280) NOT NULL UNIQUE,
    excerpt      TEXT,
    body         LONGTEXT,
    status       ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    featured     TINYINT(1) DEFAULT 0,
    category_id  INT DEFAULT NULL,
    author_id    INT NOT NULL,
    views        INT DEFAULT 0,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (author_id)   REFERENCES users(id)      ON DELETE CASCADE
);

-- Media
CREATE TABLE IF NOT EXISTS media (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    filename    VARCHAR(255) NOT NULL,
    original    VARCHAR(255) NOT NULL,
    mime_type   VARCHAR(100) NOT NULL,
    size        INT NOT NULL,
    uploader_id INT NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploader_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Settings
CREATE TABLE IF NOT EXISTS settings (
    `key`   VARCHAR(100) PRIMARY KEY,
    `value` TEXT
);

-- Activity Log
CREATE TABLE IF NOT EXISTS activity_log (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT DEFAULT NULL,
    action     VARCHAR(255) NOT NULL,
    entity     VARCHAR(100) DEFAULT NULL,
    entity_id  INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ─── Seed Data ────────────────────────────────────────────────────────────────

-- Default admin (password: admin123)
INSERT IGNORE INTO users (username, email, password, role) VALUES
('admin', 'admin@example.com', '$2y$12$ePFk3RJoHLmJi5aBU7mWw.xFR9h3p7nWlEwnkpA3ZT.EKsidNyVkW', 'admin'),
('editor', 'editor@example.com', '$2y$12$ePFk3RJoHLmJi5aBU7mWw.xFR9h3p7nWlEwnkpA3ZT.EKsidNyVkW', 'editor');

-- Default categories
INSERT IGNORE INTO categories (name, slug, description) VALUES
('Technology', 'technology', 'Tech news and tutorials'),
('Design',     'design',     'UI/UX and visual design'),
('Business',   'business',   'Business insights');

-- Default settings
INSERT IGNORE INTO settings (`key`, `value`) VALUES
('site_name',        'Prism CMS'),
('site_tagline',     'A clean, powerful content management system'),
('posts_per_page',   '10'),
('allow_comments',   '1');

-- Sample posts
INSERT IGNORE INTO posts (title, slug, excerpt, body, status, featured, category_id, author_id) VALUES
('Welcome to Prism CMS', 'welcome-to-prism-cms',
 'Get started with your new content management system.',
 '<p>Welcome! This is your first post. Edit or delete it, then start writing!</p>',
 'published', 1, 1, 1),
('Getting Started Guide', 'getting-started-guide',
 'Everything you need to know to hit the ground running.',
 '<p>This guide will walk you through the basics of managing content with Prism CMS.</p>',
 'published', 0, 1, 1),
('Design Principles', 'design-principles',
 'The core design philosophy behind great interfaces.',
 '<p>Good design is about clarity, hierarchy, and purpose...</p>',
 'draft', 0, 2, 2);
