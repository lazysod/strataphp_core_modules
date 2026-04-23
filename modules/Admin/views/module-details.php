<?php
// Ensure $config is available and complete
if (!isset($config) || !is_array($config) || empty($config['site_name'])) {
    // Try to get from global scope
    global $config;
    if (!isset($config) || !is_array($config) || empty($config['site_name'])) {
        // Fallback: load from app/config.php
        $config = include dirname(__DIR__, 3) . '/app/config.php';
    }
}
$sessionPrefix = $config['session_prefix'] ?? ($config['prefix'] ?? 'framework');
if (!isset($_SESSION[$sessionPrefix . 'admin']) || $_SESSION[$sessionPrefix . 'admin'] < 1) {
    header('Location: /admin/admin_login.php');
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/views/partials/admin_header.php';

?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?= htmlspecialchars($moduleData['name']) ?> Module Details</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/admin/modules">Modules</a></li>
        <li class="breadcrumb-item active"><?= htmlspecialchars($moduleData['name']) ?></li>
    </ol>

    <div class="row">
        <?php if (($moduleData['slug'] ?? '') === 'google-analytics') : ?>
        <div class="alert alert-info mb-4">
            <strong>Google Analytics Admin Interface:</strong>
            <a href="/admin/google-analytics-settings" class="btn btn-outline-primary btn-sm ms-2">
                <i class="fab fa-google me-1"></i>Open Google Analytics Settings
            </a>
        </div>
        <?php endif; ?>
        <!-- Module Information -->
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>
                    Module Information
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> <?= htmlspecialchars($moduleData['name']) ?></p>
                            <p><strong>Version:</strong> <?= htmlspecialchars($moduleData['version'] ?? 'Unknown') ?></p>
                            <p><strong>Author:</strong> <?= htmlspecialchars($moduleData['author'] ?? 'Unknown') ?></p>
                            <p><strong>License:</strong> <?= htmlspecialchars($moduleData['license'] ?? 'Unknown') ?></p>
                            <p><strong>Home Page:</strong> <?= '<a href="' . htmlspecialchars($moduleData['homepage'] ?? 'Unknown').'" target="_blank" title="Go to Home Page">' . htmlspecialchars($moduleData['homepage'] ?? 'Unknown').'</a>' ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Category:</strong> <?= htmlspecialchars($moduleData['category'] ?? 'Uncategorized') ?></p>
                            <p><strong>Status:</strong> 
                                <?php if ($moduleData['enabled']) : ?>
                                    <span class="badge bg-success">Enabled</span>
                                <?php else : ?>
                                    <span class="badge bg-secondary">Disabled</span>
                                <?php endif; ?>
                            </p>
                            <p><strong>Module Type:</strong> <?= htmlspecialchars($moduleData['category'] ?? 'Unknown') ?></p>
                            <p><strong>Framework Version:</strong> <?= htmlspecialchars($moduleData['framework_version'] ?? 'Unknown') ?></p>
                            <p><strong>Repository:</strong> <?= '<a href="' . htmlspecialchars($moduleData['repository'] ?? 'Unknown').'" target="_blank" title="Go to Repository">' . htmlspecialchars($moduleData['repository'] ?? 'Unknown').'</a>' ?></p>
                        </div>
                    </div>
                    <?php if (!empty($moduleData['description'])) : ?>
                        <hr>
                        <p><strong>Description:</strong></p>
                        <p><?= nl2br(htmlspecialchars($moduleData['description'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Validation Results -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-check-circle me-1"></i>Validation Results</span>
                    <button class="btn btn-sm btn-outline-primary" onclick="validateModule('<?= $moduleData['name'] ?>')">
                        <i class="fas fa-sync-alt"></i> Re-validate
                    </button>
                </div>
                <div class="card-body">
                    <div class="validation-results">
                        <?php if ($validationResults['valid']) : ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> Module validation passed!
                            </div>
                        <?php else : ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Module has validation issues
                            </div>
                        <?php endif; ?>

                        <!-- Display Errors if any -->
                        <?php if (!empty($validationResults['errors'])) : ?>
                            <div class="mb-3">
                                <h6 class="text-danger"><i class="fas fa-times-circle"></i> Errors</h6>
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        <?php foreach ($validationResults['errors'] as $error) : ?>
                                            <li><?= htmlspecialchars($error) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Display Warnings if any -->
                        <?php if (!empty($validationResults['warnings'])) : ?>
                            <div class="mb-3">
                                <h6 class="text-warning"><i class="fas fa-exclamation-triangle"></i> Warnings</h6>
                                <div class="alert alert-warning">
                                    <ul class="mb-0">
                                        <?php foreach ($validationResults['warnings'] as $warning) : ?>
                                            <li><?= htmlspecialchars($warning) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Display Suggestions if any -->
                        <?php if (!empty($validationResults['suggestions'])) : ?>
                            <div class="mb-3">
                                <h6 class="text-info"><i class="fas fa-lightbulb"></i> Suggestions</h6>
                                <div class="alert alert-info">
                                    <ul class="mb-0">
                                        <?php foreach ($validationResults['suggestions'] as $suggestion) : ?>
                                            <li><?= htmlspecialchars($suggestion) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Structure Validation -->
                        <h6>Structure Validation</h6>
                        <div class="validation-section mb-3">
                            <?php foreach ($validationResults['structure'] as $check => $result) : ?>
                                <div class="d-flex align-items-center mb-1">
                                    <?php if ($result) : ?>
                                        <i class="fas fa-check text-success me-2"></i>
                                    <?php else : ?>
                                        <i class="fas fa-times text-danger me-2"></i>
                                    <?php endif; ?>
                                    <span><?= ucfirst(str_replace('_', ' ', $check)) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Security Validation -->
                        <h6>Security Validation</h6>
                        <div class="validation-section mb-3">
                            <?php foreach ($validationResults['security'] as $check => $result) : ?>
                                <div class="d-flex align-items-center mb-1">
                                    <?php if ($result) : ?>
                                        <i class="fas fa-check text-success me-2"></i>
                                    <?php else : ?>
                                        <i class="fas fa-times text-danger me-2"></i>
                                    <?php endif; ?>
                                    <span><?= ucfirst(str_replace('_', ' ', $check)) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Quality Validation -->
                        <h6>Code Quality</h6>
                        <div class="validation-section mb-3">
                            <?php foreach ($validationResults['quality'] as $check => $result) : ?>
                                <div class="d-flex align-items-center mb-1">
                                    <?php if ($result) : ?>
                                        <i class="fas fa-check text-success me-2"></i>
                                    <?php else : ?>
                                        <i class="fas fa-times text-danger me-2"></i>
                                    <?php endif; ?>
                                    <span><?= ucfirst(str_replace('_', ' ', $check)) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- README Content -->
            <?php if (!empty($readmeContent)) : ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-book me-1"></i>
                        Documentation
                    </div>
                    <div class="card-body">
                        <div class="readme-content">
                            <?= $readmeContent ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Module Statistics -->
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Statistics
                </div>
                <div class="card-body">
                    <?php if (!empty($moduleStats)) : ?>
                        <p><strong>Controllers:</strong> <?= $moduleStats['controllers'] ?? 0 ?></p>
                        <p><strong>Models:</strong> <?= $moduleStats['models'] ?? 0 ?></p>
                        <p><strong>Views:</strong> <?= $moduleStats['views'] ?? 0 ?></p>
                        <p><strong>Routes:</strong> <?= $moduleStats['routes'] ?? 0 ?></p>
                        <p><strong>Total Files:</strong> <?= $moduleStats['total_files'] ?? 0 ?></p>
                        <p><strong>Total Size:</strong> <?= isset($moduleStats['total_size']) ? formatBytes($moduleStats['total_size']) : '0 B' ?></p>
                        <p><strong>Last Modified:</strong> <?= isset($moduleStats['last_modified']) ? date('Y-m-d H:i:s', $moduleStats['last_modified']) : 'Unknown' ?></p>
                    <?php else : ?>
                        <p class="text-muted">Statistics not available</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Dependencies -->
            <?php if (!empty($moduleData['dependencies'])) : ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-puzzle-piece me-1"></i>
                        Dependencies
                    </div>
                    <div class="card-body">
                        <?php foreach ($moduleData['dependencies'] as $dep) : ?>
                            <span class="badge bg-secondary me-1 mb-1"><?= htmlspecialchars($dep) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Keywords/Tags -->
            <?php if (!empty($moduleData['keywords'])) : ?>
                    <!-- Navigation Config Example -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-sitemap me-1"></i>
                            Navigation Config Example
                        </div>
                        <div class="card-body">
                            <p>To add this module to your admin navigation, add the following to <code>adminNavConfig.php</code>:</p>
                            <pre><code class="language-php">[
            'label' => '<?= htmlspecialchars($moduleData['name']) ?>',
            'icon' => 'fa-cube',
            'url' => '/admin/<?= htmlspecialchars(strtolower($moduleData['name'])) ?>',
            'show' => true
        ]</code></pre>
                        </div>
                    </div>
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-tags me-1"></i>
                        Keywords
                    </div>
                    <div class="card-body">
                        <?php foreach ($moduleData['keywords'] as $keyword) : ?>
                            <span class="badge bg-info me-1 mb-1"><?= htmlspecialchars($keyword) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function validateModule(moduleName) {
    const resultsContainer = document.querySelector('.validation-results');
    resultsContainer.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Validating module...</div>';
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    const headers = {
        'Content-Type': 'application/json'
    };
    if (csrfToken) {
        headers['X-CSRF-Token'] = csrfToken.getAttribute('content');
    }
    
    fetch(`/admin/modules/validate/${moduleName}`, {
        method: 'POST',
        headers: headers
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Reload to show updated results
        } else {
            resultsContainer.innerHTML = '<div class="alert alert-danger">Validation failed: ' + data.message + '</div>';
        }
    })
    .catch(error => {
        resultsContainer.innerHTML = '<div class="alert alert-danger">Error validating module: ' + error.message + '</div>';
    });
}

function formatBytes(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/views/partials/footer.php'; ?>

<?php
function formatBytes($size, $precision = 2)
{
    if (!is_numeric($size) || $size <= 0) {
        return '0 B';
    }
    $base = log($size, 1024);
    $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');
    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
}
?>