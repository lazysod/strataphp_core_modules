<?php
require_once dirname(__DIR__, 4) . '/bootstrap.php';
require dirname(__DIR__, 4) . '/views/partials/header.php';
?>
<section class="py-5">
    <div class="container px-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="bg-dark rounded-3 py-5 px-4 px-md-5 mb-5">
                    <h2>OAuth Error</h2>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error) ?>
                    </div>
                    <a href="/admin/oauth-clients" class="btn btn-primary">Back to OAuth Clients</a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
require dirname(__DIR__, 4) . '/views/partials/footer.php';
?>

