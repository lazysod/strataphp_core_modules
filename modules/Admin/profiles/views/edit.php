<?php
$localConfig = include dirname(__DIR__, 4) . '/app/config.php';
$sessionPrefix = $config['session_prefix'] ?? 'app_';
if (empty($_SESSION[$sessionPrefix . 'admin'])) {
    header('Location: /admin');
    exit;
}
include __DIR__ . '/../../../../views/partials/admin_header.php';
?>
<section class="py-5">

    <div class="container px-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="/admin/profiles">Profile List</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Profile</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="bg-light rounded-3 py-5 px-4 px-md-5 mb-5">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <h1>Edit Profile</h1>
                    <?php if (!$profile) : ?>
                        <div class="alert alert-danger">Profile not found.</div>
                    <?php else : ?>
                    <form method="post">
                        <label for="profile_name">Profile Name</label><br>
                        <input type="text" name="profile_name" class="form-control" id="profile_name" value="<?= htmlspecialchars($profile['profile_name']) ?>" required><br><br>
                        <label for="bio">Bio</label><br>
                        <textarea name="bio" class="form-control" id="bio" rows="4" cols="40"><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea><br><br>
                        <label for="verified">Verified</label>
                        <input type="checkbox" class="form-check-input" name="verified" id="verified" value="1" <?= !empty($profile['verified']) ? 'checked' : '' ?>><br><br>
                        <label for="locked">Suspended</label>
                        <input type="checkbox" class="form-check-input" name="locked" id="locked" value="1" <?= !empty($profile['locked']) ? 'checked' : '' ?>><br><br>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="/admin/profiles" class="btn btn-secondary">Cancel</a>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../../../../views/partials/footer.php'; ?>