<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin session check and initialization
$baseDir = dirname(__FILE__, 4); // Points to public_html/
require_once $baseDir . '/app/config.php';
$localConfig = include $baseDir . '/app/config.php';
$sessionPrefix = $config['session_prefix'] ?? ($config['prefix'] ?? 'framework');
if (!isset($_SESSION[$sessionPrefix . 'admin']) || $_SESSION[$sessionPrefix . 'admin'] < 1) {
    header('Location: /admin/admin_login.php');
    exit;
}

require_once $baseDir . '/views/partials/admin_header.php';

// Get all modules
$modules = $config['modules'] ?? [];

// Initialize validator
require_once $baseDir . '/app/Services/ModuleValidator.php';
$moduleValidator = new \App\Services\ModuleValidator();

// Validate all modules
$validationResults = [];
foreach ($modules as $moduleName => $moduleInfo) {
    $modulePath = $baseDir . '/modules/' . $moduleName;
    if (is_dir($modulePath)) {
        try {
            $validationResults[$moduleName] = $moduleValidator->validateModule($modulePath);
        } catch (Exception $e) {
            // If validation fails, create a basic error result
            $validationResults[$moduleName] = [
                'valid' => false,
                'errors' => ['Validation error: ' . $e->getMessage()],
                'structure' => [],
                'security' => [],
                'quality' => [],
                'performance' => []
            ];
        }
    }
}

// Calculate summary statistics
$totalModules = count($validationResults);
$validModules = count(array_filter($validationResults, function ($result) {
    return $result['valid'];
}));
$invalidModules = $totalModules - $validModules;
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Module Validation Report</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/admin/modules">Modules</a></li>
        <li class="breadcrumb-item active">Validation Report</li>
    </ol>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-white-50">Total Modules</div>
                            <div class="text-lg fw-bold"><?= $totalModules ?></div>
                        </div>
                        <div><i class="fas fa-cubes fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-white-50">Valid Modules</div>
                            <div class="text-lg fw-bold"><?= $validModules ?></div>
                        </div>
                        <div><i class="fas fa-check-circle fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-white-50">Issues Found</div>
                            <div class="text-lg fw-bold"><?= $invalidModules ?></div>
                        </div>
                        <div><i class="fas fa-exclamation-triangle fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-white-50">Success Rate</div>
                            <div class="text-lg fw-bold"><?= $totalModules > 0 ? round(($validModules / $totalModules) * 100) : 0 ?>%</div>
                        </div>
                        <div><i class="fas fa-chart-pie fa-2x"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Validation Results -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Detailed Validation Results
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Module</th>
                            <th>Overall Status</th>
                            <th>Structure</th>
                            <th>Security</th>
                            <th>Quality</th>
                            <th>Performance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($validationResults as $moduleName => $result) : ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($moduleName) ?></strong>
                                    <?php if ($moduleName === 'admin' || $moduleName === 'home') : ?>
                                        <span class="badge bg-secondary ms-2">Core</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($result['valid']) : ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle"></i> Valid
                                        </span>
                                    <?php else : ?>
                                        <span class="badge bg-warning">
                                            <i class="fas fa-exclamation-triangle"></i> Issues
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $structureValid = array_reduce($result['structure'], function ($carry, $item) {
                                        return $carry && $item;
                                    }, true);
                                    ?>
                                    <?php if ($structureValid) : ?>
                                        <i class="fas fa-check text-success" title="Structure valid"></i>
                                    <?php else : ?>
                                        <i class="fas fa-times text-danger" title="Structure issues"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $securityValid = array_reduce($result['security'], function ($carry, $item) {
                                        return $carry && $item;
                                    }, true);
                                    ?>
                                    <?php if ($securityValid) : ?>
                                        <i class="fas fa-shield-alt text-success" title="Security checks passed"></i>
                                    <?php else : ?>
                                        <i class="fas fa-shield-alt text-danger" title="Security issues"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $qualityValid = array_reduce($result['quality'], function ($carry, $item) {
                                        return $carry && $item;
                                    }, true);
                                    ?>
                                    <?php if ($qualityValid) : ?>
                                        <i class="fas fa-star text-success" title="Quality checks passed"></i>
                                    <?php else : ?>
                                        <i class="fas fa-star text-warning" title="Quality issues"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $performanceValid = array_reduce($result['performance'], function ($carry, $item) {
                                        return $carry && $item;
                                    }, true);
                                    ?>
                                    <?php if ($performanceValid) : ?>
                                        <i class="fas fa-tachometer-alt text-success" title="Performance checks passed"></i>
                                    <?php else : ?>
                                        <i class="fas fa-tachometer-alt text-warning" title="Performance issues"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="/admin/modules/details/<?= urlencode($moduleName) ?>" 
                                           class="btn btn-outline-primary btn-sm" 
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php
                                        // Add dynamic Open Module button if enabled and admin_url exists
                                        $modulesConfig = include dirname(__DIR__, 3) . '/app/modules.php';
                                        if (isset($modulesConfig['modules'][$moduleName]) && is_array($modulesConfig['modules'][$moduleName]) && !empty($modulesConfig['modules'][$moduleName]['enabled'])) {
                                            $metaFile = dirname(__DIR__, 3) . '/modules/' . $moduleName . '/module.json';
                                            if (file_exists($metaFile)) {
                                                $meta = [];
                                                try {
                                                    if (is_readable($metaFile)) {
                                                        $metaContent = file_get_contents($metaFile);
                                                        $meta = json_decode($metaContent, true);
                                                    } else {
                                                        error_log('Meta file not readable: ' . $metaFile);
                                                    }
                                                } catch (\Exception $e) {
                                                    error_log('Error reading meta file: ' . $e->getMessage());
                                                }
                                                if (!empty($meta['admin_url'])) {
                                                    echo '<a href="' . htmlspecialchars($meta['admin_url']) . '" class="btn btn-outline-success btn-sm" title="Open Module"><i class="fas fa-external-link-alt"></i></a>';
                                                }
                                            }
                                        }
                                        ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Validation Issues Details -->
    <?php
    $modulesWithIssues = array_filter($validationResults, function ($result) {
        return !$result['valid'];
    });
    if (!empty($modulesWithIssues)) :
        ?>
        <div class="card">
            <div class="card-header">
                <i class="fas fa-exclamation-triangle me-1"></i>
                Modules with Issues
            </div>
            <div class="card-body">
                <?php foreach ($modulesWithIssues as $moduleName => $result) : ?>
                    <div class="border rounded p-3 mb-3">
                        <h6 class="text-warning">
                            <i class="fas fa-cube me-2"></i><?= htmlspecialchars($moduleName) ?>
                        </h6>
                        <div class="row">
                            <?php if (!array_reduce($result['structure'], function ($carry, $item) {
    return $carry && $item;
                            }, true)) : ?>
                                <div class="col-md-6">
                                    <strong>Structure Issues:</strong>
                                    <ul class="small mb-2">
                                        <?php foreach ($result['structure'] as $check => $passed) : ?>
                                            <?php if (!$passed) : ?>
                                                <li class="text-danger"><?= ucfirst(str_replace('_', ' ', $check)) ?></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!array_reduce($result['security'], function ($carry, $item) {
    return $carry && $item;
                            }, true)) : ?>
                                <div class="col-md-6">
                                    <strong>Security Issues:</strong>
                                    <ul class="small mb-2">
                                        <?php foreach ($result['security'] as $check => $passed) : ?>
                                            <?php if (!$passed) : ?>
                                                <li class="text-danger"><?= ucfirst(str_replace('_', ' ', $check)) ?></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                        <a href="/admin/modules/details/<?= urlencode($moduleName) ?>" class="btn btn-sm btn-primary">
                            View Full Details
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once $baseDir . '/views/partials/footer.php'; ?>