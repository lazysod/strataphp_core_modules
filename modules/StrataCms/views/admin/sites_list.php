<?php
// Admin: List all sites
if (!defined('STRPHP_ROOT')) {
}
$success_message = $_SESSION['success'] ?? null;
$error_message = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

?>
<!DOCTYPE html>
<html lang="en">
<?php
// Admin: List all sites
if (!defined('STRPHP_ROOT')) {
    require_once dirname(__DIR__, 4) . '/bootstrap.php';
}
$success_message = $_SESSION['success'] ?? null;
$error_message = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
require __DIR__ . '/../partials/admin_header.php';
?>
<div class="container">
    <a href="/admin">Admin</a> > <a href="/admin/strata-cms">Strata CMS</a> > Sites

    <?php if ($success_message) : ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    <?php if ($error_message) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>
    <div class="header">
        <h1><?= htmlspecialchars($title ?? 'Manage Sites') ?></h1>
        <a href="/admin/cms/sites/create" class="btn btn-success">+ Create New Site</a>
    </div>

    <!-- Active Site Dropdown -->
    <form method="post" action="/admin/cms/sites/set-active" style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        <label for="active_site_id" style="font-weight:600;">Active Site:</label>
        <select name="active_site_id" id="active_site_id" onchange="this.form.submit()" style="padding: 8px 12px; border-radius: 4px; border: 1px solid #ccc;">
            <?php foreach ($sites as $site) : ?>
                <option value="<?= $site['id'] ?>" <?= (isset($activeSiteId) && $activeSiteId == $site['id']) ? 'selected' : '' ?>><?= htmlspecialchars($site['name']); ?></option>
            <?php endforeach; ?>
        </select>
        <span style="color:#888;">(Switching will set which site's content is shown in admin and frontend)</span>
    </form>

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>API Key</th>
            <th>Status</th>
            <th>Headless</th>
            <th>Created</th>
            <th>Updated</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($sites as $site) : ?>
            <tr<?= (isset($activeSiteId) && $activeSiteId == $site['id']) ? ' style="background:#eaf6ff;"' : '' ?>>
                <td><?= $site['id'] ?></td>
                <td>
                    <?= htmlspecialchars($site['name']) ?>
                    <?php if (isset($activeSiteId) && $activeSiteId == $site['id']) : ?>
                        <span class="badge bg-primary" style="margin-left:8px;">Active</span>
                    <?php endif; ?>
                </td>
                <td style="font-family:monospace;word-break:break-all;max-width:300px;">
                    <?= htmlspecialchars($site['api_key']) ?>
                </td>
                <td><?= htmlspecialchars($site['status']) ?></td>
                <td><?= isset($site['headless']) && $site['headless'] ? '<span style="color:#27ae60;font-weight:bold;">Yes</span>' : '<span style="color:#888;">No</span>' ?></td>
                <td><?= htmlspecialchars($site['created_at']) ?></td>
                <td><?= htmlspecialchars($site['updated_at']) ?></td>
                <td>
                    <a href="/admin/cms/sites/edit/<?= $site['id'] ?>" class="btn btn-primary btn-small btn-sm" style="margin-right:5px;">Edit</a>
                    <a href="/admin/cms/sites/regenerate/<?= $site['id'] ?>" class="btn btn-warning btn-small btn-sm" onclick="return confirm('Regenerate API key for this site?')">Regenerate Key</a>
                    <form method="post" action="/admin/cms/sites/delete/<?= $site['id'] ?>" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this site?\n\nAll pages belonging to this site will also be permanently deleted! This cannot be undone.');">
                        <button type="submit" class="btn btn-danger btn-small btn-sm" title="Delete Site" style="margin-left:5px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </td>
                </tr>
            <?php endforeach; ?>
    </tbody>
</table>

</div>
</div>

<?php require __DIR__ . '/../partials/admin_footer.php'; ?>