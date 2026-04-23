<?php
// require_once dirname(__DIR__, 4) . '/bootstrap.php';
require dirname(__DIR__, 3) . '/views/partials/header.php';
?>
<div class="container mt-5">
    <div class="row">
        <div class="col-md-6 bg-light p-4 mx-auto">
            <h2>Account Activation</h2>
            <?php if (!empty($success)) : ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <div class="pt-4 text-center">
                    <a href="/user/login" class="btn btn-primary">Go to Login</a>
                </div>
            <?php endif; ?>
            <?php if (!empty($error)) : ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <div class="pt-4 justify-content-between d-flex">
                    <a href="/user/login" class="btn btn-primary">Go to Login</a>
                </div>
                <div class="mt-4">
                    <form id="resendActivationForm" class="needs-validation" novalidate>
                        <label for="resendEmail" class="form-label">Resend Activation Email</label>
                        <input type="email" class="form-control mb-2" id="resendEmail" name="email" placeholder="Enter your email" required>
                        <input type="hidden" id="csrf_token" name="token" value="<?php echo htmlspecialchars(\App\TokenManager::csrf($config)); ?>">
                        <button type="submit" class="btn btn-secondary">Request New Activation Link</button>
                        <div id="resendActivationMsg" class="form-text mt-2"></div>
                    </form>
                </div>
                <script>
                document.getElementById('resendActivationForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    var email = document.getElementById('resendEmail').value.trim();
                    var msg = document.getElementById('resendActivationMsg');
                    msg.textContent = '';
                    var token = document.getElementById('csrf_token').value;
                    fetch('/modules/user/ajax/resendActivation.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'email=' + encodeURIComponent(email) + '&token=' + encodeURIComponent(token)
                    })
                    .then(res => res.json())
                    .then(data => {
                        msg.textContent = data.message;
                        msg.style.color = data.status === 'success' ? 'green' : 'red';
                    })
                    .catch(() => {
                        msg.textContent = 'Could not process request.';
                        msg.style.color = 'red';
                    });
                });
                </script>
            <?php endif; ?>
        </div>
    </div>

</div>
<?php
require dirname(__DIR__, 3) . '/views/partials/footer.php';
