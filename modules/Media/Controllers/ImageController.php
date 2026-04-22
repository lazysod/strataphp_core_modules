<?php
namespace App\Modules\Media\Controllers;

/**
 * Image Upload Handler for Media Module
 */
class ImageController
{
        /**
         * Render the media library UI
         */
    public function mediaLibrary()
    {
        // Gather images from the uploads directory
        $images = [];
        $uploadDir = $this->uploadDir;
        if (is_dir($uploadDir)) {
            $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($uploadDir, \FilesystemIterator::SKIP_DOTS));
            foreach ($rii as $fileInfo) {
                // Skip files in thumbs/ subdirectory
                if (strpos($fileInfo->getPath(), DIRECTORY_SEPARATOR . 'thumbs') !== false) {
                    continue;
                }
                if ($fileInfo->isFile()) {
                    $filename = $fileInfo->getFilename();
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg','jpeg','png','gif','webp','heic','pdf'])) {
                        $thumbName = (in_array($ext, ['jpg','jpeg','png','gif','webp'])) ? preg_replace('/(\.[a-zA-Z0-9]+)$/', '_thumb$1', $filename) : $filename;
                        $images[] = [
                            'filename' => $filename,
                            'url' => '/storage/uploads/media/' . $filename,
                            'thumbnail' => (in_array($ext, ['jpg','jpeg','png','gif','webp']) ? '/storage/uploads/media/thumbs/' . $thumbName : '/storage/uploads/media/' . $filename),
                            'size' => $fileInfo->getSize(),
                            'uploaded' => date('Y-m-d H:i', $fileInfo->getMTime()),
                        ];
                    }
                }
            }
        }
        // Render the view
        $viewFile = __DIR__ . '/../views/media_library.php';
        if (file_exists($viewFile)) {
            // Make $images available to the view
            $config = include __DIR__ . '/../../../app/config.php';
            include $viewFile;
        } else {
            echo '<h2>Media Library view not found.</h2>';
        }
    }
    /**
     * Delete a media file (image, PDF, etc.) via AJAX
     */
    public function deleteMedia()
    {
        header('Content-Type: application/json');
        $this->requireAuth();
        try {
            $filename = $_POST['filename'] ?? '';
            if (!$filename || strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, "\\") !== false) {
                throw new \Exception('Invalid filename');
            }
            // Find the file in the upload directory or subdirs
            $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->uploadDir, \FilesystemIterator::SKIP_DOTS));
            $filePath = null;
            foreach ($rii as $fileInfo) {
                if ($fileInfo->isFile() && $fileInfo->getFilename() === $filename) {
                    $filePath = $fileInfo->getPathname();
                    break;
                }
            }
            if (!$filePath || !file_exists($filePath)) {
                throw new \Exception('File not found');
            }
            // Delete the file
            if (!unlink($filePath)) {
                throw new \Exception('Failed to delete file');
            }
            // If image, also delete thumbnail if exists
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','gif','webp','heic'])) {
                $dotPos = strrpos($filename, '.');
                if ($dotPos !== false) {
                    $thumbFilename = substr($filename, 0, $dotPos) . '_thumb' . substr($filename, $dotPos);
                } else {
                    $thumbFilename = $filename . '_thumb';
                }
                $thumbPath = dirname($filePath) . '/thumbs/' . $thumbFilename;
                if (!file_exists($thumbPath)) {
                    // Try thumbs dir in main upload dir
                    $thumbPath = $this->uploadDir . 'thumbs/' . $thumbFilename;
                }
                if (file_exists($thumbPath)) {
                    @unlink($thumbPath);
                }
            }
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    private $uploadDir;
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 'image/heic'];
    private $maxFileSize = 5 * 1024 * 1024; // 5MB
    private $config;

    public function __construct()
    {
        $this->uploadDir = __DIR__ . '/../../../storage/uploads/media/';
        $this->config = include __DIR__ . '/../../../app/config.php';
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    private function requireAuth()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $sessionPrefix = $this->config['session_prefix'] ?? 'app_';
        if (!isset($_SESSION[$sessionPrefix . 'admin']) || $_SESSION[$sessionPrefix . 'admin'] < 1) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => 'Authentication required'
            ]);
            exit;
        }
    }

    public function upload()
    {
        ob_start();
        // Suppress PHP and GD warnings to prevent HTML output in JSON response
        $originalErrorReporting = error_reporting();
        $originalDisplayErrors = ini_get('display_errors');
        $originalLogErrors = ini_get('log_errors');
        error_reporting(E_ERROR | E_PARSE);
        ini_set('display_errors', '0');
        ini_set('log_errors', '0');
        header('Content-Type: application/json');
        $this->requireAuth();
        try {
            if (!isset($_FILES['image'])) {
                throw new \Exception('No image file provided');
            }
            $file = $_FILES['image'];
            $mimeType = mime_content_type($file['tmp_name']);
            $this->validateFile($file);
            $filename = $this->generateFilename($file['name']);
            $filepath = $this->uploadDir . $filename;
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new \Exception('Failed to upload file');
            }
            $thumbnailPath = null;
            $thumbFilename = $filename;
            if (strpos($mimeType, 'image/') === 0 && $mimeType !== 'image/heic') {
                $thumbnailPath = $this->createThumbnail($filepath, $filename);
                $dotPos = strrpos($filename, '.');
                if ($dotPos !== false) {
                    $thumbFilename = substr($filename, 0, $dotPos) . '_thumb' . substr($filename, $dotPos);
                } else {
                    $thumbFilename = $filename . '_thumb';
                }
            }
            ob_clean();
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $baseUrl = $protocol . $host;
            echo json_encode([
                'success' => true,
                'filename' => $filename,
                'url' => $baseUrl . '/storage/uploads/media/' . $filename,
                'thumbnail' => $thumbnailPath ? $baseUrl . '/storage/uploads/media/thumbs/' . $thumbFilename : $baseUrl . '/storage/uploads/media/' . $filename,
                'size' => filesize($filepath)
            ]);
        } catch (\Exception $e) {
            ob_clean();
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        // Restore error reporting settings
        error_reporting($originalErrorReporting);
        ini_set('display_errors', $originalDisplayErrors);
        ini_set('log_errors', $originalLogErrors);
        ob_end_flush();
    }

    private function validateFile($file)
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('Upload failed with error code: ' . $file['error']);
        }
        if ($file['size'] > $this->maxFileSize) {
            throw new \Exception('File size exceeds maximum allowed size of 5MB');
        }
        $mimeType = mime_content_type($file['tmp_name']);
        if (!in_array($mimeType, $this->allowedTypes)) {
            throw new \Exception('Invalid file type. Only JPEG, PNG, GIF, WebP, HEIC images, and PDFs are allowed');
        }
        // Only validate image dimensions for images (not PDFs)
        if (strpos($mimeType, 'image/') === 0 && $mimeType !== 'image/heic') {
            $imageInfo = getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                throw new \Exception('Invalid image file');
            }
        }
    }

    private function generateFilename($originalName)
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        $basename = preg_replace('/[^a-zA-Z0-9_-]/', '', $basename);
        return date('Y-m-d_H-i-s') . '_' . $basename . '.' . strtolower($extension);
    }

    private function createThumbnail($filepath, $filename)
    {
        $thumbDir = $this->uploadDir . 'thumbs/';
        if (!is_dir($thumbDir)) {
            mkdir($thumbDir, 0755, true);
        }
        // Insert _thumb before the file extension
        $dotPos = strrpos($filename, '.');
        if ($dotPos !== false) {
            $thumbFilename = substr($filename, 0, $dotPos) . '_thumb' . substr($filename, $dotPos);
        } else {
            $thumbFilename = $filename . '_thumb';
        }
        $thumbPath = $thumbDir . $thumbFilename;
        try {
            $originalErrorReporting = error_reporting();
            $originalDisplayErrors = ini_get('display_errors');
            error_reporting(E_ERROR | E_PARSE);
            ini_set('display_errors', '0');
            $imageInfo = getimagesize($filepath);
            $originalWidth = $imageInfo[0];
            $originalHeight = $imageInfo[1];
            $mimeType = $imageInfo['mime'];
            $maxWidth = 300;
            $maxHeight = 200;
            $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
            $thumbWidth = intval($originalWidth * $ratio);
            $thumbHeight = intval($originalHeight * $ratio);
            switch ($mimeType) {
                case 'image/jpeg':
                    $source = @imagecreatefromjpeg($filepath);
                    break;
                case 'image/png':
                    $source = @imagecreatefrompng($filepath);
                    break;
                case 'image/gif':
                    $source = @imagecreatefromgif($filepath);
                    break;
                case 'image/webp':
                    $source = @imagecreatefromwebp($filepath);
                    break;
                default:
                    $source = false;
                    break;
            }
            if (!$source) {
                error_reporting($originalErrorReporting);
                ini_set('display_errors', $originalDisplayErrors);
                return false;
            }
            $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
            if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
                imagealphablending($thumb, false);
                imagesavealpha($thumb, true);
                $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
                imagefill($thumb, 0, 0, $transparent);
            }
            imagecopyresampled($thumb, $source, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $originalWidth, $originalHeight);
            switch ($mimeType) {
                case 'image/jpeg':
                    imagejpeg($thumb, $thumbPath, 85);
                    break;
                case 'image/png':
                    imagepng($thumb, $thumbPath, 6);
                    break;
                case 'image/gif':
                    imagegif($thumb, $thumbPath);
                    break;
                case 'image/webp':
                    imagewebp($thumb, $thumbPath, 85);
                    break;
            }
            imagedestroy($source);
            imagedestroy($thumb);
            error_reporting($originalErrorReporting);
            ini_set('display_errors', $originalDisplayErrors);
            return $thumbPath;
        } catch (\Exception $e) {
            if (isset($originalErrorReporting)) {
                error_reporting($originalErrorReporting);
            }
            if (isset($originalDisplayErrors)) {
                ini_set('display_errors', $originalDisplayErrors);
            }
            return false;
        }
    }
}
