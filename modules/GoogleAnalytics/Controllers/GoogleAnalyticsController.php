<?php

namespace App\Modules\GoogleAnalytics\Controllers;

use App\DB;
use App\Modules\GoogleAnalytics\Models\GoogleAnalytics;

class GoogleAnalyticsController
{
    private $db;
    private $config;
    public function __construct()
    {
        $this->config = include dirname(__DIR__, 3) . '/app/config.php';
        $this->db = new DB($this->config['db']);
    }
    
    /**
     * Display a listing of the resource
     */
    public function index()
    {
        try {
            $google_analyticsModel = new GoogleAnalytics($this->db);
            $items = $google_analyticsModel->getAll();
            
            $data = [
                'items' => $items,
                'title' => 'GoogleAnalytics'
            ];
            
            include __DIR__ . '/../views/index.php';
        } catch (\Exception $e) {
            http_response_code(500);
            echo 'An error occurred while loading the google_analytics.';
        }
    }
    
    /**
     * Show the form for creating a new resource
     */
    public function create()
    {
        try {
            $data = [
                'title' => 'Create GoogleAnalytics'
            ];
            
            include __DIR__ . '/../views/create.php';
        } catch (\Exception $e) {
            http_response_code(500);
            echo 'An error occurred while loading the create form.';
        }
    }
    
    /**
     * Store a newly created resource
     */
    public function store()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: /google_analytics');
                exit;
            }
            
            // Basic validation
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            
            if (empty($title) || empty($content)) {
                $_SESSION['error'] = 'Title and content are required';
                header('Location: /google_analytics/create');
                exit;
            }
            
            $google_analyticsModel = new GoogleAnalytics($this->db);
            
            $data = [
                'title' => $title,
                'content' => $content,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $result = $google_analyticsModel->create($data);
            
            if ($result) {
                $_SESSION['success'] = 'GoogleAnalytics created successfully';
            } else {
                $_SESSION['error'] = 'Failed to create google_analytics';
            }
            
            header('Location: /google_analytics');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'An error occurred while creating the google_analytics';
            header('Location: /google_analytics/create');
            exit;
        }
    }
    
    /**
     * Display the specified resource
     */
    public function show($id)
    {
        try {
            // Validate ID
            if (!is_numeric($id) || $id <= 0) {
                header('HTTP/1.0 404 Not Found');
                echo '404 - Invalid google_analytics ID';
                exit;
            }
            
            $google_analyticsModel = new GoogleAnalytics($this->db);
            $item = $google_analyticsModel->getById($id);
            
            if (!$item) {
                header('HTTP/1.0 404 Not Found');
                echo '404 - GoogleAnalytics not found';
                exit;
            }
            
            $data = [
                'item' => $item,
                'title' => $item['title']
            ];
            
            include __DIR__ . '/../views/show.php';
        } catch (\Exception $e) {
            http_response_code(500);
            echo 'An error occurred while loading the google_analytics.';
        }
    }
    
    /**
     * Show the form for editing the specified resource
     */
    public function edit($id)
    {
        try {
            // Validate ID
            if (!is_numeric($id) || $id <= 0) {
                header('Location: /google_analytics');
                exit;
            }
            
            $google_analyticsModel = new GoogleAnalytics($this->db);
            $item = $google_analyticsModel->getById($id);
            
            if (!$item) {
                $_SESSION['error'] = 'GoogleAnalytics not found';
                header('Location: /google_analytics');
                exit;
            }
            
            $data = [
                'item' => $item,
                'title' => 'Edit GoogleAnalytics'
            ];
            
            include __DIR__ . '/../views/edit.php';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'An error occurred while loading the edit form';
            header('Location: /google_analytics');
            exit;
        }
    }
    
    /**
     * Update the specified resource
     */
    public function update($id)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: /google_analytics');
                exit;
            }
            
            // Validate ID
            if (!is_numeric($id) || $id <= 0) {
                $_SESSION['error'] = 'Invalid google_analytics ID';
                header('Location: /google_analytics');
                exit;
            }
            
            // Basic validation
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            
            if (empty($title) || empty($content)) {
                $_SESSION['error'] = 'Title and content are required';
                header('Location: /google_analytics/{$id}/edit');
                exit;
            }
            
            $google_analyticsModel = new GoogleAnalytics($this->db);
            
            $data = [
                'title' => $title,
                'content' => $content,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $result = $google_analyticsModel->update($id, $data);
            
            if ($result) {
                $_SESSION['success'] = 'GoogleAnalytics updated successfully';
            } else {
                $_SESSION['error'] = 'Failed to update google_analytics';
            }
            
            header('Location: /google_analytics');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'An error occurred while updating the google_analytics';
            header('Location: /google_analytics');
            exit;
        }
    }
    
    /**
     * Remove the specified resource
     */
    public function delete($id)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: /google_analytics');
                exit;
            }
            
            // Validate ID
            if (!is_numeric($id) || $id <= 0) {
                $_SESSION['error'] = 'Invalid google_analytics ID';
                header('Location: /google_analytics');
                exit;
            }
            
            $google_analyticsModel = new GoogleAnalytics($this->db);
            $result = $google_analyticsModel->delete($id);
            
            if ($result) {
                $_SESSION['success'] = 'GoogleAnalytics deleted successfully';
            } else {
                $_SESSION['error'] = 'Failed to delete google_analytics';
            }
            
            header('Location: /google_analytics');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'An error occurred while deleting the google_analytics';
            header('Location: /google_analytics');
            exit;
        }
    }
    
    /**
     * API endpoint for listing resources
     */
    public function apiIndex()
    {
        try {
            header('Content-Type: application/json');
            
            $google_analyticsModel = new GoogleAnalytics($this->db);
            $items = $google_analyticsModel->getAll();
            
            echo json_encode([
                'success' => true,
                'data' => $items
            ]);
            exit;
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred while fetching google_analytics'
            ]);
            exit;
        }
    }
}
