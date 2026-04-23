<?php
// CMS Pages Management Template
if (!defined('STRPHP_ROOT')) {
}

// Check for session messages
$success_message = $_SESSION['success'] ?? null;
$error_message = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Manage Pages') ?></title>
    <link rel="stylesheet" href="/themes/default/assets/fontawesome/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #3498db;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #2c3e50;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover {
            background: #2980b9;
        }
        .btn-success {
            background: #27ae60;
        }
        .btn-success:hover {
            background: #229954;
        }
        .btn-danger {
            background: #e74c3c;
            padding: 5px 10px;
            font-size: 12px;
        }
        .btn-danger:hover {
            background: #c0392b;
        }
        .btn-small {
            padding: 5px 10px;
            font-size: 12px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .alert-error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }
        .table tr:hover {
            background-color: #f8f9fa;
        }
        .status {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            text-transform: uppercase;
            font-weight: bold;
        }
        .status.published {
            background: #d4edda;
            color: #155724;
        }
        .status.draft {
            background: #fff3cd;
            color: #856404;
        }
        .status.private {
            background: #d1ecf1;
            color: #0c5460;
        }
        .actions {
            display: flex;
            gap: 5px;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        .empty-state h3 {
            margin-bottom: 10px;
        }
        .breadcrumb {
            margin-bottom: 20px;
            font-size: 14px;
        }
        .breadcrumb a {
            color: #3498db;
            text-decoration: none;
        }
        .breadcrumb a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="breadcrumb">
            <a href="/admin">Admin</a> > <a href="/admin/strata-cms">StrataCMS</a> > Pages
        </div>

        <?php if ($success_message) : ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <?php if ($error_message) : ?>
            <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>


        <form method="get" action="" style="margin-bottom: 20px; display: flex; align-items: center; gap: 16px;">
            <label for="site_id" style="font-weight:600;">Filter by Site:</label>
            <select name="site_id" id="site_id" onchange="this.form.submit()" style="padding: 8px 12px; border-radius: 4px; border: 1px solid #ccc;">
                <option value="">All Sites</option>
                <?php if (isset($sites) && is_array($sites)) : ?>
                    <?php foreach ($sites as $site) : ?>
                        <option value="<?= htmlspecialchars($site['id']) ?>" <?= (isset($selectedSiteId) && $selectedSiteId == $site['id']) ? 'selected' : '' ?>><?= htmlspecialchars($site['name']) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <noscript><button type="submit" class="btn btn-outline">Filter</button></noscript>
            <a href="/admin/strata-cms/pages/create" class="btn btn-success" style="margin-left:auto;">+ Create New Page</a>
        </form>

        <div class="header">
            <h1><?= htmlspecialchars($title ?? 'Manage Pages') ?></h1>
        </div>

        <?php if (isset($pages) && !empty($pages)) : ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Site</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th>Author</th>
                        <th>Created</th>
                        <th>Home</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Build a tree from flat $pages array
                    $pageTree = [];
                    $pageIndex = [];
                    foreach ($pages as $p) {
                        $p['children'] = [];
                        $pageIndex[$p['id']] = $p;
                    }
                    foreach ($pageIndex as $id => &$p) {
                        if (!empty($p['parent_id']) && isset($pageIndex[$p['parent_id']])) {
                            $pageIndex[$p['parent_id']]['children'][] = &$p;
                        } else {
                            $pageTree[] = &$p;
                        }
                    }
                    unset($p);

                    // Recursive render function
                    // Build a site lookup for display
                    $siteLookup = [];
                    if (isset($sites) && is_array($sites)) {
                        foreach ($sites as $site) {
                            $siteLookup[$site['id']] = $site['name'];
                        }
                    }
                    function renderPageRow($page, $level = 0, $siteLookup = [])
                    {
                        $indent = $level * 24;
                        $isParent = !empty($page['children']);
                        echo '<tr' . ($isParent ? ' style="background:#f6fbff;"' : '') . '>';
                        echo '<td style="padding-left:' . $indent . 'px">';
                        if ($isParent) {
                            echo '<span style="font-weight:bold;color:#2980b9;">' . htmlspecialchars($page['title']) . '</span>';
                        } else {
                            echo htmlspecialchars($page['title']);
                        }
                        if (!empty($page['excerpt'])) {
                            echo '<br><small style="color: #666;">' . htmlspecialchars(substr($page['excerpt'], 0, 100)) . (strlen($page['excerpt']) > 100 ? '...' : '') . '</small>';
                        }
                        echo '</td>';
                        // Site column
                        $siteName = isset($siteLookup[$page['site_id']]) ? htmlspecialchars($siteLookup[$page['site_id']]) : '<span style="color:#aaa;">(none)</span>';
                        echo '<td>' . $siteName . '</td>';
                        echo '<td><code>' . htmlspecialchars($page['slug']) . '</code><br><small><a href="/' . htmlspecialchars($page['slug']) . '" target="_blank" style="color: #3498db;">View →</a></small></td>';
                        echo '<td><span class="status ' . $page['status'] . '">' . ucfirst($page['status']) . '</span></td>';
                        echo '<td>' . ($page['author_id'] ? 'User #' . $page['author_id'] : 'System') . '</td>';
                        echo '<td>' . date('M j, Y', strtotime($page['created_at'])) . '<br><small style="color: #666;">' . date('g:i A', strtotime($page['created_at'])) . '</small></td>';
                        echo '<td style="text-align:center;">';
                        if (!empty($page['is_home'])) {
                            echo '<span style="color: #27ae60; font-weight: bold;">Home</span>';
                        } else {
                            echo '<form method="POST" action="/admin/strata-cms/pages/' . $page['id'] . '/set-home" style="display:inline;"><button type="submit" class="btn btn-small" onclick="return confirm(\'Set this page as the home page?\')">Set as Home</button></form>';
                        }
                        echo '</td>';
                        echo '<td><div class="actions"><a href="/admin/strata-cms/pages/' . $page['id'] . '/edit" class="btn btn-small">Edit</a>';
                        echo '<form method="POST" action="/admin/strata-cms/pages/' . $page['id'] . '/delete" style="display:inline;" onsubmit="return confirm(\'Are you sure you want to delete this page? This action cannot be undone.\')"><button type="submit" class="btn btn-danger">Delete</button></form></div></td>';
                        echo '</tr>';
                        if (!empty($page['children'])) {
                            foreach ($page['children'] as $child) {
                                renderPageRow($child, $level + 1, $siteLookup);
                            }
                        }
                    }
                    foreach ($pageTree as $rootPage) {
                        renderPageRow($rootPage, 0, $siteLookup);
                    }
                    ?>
                </tbody>
            </table>
        <?php else : ?>
            <div class="empty-state">
                <h3>No pages found</h3>
                <p>Get started by creating your first page!</p>
                <a href="/admin/strata-cms/pages/create" class="btn btn-success">Create Your First Page</a>
            </div>
        <?php endif; ?>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; font-size: 14px;">
            <p><a href="/admin/strata-cms" style="color: #3498db;">← Back to StrataCMS Dashboard</a></p>
        </div>
    </div>
</body>
</html>