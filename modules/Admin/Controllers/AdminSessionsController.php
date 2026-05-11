<?php
namespace App\Modules\Admin\Controllers;
use App\Logger;
/**
 * Admin Sessions Controller
 *
 * Manages admin user sessions including viewing active sessions,
 * revoking sessions, and updating device information
 */
class AdminSessionsController
{
    /**
     * Display active admin sessions
     *
     * @return void
     */
    public function index()
    {
        try {
            $bootstrapPath = realpath(__DIR__ . '/../../../bootstrap.php');
            if ($bootstrapPath && file_exists($bootstrapPath)) {
                include_once $bootstrapPath;
            } else {
                error_log('AdminSessionsController: bootstrap.php not found at ' . ($bootstrapPath ?: 'resolved path'));
                http_response_code(500);
                echo '<h1>Critical error: bootstrap.php not found.</h1>';
                exit;
            }
            global $config;
            $sessionPrefix = $config['session_prefix'] ?? ($config['prefix'] ?? 'app_');
            $db = new \App\DB($config);
            $logger = new Logger($config);
            $logger->info('ADMIN SESSIONS INDEX', [
                'session_name' => session_name(),
                'session_id' => session_id(),
                'SESSION' => $_SESSION,
                'COOKIES' => $_COOKIE
            ]);
            $admin_id = $_SESSION[$sessionPrefix . 'admin'] ?? null;
            if (!$admin_id) {
                $logger->warning('ADMIN SESSIONS INDEX: Not logged in, redirecting.', [
                    'SESSION' => $_SESSION
                ]);
                header('Location: /admin/login');
                exit;
            }
            // Get all active sessions for this admin user
            $sql = "SELECT us.*, u.display_name, u.email FROM user_sessions us JOIN users u ON us.user_id = u.id WHERE u.id = ? AND us.revoked = 0 AND (us.expires_at IS NULL OR us.expires_at > NOW()) ORDER BY us.last_seen DESC";
            $sessions = $db->fetchAll($sql, [$admin_id]);
            $debug_sql = $sql;
            $debug_sessions = $sessions;
            // extract(['debug_sql' => $debug_sql, 'debug_sessions' => $debug_sessions, 'sessions' => $sessions]);
            include __DIR__ . '/../views/sessions.php';
        } catch (\Exception $e) {
            http_response_code(500);
            echo '<h1>Error loading sessions</h1>';
        }
    }
    /**
     * Revoke an admin session
     *
     * @return void
     */
    public function revoke()
    {
        try {
            $bootstrapPath = realpath(__DIR__ . '/../../../bootstrap.php');
            if ($bootstrapPath && file_exists($bootstrapPath)) {
                include_once $bootstrapPath;
            } else {
                error_log('AdminSessionsController: bootstrap.php not found at ' . ($bootstrapPath ?: 'resolved path'));
                http_response_code(500);
                echo '<h1>Critical error: bootstrap.php not found.</h1>';
                exit;
            }
            global $config;
            $sessionPrefix = $config['session_prefix'] ?? ($config['prefix'] ?? 'app_');
            $db = new \App\DB($config);
            $logger = new Logger($config);
            $logger->info('ADMIN SESSIONS REVOKE', [
                'session_name' => session_name(),
                'session_id' => session_id(),
                'SESSION' => $_SESSION,
                'COOKIES' => $_COOKIE
            ]);
            $admin_id = $_SESSION[$sessionPrefix . 'admin'] ?? null;
            $session_id = $_POST['session_id'] ?? null;
            if (!$admin_id || !$session_id) {
                $logger->warning('ADMIN SESSIONS REVOKE: Not logged in or missing session_id, redirecting.', [
                    'SESSION' => $_SESSION
                ]);
                header('Location: /admin/sessions');
                exit;
            }
            // Revoke session in user_sessions
            $db->query("UPDATE user_sessions SET revoked = 1 WHERE id = ? AND user_id = ?", [$session_id, $admin_id]);
            header('Location: /admin/sessions');
            exit;
        } catch (\Exception $e) {
            header('Location: /admin/sessions');
            exit;
        }
    }

    /**
     * Allow admin to update device name for current session
     *
     * @return void
     */
    public function updateDevice()
    {
        try {
            $bootstrapPath = realpath(__DIR__ . '/../../../bootstrap.php');
            if ($bootstrapPath && file_exists($bootstrapPath)) {
                include_once $bootstrapPath;
            } else {
                error_log('AdminSessionsController: bootstrap.php not found at ' . ($bootstrapPath ?: 'resolved path'));
                http_response_code(500);
                echo '<h1>Critical error: bootstrap.php not found.</h1>';
                exit;
            }
            global $config;
            $sessionPrefix = $config['session_prefix'] ?? ($config['prefix'] ?? 'app_');
            $db = new \App\DB($config);
            $logger = new Logger($config);
            $logger->info('ADMIN SESSIONS UPDATEDEVICE', [
                'session_name' => session_name(),
                'session_id' => session_id(),
                'SESSION' => $_SESSION,
                'COOKIES' => $_COOKIE
            ]);
            $admin_id = $_SESSION[$sessionPrefix . 'admin'] ?? null;
            $session_id = $_POST['session_id'] ?? null;
            $device_info = trim($_POST['device_info'] ?? '');
            if (!$admin_id || !$session_id || $device_info === '') {
                $logger->warning('ADMIN SESSIONS UPDATEDEVICE: Not logged in, missing session_id or device_info, redirecting.', [
                    'SESSION' => $_SESSION
                ]);
                header('Location: /admin/sessions');
                exit;
            }
            // Only allow update for current session
            if ($session_id == ($_SESSION[$sessionPrefix . 'session_id'] ?? null)) {
                $db->query("UPDATE user_sessions SET device_info = ? WHERE id = ? AND user_id = ?", [$device_info, $session_id, $admin_id]);
            }
            header('Location: /admin/sessions');
            exit;
        } catch (\Exception $e) {
            header('Location: /admin/sessions');
            exit;
        }
    }
}
