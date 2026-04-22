<?php require dirname(__DIR__, 3) . '/views/partials/header.php'; ?>
<main class="container py-5">
    <h1 class="mb-4">Edit Link</h1>
    <form method="post">
        <div class="form-group mb-3">
            <label for="title">Title</label>
            <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($link['title'] ?? '') ?>" required>
        </div>
        <div class="form-group mb-3">
            <label for="url">URL</label>
            <input type="url" class="form-control" id="url" name="url" value="<?= htmlspecialchars($link['url'] ?? '') ?>" required>
        </div>
        <div class="form-group mb-3">
            <label for="icon">Icon</label>
            <input type="text" class="form-control" id="icon" name="icon" value="<?= htmlspecialchars($link['icon'] ?? '') ?>">
        </div>
        <div class="form-group mb-3">
            <label for="nsfw">NSFW?</label>
            <input type="checkbox" id="nsfw" name="nsfw" value="1" <?= !empty($link['nsfw']) ? 'checked' : '' ?>>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</main>
<?php require dirname(__DIR__, 3) . '/views/partials/footer.php'; ?>
