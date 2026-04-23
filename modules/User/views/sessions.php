<?php
require_once dirname(__DIR__, 3) . '/bootstrap.php';
require dirname(__DIR__, 3) . '/views/partials/header.php';
?>
<section class="py-5">
    <div class="container px-5">
        <div class="bg-light rounded-3 py-5 px-4 px-md-5 mb-5">
            <div class="text-center mb-5">
                <h1 class="fw-bolder">Session Management</h1>
                <p class="text-center ">
                    This is your session management page where you can view and manage your active sessions on all the devices you have logged into</p> 
                    
                <p class="text-center">You can revoke sessions that are no longer needed or update the device information for your current session.
                </p>
            </div>
            <div class="row gx-5 justify-content-center">
                <div class="col-lg-12 col-xl-12">
                    <h2>Your Active Sessions</h2>
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Device</th>
                                <th>IP Address</th>
                                <th>Created</th>
                                <th>Last Active</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($sessions as $session) : ?>
                            <?php $isCurrent = ($session['id'] ?? null) == ($_SESSION[$sessionPrefix . 'session_id'] ?? null); ?>
                            <tr<?= $isCurrent ? ' style="background:linear-gradient(90deg,#1e90ff 0,#00c3ff 100%);color:#fff;font-weight:bold;box-shadow:0 0 10px #1e90ff;"' : '' ?>>
                                <td>
                                    <?php
                                    $deviceLabel = !empty($session['device_info']) ? $session['device_info'] : ($session['device_type'] ?? 'Unknown');
                                    ?>
                                    <?php if ($isCurrent) : ?>
                                        <span title="This is your current session" style="margin-right:6px;vertical-align:middle;">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="#fff200" style="display:inline-block;vertical-align:middle;"><circle cx="12" cy="12" r="10" fill="#fff200"/><path d="M10.5 16l-3.5-3.5 1.41-1.41L10.5 13.17l5.09-5.09 1.41 1.41z" fill="#222"/></svg>
                                        </span>
                                        <strong>This session</strong><br>
                                        <form method="post" action="/user/sessions/update-device" style="display:inline-block;margin-top:4px;">
                                            <input type="hidden" name="session_id" value="<?= $session['id'] ?>">
                                            <input type="text" name="device_info" value="<?= htmlspecialchars($deviceLabel) ?>" size="18">
                                            <button type="submit">Rename</button>
                                        </form>
                                    <?php else : ?>
                                        <?= htmlspecialchars($deviceLabel) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($session['ip_address'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($session['created_at'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($session['last_active'] ?? '-') ?></td>
                                <td>
                                    <?php if (!$isCurrent) : ?>
                                        <form method="post" action="/user/sessions/revoke">
                                            <input type="hidden" name="session_id" value="<?= $session['id'] ?>">
                                            <button type="submit" class="btn btn-primary btn-sm">Revoke</button>
                                        </form>
                                    <?php else : ?>
                                        Current Session
                                    <?php endif; ?>
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

<?php require $_SERVER['DOCUMENT_ROOT'] . '/views/partials/footer.php'; ?>