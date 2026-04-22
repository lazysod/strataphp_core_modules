<?php
$title = $data['title'] ?? 'Create GoogleAnalytics';
$showNav = true;
require __DIR__ . '/../../../views/partials/header.php';
?>

<section class="py-5">
    <div class="container px-5">
        <div class="row gx-5 justify-content-center">
            <div class="col-lg-8">
                <h1 class="fw-bolder mb-4">Create GoogleAnalytics</h1>
                
                <form method="post" action="/google_analytics/create">
                    <div class="form-floating mb-3">
                        <input class="form-control" id="title" name="title" type="text" value="<?= htmlspecialchars($data['item']['title']) ?>" required>
                        <label for="title">Title</label>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <textarea class="form-control" id="content" name="content" style="height: 200px" required><?= htmlspecialchars($data['item']['content']) ?></textarea>
                        <label for="content">Content</label>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="/google_analytics" class="btn btn-secondary">Cancel</a>
                        <button class="btn btn-primary" type="submit">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../../../views/partials/footer.php'; ?>