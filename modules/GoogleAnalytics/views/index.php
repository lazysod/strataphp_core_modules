<?php
$title = $data['title'] ?? 'GoogleAnalytics';
$showNav = true;
require __DIR__ . '/../../../views/partials/header.php';
?>

<section class="py-5">
    <div class="container px-5">
        <div class="row gx-5">
            <div class="col-lg-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="fw-bolder">GoogleAnalytics</h1>
                    <div>
                        <a href="/google_analytics/settings" class="btn btn-outline-info me-2">Settings</a>
                        <a href="/google_analytics/create" class="btn btn-primary">Create New</a>
                    </div>
                </div>
                
                <?php if (isset($_SESSION['success'])) : ?>
                    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])) : ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <?php if (empty($data['items'])) : ?>
                    <div class="alert alert-info">
                        No google_analytics found. <a href="/google_analytics/create">Create the first one</a>
                    </div>
                <?php else : ?>
                    <div class="row">
                        <?php foreach ($data['items'] as $item) : ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($item['title']) ?></h5>
                                        <p class="card-text"><?= htmlspecialchars(substr($item['content'], 0, 100)) ?>...</p>
                                        <small class="text-muted"><?= date('M j, Y', strtotime($item['created_at'])) ?></small>
                                    </div>
                                    <div class="card-footer">
                                        <a href="/google_analytics/<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                        <a href="/google_analytics/<?= $item['id'] ?>/edit" class="btn btn-sm btn-outline-secondary">Edit</a>
                                        <form method="post" action="/google_analytics/<?= $item['id'] ?>/delete" class="d-inline">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                    onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../../../views/partials/footer.php'; ?>