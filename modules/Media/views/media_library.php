<?php
// Media Library Template (moved from CMS)
// Security: Only allow admin users
$sessionPrefix = $config['session_prefix'] ?? 'app_';
if (!isset($_SESSION[$sessionPrefix . 'admin']) || $_SESSION[$sessionPrefix . 'admin'] < 1) {
    http_response_code(403);
    echo '<h2>Access denied: Admins only.</h2>';
    exit;
}
// Include header
include_once __DIR__ . '/../../../views/partials/admin_header.php';
// DEBUG: media_library.php loaded

?>
<!-- DEBUG: before container -->
<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <nav style="margin-bottom: 18px; font-size: 15px;">
                <a href="/admin/media/dashboard" style="color: #007cba; text-decoration: none;">Dashboard</a>
                <span style="color: #888;"> &gt; </span>
                <span style="color: #333; font-weight: 500;">Media Library</span>
            </nav>
        </div>
    </div>

    <div class="header d-flex justify-content-between align-items-center bg-white rounded shadow-sm p-4 mb-4">
        <h1 class="mb-0">Media Library</h1>
        <div>
            <a href="/admin/media/dashboard" class="btn btn-secondary">Dashboard</a>
        </div>
    </div>
    <div class="upload-area text-center bg-white rounded p-4 mb-4 border border-dashed" onclick="document.getElementById('media-upload').click()">
        <h3>📷 Upload New Images or PDFs</h3>
        <p>Click here or drag and drop images or PDFs to upload</p>
        <input type="file" id="media-upload" multiple accept="image/*,application/pdf" style="display:none;">
        <div id="upload-progress" class="mt-2" style="display:none;">
            <div class="progress" style="height:18px;">
                <div id="progress-bar" class="progress-bar bg-primary" style="width:0%;"></div>
            </div>
            <div id="progress-label" class="small text-muted mt-1"></div>
        </div>
    </div>
    <!-- DEBUG: after col-md-12 -->
    <?php if (empty($images)) : ?>
        <div class="empty-state text-center bg-white rounded p-5 shadow-sm">
            <h2 class="text-muted mb-2">No images uploaded yet</h2>
            <p class="text-secondary mb-4">Upload your first image using the upload area above.</p>
        </div>
    <?php else : ?>
        <div class="media-grid row">
            <?php foreach ($images as $image) :
                $ext = strtolower(pathinfo($image['filename'], PATHINFO_EXTENSION));
                ?>
                <div class="media-item col-md-4 mb-4" data-filename="<?= htmlspecialchars($image['filename']) ?>">
                    <div class="media-preview bg-light d-flex align-items-center justify-content-center rounded" style="height:200px;">
                        <?php if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) : ?>
                            <img src="<?= htmlspecialchars($image['thumbnail']) ?>" alt="<?= htmlspecialchars($image['filename']) ?>" class="img-fluid rounded">
                        <?php elseif ($ext === 'pdf') : ?>
                            <a href="<?= htmlspecialchars($image['url']) ?>" target="_blank" class="d-flex align-items-center justify-content-center w-100 h-100 text-decoration-none">
                                <span style="font-size:48px;">📄</span>
                            </a>
                        <?php else : ?>
                            <a href="<?= htmlspecialchars($image['url']) ?>" target="_blank" class="d-flex align-items-center justify-content-center w-100 h-100 text-decoration-none">
                                <span style="font-size:36px;">📁</span>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="media-info p-3">
                        <div class="media-filename fw-bold mb-1"><?= htmlspecialchars($image['filename']) ?></div>
                        <div class="media-meta small text-muted mb-2">
                            Size: <?= number_format($image['size'] / 1024, 1) ?> KB<br>
                            Uploaded: <?= htmlspecialchars($image['uploaded']) ?>
                        </div>
                        <div class="media-actions d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary" onclick="copyUrl(this, '<?= htmlspecialchars($image['url']) ?>')">Copy URL</button>
                            <a class="btn btn-sm btn-outline-info" href="<?= htmlspecialchars($image['url']) ?>" download target="_blank">Download</a>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteImage('<?= htmlspecialchars($image['filename']) ?>')">Delete</button>
                            <button class="btn btn-sm btn-outline-success" onclick="insertMediaUrl('<?= htmlspecialchars($image['url']) ?>')">Insert</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (!empty($images) && isset($page) && isset($totalPages) && $totalPages > 1) : ?>
            <div class="text-center my-4">
                <nav aria-label="Media pagination">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1) : ?>
                            <li class="page-item"><a href="?page=<?= $page - 1 ?>" class="page-link">&laquo; Prev</a></li>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                            <li class="page-item<?= ($i == $page ? ' active' : '') ?>"><a href="?page=<?= $i ?>" class="page-link"><?= $i ?></a></li>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages) : ?>
                            <li class="page-item"><a href="?page=<?= $page + 1 ?>" class="page-link">Next &raquo;</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
    // File upload handling
    const uploadInput = document.getElementById('media-upload');
    const uploadArea = document.querySelector('.upload-area');

    uploadInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        files.forEach(file => uploadFile(file));
    });

    // Drag and drop
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        
        const files = Array.from(e.dataTransfer.files).filter(file => file.type.startsWith('image/'));
        files.forEach(file => uploadFile(file));
    });

    function uploadFile(file) {
        const allowedTypes = [
            'image/jpeg','image/png','image/gif','image/webp','image/svg+xml','application/pdf'
        ];
        if (!allowedTypes.includes(file.type)) {
            alert('Only images and PDFs are allowed.');
            return;
        }
        if (file.size > 10 * 1024 * 1024) {
            alert('File size must be less than 10MB: ' + file.name);
            return;
        }
        const formData = new FormData();
        formData.append('image', file);
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/admin/media/upload/image', true);
        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                document.getElementById('upload-progress').style.display = 'block';
                document.getElementById('progress-bar').style.width = percent + '%';
                document.getElementById('progress-label').textContent = 'Uploading ' + file.name + ' (' + percent + '%)';
            }
        };
        xhr.onload = function() {
            document.getElementById('progress-bar').style.width = '0%';
            document.getElementById('progress-label').textContent = '';
            document.getElementById('upload-progress').style.display = 'none';
            if (xhr.status === 200) {
                let data;
                try { data = JSON.parse(xhr.responseText); } catch (e) { data = {}; }
                if (data.location || data.url) {
                    // Add new file to grid dynamically (simple reload for now)
                    window.location.reload();
                } else if (data.error) {
                    alert('Upload failed: ' + data.error);
                } else {
                    alert('Upload failed: Unknown error.');
                }
            } else {
                alert('Upload failed: Server error.');
            }
        };
        xhr.onerror = function() {
            document.getElementById('upload-progress').style.display = 'none';
            alert('Upload failed: Network error.');
        };
        xhr.send(formData);
    }

    function copyUrl(btn, url) {
        const fullUrl = window.location.origin + url;
        navigator.clipboard.writeText(fullUrl).then(function() {
            btn.textContent = 'Copied!';
            setTimeout(() => {
                btn.textContent = 'Copy URL';
            }, 1000);
        }).catch(function() {
            // Fallback for older browsers
            const textarea = document.createElement('textarea');
            textarea.value = fullUrl;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            btn.textContent = 'Copied!';
            setTimeout(() => {
                btn.textContent = 'Copy URL';
            }, 1000);
        });
    }

    function deleteImage(filename) {
        if (confirm('Are you sure you want to delete this file? This action cannot be undone.')) {
            fetch('/admin/media/media/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'filename=' + encodeURIComponent(filename)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the media item from the grid
                    const item = document.querySelector('.media-item[data-filename="' + filename.replace(/"/g, '\\"') + '"]');
                    if (item) item.remove();
                } else {
                    alert('Delete failed: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(() => {
                alert('Delete failed: Network error');
            });
        }
    }

    // Insert media into parent window (for modal/iframe usage)
    function insertMediaUrl(url) {
        // Try TinyMCE file picker callback first
        if (window.opener && window.opener.tinymceFilePickerCallback) {
            window.opener.tinymceFilePickerCallback(url);
            window.close();
            return;
        }
        // Post message to parent if in iframe (for modal usage)
        if (window.parent && window.parent !== window) {
            window.parent.postMessage({ mediaUrl: url }, window.location.origin);
            return;
        }
        // Fallback: try to set input field in parent
        if (window.opener) {
            var field = new URLSearchParams(window.location.search).get('field');
            if (field) {
                var input = window.opener.document.getElementById(field);
                if (input) {
                    input.value = url;
                    window.close();
                    return;
                }
            }
        }
        // If all else fails, just copy to clipboard
        navigator.clipboard.writeText(url);
        alert('Image URL copied to clipboard. Paste it into your form.');
    }
</script>
<?php include_once __DIR__ . '/../../../views/partials/footer.php'; ?>
