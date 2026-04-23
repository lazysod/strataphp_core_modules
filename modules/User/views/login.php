<?php
require_once dirname(__DIR__, 3) . '/bootstrap.php';
require dirname(__DIR__, 3) . '/views/partials/header.php';
?>

<section class="py-5">
    <div class="container px-5">
        <div class="bg-light rounded-3 py-5 px-4 px-md-5 mb-5">
            <div class="text-center mb-5">

                <h1 class="fw-bolder"><i class="bi bi-person"></i> User Login</h1>
            </div>
            <div class="row gx-5 justify-content-center">
                <div class="col-lg-8 col-xl-6">
                    <?php if (!empty($success)) : ?>
                        <div class="alert alert-success text-center alert-dismissible fade show" role="alert">
                            <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($error)) : ?>
                        <div class="alert alert-danger text-center alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <form id="userLoginForm" method="post" action="">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars(\App\TokenManager::csrf()); ?>">
                        <div class="form-floating mb-3">
                            <input class="form-control" id="email" name="email" type="email" placeholder="name@example.com" required />
                            <label for="email">Email address</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input class="form-control" id="password" name="password" type="password" placeholder="Enter password" required />
                            <label for="password">Password</label>
                        </div>
                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember" checked>
                                <label class="form-check-label text-white" for="remember">Remember me</label>
                            </div>
                        </div>
                        <div class="d-grid"><button class="btn btn-primary btn-lg " id="submitButton" type="submit">Login</button></div>
                        <div class="mt-3 text-center">
                            <a href="/user/reset-request">Forgot password?</a>
                        </div>
                    </form>
                </div>
                <p class="text-center mt-4">
                    Don't have an account? <a href="/user/register">Register here</a> - or you may <a href="/user/login">login</a> here
                <?php
                // Use $config and $sessionPrefix from controller
                $sessionPrefix = $config['session_prefix'] ?? 'app_';
                ?>
                </p>
            </div>
        </div>
    </div>
</section>
<?php require dirname(__DIR__, 3) . '/views/partials/footer.php'; ?>