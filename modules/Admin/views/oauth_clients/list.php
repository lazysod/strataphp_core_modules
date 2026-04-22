<?php 
require_once __DIR__ . '/../../../../bootstrap.php'; 
$base_url = isset($GLOBALS['config']['base_url']) ? $GLOBALS['config']['base_url'] : '';
$sessionPrefix = $GLOBALS['config']['session_prefix'] ?? ($GLOBALS['config']['prefix'] ?? 'app_');
if (!isset($_SESSION[$sessionPrefix . 'admin']) || $_SESSION[$sessionPrefix . 'admin'] < 1) {
    header('Location: /admin/admin_login.php');
    exit;
}
?>
<?php require __DIR__ . '/../../../../views/partials/admin_header.php'; ?>
<section class="py-5">
    <div class="container px-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item active" aria-current="page">OAuth Clients</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="bg-light rounded-3 py-5 px-4 px-md-5 mb-5">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <h1>OAuth Client Management</h1>
                    <div class="mb-3 text-end">
                        <a href="/admin/oauth-clients/add" class="btn btn-primary">Add New Client</a>
                    </div>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Client ID</th>
                                <th>Redirect URI</th>
                                <th>Info Exchanged</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($clients as $client) : ?>
                            <tr>
                                <td><?= htmlspecialchars($client['id']) ?></td>
                                <td><?= htmlspecialchars($client['name']) ?></td>
                                <td><code><?= htmlspecialchars($client['client_id']) ?></code></td>
                                <td>
                                    <?= htmlspecialchars($client['redirect_uri']) ?>
                                    <input type="text" id="redirect-<?= $client['id'] ?>" value="<?= htmlspecialchars($client['redirect_uri']) ?>" style="position:absolute;left:-9999px;">
                                    <button class="btn btn-sm btn-outline-primary ms-2" onclick="copyToClipboard('authurl-<?= $client['id'] ?>')" title="Copy Auth URL">
                                        Copy Auth URL
                                    </button>
                                    
                                    <input type="text" id="authurl-<?= $client['id'] ?>" value="<?= htmlspecialchars($base_url) ?>/oauth/authorize?client_id=<?= htmlspecialchars($client['client_id']) ?>&redirect_uri=<?= urlencode($client['redirect_uri']) ?>&response_type=code&state=xyz" style="position:absolute;left:-9999px;">
                                </td>
                                <td><?= htmlspecialchars($client['data_shared'] ?? '') ?></td>
                                <td>
                                    <a href="/admin/oauth-clients/edit/<?= $client['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="/admin/oauth-clients/delete/<?= $client['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this client?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/../../../../views/partials/footer.php'; ?>
<script>
function copyToClipboard(id) {
    var copyText = document.getElementById(id);
    copyText.type = 'text';
    copyText.select();
    copyText.setSelectionRange(0, 99999); // For mobile devices
    document.execCommand('copy');
    copyText.type = 'hidden';
    alert('Copied to clipboard');
}
</script>