<?php
use App\Version;
// CMS Dashboard Template
if (!defined('STRPHP_ROOT')) {
    require_once dirname(__DIR__, 4) . '/bootstrap.php';
}
require __DIR__ . '/../partials/admin_header.php';
?>
    <div class="container">
<!-- Dynamic Module Links -->
        <div class="actions" style="margin-bottom: 30px;">
        <?php
        $modulesConfig = include dirname(__DIR__, 4) . '/app/modules.php';
        // SAFE: Only reads validated, local module.json files. See safe_file_get_contents().
        // Safe file_get_contents wrapper
        function safe_file_get_contents($file)
        {
            // Only allow reading files within the modules directory
            $modulesDir = realpath(dirname(__DIR__, 4) . '/modules');
            $realFile = realpath($file);
            if ($realFile && strpos($realFile, $modulesDir) === 0 && is_readable($realFile)) {
                return file_get_contents($realFile);
            }
            return false;
        }

        foreach ($modulesConfig['modules'] as $modName => $modInfo) {
            // Only allow valid module names: letters, numbers, underscores, hyphens
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $modName)) {
                continue;
            }
            if (is_array($modInfo) && !empty($modInfo['enabled'])) {
                $metaFile = dirname(__DIR__, 4) . '/modules/' . $modName . '/module.json';
                if (file_exists($metaFile)) {
                    $metaJson = safe_file_get_contents($metaFile);
                    $meta = $metaJson ? json_decode($metaJson, true) : [];
                    if (!empty($meta['admin_url'])) {
                        echo '<div class="action-card">';
                        echo '<h3>' . htmlspecialchars($meta['title'] ?? ucfirst($modName)) . '</h3>';
                        echo '<p>' . htmlspecialchars($meta['description'] ?? '') . '</p>';
                        echo '<a href="' . htmlspecialchars($meta['admin_url']) . '" class="btn btn-info">Open ' . htmlspecialchars($meta['title'] ?? ucfirst($modName)) . '</a>';
                        echo '</div>';
                    }
                }
            }
        }
        ?>
        </div>
        <div class="header">
            <h1><?= htmlspecialchars($title ?? 'CMS Dashboard') ?></h1>
            <p>Welcome to your StrataPHP Content Management System</p>
        </div>

        <?php if (isset($stats)) : ?>
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?= $stats['total_pages'] ?? 0 ?></h3>
                <p>Total Pages</p>
            </div>
            <div class="stat-card">
                <h3><?= $stats['published_pages'] ?? 0 ?></h3>
                <p>Published Pages</p>
            </div>
            <div class="stat-card">
                <h3><?= $stats['draft_pages'] ?? 0 ?></h3>
                <p>Draft Pages</p>
            </div>
        </div>
        <?php endif; ?>

        <div class="actions">
            <div class="action-card">
                <h3>📝 Manage Pages</h3>
                <p>Create, edit, and organize your website pages</p>
                <a href="/admin/strata-cms/pages" class="btn btn-info">Manage Pages</a>
            </div>
            <div class="action-card">
                <h3>➕ Create New Page</h3>
                <p>Add new content to your website</p>
                <a href="/admin/strata-cms/pages/create" class="btn btn-success">Create Page</a>
            </div>
            <div class="action-card">
                <h3>🖼️ Media Library</h3>
                <p>Upload and manage images, documents, and other media files</p>
                <a href="/admin/strata-cms/media-library" class="btn btn-info">Open Media Library</a>
            </div>
            <div class="action-card">
                <h3>🌐 View Website</h3>
                <p>See how your site looks to visitors</p>
                <a href="/" class="btn btn-info" target="_blank">View Site</a>
            </div>
            <div class="action-card">
                <h3>📊 API Access</h3>
                <p>Access your content via REST API</p>
                <a href="/api/pages" class="btn btn-info" target="_blank">View API</a>
            </div>
            <div class="action-card">
                <h3>🔑 Manage Sites & API Keys</h3>
                <p>Create, edit, and manage API access for headless CMS usage</p>
                <a href="/admin/strata-cms/sites" class="btn btn-info">Manage Sites</a>
            </div>
        </div>

        <?php if (isset($stats['recent_pages']) && !empty($stats['recent_pages'])) : ?>
        <div class="recent-content">
            <h2>Recent Pages</h2>
            <ul class="content-list">
                <?php foreach ($stats['recent_pages'] as $page) : ?>
                <li>
                    <div>
                        <strong><?= htmlspecialchars($page['title']) ?></strong>
                        <br>
                        <small>Created: <?= date('M j, Y', strtotime($page['created_at'])) ?></small>
                    </div>
                    <div>
                        <span class="status <?= $page['status'] ?>"><?= ucfirst($page['status']) ?></span>
                        <a href="/admin/strata-cms/pages/<?= $page['id'] ?>/edit" class="btn btn-info" style="margin-left: 10px; padding: 5px 10px; font-size: 12px;">Edit</a>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; font-size: 14px;">
            <p>StrataPHP CMS Module - Version <?php echo htmlspecialchars(Version::get() ?? ''); ?></p>
            <p><a href="/admin" style="color: #3498db;">← Back to Admin Panel</a></p>
        </div>
    </div>
<?php require __DIR__ . '/../partials/admin_footer.php'; ?>