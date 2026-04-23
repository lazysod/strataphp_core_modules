<?php require dirname(__DIR__, 3) . '/views/partials/header.php'; ?>
<main class="container py-5">
    <h1 class="text-center mb-4">About Me</h1>
    <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
            <p class="lead text-center"><?php echo htmlspecialchars($bio) ?></p>
        </div>
    </div>
</main>
<?php require dirname(__DIR__, 3) . '/views/partials/footer.php'; ?>
