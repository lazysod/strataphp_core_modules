<?php
$localConfig = include dirname(__DIR__, 4) . '/app/config.php';
$sessionPrefix = $config['session_prefix'] ?? 'app_';
if (empty($_SESSION[$sessionPrefix . 'admin'])) {
    header('Location: /admin');
    exit;
}
require __DIR__ . '/../../../../views/partials/admin_header.php'; ?>
<section class="py-5">
    <div class="container px-5">
                <!-- Breadcrumbs -->
                        <div class="row">
                            <div class="col">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-4">
                                        <li class="breadcrumb-item active" aria-current="page">Profile List</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                <!-- Contact form-->
        <div class="bg-light rounded-3 py-5 px-4 px-md-5 mb-5">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h1>Profile Management</h1>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Profile ID</th>
                                <th>Profile Name</th>
                                <th>User ID</th>
                                <th>Verified</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($profiles)) : ?>
                            <?php foreach ($profiles as $profile) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($profile['profile_id']) ?></td>
                                    <td><?php echo htmlspecialchars($profile['profile_name'] ?? '') ?></td>
                                    <td><a href="/admin/users/edit/<?php echo $profile['user_id'] ?>"><?php echo htmlspecialchars($profile['user_id']) ?></a></td>
                                    <td><?php echo isset($profile['verified']) && $profile['verified'] ? 'Yes' : 'No' ?></td>
                                    <td><?php echo isset($profile['admin_locked']) && $profile['admin_locked'] ? 'Suspended' : 'Active' ?></td>
                                    <td>
                                        <a href="/admin/admin_profiles/edit/<?php echo $profile['profile_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <?php
                                            // Show Verify/Unverify
                                        if (empty($profile['verified'])) {
                                            echo '<a href="/admin/admin_profiles/verify/' . $profile['profile_id'] . '" class="btn btn-sm btn-success">Verify</a>';
                                        } else {
                                            echo '<a href="/admin/admin_profiles/unverify/' . $profile['profile_id'] . '" class="btn btn-sm btn-outline-secondary">Unverify</a>';
                                        }
                                            // Show Suspend/Unsuspend
                                        if (isset($profile['admin_locked']) && $profile['admin_locked']) {
                                            echo '<a href="/admin/admin_profiles/unsuspend/' . $profile['profile_id'] . '" class="btn btn-sm btn-secondary">Unsuspend</a>';
                                        } else {
                                            echo '<a href="/admin/admin_profiles/suspend/' . $profile['profile_id'] . '" class="btn btn-sm btn-secondary">Suspend</a>';
                                        }
                                        ?>
                                        <a href="/admin/admin_profiles/delete/<?php echo $profile['profile_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this profile?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr><td colspan="6">No profiles found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
                <!-- Contact cards-->
                <a href="/admin/admin_profiles/add" class="btn btn-primary">Add Profile</a>

                <!-- Pagination Controls -->
                <?php if (isset($totalPages) && $totalPages > 1) : ?>
                <nav aria-label="Profile pagination">
                    <ul class="pagination mt-3 justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                            <li class="page-item<?php echo $i == $page ? ' active' : '' ?>">
                                <a class="page-link" href="?page=<?php echo $i ?>"><?php echo $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/../../../../views/partials/footer.php'; ?>