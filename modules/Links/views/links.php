<?php require dirname(__DIR__, 3) . '/views/partials/header.php'; ?>
<main class="container py-5">
    <h1 class="text-center mb-4">My Links</h1>
    <?php if (!empty($show_adult_warning)) : ?>
        <div class="alert alert-warning text-center">This page may contain links to adult sites. Please confirm you are 18+.</div>
    <?php endif; ?>
    <ul class="list-group list-group-flush mx-auto" style="max-width: 400px;">
        <?php foreach ($links as $link) : ?>
            <li class="list-group-item text-center">
                <?php if (!empty($link['nsfw'])) : ?>
                    <button class="btn btn-outline-danger w-100 mb-2 nsfw-link" data-url="<?php echo htmlspecialchars($link['url'] ?? '') ?>">
                        <?php if (!empty($link['icon'])) : ?>
                            <i class="<?php echo htmlspecialchars($link['icon']) ?> me-2"></i>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($link['title'] ?? ($link['label'] ?? '')) ?>
                        <span class="badge bg-danger ms-2">NSFW</span>
                    </button>
                <?php else : ?>
                    <a href="<?php echo htmlspecialchars($link['url'] ?? '') ?>" target="_blank" rel="noopener" class="btn btn-outline-primary w-100 mb-2">
                        <?php if (!empty($link['icon'])) : ?>
                            <i class="<?php echo htmlspecialchars($link['icon']) ?> me-2"></i>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($link['title'] ?? ($link['label'] ?? '')) ?>
                    </a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.nsfw-link').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    if (confirm('This link is marked NSFW. Are you sure you want to proceed?')) {
                        window.open(btn.getAttribute('data-url'), '_blank', 'noopener');
                    }
                });
            });
        });
        </script>
    </ul>
</main>
<?php require dirname(__DIR__, 3) . '/views/partials/footer.php'; ?>
