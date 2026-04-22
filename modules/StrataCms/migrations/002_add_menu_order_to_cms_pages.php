<?php
/**
 * Add menu_order column to cms_pages table
 * Migration: 002_add_menu_order_to_cms_pages
 */

// Check if we're being run directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $localConfig = include dirname(__DIR__, 3) . '/app/config.php';
    require_once dirname(__DIR__, 3) . '/app/DB.php';
    
    error_log('DEBUG: 002_add_menu_order_to_cms_pages.php DB config: ' . print_r($localConfig['db'], true));
    $db = new App\DB($localConfig['db']);
    
    echo "Running migration: Add menu_order to cms_pages\n";
    
    try {
        // Check if column already exists
        $columns = $db->fetchAll("SHOW COLUMNS FROM cms_pages LIKE 'menu_order'");
        
        if (empty($columns)) {
            // Add menu_order column
            $db->query("
                ALTER TABLE cms_pages 
                ADD COLUMN menu_order INT DEFAULT 0 AFTER template
            ");
            
            echo "✅ Added menu_order column to cms_pages table\n";
            
            // Update existing pages with menu order based on creation date
            $db->query("
                UPDATE cms_pages 
                SET menu_order = id * 10 
                WHERE menu_order = 0
            ");
            
            echo "✅ Set default menu order for existing pages\n";
        } else {
            echo "ℹ️  menu_order column already exists\n";
        }
    } catch (Exception $e) {
        echo "❌ Migration failed: " . $e->getMessage() . "\n";
    }
    
    echo "Migration completed.\n";
}

// Migration functions for use by migration runner
function up($db)
{
    // Check if column already exists
    $columns = $db->fetchAll("SHOW COLUMNS FROM cms_pages LIKE 'menu_order'");
    
    if (empty($columns)) {
        // Add menu_order column
        $db->query("
            ALTER TABLE cms_pages 
            ADD COLUMN menu_order INT DEFAULT 0 AFTER template
        ");
        
        // Update existing pages with menu order
        $db->query("
            UPDATE cms_pages 
            SET menu_order = id * 10 
            WHERE menu_order = 0
        ");
        
        return "Added menu_order column to cms_pages table";
    }
    
    return "menu_order column already exists";
}

function down($db)
{
    // Remove menu_order column
    $db->query("ALTER TABLE cms_pages DROP COLUMN IF EXISTS menu_order");
    return "Removed menu_order column from cms_pages table";
}
