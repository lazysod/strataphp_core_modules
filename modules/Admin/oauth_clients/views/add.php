<?php
// $error, $success provided by controller
?>
<h2>Add OAuth Client</h2>
<?php if ($error) : ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if ($success) : ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<form method="post">
    <div class="mb-3">
        <label for="name" class="form-label">Client Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
    </div>
    <div class="mb-3">
        <label for="redirect_uri" class="form-label">Redirect URI</label>
        <input type="url" class="form-control" id="redirect_uri" name="redirect_uri" required>
    </div>
    <button type="submit" class="btn btn-primary">Register Client</button>
</form>
