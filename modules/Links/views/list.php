<?php require dirname(__DIR__, 3) . '/views/partials/header.php'; ?>
<main class="container py-5">
    <h1 class="mb-4">Links List</h1>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Title</th>
                <th>URL</th>
                <th>Icon</th>
                <th>NSFW</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($links as $link) : ?>
                <tr>
                    <td><?= htmlspecialchars($link['title'] ?? '') ?></td>
                    <td><a href="<?= htmlspecialchars($link['url'] ?? '') ?>" target="_blank">Visit</a></td>
                    <td><i class="fa <?= htmlspecialchars($link['icon'] ?? '') ?>"></i></td>
                    <td><?= !empty($link['nsfw']) ? '<span class="badge bg-danger">NSFW</span>' : '' ?></td>
                    <td>
                        <a href="/admin/links/edit/<?= $link['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="/admin/links/delete/<?= $link['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this link?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="/admin/links/add" class="btn btn-success">Add New Link</a>
</main>
<?php require dirname(__DIR__, 3) . '/views/partials/footer.php'; ?>
