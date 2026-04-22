<?php
require_once dirname(__DIR__, 4) . '/bootstrap.php';
require dirname(__DIR__, 3) . '/views/partials/header.php';
?>
<section class="py-5">
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <h1 class="text-center mb-4">SSO Connections</h1>
                <p class="text-center">Manage your Single Sign-On (SSO) connections for third-party websites and applications.</p>
            </div>
        </div>
        <?php if (empty($ssos)) : ?>
            <div class="alert alert-info">You have no SSO connections.</div>
        <?php else : ?>
            <div class="card bg-dark">
                <table class="table table-dark table-bordered table-dark table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Website / App</th>
                            <th>Client ID</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($ssos as $sso) : ?>
                        <tr>
                            <td><?= htmlspecialchars($sso['client_name'] ?? 'Unknown') ?></td>
                            <td><?= htmlspecialchars($sso['client_id']) ?></td>
                            <td><?= ((int)$sso['status'] === 1) ? 'Active' : 'Revoked' ?></td>
                            <td>
                                <?php if ((int)$sso['status'] === 1) : ?>
                                    <form method="post" action="/user/sso/revoke" style="display:inline;">
                                        <input type="hidden" name="revoke_id" value="<?= (int)$sso['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Revoke</button>
                                    </form>
                                <?php else : ?>
                                    <span class="text-muted">Revoked</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>
    </div>    
</section>
<?php
include dirname(__DIR__, 3) . '/views/partials/footer.php';
?>