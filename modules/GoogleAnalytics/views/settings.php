<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/config.php';
$sessionPrefix = $config['session_prefix'] ?? ($config['prefix'] ?? 'framework');
if (!isset($_SESSION[$sessionPrefix . 'admin']) || $_SESSION[$sessionPrefix . 'admin'] < 1) {
    header('Location: /admin/admin_login.php');
    exit;
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/views/partials/admin_header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/DB.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/modules/GoogleAnalytics/Models/GoogleAnalytics.php';
$config = file_exists($_SERVER['DOCUMENT_ROOT'] . '/app/config.php') ? include $_SERVER['DOCUMENT_ROOT'] . '/app/config.php' : [];
$db = new \App\DB($config);
$gaModel = new \App\Modules\GoogleAnalytics\Models\GoogleAnalytics($db);
$measurementId = $gaModel->getMeasurementId();
?>
<div class="container">
    <div class="row mt-4 mb-4">
        <div class="col-lg-6 mx-auto text-center">
            <h2><i class="fab fa-google me-2"></i>Google Analytics Settings</h2>
        </div>
    </div>
    <div class="row mt-4 mb-4">
        <div class="col-lg-6 mx-auto">
            <?php if (!empty($_SESSION['ga_settings_success'])) : ?>
                <div class="alert alert-success"> <?= htmlspecialchars($_SESSION['ga_settings_success']) ?> </div>
                <?php unset($_SESSION['ga_settings_success']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['ga_settings_error'])) : ?>
                <div class="alert alert-danger"> <?= htmlspecialchars($_SESSION['ga_settings_error']) ?> </div>
                <?php unset($_SESSION['ga_settings_error']); ?>
            <?php endif; ?>
            <form method="post" action="/admin/google-analytics-settings/save" class="mb-4">
                <label for="measurement_id" class="form-label">Measurement ID (e.g., G-XXXXXXXXXX):</label><br>
                <input type="text" id="measurement_id" name="measurement_id" class="form-control" value="<?= htmlspecialchars($measurementId) ?>" required pattern="^G-[A-Z0-9]{10}$" title="Format: G-XXXXXXXXXX" style="max-width:400px;">
                <br>
                <button type="submit" class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
</div>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/views/partials/footer.php'; ?>