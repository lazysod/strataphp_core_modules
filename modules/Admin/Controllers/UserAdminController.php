<?php
namespace App\Modules\Admin\Controllers;
require_once dirname(__DIR__, 4) . '/public_html/bootstrap.php';
use App\DB;
use App\User;
use App\App;

/**
 * Admin User Management Controller
 *
 * Handles CRUD operations for user management in the admin interface
 */
class UserAdminController
{
    /**
     * List/search users with pagination
     *
     * @return void
     */
    public function index()
    {
        try {
            // Pagination logic
            global $config;
            // Use autoloader for User.php, and instantiate DB directly
            $db = new DB($config);
            $userModel = new User($db, $config);
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $perPage = 20;
            $offset = ($page - 1) * $perPage;
            $totalUsers = $userModel->countAll();
            $users = $userModel->getPaginated($perPage, $offset);
            $totalPages = ceil($totalUsers / $perPage);
            include __DIR__ . '/../users/views/list.php';
        } catch (\Exception $e) {
            http_response_code(500);
            echo '<h1>Error loading users</h1>';
        }
    }

    /**
     * Add new user form and processing
     *
     * @return void
     */
    public function add()
    {
        try {
            global $config;
            // ...existing code...
            $db = new DB($config);
            $userModel = new User($db, $config);
            $error = '';
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // display_name removed
                $first_name = trim($_POST['first_name'] ?? '');
                $second_name = trim($_POST['second_name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $role = $_POST['role'] ?? 'user';
                $password = $_POST['password'] ?? '';
                if ($first_name === '' || $second_name === '') {
                    $error = 'First name and second name are required.';
                } else {
                    $is_admin = ($role === 'admin') ? 1 : 0;
                    $userModel->createUser(
                        [
                        'first_name' => $first_name,
                        'second_name' => $second_name,
                        'email' => $email,
                        'is_admin' => $is_admin,
                        'pwd' => $password
                        ]
                    );
                    header('Location: /admin/users');
                    exit;
                }
            }
            include __DIR__ . '/../users/views/add.php';
        } catch (\Exception $e) {
            $error = 'Error creating user. Please try again.';
            include __DIR__ . '/../users/views/add.php';
        }
    }

    /**
     * Edit user form and processing
     *
     * @param string $id User ID
     * @return void
     */
    public function edit($id)
    {
        try {
            global $config;
            // ...existing code...
            $db = new DB($config);
            $userModel = new User($db, $config);
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $email = trim($_POST['email'] ?? '');
                $role = $_POST['role'] ?? 'user';
                $status = $_POST['status'] ?? 'active';
                // Update user in DB
                $user = $userModel->getById($id);
                if (!$user) {
                    http_response_code(404);
                    echo '<h1>User not found</h1>';
                    exit;
                }
                $is_admin = ($role === 'admin') ? 1 : 0;
                $active = ($status === 'active') ? 1 : 0;
                $dead_switch = ($active === 0) ? 1 : 0;
                $updateData = [
                    'first_name' => trim($_POST['first_name'] ?? ''),
                    'second_name' => trim($_POST['second_name'] ?? ''),
                    'email' => $email,
                    'is_admin' => $is_admin,
                    'active' => $active,
                    'display_name' => trim($_POST['display_name'] ?? ''),
                    'dead_switch' => $dead_switch
                ];

                $newPassword = trim($_POST['password'] ?? '');
                if ($newPassword !== '') {
                    // Hash password before saving
                    $updateData['pwd'] = password_hash($newPassword, PASSWORD_DEFAULT);
                }
                $userModel->updateUser($id, $updateData);
                header('Location: /admin/users');
                exit;
            } else {
                $user = $userModel->getById($id);
                if (!$user) {
                    http_response_code(404);
                    echo '<h1>User not found</h1>';
                    exit;
                }
                include __DIR__ . '/../users/views/edit.php';
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo '<h1>Error editing user</h1>';
        }
    }

    /**
     * Suspend a user account
     *
     * @param string $id User ID
     * @return void
     */
    public function suspend($id)
    {
        try {
            global $config;
            // ...existing code...
            $db = new DB($config);
            $userModel = new User($db, $config);
            $user = $userModel->getById($id);
            if (!$user) {
                http_response_code(404);
                echo '<h1>User not found</h1>';
                exit;
            }
            $userModel->suspend($id); // Call the suspend method on the model
            header('Location: /admin/users');
            exit;
        } catch (\Exception $e) {
            http_response_code(500);
            echo '<h1>Error suspending user</h1>';
        }
    }

    /**
     * Unsuspend a user account
     *
     * @param string $id User ID
     * @return void
     */
    public function unsuspend($id)
    {
        try {
            global $config;
            // ...existing code...
            $db = new DB($config);
            $userModel = new User($db, $config);
            $user = $userModel->getById($id);
            if (!$user) {
                http_response_code(404);
                echo '<h1>User not found</h1>';
                exit;
            }
            $userModel->unsuspend($id); // Call the unsuspend method on the model
            header('Location: /admin/users');
            exit;
        } catch (\Exception $e) {
            http_response_code(500);
            echo '<h1>Error unsuspending user</h1>';
        }
    }

    /**
     * Delete a user account
     *
     * @param string $id User ID
     * @return void
     */
    public function delete($id)
    {
        try {
            global $config;
            $sessionPrefix = $config['session_prefix'] ?? 'app_';
            // Prevent user from deleting themselves
            $currentUserId = $_SESSION[$sessionPrefix . 'admin']['id'] ?? null;
            if ($currentUserId && $currentUserId == $id) {
                // Optionally set a flash message or error
                $_SESSION['error'] = 'You cannot delete your own account.';
                header('Location: /admin/users');
                exit;
            }
            // ...existing code...
            $db = new DB($config);
            $userModel = new User($db, $config);
            $userModel->deleteUser($id);
            header('Location: /admin/users');
            exit;
        } catch (\Exception $e) {
            http_response_code(500);
            echo '<h1>Error deleting user</h1>';
        }
    }
}
