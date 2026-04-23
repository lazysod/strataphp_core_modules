<?php
require_once dirname(__DIR__, 3) . '/bootstrap.php';
require dirname(__DIR__, 3) . '/views/partials/header.php';
?>
<section class="py-5">
    <div class="container px-5">
        <div class="bg-light rounded-3 py-5 px-4 px-md-5 mb-5">
            <div class="text-center mb-5">
               
                <h1 class="fw-bolder"><i class="bi bi-person-plus"></i> User Registration</h1>
            </div>
            <div class="row gx-5  justify-content-center">
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
                    <?php if (empty($success)) : ?>
                        <form id="userRegisterForm" method="post" action="" class="">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars(\App\TokenManager::csrf()); ?>">
                            <div class="form-floating mb-3">
                                <input class="form-control" id="display_name" value="<?php if (isset($_POST['display_name'])) {
                                    echo htmlspecialchars($_POST['display_name']);
                                                                                     } ?>" name="display_name" type="text" placeholder="Display Name" required />
                                <label for="display_name"><span class="text-danger">*</span>Display Name</label>
                                <div id="displayNameFeedback" class="form-text"></div>
                            </div>
                            <script>
                            // Live display name validation
                            document.addEventListener('DOMContentLoaded', function() {
                                const displayNameInput = document.getElementById('display_name');
                                const feedback = document.getElementById('displayNameFeedback');
                                let lastValue = '';
                                let timeout = null;
                                displayNameInput.addEventListener('input', function() {
                                    const value = displayNameInput.value.trim();
                                    if (value === lastValue) return;
                                    lastValue = value;
                                    feedback.textContent = '';
                                    if (timeout) clearTimeout(timeout);
                                    if (!value) return;
                                    timeout = setTimeout(function() {
                                        fetch('/modules/User/ajax/checkDisplayName.php?display_name=' + encodeURIComponent(value))
                                            .then(res => res.json())
                                            .then(data => {
                                                feedback.textContent = data.message;
                                                feedback.style.color = data.valid ? 'green' : 'red';
                                            })
                                            .catch(() => {
                                                feedback.textContent = 'Could not validate display name.';
                                                feedback.style.color = 'red';
                                            });
                                    }, 300);
                                });
                            });
                            </script>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="first_name" name="first_name" value="<?php if (isset($_POST['first_name'])) {
                                    echo htmlspecialchars($_POST['first_name']);
                                                                                                     } ?>" type="text" placeholder="First Name" required />
                                <label for="first_name"><span class="text-danger">*</span>First Name</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="second_name" name="second_name" value="<?php if (isset($_POST['second_name'])) {
                                    echo htmlspecialchars($_POST['second_name']);
                                                                                                       } ?>" type="text" placeholder="Second Name" required />
                                <label for="second_name"><span class="text-danger">*</span>Second Name</label>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="email" name="email" type="email" placeholder="name@example.com" value="<?php if (isset($_POST['email'])) {
                                    echo htmlspecialchars($_POST['email']);
                                                                                                                                       } ?>" required />
                                <label for="email"><span class="text-danger">*</span>Email address</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="password" name="password" type="password" placeholder="Enter password" required />
                                <label for="password">Password</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="confirm_password" name="confirm_password" type="password" placeholder="Confirm password" required />
                                <label for="confirm_password">Confirm Password</label>
                            </div>
                            <div class="d-grid"><button class="btn btn-primary btn-lg " id="submitButton" type="submit">Register</button></div>
                        </form>
                    <?php endif; ?>

                    <p class="text-center  mt-4">
                        Don't have an account? <a href="/user/register">Register here</a> - or you may <a href="/user/login">login</a> here
                    </p>
                    <p class="">
                        By registering, you agree to our <a href="/terms">Terms of Service</a> and <a href="/privacy">Privacy Policy</a>.
                    </p>
                </div>

            </div>
        </div>
    </div>
</section>
<?php require dirname(__DIR__, 3) . '/views/partials/footer.php'; ?>