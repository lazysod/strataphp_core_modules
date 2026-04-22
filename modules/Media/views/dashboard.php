
<?php
// Media Module Dashboard View
include_once __DIR__ . '/../../../views/partials/admin_header.php';
?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="bg-white rounded shadow-sm p-4 mb-4">
                <h1 class="mb-3 text-primary">Media Dashboard</h1>
                <p class="text-secondary mb-4">Welcome to the Media Module Dashboard. Here you can manage uploads, view the media library, and configure settings.</p>
                <a href="/admin/media/media-library" class="btn btn-primary me-2">Go to Media Library</a>
                <a href="/admin/strata-cms" class="btn btn-outline-secondary">Go to CMS</a>
            </div>
        </div>
    </div>
</div>
<?php include_once __DIR__ . '/../../../views/partials/footer.php'; ?>
