<?php
$title = $data['title'] ?? 'Create Cms';
$showNav = true;
require __DIR__ . '/../../../views/partials/header.php';
?>

<section class="py-5">
    <div class="container px-5">
        <div class="row gx-5 justify-content-center">
            <div class="col-lg-8">
                <h1 class="fw-bolder mb-4">Create Cms</h1>
                
                <form method="post" action="/cms/create">
                    <div class="form-floating mb-3">
                        <input class="form-control" id="title" name="title" type="text" required>
                        <label for="title">Title</label>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <textarea class="form-control" id="content" name="content" style="height: 200px" required></textarea>
                        <label for="content">Content</label>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="/cms" class="btn btn-secondary">Cancel</a>
                        <button class="btn btn-primary" type="submit">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../../../views/partials/footer.php'; ?>