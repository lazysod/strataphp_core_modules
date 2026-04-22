<?php
/**
 * Add social media and SEO fields to cms_pages table
 * Migration: 003_add_social_seo_fields_to_cms_pages
 */

// Check if we're being run directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $localConfig = include dirname(__DIR__, 3) . '/app/config.php';
    require_once dirname(__DIR__, 3) . '/app/DB.php';
    
    error_log('DEBUG: 003_add_social_seo_fields_to_cms_pages.php DB config: ' . print_r($localConfig['db'], true));
    $db = new App\DB($localConfig['db']);
    
    echo "Running migration: Add social media and SEO fields to cms_pages\n";
    
    try {
        // List of new columns to add
        $newColumns = [
            'meta_keywords' => "VARCHAR(255) DEFAULT NULL AFTER meta_description",
            'og_image' => "VARCHAR(500) DEFAULT NULL AFTER meta_keywords",
            'og_type' => "VARCHAR(50) DEFAULT 'article' AFTER og_image",
            'twitter_card' => "VARCHAR(50) DEFAULT 'summary_large_image' AFTER og_type",
            'canonical_url' => "VARCHAR(500) DEFAULT NULL AFTER twitter_card",
            'noindex' => "TINYINT(1) DEFAULT 0 AFTER canonical_url"
        ];
        
        foreach ($newColumns as $columnName => $columnDefinition) {
            // Check if column already exists
            $columns = $db->fetchAll("SHOW COLUMNS FROM cms_pages LIKE '{$columnName}'");
            
            if (empty($columns)) {
                $db->query("ALTER TABLE cms_pages ADD COLUMN {$columnName} {$columnDefinition}");
                echo "✅ Added {$columnName} column\n";
            } else {
                echo "ℹ️  {$columnName} column already exists\n";
            }
        }
        
        echo "✅ Migration completed successfully\n";
    } catch (Exception $e) {
        echo "❌ Migration failed: " . $e->getMessage() . "\n";
    }
}

// Migration functions for use by migration runner
function up($db)
{
    $newColumns = [
        'meta_keywords' => "VARCHAR(255) DEFAULT NULL AFTER meta_description",
        'og_image' => "VARCHAR(500) DEFAULT NULL AFTER meta_keywords",
        'og_type' => "VARCHAR(50) DEFAULT 'article' AFTER og_image",
        'twitter_card' => "VARCHAR(50) DEFAULT 'summary_large_image' AFTER og_type",
        'canonical_url' => "VARCHAR(500) DEFAULT NULL AFTER twitter_card",
        'noindex' => "TINYINT(1) DEFAULT 0 AFTER canonical_url"
    ];
    
    $results = [];
    
    foreach ($newColumns as $columnName => $columnDefinition) {
        $columns = $db->fetchAll("SHOW COLUMNS FROM cms_pages LIKE '{$columnName}'");
        
        if (empty($columns)) {
            $db->query("ALTER TABLE cms_pages ADD COLUMN {$columnName} {$columnDefinition}");
            $results[] = "Added {$columnName} column";
        } else {
            $results[] = "{$columnName} column already exists";
        }
    }
    
    return implode(", ", $results);
}

function down($db)
{
    $columnsToRemove = [
        'meta_keywords',
        'og_image',
        'og_type',
        'twitter_card',
        'canonical_url',
        'noindex'
    ];
    
    foreach ($columnsToRemove as $column) {
        $db->query("ALTER TABLE cms_pages DROP COLUMN IF EXISTS {$column}");
    }
    
    return "Removed social media and SEO columns from cms_pages table";
}
