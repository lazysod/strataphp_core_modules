
<?php require __DIR__ . '/../../../../views/partials/admin_header.php'; ?>
<section class="py-5">
    <div class="container px-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="/admin/oauth-clients">OAuth Clients</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Client</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="bg-light rounded-3 py-5 px-4 px-md-5 mb-5">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <h1>Edit OAuth Client</h1>
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($client['name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="redirect_uri" class="form-label">Redirect URI</label>
                            <input type="text" class="form-control" id="redirect_uri" name="redirect_uri" value="<?= htmlspecialchars($client['redirect_uri']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="data_shared" class="form-label">Information Exchanged (comma separated)</label>
                            <input type="text" class="form-control" id="data_shared" name="data_shared" value="<?= htmlspecialchars($client['data_shared'] ?? '') ?>" placeholder="e.g. email,profile_name,avatar">
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="/admin/oauth-clients" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
