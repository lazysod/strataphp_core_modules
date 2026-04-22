<?php
$localConfig = include dirname(__DIR__, 4) . '/app/config.php';
$sessionPrefix = $config['session_prefix'] ?? 'app_';
if (empty($_SESSION[$sessionPrefix . 'admin'])) {
    header('Location: /admin');
    exit;
}

include __DIR__ . '/../../../../views/partials/admin_header.php'; ?>
<section class="py-5">

    <div class="container px-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="/admin/users">User List</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit User</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="bg-light rounded-3 py-5 px-4 px-md-5 mb-5">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <h1>Edit User</h1>
                    <form method="post" action="">
                        <!-- Display Name removed -->
                        <div class="mb-3">
                            <label for="display_name" class="form-label">Display Name</label>
                            <input type="text" class="form-control" id="display_name" name="display_name" value="<?php echo htmlspecialchars($user['display_name'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="second_name" class="form-label">Second Name</label>
                            <input type="text" class="form-control" id="second_name" name="second_name" value="<?php echo htmlspecialchars($user['second_name'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
</div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role">
                                <option value="user" <?php if (($user['is_admin'] ?? '') === 0) {
                                    echo 'selected';
                                                     } ?>>User</option>
                                <option value="admin" <?php if (($user['is_admin'] ?? '') === 1) {
                                    echo 'selected';
                                                      } ?>>Admin</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" <?php if (($user['status'] ?? '') === 'active') {
                                    echo 'selected';
                                                       } ?>>Active</option>
                                <option value="suspended" <?php if (($user['status'] ?? '') === 'suspended') {
                                    echo 'selected';
                                                          } ?>>Suspended</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                            <input type="password" class="form-control" id="password" name="password" autocomplete="new-password">
                        </div>
                        <button type="submit" class="btn btn-success">Save Changes</button>
                        <a href="/admin/users" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
</section>
<?php include __DIR__ . '/../../../../views/partials/footer.php'; ?>