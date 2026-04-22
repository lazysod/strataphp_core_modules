<?php
// require_once dirname(__DIR__, 5) . '/app/bootstrap.php';
require dirname(__DIR__, 3) . '/views/partials/header.php';
?>
<section class="py-5">
    <div class="container px-5">
        <div class="rounded-3 py-5 px-4 px-md-5 mb-5">
            <div class="text-center mb-5">
                <h1 class="fw-bolder">Set New Password</h1>
            </div>
            <div class="row gx-5 justify-content-center">
                <div class="col-lg-8 col-xl-6">
                    <?php if (!empty($success)) : ?>
                        <div class="alert alert-success text-center"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($error)) : ?>
                        <div class="alert alert-danger text-center"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="post" action="">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars(\App\TokenManager::csrf()); ?>">
                        <input type="hidden" name="reset_token" value="<?php echo htmlspecialchars($token ?? ''); ?>">
                        <div class="form-floating mb-3">
                            <input class="form-control" id="pwd" name="pwd" type="password" required />
                            <label for="pwd">New Password</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input class="form-control" id="pwd2" name="pwd2" type="password" required />
                            <label for="pwd2">Confirm New Password</label>
                        </div>
                        <div class="d-grid"><button class="btn btn-primary btn-lg" type="submit">Reset Password</button></div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</section>
<?php require dirname(__DIR__, 3) . '/views/partials/footer.php'; ?>