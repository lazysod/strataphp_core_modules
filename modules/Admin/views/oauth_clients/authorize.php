<?php
require_once dirname(__DIR__, 5) . '/bootstrap.php';
require dirname(__DIR__, 4) . '/views/partials/header.php';
?>
<section class="py-5">
    <div class="container px-5">
        <div class="row mt-4">
            <div class="col-lg-6 mx-auto">
                <div class="bg-dark text-white rounded-3 py-5 px-4 px-md-5 mb-5">
                    <h2>Authorize Application!</h2>
                    <p><strong><?= htmlspecialchars($client['name']) ?></strong> is requesting access to your account.</p>
                    <p>
                        It will be able to access:
                        <ul>
                            <?php
                                $data_shared = explode(',', $client['data_shared']);
                            if (empty($data_shared)) {
                                echo 'No data given';
                            } else {
                                foreach ($data_shared as $shared) : ?>
                                <li><?= htmlspecialchars(ucfirst($shared)) ?></li>
                                <?php endforeach; ?>
                            <?php } ?>

                        </ul>
                    </p>
                    <form method="post">
                        <input type="hidden" name="client_id" value="<?= htmlspecialchars($client['client_id']) ?>">
                        <input type="hidden" name="redirect_uri" value="<?= htmlspecialchars($client['redirect_uri']) ?>">
                        <input type="hidden" name="state" value="<?= htmlspecialchars($state) ?>">
                        <button type="submit" class="btn btn-success">Authorize</button>
                        <a href="<?= htmlspecialchars($client['redirect_uri']) ?>?error=access_denied<?= $state ? '&state=' . urlencode($state) : '' ?>" class="btn btn-secondary ms-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
require_once dirname(__DIR__, 5) . '/bootstrap.php';
require dirname(__DIR__, 4) . '/views/partials/footer.php';
?>