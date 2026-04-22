<?php
// Ensure $config and user is authenticated
global $config;

// Admin session check
$sessionPrefix = $config['session_prefix'] ?? ($config['prefix'] ?? 'framework');
if (!isset($_SESSION[$sessionPrefix . 'admin']) || $_SESSION[$sessionPrefix . 'admin'] < 1) {
    header('Location: /admin/admin_login.php');
    exit;
}

// Prepare CSRF token and formatted file size for view
$csrfToken = $_SESSION['csrf_token'] ?? '';
$maxFileSizeFormatted = (function ($bytes) {
    if ($bytes==0) {
        return'0 Bytes';
    }$k=1024;
    $sizes=['Bytes','KB','MB','GB'];
    $i=floor(log($bytes)/log($k));
    return round($bytes/pow($k, $i), 2).' '.$sizes[$i];
})($maxFileSize);

require $_SERVER['DOCUMENT_ROOT'] . '/views/partials/admin_header.php'; ?>

<style>
    .module-card {
        transition: transform 0.3s ease;
    }
    .module-card:hover {
        transform: translateY(-5px);
    }
    .upload-area {
        border: 2px dashed #dee2e6;
        border-radius: 10px;
        padding: 3rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .upload-area:hover, .upload-area.dragover {
        border-color: #0d6efd;
        background-color: #f8f9fa;
    }
    .progress-container {
        display: none;
        margin-top: 1rem;
    }
    .install-log {
        background: #1a1a1a;
        color: #00ff00;
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
        max-height: 300px;
        overflow-y: auto;
        padding: 1rem;
        border-radius: 5px;
        white-space: pre-wrap;
    }
</style>

<section class="py-5">
    <div class="container px-5">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-download me-2"></i><?php echo htmlspecialchars($title); ?></h1>
                    <a href="/admin/modules" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Modules
                    </a>
                </div>
            </div>
        </div>

        <!-- Installation Methods -->
        <div class="row">
            <!-- ZIP File Upload -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-file-archive me-2"></i>Upload ZIP File</h5>
                    </div>
                    <div class="card-body">
                        <form id="zipUploadForm" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                            
                            <div class="upload-area" id="uploadArea">
                                <div class="upload-content">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                    <h5>Drop ZIP file here or click to browse</h5>
                                    <p class="text-muted">Maximum file size: <?php echo htmlspecialchars($maxFileSizeFormatted); ?></p>
                                    <input type="file" id="moduleZip" name="module_zip" accept=".zip" class="d-none">
                                </div>
                            </div>
                            
                            <div class="progress-container">
                                <div class="progress mb-3">
                                    <div class="progress-bar" id="uploadProgress" role="progressbar" style="width: 0%"></div>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-success" id="uploadButton" disabled>
                                        <i class="fas fa-upload me-2"></i>Install Module
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- URL Installation -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-link me-2"></i>Install from URL</h5>
                    </div>
                    <div class="card-body">
                        <form id="urlInstallForm">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                            
                            <div class="mb-3">
                                <label for="sourceUrl" class="form-label">Source URL</label>
                                <input type="url" class="form-control" id="sourceUrl" name="source_url" 
                                       placeholder="https://github.com/user/module.git" required>
                                <div class="form-text">
                                    Supported: GitHub repositories (.git), ZIP file URLs
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-download me-2"></i>Install Module
                                </button>
                            </div>
                        </form>
                        
                        <hr>
                        
                        <h6><i class="fas fa-lightbulb me-2"></i>Examples:</h6>
                        <div class="small">
                            <div class="mb-2">
                                <strong>GitHub:</strong><br>
                                <code>https://github.com/user/strataphp-blog.git</code>
                            </div>
                            <div>
                                <strong>ZIP URL:</strong><br>
                                <code>https://example.com/module.zip</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Module Generator -->
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-magic me-2"></i>Generate New Module</h5>
                    </div>
                    <div class="card-body">
                        <form id="generateForm">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                            
                            <div class="mb-3">
                                <label for="moduleName" class="form-label">Module Name</label>
                                <input type="text" class="form-control" id="moduleName" name="module_name" 
                                       placeholder="blog" pattern="[a-zA-Z][a-zA-Z0-9_-]*" required>
                                <div class="form-text">
                                    Letters, numbers, underscores, and hyphens only
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-warning text-dark">
                                    <i class="fas fa-plus me-2"></i>Generate Module
                                </button>
                            </div>
                        </form>
                        
                        <hr>
                        
                        <h6><i class="fas fa-info-circle me-2"></i>What's Generated:</h6>
                        <ul class="small mb-0">
                            <li>Complete MVC structure</li>
                            <li>RESTful routes</li>
                            <li>Database model</li>
                            <li>Bootstrap views</li>
                            <li>API endpoints</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Installation Log -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-terminal me-2"></i>Installation Log</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="install-log" id="installLog">
                            Ready for module installation...
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Installed Modules -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-cubes me-2"></i>Installed Modules</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($installedModules)) : ?>
                            <div class="text-center py-4">
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <h5>No modules installed</h5>
                                <p class="text-muted">Install your first module using the options above.</p>
                            </div>
                        <?php else : ?>
                            <div class="row">
                                <?php foreach ($installedModules as $module) : ?>
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card module-card h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="card-title mb-0"><?php echo htmlspecialchars($module['name'] ?? 'Unknown'); ?></h6>
                                                    <span class="badge bg-<?php echo $module['enabled'] ? 'success' : 'secondary'; ?>">
                                                        <?php echo $module['enabled'] ? 'Enabled' : 'Disabled'; ?>
                                                    </span>
                                                </div>
                                                <p class="card-text small text-muted mb-2">
                                                    <?php echo htmlspecialchars($module['description'] ?? 'No description'); ?>
                                                </p>
                                                <div class="small">
                                                    <div><strong>Version:</strong> <?php echo htmlspecialchars($module['version'] ?? 'Unknown'); ?></div>
                                                    <div><strong>Author:</strong> <?php echo htmlspecialchars($module['author'] ?? 'Unknown'); ?></div>
                                                    <?php if (!empty($module['category'])) : ?>
                                                        <div><strong>Category:</strong> <?php echo htmlspecialchars($module['category']); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Modals -->
        <div class="modal fade" id="resultModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="resultModalTitle">Result</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="resultModalBody">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="reloadPageBtn">Reload Page</button>
                    </div>
                </div>
            </div>
        </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Global variables
            let currentOperation = null;
            const log = document.getElementById('installLog');
            const resultModal = new bootstrap.Modal(document.getElementById('resultModal'));

        // Utility functions
        function logMessage(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            const icon = type === 'error' ? '❌' : type === 'success' ? '✅' : 'ℹ️';
            log.innerHTML += `[${timestamp}] ${icon} ${message}\n`;
            log.scrollTop = log.scrollHeight;
        }

        function showResult(title, message, isSuccess = true) {
            document.getElementById('resultModalTitle').textContent = title;
            document.getElementById('resultModalBody').innerHTML = message;
            document.getElementById('reloadPageBtn').style.display = isSuccess ? 'inline-block' : 'none';
            resultModal.show();
        }

        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // ZIP Upload handling
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('moduleZip');
        const uploadButton = document.getElementById('uploadButton');
        const progressContainer = document.querySelector('.progress-container');
        const progressBar = document.getElementById('uploadProgress');

        uploadArea.addEventListener('click', () => fileInput.click());
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFileSelection();
            }
        });

        fileInput.addEventListener('change', handleFileSelection);

        function handleFileSelection() {
            const file = fileInput.files[0];
            if (file) {
                if (file.type !== 'application/zip' && !file.name.endsWith('.zip')) {
                    alert('Please select a ZIP file.');
                    return;
                }
                
                if (file.size > <?php echo $maxFileSize; ?>) {
                    alert('File is too large. Maximum size: <?php echo $this->formatBytes($maxFileSize); ?>');
                    return;
                }

                document.querySelector('.upload-content').innerHTML = `
                    <i class="fas fa-file-archive fa-3x text-primary mb-3"></i>
                    <h6>${file.name}</h6>
                    <p class="text-muted">${formatBytes(file.size)}</p>
                `;
                progressContainer.style.display = 'block';
                uploadButton.disabled = false;
            }
        }

        // Form submissions
        document.getElementById('zipUploadForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            if (currentOperation) return;

            currentOperation = 'upload';
            uploadButton.disabled = true;
            logMessage('Starting ZIP file upload and installation...');

            // Create FormData manually to ensure file is included
            const formData = new FormData();
            const csrfToken = document.querySelector('input[name="csrf_token"]').value;
            
            // Add the file if selected
            if (fileInput.files.length > 0) {
                formData.append('module_zip', fileInput.files[0]);
            } else {
                alert('Please select a file first');
                currentOperation = null;
                uploadButton.disabled = false;
                return;
            }
            formData.append('csrf_token', csrfToken);

            try {
                const response = await fetch('/admin/module-installer/upload', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    logMessage('Module installed successfully!', 'success');
                    showResult('Success', `Module "${result.module?.name || 'Unknown'}" has been installed successfully!`, true);
                } else {
                    logMessage(`Installation failed: ${result.message}`, 'error');
                    showResult('Installation Failed', result.message, false);
                }
            } catch (error) {
                logMessage(`Network error: ${error.message}`, 'error');
                showResult('Error', 'Network error occurred during installation.', false);
            } finally {
                currentOperation = null;
                uploadButton.disabled = false;
                progressBar.style.width = '0%';
            }
        });

        document.getElementById('urlInstallForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            if (currentOperation) return;

            currentOperation = 'url';
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Installing...';

            const sourceUrl = document.getElementById('sourceUrl').value;
            logMessage(`Starting installation from URL: ${sourceUrl}`);

            const formData = new FormData(e.target);

            try {
                const response = await fetch('/admin/module-installer/url', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    logMessage('Module installed successfully!', 'success');
                    if (result.output) {
                        logMessage(result.output);
                    }
                    showResult('Success', 'Module has been installed successfully!', true);
                } else {
                    logMessage(`Installation failed: ${result.message}`, 'error');
                    showResult('Installation Failed', result.message, false);
                }
            } catch (error) {
                logMessage(`Network error: ${error.message}`, 'error');
                showResult('Error', 'Network error occurred during installation.', false);
            } finally {
                currentOperation = null;
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });

        document.getElementById('generateForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            if (currentOperation) return;

            currentOperation = 'generate';
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating...';

            const moduleName = document.getElementById('moduleName').value;
            logMessage(`Generating new module: ${moduleName}`);

            const formData = new FormData(e.target);

            try {
                const response = await fetch('/admin/module-installer/generate', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    logMessage('Module generated successfully!', 'success');
                    if (result.output) {
                        logMessage(result.output);
                    }
                    showResult('Success', `Module "${moduleName}" has been generated successfully!`, true);
                } else {
                    logMessage(`Generation failed: ${result.message}`, 'error');
                    showResult('Generation Failed', result.message, false);
                }
            } catch (error) {
                logMessage(`Network error: ${error.message}`, 'error');
                showResult('Error', 'Network error occurred during generation.', false);
            } finally {
                currentOperation = null;
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });

        // Reload page button
        document.getElementById('reloadPageBtn').addEventListener('click', () => {
            window.location.reload();
        });

        // Initialize
        logMessage('Module installer ready. Choose an installation method above.');
        }); // End DOMContentLoaded
    </script>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/views/partials/footer.php'; ?>