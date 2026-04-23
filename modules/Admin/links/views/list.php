<?php require __DIR__ . '/../../../../views/partials/admin_header.php'; ?>

<section class="py-5">
    <div class="container px-5">
        <h1>Manage Links</h1>
        <a href="/admin/links/add" class="btn btn-primary mb-3">Add Link</a>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Icon</th>
                    <th>Title</th>
                    <th>URL</th>
                    <th>Order</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>

                <?php foreach ($links as $i => $link) : ?>
                    <tr>
                        <td><i class="<?php echo htmlspecialchars($link['icon'] ?? 'fas fa-link'); ?>"></i></td>
                        <td><?php echo htmlspecialchars($link['title']); ?></td>
                        <td><a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank"><?php echo htmlspecialchars($link['url']); ?></a></td>
                        <td>
                            <form method="post" action="/admin/links/order" style="display:inline-block">
                                <input type="hidden" name="id" value="<?php echo $link['id']; ?>">
                                <button type="submit" name="direction" value="up" class="btn btn-sm btn-light" <?php if ($i === 0) {
                                    echo 'disabled';
                                                                                                               } ?>>&#8593;</button>
                                <button type="submit" name="direction" value="down" class="btn btn-sm btn-light" <?php if ($i === count($links) - 1) {
                                    echo 'disabled';
                                                                                                                 } ?>>&#8595;</button>
                            </form>
                        </td>
                        <td>
                            <a href="/admin/links/edit/<?php echo $link['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="/admin/links/delete/<?php echo $link['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this link?');">Delete</a>
                        </td>
                    </tr>

                <?php endforeach; ?>

            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/../../../../views/partials/footer.php'; ?>