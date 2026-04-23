<?php
// Always load bootstrap.php for config, autoload, session, etc.
require_once $_SERVER['DOCUMENT_ROOT'] . '/bootstrap.php';
global $config;
$sessionPrefix = $config['session_prefix'] ?? ($config['prefix'] ?? 'app_');

if (!isset($_SESSION[$sessionPrefix . 'admin']) || $_SESSION[$sessionPrefix . 'admin'] < 1) {
    header('Location: /admin/admin_login.php');
    exit;
}

// Use central header partial
require $_SERVER['DOCUMENT_ROOT'] . '/views/partials/admin_header.php';
?>
<section class="py-5">
    <div class="container px-5">
        <!-- Contact form-->
        <div class="bg-light rounded-3 py-5 px-4 px-md-5 mb-5">
            <div class="text-center mb-5">
                <i class="bi bi-person-fill-lock"></i>
                <h1 class="fw-bolder">Welcome!!</h1>
            </div>
            <div class="row gx-5 justify-content-center">
                <div class="col-lg-8 col-xl-6 text-center">
                    <a href="/logout.php" class="btn btn-danger btn-lg">Logout</a>
                </div>
            </div>
        </div>
        
        <!-- CMS Quick Access Cards -->
        <?php if (isset($config['modules']['cms']['enabled']) && $config['modules']['cms']['enabled']): ?>
        <div class="row gx-5 mb-5">
            <div class="col-lg-12">
                <h2 class="fw-bolder mb-4">Content Management</h2>
            </div>
            <div class="col-lg-4 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="fs-4 mb-3">📝</div>
                        <h5 class="card-title">CMS Dashboard</h5>
                        <p class="card-text">Manage your website content and pages</p>
                        <a href="/admin/cms" class="btn btn-primary">Open CMS</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="fs-4 mb-3">📄</div>
                        <h5 class="card-title">Manage Pages</h5>
                        <p class="card-text">View, edit, and organize your pages</p>
                        <a href="/admin/cms/pages" class="btn btn-outline-primary">Manage Pages</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="fs-4 mb-3">➕</div>
                        <h5 class="card-title">Create Page</h5>
                        <p class="card-text">Add new content to your website</p>
                        <a href="/admin/cms/pages/create" class="btn btn-success">Create Page</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Contact cards-->

    </div>
</section>

<?php 
// Footer include (create this file if missing)
// Use central footer partial
require $_SERVER['DOCUMENT_ROOT'] . '/views/partials/footer.php';
?>