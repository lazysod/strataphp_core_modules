<?php
namespace App\Modules\StrataCms\Helpers;

use App\Modules\StrataCms\Models\Site;

class SiteHelper
{
    /**
     * Detect the current site ID based on domain or config.
     *
     * @return int|null
     */
    public static function getCurrentSiteId()
    {
        // 1. Try config table (persistent, global)
        try {
            $config = include dirname(__DIR__, 3) . '/app/config.php';
            $db = new \App\DB($config);
            $row = $db->fetch("SELECT config_value FROM config WHERE config_key = 'active_site_id' LIMIT 1");
            if ($row && isset($row['config_value'])) {
                return (int)$row['config_value'];
            }
        } catch (\Throwable $e) {
            // Ignore DB errors, fallback below
        }
        // 2. Try session (per-user override)
        if (!empty($_SESSION['current_site_id'])) {
            return (int)$_SESSION['current_site_id'];
        }
        // 3. Fallback to default site (ID 1)
        return 1;
    }
}
