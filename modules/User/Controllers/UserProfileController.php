<?php
namespace App\Modules\User\Controllers;

use App\DB;
use App\App;
use App\User;

// User profile controller for updating user details
/**
 * User Profile Controller
 *
 * Manages user profile viewing and editing functionality
 * Handles profile updates, password changes, and user data management
 */
class UserProfileController
{
    /**
     * Handle user profile requests
     *
     * Displays user profile and processes profile update requests
     * Includes validation, security checks, and error handling
     *
     * @return void
     */
    public function index()
    {
        try {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/bootstrap.php';
            global $config;
            if (empty($config['modules']['User'])) {
                header('Location: /');
                exit;
            }
            $sessionPrefix = $config['session_prefix'] ?? 'app_';
            if (empty($_SESSION[$sessionPrefix . 'user_id'])) {
                header('Location: /user/login');
                exit;
            }
            $error = '';
            $success = '';
            $db = new DB($config);
            $userModel = new User($db, $config);
            $userId = $_SESSION[$sessionPrefix . 'user_id'];
        // Fetch current user info
            $sql = "SELECT * FROM users WHERE id = ?";
            $row = $db->fetchAll($sql, [$userId]);
            $user = $row[0] ?? [];
        // App::dump($user, 'Current User Data');
        // die();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!empty($_POST['pwd']) && $_POST['pwd'] != $_POST['pwd2']) {
                    $error = 'Passwords do not match.';
                } elseif (empty($_POST['email'])) {
                    $error = 'Please fill in all required fields.';
                } else {
                    // Proceed with update
                    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                        $error = 'Invalid email address.';
                    }
                }
                // Avatar upload
                $avatarPath = '';
                if ($error == '' && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                    $allowedTypes = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/jpg' => 'jpg', 'image/webp' => 'webp'];
                    $fileType = mime_content_type($_FILES['avatar']['tmp_name']);
                    if (isset($allowedTypes[$fileType])) {
                        $ext = $allowedTypes[$fileType];
                        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/public_uploads/users/' . $userId . '/';
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
                            $avatarPath = '/public_uploads/users/' . $userId . '/' . $fileName;
                        } else {
                            $error = 'Failed to save avatar.';
                        }
                    } else {
                        $error = 'Invalid avatar file type.';
                    }
                }
                if ($error == '') {
                    $updateInfo = [
                    'id' => $userId,
                    'first_name' => trim($_POST['first_name'] ?? ($user['first_name'] ?? '')),
                    'second_name' => trim($_POST['second_name'] ?? ($user['second_name'] ?? '')),
                    'display_name' => trim($_POST['display_name'] ?? ($user['display_name'] ?? '')),
                    'email' => trim($_POST['email'] ?? ($user['email'] ?? '')),
                    'pwd' => $_POST['pwd'] ?? '',
                    'pwd2' => $_POST['pwd2'] ?? '',
                    'avatar' => $avatarPath,
                    ];

                    $result = $userModel->update($updateInfo);
                    if ($result['status'] === 'success') {
                        $success = $result['message'];
                        // Always refresh user info after update
                        $rows = $db->fetchAll($sql, [$userId]);
                        $user = $rows[0] ?? [];
                        // Force session avatar update
                        if (!empty($avatarPath)) {
                            $_SESSION[$sessionPrefix . 'avatar'] = $avatarPath;
                        } elseif (!empty($user['avatar'])) {
                            $_SESSION[$sessionPrefix . 'avatar'] = $user['avatar'];
                        }
                    } else {
                        $error = $result['message'];
                    }
                }
            }
            $viewPath = \App\Modules\User\Helpers\CmsHelper::getViewPath('user/profile.php', __DIR__ . '/../views/profile.php');
            include $viewPath;
        } catch (\Exception $e) {
            $error = 'An unexpected error occurred. Please try again.';
            $success = '';
            $viewPath = \App\Modules\User\Helpers\CmsHelper::getViewPath('user/profile.php', __DIR__ . '/../views/profile.php');
            include $viewPath;
        }
    }
}
