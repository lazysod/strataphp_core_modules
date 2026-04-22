<?php require __DIR__ . '/../../../../views/partials/admin_header.php'; ?>
<section class="py-5">
    <div class="container px-5">
        <h1>Add Link</h1>
        <form method="post" action="">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="url" class="form-label">URL</label>
                <input type="url" class="form-control" id="url" name="url" required>
            </div>
            <div class="mb-3">
                <label for="icon" class="form-label">FontAwesome Icon (e.g. fab fa-twitter)</label>
                <input type="text" class="form-control" id="icon" name="icon" placeholder="Auto-detected if left blank">
            </div>
            <div class="mb-3">
                <label for="nsfw" class="form-label">NSFW?</label>
                <input type="checkbox" id="nsfw" name="nsfw" value="1">
            </div>
            <button type="submit" class="btn btn-success">Add Link</button>
            <a href="/admin/links" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</section>
<?php require __DIR__ . '/../../../../views/partials/footer.php'; ?>
