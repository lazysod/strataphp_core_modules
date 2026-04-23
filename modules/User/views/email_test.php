<?php
require dirname(__DIR__, 3) . '/views/partials/header.php';
?>
<section class="py-5">
    <div class="container px-5">
        <div class="bg-light rounded-3 py-5 px-4 px-md-5 mb-5">
            <div class="text-center mb-5">
                <h1 class="fw-bolder">Email Test</h1>
                <p>Send a test email using your current SMTP/PHPMailer config.</p>
            </div>
            <div class="row gx-5 justify-content-center">
                <div class="col-lg-8 col-xl-6">
                    <?php if (!empty($success)) : ?>
                        <div class="alert alert-success text-center"><?php echo htmlspecialchars($success ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                    <?php if (!empty($error)) : ?>
                        <div class="alert alert-danger text-center"><?php echo htmlspecialchars($error ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                    <form method="post" action="">
                        <div class="form-floating mb-3">
                            <input class="form-control" id="to" name="to" type="email" required />
                            <label for="to">Recipient Email</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input class="form-control" id="subject" name="subject" type="text" value="Test Email" required />
                            <label for="subject">Subject</label>
                        </div>
                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="body" name="body" style="height: 100px;">This is a test email from your PHP framework.</textarea>
                            <label for="body">Message</label>
                        </div>
                        <div class="d-grid"><button class="btn btn-primary btn-lg" type="submit">Send Test Email</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require dirname(__DIR__, 3) . '/views/partials/footer.php'; ?>
