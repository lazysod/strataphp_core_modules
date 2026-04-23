<?php
/**
 * Migration: Create CMS tables for pages, posts, menus, and content management
 *
 * This migration creates the following tables:
 * - cms_pages: Stores CMS pages and metadata
 * - cms_posts: Stores blog/news posts
 * - cms_categories: Stores categories for posts
 * - cms_menus, cms_menu_items: Stores navigation menus and items
 * - cms_content_revisions: Stores revision history for content
 *
 * @param \App\DB $db Database connection instance
 * @return void
 */
return function ($db) {
    try {
        // CMS Pages table
        $db->query("
            CREATE TABLE IF NOT EXISTS cms_pages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL UNIQUE,
                content LONGTEXT,
                excerpt TEXT,
                meta_title VARCHAR(255),
                meta_description TEXT,
                meta_keywords VARCHAR(255),
                status ENUM('draft', 'published', 'private') DEFAULT 'draft',
                template VARCHAR(100) DEFAULT 'default',
                featured_image VARCHAR(255),
                author_id INT,
                parent_id INT DEFAULT NULL,
                menu_order INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                published_at TIMESTAMP NULL,
                INDEX idx_slug (slug),
                INDEX idx_status (status),
                INDEX idx_author (author_id),
                INDEX idx_parent (parent_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // CMS Posts table (for blog functionality)
        $db->query("
            CREATE TABLE IF NOT EXISTS cms_posts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL UNIQUE,
                content LONGTEXT,
                excerpt TEXT,
                meta_title VARCHAR(255),
                meta_description TEXT,
                meta_keywords VARCHAR(255),
                status ENUM('draft', 'published', 'private') DEFAULT 'draft',
                featured_image VARCHAR(255),
                author_id INT,
                category_id INT DEFAULT NULL,
                tags TEXT,
                views INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                published_at TIMESTAMP NULL,
                INDEX idx_slug (slug),
                INDEX idx_status (status),
                INDEX idx_author (author_id),
                INDEX idx_category (category_id),
                INDEX idx_published (published_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // CMS Categories table (for posts)
        $db->query("
            CREATE TABLE IF NOT EXISTS cms_categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(100) NOT NULL UNIQUE,
                description TEXT,
                parent_id INT DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_slug (slug),
                INDEX idx_parent (parent_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // CMS Menus table
        $db->query("
            CREATE TABLE IF NOT EXISTS cms_menus (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                location VARCHAR(50) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_location (location)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // CMS Menu Items table
        $db->query("
            CREATE TABLE IF NOT EXISTS cms_menu_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                menu_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                url VARCHAR(255),
                page_id INT DEFAULT NULL,
                parent_id INT DEFAULT NULL,
                menu_order INT DEFAULT 0,
                target VARCHAR(20) DEFAULT '_self',
                css_class VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_menu (menu_id),
                INDEX idx_parent (parent_id),
                INDEX idx_order (menu_order)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // CMS Content Revisions table
        $db->query("
            CREATE TABLE IF NOT EXISTS cms_content_revisions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                content_type ENUM('page', 'post') NOT NULL,
                content_id INT NOT NULL,
                title VARCHAR(255),
                content LONGTEXT,
                author_id INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_content (content_type, content_id),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Insert default data
        // Create default category
        $db->query("
            INSERT IGNORE INTO cms_categories (name, slug, description) 
            VALUES ('General', 'general', 'General blog posts')
        ");

        // Create default menu
        $db->query("
            INSERT IGNORE INTO cms_menus (name, location) 
            VALUES ('Main Menu', 'header')
        ");

        // Create default home page
        $db->query("
            INSERT IGNORE INTO cms_pages (title, slug, content, status, published_at) 
            VALUES ('Welcome to StrataPHP', 'home', '<h1>Welcome to StrataPHP</h1><p>This is your homepage. Edit this page through the CMS admin panel.</p>', 'published', NOW())
        ");
    } catch (Exception $e) {
        throw new Exception("Failed to create CMS tables: " . $e->getMessage());
    }
};
