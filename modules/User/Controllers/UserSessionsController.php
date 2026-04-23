<?php
namespace App\Modules\User\Controllers;

use App\DB;
use App\App;
/**
 * User Sessions Controller
 *
 * Manages user session viewing and revocation functionality
 * Allows users to see active sessions and revoke access
 */
class UserSessionsController 
{
    /**
     * Admin: Revoke any user session
     * Only accessible to admins
     */
    public function adminRevoke()
    {
        require_once dirname(__DIR__, 3) . '/bootstrap.php';
        global $config;
        $db = new DB($config);
        $sessionPrefix = $config['session_prefix'] ?? 'app_';
        $isAdmin = isset($_SESSION[$sessionPrefix . 'admin']) && $_SESSION[$sessionPrefix . 'admin'] == 1;
        if (!$isAdmin) {
            header('Location: /user/login');
            exit;
        }
        $session_id = $_POST['session_id'] ?? null;
        if (!$session_id) {
            header('Location: /admin/user/sessions');
            exit;
        }
        // Revoke session (admin can revoke any session)
        $db->query("UPDATE user_sessions SET revoked = 1 WHERE id = ?", [$session_id]);
        header('Location: /admin/user/sessions');
        exit;
    }

    /**
     * Display user sessions
     *
     * Shows active sessions for the current user with device information
     * Includes session management and revocation capabilities
     *
     * @return void
     */
    public function index()
    {
        try {
            require_once dirname(__DIR__, 3) . '/bootstrap.php';
            global $config;
            $db = new DB($config);
            $sessionPrefix = $config['session_prefix'] ?? 'app_';
            $user_id = $_SESSION[$sessionPrefix . 'user_id'] ?? null;
            if (!$user_id) {
                header('Location: /user/login');
                exit;
            }
            // Get latest active session per device, not expired
            $latestSessions = $db->fetchAll("SELECT * FROM user_sessions WHERE user_id = ? AND revoked = 0 AND (expires_at IS NULL OR expires_at > NOW()) AND id IN (SELECT MAX(id) FROM user_sessions WHERE user_id = ? AND revoked = 0 AND (expires_at IS NULL OR expires_at > NOW()) GROUP BY device_id)", [$user_id, $user_id]);

            // Always include the current session, even if not the latest for its device
            $currentSessionId = $_SESSION[$sessionPrefix . 'session_id'] ?? null;
            $currentSession = null;
            if ($currentSessionId) {
                $currentSession = $db->fetch("SELECT * FROM user_sessions WHERE id = ? AND user_id = ? AND revoked = 0 AND (expires_at IS NULL OR expires_at > NOW())", [$currentSessionId, $user_id]);
            }
            $sessions = $latestSessions;
            if ($currentSession && !in_array($currentSession['id'], array_column($latestSessions, 'id'))) {
                $sessions[] = $currentSession;
            }
            include __DIR__ . '/../views/sessions.php';
        } catch (\Exception $e) {
            $sessions = [];
            include __DIR__ . '/../views/sessions.php';
        }
    }
        


    /**
     * Revoke a user session
     *
     * Allows users to revoke specific sessions for security purposes
     * Validates session ownership before revocation
     *
     * @return void
     */
    public function revoke()
    {
        require_once dirname(__DIR__, 3) . '/bootstrap.php';
        global $config;
        $db = new DB($config);
        $sessionPrefix = $config['session_prefix'] ?? 'app_';
        $user_id = $_SESSION[$sessionPrefix . 'user_id'] ?? null;
        if (!$user_id) {
            App::log('Session revoke failed: missing user_id', 'ERROR', ['user_id' => $user_id]);
            header('Location: /user/login');
            exit;
        }
        // Get session_id from POST data
        $session_id = $_POST['session_id'] ?? null;
        if (!$session_id) {
            App::log('Session revoke failed: missing session_id', 'ERROR', ['session_id' => $session_id]);
            header('Location: /user/sessions');
            exit;
        }
        // Revoke session and log affected rows
        $stmt = $db->getPdo()->prepare("UPDATE user_sessions SET revoked = 1 WHERE id = ? AND user_id = ?");
        $success = $stmt->execute([$session_id, $user_id]);
        $affected = $stmt->rowCount();
        if (!$success || $affected === 0) {
            App::log('Session revoke DB update failed', 'ERROR', ['session_id' => $session_id, 'user_id' => $user_id, 'success' => $success, 'affected' => $affected]);
        } else {
            App::log('Session revoked successfully', 'INFO', ['session_id' => $session_id, 'user_id' => $user_id, 'affected' => $affected]);
        }
        header('Location: /user/sessions');
        exit;
    }

    // Allow user to update device name for current session
    public function updateDevice()
    {
        require_once dirname(__DIR__, 3) . '/bootstrap.php';
        global $config;
        $db = new DB($config);
        
        $sessionPrefix = $config['session_prefix'] ?? 'app_';
        $user_id = $_SESSION[$sessionPrefix . 'user_id'] ?? null;
        $session_id = $_POST['session_id'] ?? null;
        $device_info = trim($_POST['device_info'] ?? '');
        if (!$user_id || !$session_id || $device_info === '') {
            header('Location: /user/sessions');
            exit;
        }
        // Only allow update for current session
        if ($session_id == ($_SESSION[$sessionPrefix . 'session_id'] ?? null)) {
            $db->query("UPDATE user_sessions SET device_info = ? WHERE id = ? AND user_id = ?", [$device_info, $session_id, $user_id]);
        }
        header('Location: /user/sessions');
        exit;
    }

    /**
     * Admin: Display all active non-admin user sessions
     * Only accessible to admins
     */
    public function adminSessions()
    {
        require_once dirname(__DIR__, 3) . '/bootstrap.php';
        global $config;
        $db = new DB($config);
        $sessionPrefix = $config['session_prefix'] ?? 'app_';
        $isAdmin = isset($_SESSION[$sessionPrefix . 'admin']) && $_SESSION[$sessionPrefix . 'admin'] == 1;
        if (!$isAdmin) {
            header('Location: /user/login');
            exit;
        }
        $this_user = $_SESSION[$sessionPrefix . 'user_id'] ?? null;
        $sql = "SELECT us.*, u.display_name, u.first_name, u.second_name, u.email FROM user_sessions us JOIN users u ON us.user_id = u.id WHERE us.revoked = 0 AND (us.expires_at IS NULL OR us.expires_at > NOW()) AND us.user_id != ? ORDER BY us.last_seen DESC";
        $sessions = $db->fetchAll($sql, [$this_user]);
        include __DIR__ . '/../views/admin_sessions.php';
    }
}
