<?php
require dirname(__DIR__, 3) . '/views/partials/header.php';
?>
<!-- start -->
<div class="container mt-5">
    <div class="row mt-4 justify-content-center">
        <div class="col-md-4 mx-auto">
            <div class="card shadow">
                <div class="card-body">
                    <h1 class="mb-4">Authorize <?= htmlspecialchars($client['name']) ?></h1>
                    <form method="post">
                        <p><strong><?= htmlspecialchars($client['name']) ?></strong> is requesting access to your account.</p>
                        <p>
                        <button type="submit" name="approve" value="1" class="btn btn-primary btn-lg">Authorize</button>
                        <button type="submit" name="deny" value="1" class="btn btn-secondary ms-2">Cancel</button>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require dirname(__DIR__, 3) . '/views/partials/footer.php'; ?>