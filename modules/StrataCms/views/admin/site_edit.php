<?php
// Admin: Edit site form
if (!defined('STRPHP_ROOT')) {
    require_once dirname(__DIR__, 4) . '/bootstrap.php';
}
$success_message = $_SESSION['success'] ?? null;
$error_message = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
require __DIR__ . '/../partials/admin_header.php';
?>
<div class="container">
        <div class="breadcrumb">
            <a href="/admin">Admin</a> > <a href="/admin/strata-cms">CMS</a> > <a href="/admin/strata-cms/sites">Sites</a> > Edit
        </div>
        <?php if ($success_message) : ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        <?php if ($error_message) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>
        <div class="row">
            <div class="col">
                <div class="header">
                    <h1><?= htmlspecialchars($title ?? 'Edit Site') ?></h1>
                </div>
                <form method="post" action="/admin/cms/sites/update/<?= $site['id'] ?>">
                    <div class="mb-3">
                        <label for="name" class="form-label">Site Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($site['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="headless" class="form-label">Headless (API Only)</label>
                        <input type="checkbox" id="headless" name="headless" value="1" <?= $site['headless'] ? 'checked' : '' ?>>
                        <span style="font-size:13px;color:#666;">If checked, this site will be headless (API only, no frontend).</span>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-success">Save Changes</button>
                        <a href="/admin/cms/sites" class="btn btn-secondary">Cancel</a>
                    </div>

                </form>                
            </div>
        </div>

    </div>
</div>

<?php require __DIR__ . '/../partials/admin_footer.php'; ?>