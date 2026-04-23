<?php
require_once dirname(__DIR__, 3) . '/bootstrap.php';
require dirname(__DIR__, 3) . '/views/partials/admin_header.php';
?>
<section class="py-5">
	<div class="container px-5">
		<div class="bg-light rounded-3 py-5 px-4 px-md-5 mb-5">
			<div class="text-center mb-5">
				<h1 class="fw-bolder">Admin Session Management</h1>
				<p class="text-center">View and manage all user sessions (excluding admins). Revoke sessions for security or troubleshooting.</p>
			</div>
			<div class="row">
				<div class="col">
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb mb-4">
							<li class="breadcrumb-item"><a href="/admin/users">User List</a></li>
							<li class="breadcrumb-item active" aria-current="page">User Sessions</li>
						</ol>
					</nav>
				</div>
			</div>
			<div class="row gx-5 justify-content-center">
				<div class="col-lg-12 col-xl-12">
					<h2>Active User Sessions</h2>
					<table class="table table-striped table-bordered">
						<thead>
							<tr>
								<th>User</th>
								<th>Email</th>
								<th>Device</th>
								<th>IP Address</th>
								<th>Created</th>
								<th>Last Active</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($sessions as $session) : ?>
								<tr>
									<td><?= htmlspecialchars($session['display_name'] ?? (($session['first_name'] ?? '') . ' ' . ($session['second_name'] ?? ''))) ?></td>
									<td><?= htmlspecialchars($session['email'] ?? '-') ?></td>
									<td><?= htmlspecialchars($session['device_info'] ?? $session['device_type'] ?? 'Unknown') ?></td>
									<td><?= htmlspecialchars($session['ip_address'] ?? '-') ?></td>
									<td><?= htmlspecialchars($session['created_at'] ?? '-') ?></td>
									<td><?= htmlspecialchars($session['last_seen'] ?? '-') ?></td>
									<td>
										<form method="post" action="/admin/user/sessions/revoke">
											<input type="hidden" name="session_id" value="<?= $session['id'] ?>">
											<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Revoke this session?')">Revoke</button>
										</form>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</section>
<?php require dirname(__DIR__, 3) . '/views/partials/footer.php'; ?>