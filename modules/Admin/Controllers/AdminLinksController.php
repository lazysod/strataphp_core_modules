<?php
namespace App\Modules\Admin\Controllers;

use App\DB;
use App\Modules\Admin\Models\Links;
use Exception;

/**
 * Admin Links Controller
 *
 * Handles administrative operations for link management including CRUD operations,
 * ordering, and admin authentication checks.
 *
 * @package App\Modules\Admin\Controllers
 * @author  StrataPHP Framework
 * @version 1.0.0
 */
class AdminLinksController
{
    /**
     * Require admin authentication
     *
     * Checks if user is authenticated as admin and redirects if not.
     *
     * @return void
     */
    private function requireAdmin()
    {
        try {
            global $config;
            $sessionPrefix = $config['session_prefix'];
            if (!isset($_SESSION[$sessionPrefix . 'admin']) || $_SESSION[$sessionPrefix . 'admin'] < 1) {
                header('Location: /admin/admin_login.php');
                exit;
            }
        } catch (Exception $e) {
            header('Location: /admin/admin_login.php');
            exit;
        }
    }

    /**
     * Handle link ordering operations
     *
     * Processes POST requests to move links up or down in the display order.
     *
     * @return void
     */
    public function order()
    {
        try {
            $this->requireAdmin();
            global $config;
            $db = new DB($config);
            $linksModel = new Links($db, $config);
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = intval($_POST['id'] ?? 0);
                $direction = $_POST['direction'] ?? '';
                $links = $linksModel->getAll();
                $index = array_search($id, array_column($links, 'id'));
                if ($index !== false) {
                    if ($direction === 'up' && $index > 0) {
                        $prev = $links[$index-1];
                        $linksModel->swapOrder($id, $prev['id']);
                    } elseif ($direction === 'down' && $index < count($links)-1) {
                        $next = $links[$index+1];
                        $linksModel->swapOrder($id, $next['id']);
                    }
                }
            }
        } catch (Exception $e) {
        }
        header('Location: /admin/links');
        exit;
    }
    /**
     * Display links list page
     *
     * Shows all links in the admin interface with management options.
     *
     * @return void
     */
    public function index()
    {

        try {
            $this->requireAdmin();
            global $config;
            $db = new DB($config);
            $linksModel = new Links($db, $config);
            $links = $linksModel->getAll();
            include __DIR__ . '/../links/views/list.php';
        } catch (Exception $e) {
            $links = [];
            include __DIR__ . '/../links/views/list.php';
        }
    }
    
    /**
     * Handle add link page and form submission
     *
     * Displays add form on GET, processes form submission on POST.
     *
     * @return void
     */
    public function add()
    {
        try {
            $this->requireAdmin();
            global $config;
            $db = new DB($config);
            $linksModel = new Links($db, $config);
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $title = trim($_POST['title'] ?? '');
                $url = trim($_POST['url'] ?? '');
                $icon = trim($_POST['icon'] ?? '');
                $nsfw = !empty($_POST['nsfw']) ? 1 : 0;
                $linksModel->addLink($title, $url, $icon, $nsfw);
                header('Location: /admin/links');
                exit;
            }
            include __DIR__ . '/../links/views/add.php';
        } catch (Exception $e) {
            include __DIR__ . '/../links/views/add.php';
        }
    }
    
    /**
     * Handle edit link page and form submission
     *
     * Displays edit form on GET, processes form submission on POST.
     *
     * @param int $id Link ID to edit
     * @return void
     */
    public function edit($id)
    {
        try {
            $this->requireAdmin();
            global $config;
            $db = new DB($config);
            $linksModel = new Links($db, $config);
            $link = $linksModel->getById($id);
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $title = trim($_POST['title'] ?? '');
                $url = trim($_POST['url'] ?? '');
                $icon = trim($_POST['icon'] ?? '');
                $nsfw = !empty($_POST['nsfw']) ? 1 : 0;
                $linksModel->updateLink($id, $title, $url, $icon, $nsfw);
                header('Location: /admin/links');
                exit;
            }
            include __DIR__ . '/../links/views/edit.php';
        } catch (Exception $e) {
            $link = null;
            include __DIR__ . '/../links/views/edit.php';
        }
    }
    
    /**
     * Handle link deletion
     *
     * Deletes the specified link and redirects to links list.
     *
     * @param int $id Link ID to delete
     * @return void
     */
    public function delete($id)
    {
        try {
            $this->requireAdmin();
            global $config;
            $db = new DB($config);
            $linksModel = new Links($db, $config);
            $linksModel->deleteLink($id);
        } catch (Exception $e) {
        }
        header('Location: /admin/links');
        exit;
    }
}
