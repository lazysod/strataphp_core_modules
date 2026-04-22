<?php
namespace App\Modules\Admin\Controllers;

use App\DB;

class AdminProfileController
{
    public function profile()
    {
        require_once dirname(__DIR__, 3) . '/bootstrap.php';
        global $config;
        $sessionPrefix = $config['session_prefix'] ?? 'app_';
        $showNav = true;
        $admin = $_SESSION[$sessionPrefix . 'user'] ?? null;
        $success = '';
        /**
         * Display and update admin profile.
         * Handles POST for profile update, including password change.
         * Performs CSRF validation and error handling.
         * @throws \Exception
         */
        $error = '';
        if (!$admin || empty($admin['is_admin'])) {
            header('Location: /admin/login');
            exit;
        }
        $userId = $_SESSION[$sessionPrefix . 'user_id'];
        $db = new DB($config);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $csrfValid = isset($_POST['csrf_token']) && isset($_SESSION[$sessionPrefix . 'token']['token_id']) && hash_equals($_SESSION[$sessionPrefix . 'token']['token_id'], $_POST['csrf_token']);
            if (!$csrfValid) {
                // Regenerate CSRF token and reload form with message
                if (class_exists('App\\TokenManager')) {
                    $tm = new \App\TokenManager($config);
                    $tm->renew();
                }
                $error = 'Session expired or invalid CSRF token. Please try again.';
                $sql = "SELECT * FROM users WHERE id = ?";
                $rows = $db->fetchAll($sql, [$userId]);
                $user = $rows[0] ?? [];
                include __DIR__ . '/../views/profile.php';
                return;
            } else {
                $first_name = trim($_POST['first_name'] ?? '');
                $second_name = trim($_POST['second_name'] ?? '');
                $display_name = trim($_POST['display_name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $pwd = $_POST['pwd'] ?? '';
                $pwd2 = $_POST['pwd2'] ?? '';
                // Validation
                if (!$first_name || !$second_name || !$email) {
                    $error = 'All fields except password are required.';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Invalid email address.';
                } elseif ($pwd && $pwd !== $pwd2) {
                    $error = 'Passwords do not match.';
                } elseif ($pwd && strlen($pwd) < 8) {
                    $error = 'Password must be at least 8 characters.';
                }

                // Avatar upload logic (mirroring user module)
                $avatarPath = '';
                if ($error == '' && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                    $allowedTypes = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/jpg' => 'jpg', 'image/webp' => 'webp'];
                    $fileType = mime_content_type($_FILES['avatar']['tmp_name']);
                    if (isset($allowedTypes[$fileType])) {
                        $ext = $allowedTypes[$fileType];
                        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/storage/uploads/admins/' . $userId . '/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0775, true);
                        }
                        // Remove existing avatar files
                        foreach (['png', 'jpg', 'jpeg', 'webp'] as $oldExt) {
                            $oldFile = $uploadDir . 'avatar.' . $oldExt;
                            if (file_exists($oldFile)) {
                                @unlink($oldFile);
                            }
                        }
                        $fileName = 'avatar.' . $ext;
                        $destPath = $uploadDir . $fileName;
                        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destPath)) {
                            $avatarPath = '/storage/uploads/admins/' . $userId . '/' . $fileName;
                        } else {
                            $error = 'Failed to save avatar.';
                        }
                    } else {
                        $error = 'Invalid avatar file type.';
                    }
                }

                if ($error == '') {
                    // Update DB
                    $params = [$first_name, $second_name, $display_name, $email, $userId];
                    $db->query('UPDATE users SET first_name = ?, second_name = ?, display_name = ?, email = ? WHERE id = ?', $params);
                    if ($pwd) {
                        $hash = password_hash($pwd, PASSWORD_DEFAULT);
                        $db->query('UPDATE users SET password = ? WHERE id = ?', [$hash, $userId]);
                    }
                    if ($avatarPath) {
                        $db->query('UPDATE users SET avatar = ? WHERE id = ?', [$avatarPath, $userId]);
                    }
                    $sql = "SELECT * FROM users WHERE id = ?";
                    $rows = $db->fetchAll($sql, [$userId]);
                    $user = $rows[0] ?? [];
                    $_SESSION[$sessionPrefix . 'user']['first_name'] = $user['first_name'] ?? '';
                    $_SESSION[$sessionPrefix . 'user']['second_name'] = $user['second_name'] ?? '';
                    $_SESSION[$sessionPrefix . 'user']['display_name'] = $user['display_name'] ?? '';
                    $_SESSION[$sessionPrefix . 'user']['email'] = $user['email'] ?? '';
                    if (!empty($avatarPath)) {
                        $_SESSION[$sessionPrefix . 'user']['avatar'] = $avatarPath;
                    } elseif (!empty($user['avatar'])) {
                        $_SESSION[$sessionPrefix . 'user']['avatar'] = $user['avatar'];
                    }
                    $success = 'Profile updated successfully.';
                }
            }
        }
        $sql = "SELECT * FROM users WHERE id = ?";
        $rows = $db->fetchAll($sql, [$userId]);
        $user = $rows[0] ?? [];
        include __DIR__ . '/../views/profile.php';
    }
}
