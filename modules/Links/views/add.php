<?php require dirname(__DIR__, 3) . '/views/partials/header.php'; ?>
<main class="container py-5">
    <h1 class="mb-4">Add Link</h1>
    <form method="post">
        <div class="form-group mb-3">
            <label for="title">Title</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="form-group mb-3">
            <label for="url">URL</label>
            <input type="url" class="form-control" id="url" name="url" required>
        </div>
        <div class="form-group mb-3">
            <label for="icon">Icon</label>
            <input type="text" class="form-control" id="icon" name="icon" placeholder="FontAwesome icon (optional)">
        </div>
        <div class="form-group mb-3">
            <label for="nsfw">NSFW?</label>
            <input type="checkbox" id="nsfw" name="nsfw" value="1">
        </div>
        <button type="submit" class="btn btn-primary">Add Link</button>
    </form>
</main>
<?php require dirname(__DIR__, 3) . '/views/partials/footer.php'; ?>
