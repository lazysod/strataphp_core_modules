<?php
// modules/Admin/views/profile.php
$sessionPrefix = $config['session_prefix'] ?? ($config['prefix'] ?? 'framework');
$adminId = $_SESSION[$sessionPrefix . 'admin'] ?? null;
$success = $success ?? '';
$error = $error ?? '';
require_once $_SERVER['DOCUMENT_ROOT'] . '/views/partials/admin_header.php';
?>
<div class="container mt-5">
    <div class="row">
        <div class="col-lg-6 bg-light p-4 mx-auto">
            <div>
                <a href="/admin/dashboard" class="btn btn-secondary mb-3"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
            </div>
            <h1>Admin Profile</h1>
            <?php if ($success) : ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error) : ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\App\TokenManager::csrf($config)) ?>">
                <div class="mb-3">
                    <label for="avatar" class="form-label">Avatar</label><br>
                    <?php if (!empty($user['avatar'])) : ?>
                        <div class="p-3 text-center">
                            <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" style="max-width:80px;max-height:80px;" class="rounded-circle mb-2">
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                </div>
                <div class="mb-3">
                    <label for="display_name" class="form-label">Display Name</label>
                    <input type="text" class="form-control" id="display_name" name="display_name" value="<?= htmlspecialchars($user['display_name'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label for="second_name" class="form-label">Second Name</label>
                    <input type="text" class="form-control" id="second_name" name="second_name" value="<?= htmlspecialchars($user['second_name'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label for="pwd" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="pwd" name="pwd" autocomplete="new-password">
                </div>
                <div class="mb-3">
                    <label for="pwd2" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="pwd2" name="pwd2" autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>
    </div>

</div>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/views/partials/footer.php';
?>