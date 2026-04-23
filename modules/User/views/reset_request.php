<?php
require_once dirname(__DIR__, 4) . '/bootstrap.php';
require dirname(__DIR__, 3) . '/views/partials/header.php';
?>
<section class="py-5">
    <div class="container px-5">
        <div class=" rounded-3 py-5 px-4 px-md-5 mb-5">
            <div class="text-center mb-5">
                <h1 class="fw-bolder">Reset Password</h1>
                <p>Enter your email address to receive a password reset link.</p>
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
                        <div class="form-floating mb-3">
                            <input class="form-control" id="email" name="email" type="email" required />
                            <label for="email">Email address</label>
                        </div>
                        <div class="d-grid"><button class="btn btn-primary btn-lg" type="submit">Send Reset Link</button></div>
                    </form>
                    <p class="text-center mt-4">
                        Don't have an account? <a href="/user/register">Register here</a> - or you may <a href="/user/login">login</a> here
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require dirname(__DIR__, 3) . '/views/partials/footer.php'; ?>