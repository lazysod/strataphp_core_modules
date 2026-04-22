<?php
// $clients is provided by controller
?>
<h2>OAuth Clients</h2>
<a href="?action=add" class="btn btn-primary mb-3">Add New Client</a>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Redirect URI</th>
            <th>Client ID</th>
            <th>Client Secret</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($clients as $client) : ?>
            <tr>
                <td><?= htmlspecialchars($client['id']) ?></td>
                <td><?= htmlspecialchars($client['name']) ?></td>
                <td><?= htmlspecialchars($client['redirect_uri']) ?></td>
                <td><code><?= htmlspecialchars($client['client_id']) ?></code></td>
                <td><code><?= htmlspecialchars($client['client_secret']) ?></code></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
