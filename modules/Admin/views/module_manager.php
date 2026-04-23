<?php
global $config;
$modules = isset($modules) ? array_change_key_case($modules, CASE_LOWER) : [];
$modulesPath = $_SERVER['DOCUMENT_ROOT'] . '/modules';
if (is_dir($modulesPath)) {
    $moduleDirectories = array_filter(glob($modulesPath . '/*'), 'is_dir');
    foreach ($moduleDirectories as $moduleDir) {
        $moduleName = strtolower(basename($moduleDir));
        $moduleIndexPath = $moduleDir . '/index.php';
        if (!file_exists($moduleIndexPath)) {
            continue;
        }
        if (!isset($modules[$moduleName])) {
            $modules[$moduleName] = [
                'enabled' => false,
                'suitable_as_default' => false
            ];
        }
    }
}

$sessionPrefix = $config['session_prefix'] ?? ($config['prefix'] ?? 'framework');
if (empty($_SESSION[$sessionPrefix . 'admin'])) {
    header('Location: /admin/admin_login.php');
    exit;
}

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/views/partials/admin_header.php')) {
    require $_SERVER['DOCUMENT_ROOT'] . '/views/partials/admin_header.php';
} else {
    echo '<!-- Missing admin_header.php -->';
}
// Show module update messages at the top
if (!empty($_SESSION['module_update_success'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['module_update_success']) . '</div>';
    unset($_SESSION['module_update_success']);
}
if (!empty($_SESSION['module_update_error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['module_update_error']) . '</div>';
    unset($_SESSION['module_update_error']);
}
?>
<section class="py-5">
    <div class="container px-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-cubes me-2"></i>Module Manager</h1>
            <div>
                <a href="/admin/module-installer" class="btn btn-primary">
                    <i class="fas fa-download me-2"></i>Install New Module
                </a>
                <a href="/admin/google-analytics-settings" class="btn btn-outline-info ms-2">
                    <i class="fab fa-google me-1"></i>Google Analytics Settings
                </a>
            </div>
        </div>
        
        <!-- Module Installation Info Box -->
        <div class="alert alert-info mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h6 class="mb-1"><i class="fas fa-lightbulb me-2"></i>Need More Modules?</h6>
                    <p class="mb-0">Install modules from GitHub, ZIP files, or generate new ones using our module installer.</p>
                </div>
                <div class="col-md-4 text-md-end mt-2 mt-md-0">
                    <a href="/admin/module-installer" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>Get Modules
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Module Statistics Dashboard -->
        <?php if (!empty($modules)) : ?>
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-cubes fa-2x mb-2"></i>
                        <h4 class="mb-0"><?= count($modules) ?></h4>
                        <small>Total Modules</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <h4 class="mb-0" id="enabledCount"><?= array_sum(array_map(function ($m) {
    return !empty($m['enabled']) ? 1 : 0;
                                                           }, $modules)) ?></h4>
                        <small>Enabled</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-pause-circle fa-2x mb-2"></i>
                        <h4 class="mb-0" id="disabledCount"><?= array_sum(array_map(function ($m) {
    return empty($m['enabled']) ? 1 : 0;
                                                            }, $modules)) ?></h4>
                        <small>Disabled</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-layer-group fa-2x mb-2"></i>
                        <h4 class="mb-0"><?= count(array_unique(array_map(function ($m) {
                            $path = ($_SERVER['DOCUMENT_ROOT'] ?? dirname(__FILE__, 4)) . '/modules/' . $m;
                            $metadata = file_exists($path . '/index.php') ? (include $path . '/index.php') : [];
                                $moduleMeta = file_exists($path . '/index.php') ? (include $path . '/index.php') : [];
                                $metadata = is_array($moduleMeta) ? $moduleMeta : [];
                            return $metadata['category'] ?? 'Uncategorized';
                                         }, array_keys($modules)))) ?></h4>
                        <small>Categories</small>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Search and Filter Controls -->
        <?php if (!empty($modules)) : ?>
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Filter & Search Modules</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="categoryFilter" class="form-label">Filter by Category</label>
                        <select class="form-select" id="categoryFilter">
                            <option value="">All Categories</option>
                            <?php
                            $categories = [];
                            foreach ($modules as $modName => $modInfo) {
                                $modulePath = ($_SERVER['DOCUMENT_ROOT'] ?? dirname(__FILE__, 4)) . '/modules/' . $modName;
                                $metadata = file_exists($modulePath . '/index.php') ? (include $modulePath . '/index.php') : [];
                                    $moduleMeta = file_exists($modulePath . '/index.php') ? (include $modulePath . '/index.php') : [];
                                    $metadata = is_array($moduleMeta) ? $moduleMeta : [];
                                    $moduleMeta = file_exists($modulePath . '/index.php') ? (include $modulePath . '/index.php') : [];
                                    $metadata = is_array($moduleMeta) ? $moduleMeta : [];
                                $category = $metadata['category'] ?? 'Uncategorized';
                                $categories[$category] = ($categories[$category] ?? 0) + 1;
                            }
                            ksort($categories);
                            foreach ($categories as $category => $count) : ?>
                                <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?> (<?= $count ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="statusFilter" class="form-label">Filter by Status</label>
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="enabled">Enabled Only</option>
                            <option value="disabled">Disabled Only</option>
                            <option value="core">Core Modules</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="searchFilter" class="form-label">Search Modules</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="searchFilter" placeholder="Search by name or description...">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="btn-group btn-group-sm" role="group">
                            <input type="radio" class="btn-check" name="viewMode" id="tableView" value="table" checked>
                            <label class="btn btn-outline-primary" for="tableView">
                                <i class="fas fa-table me-1"></i>Table View
                            </label>
                            <input type="radio" class="btn-check" name="viewMode" id="cardView" value="cards">
                            <label class="btn btn-outline-primary" for="cardView">
                                <i class="fas fa-th me-1"></i>Card View
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">
                            <i class="fas fa-refresh me-1"></i>Reset Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <form method="post" action="/admin/modules/save.php">
            <!-- Table View -->
            <div id="tableViewContainer">
                <table class="table table-bordered" id="modulesTable">
                    <thead class="table-dark">
                        <tr>
                            <th><i class="fas fa-cube me-2"></i>Module</th>
                            <th><i class="fas fa-layer-group me-2"></i>Category</th>
                            <th><i class="fas fa-toggle-on me-2"></i>Enabled</th>
                            <th><i class="fas fa-info-circle me-2"></i>Status</th>
                            <th><i class="fas fa-check-circle me-2"></i>Validation</th>
                            <th><i class="fas fa-cog me-2"></i>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($modules)) : ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-box-open fa-2x mb-3"></i>
                                        <h6>No modules installed</h6>
                                        <p class="mb-0">Get started by installing your first module!</p>
                                        <a href="/admin/module-installer" class="btn btn-primary btn-sm mt-2">
                                            <i class="fas fa-download me-1"></i>Install Module
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php else : ?>
                            <?php
                            // Initialize module validator for validation checks
                            require_once $_SERVER['DOCUMENT_ROOT'] . '/app/Services/ModuleValidator.php';
                            $moduleValidator = class_exists('App\\Services\\ModuleValidator') ? new \App\Services\ModuleValidator() : null;
                            ?>
                            <?php
                            require_once $_SERVER['DOCUMENT_ROOT'] . '/app/ModuleUpdater.php';
                            $coreModules = include $_SERVER['DOCUMENT_ROOT'] . '/app/core_modules.php';
                            foreach ($modules as $modName => $modInfo) : ?>
                                <?php
                                // Get module metadata
                                $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
                                if (empty($documentRoot)) {
                                    $modulePath = dirname(__FILE__, 4) . '/modules/' . $modName;
                                } else {
                                    $modulePath = $documentRoot . '/modules/' . $modName;
                                }
                                $metadata = [];
                                if (file_exists($modulePath . '/index.php')) {
                                    try {
                                        $metadata = include $modulePath . '/index.php';
                                    } catch (Exception $e) {
                                        $metadata = [];
                                    }
                                }
                                $category = $metadata['category'] ?? 'Uncategorized';
                                $description = $metadata['description'] ?? '';
                                $version = $metadata['version'] ?? '1.0.0';
                                $author = $metadata['author'] ?? '';
                                $adminUrl = $metadata['admin_url'] ?? '';
                                $adminTitle = $metadata['title'] ?? $modName;
                                // Get validation status for each module
                                $validationStatus = null;
                                if ($moduleValidator) {
                                    if (is_dir($modulePath)) {
                                        $validationResults = $moduleValidator->validateModule($modulePath);
                                        $validationStatus = $validationResults['valid'];
                                    }
                                }
                                $isCore = array_key_exists($modName, $coreModules);
                                $isEnabled = !empty($modInfo['enabled']);
                                $updateAvailable = false;
                                if ($isCore) {
                                    $localJsonPath = $modulePath . '/module.json';
                                    $remoteJsonUrl = $coreModules[$modName]['json'] ?? '';
                                    if (file_exists($localJsonPath) && $remoteJsonUrl) {
                                        $updateAvailable = \App\ModuleUpdater::checkUpdate($modName, $localJsonPath, $remoteJsonUrl);
                                    }
                                }
                                ?>
                                <tr class="module-row" 
                                    data-module-name="<?= htmlspecialchars(strtolower($modName)) ?>"
                                    data-category="<?= htmlspecialchars($category) ?>"
                                    data-status="<?= $isEnabled ? 'enabled' : 'disabled' ?>"
                                    data-core="<?= $isCore ? 'true' : 'false' ?>"
                                    data-description="<?= htmlspecialchars(strtolower($description)) ?>">
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($modName); ?></strong>
                                            <?php if ($isCore) : ?>
                                                <span class="badge bg-secondary ms-2">Core</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($description) : ?>
                                            <small class="text-muted d-block"><?= htmlspecialchars($description) ?></small>
                                        <?php endif; ?>
                                        <?php if ($version) : ?>
                                            <small class="text-muted">v<?= htmlspecialchars($version) ?></small>
                                        <?php endif; ?>
                                        <?php if ($isCore && $updateAvailable) : ?>
                                            <form method="post" action="/admin/modules/update.php" style="display:inline; margin-top:5px;">
                                                <input type="hidden" name="module" value="<?= htmlspecialchars($modName) ?>">
                                                <button type="submit" class="btn btn-warning btn-sm" title="Update Available">
                                                    <i class="fas fa-sync-alt"></i> Update
                                                </button>
                                            </form>
                                        <?php elseif ($isCore) : ?>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" disabled title="Up to date" style="margin-top:5px;">
                                                <i class="fas fa-check"></i> Up to date
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        // Get category badge class
                                        $badgeClass = 'bg-secondary';
                                        switch ($category) {
                                            case 'Utility':
                                                $badgeClass = 'bg-primary';
                                                break;
                                            case 'Content':
                                                $badgeClass = 'bg-success';
                                                break;
                                            case 'Admin':
                                                $badgeClass = 'bg-danger';
                                                break;
                                            case 'Security':
                                                $badgeClass = 'bg-warning text-dark';
                                                break;
                                            case 'API':
                                                $badgeClass = 'bg-info text-dark';
                                                break;
                                            case 'Social':
                                                $badgeClass = 'bg-primary';
                                                break;
                                            case 'E-commerce':
                                                $badgeClass = 'bg-dark';
                                                break;
                                            case 'Analytics':
                                                $badgeClass = 'bg-info';
                                                break;
                                            case 'SEO':
                                                $badgeClass = 'bg-success';
                                                break;
                                            case 'Media':
                                                $badgeClass = 'bg-warning text-dark';
                                                break;
                                            case 'Development':
                                                $badgeClass = 'bg-dark';
                                                break;
                                            case 'Marketing':
                                                $badgeClass = 'bg-danger';
                                                break;
                                            default:
                                                $badgeClass = 'bg-secondary';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?= $badgeClass ?>">
                                            <?= htmlspecialchars($category) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <input type="hidden" name="enabled_present[]" value="<?php echo htmlspecialchars($modName); ?>">
                                        <?php if ($modName === 'admin') : ?>
                                            <input type="checkbox" checked disabled>
                                            <input type="hidden" class="table-view-input" name="enabled[]" value="admin">
                                            <small class="text-muted ms-2">Required</small>
                                        <?php elseif ($modName === 'home') : ?>
                                            <input type="checkbox" checked disabled>
                                            <input type="hidden" class="table-view-input" name="enabled[]" value="home">
                                            <small class="text-muted ms-2">Required</small>
                                        <?php else : ?>
                                            <input type="checkbox" class="table-view-checkbox" name="enabled[]" value="<?php echo htmlspecialchars($modName); ?>" <?php echo (!empty($modInfo['enabled']) ? 'checked' : ''); ?>>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($isEnabled) : ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else : ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($modInfo['suitable_as_default'])) : ?>
                                            <span class="badge bg-info ms-1">Can be Default</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($validationStatus === true) : ?>
                                            <span class="badge bg-success" title="All validations passed">
                                                <i class="fas fa-check-circle"></i> Valid
                                            </span>
                                        <?php elseif ($validationStatus === false) : ?>
                                            <span class="badge bg-warning" title="Some validation issues found">
                                                <i class="fas fa-exclamation-triangle"></i> Issues
                                            </span>
                                        <?php else : ?>
                                            <span class="badge bg-secondary" title="Validation not available">
                                                <i class="fas fa-question-circle"></i> Unknown
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="/admin/modules/details/<?php echo urlencode($modName); ?>" 
                                               class="btn btn-outline-primary btn-sm" 
                                               title="View Details">
                                                <i class="fas fa-info-circle"></i>
                                            </a>
                                            <?php if ($modName === 'cms' && $isEnabled) : ?>
                                                <a href="/admin/cms" class="btn btn-outline-success btn-sm" title="Open CMS Backend">
                                                    <i class="fas fa-external-link-alt"></i> CMS Backend
                                                </a>
                                            <?php elseif ($isEnabled && !empty($adminUrl)) : ?>
                                                <a href="<?= htmlspecialchars($adminUrl) ?>" class="btn btn-outline-success btn-sm" title="Open <?= htmlspecialchars($adminTitle) ?>">
                                                    <i class="fas fa-external-link-alt"></i> Open Module
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($isCore && $updateAvailable) : ?>
                                                <form method="post" action="/admin/modules/update.php" style="display:inline;">
                                                    <input type="hidden" name="module" value="<?= htmlspecialchars($modName) ?>">
                                                    <button type="submit" class="btn btn-warning btn-sm" title="Update Available">
                                                        <i class="fas fa-sync-alt"></i> Update
                                                    </button>
                                                </form>
                                            <?php elseif ($isCore) : ?>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" disabled title="Up to date">
                                                    <i class="fas fa-check"></i> Up to date
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($moduleValidator) : ?>
                                                <button type="button" 
                                                        class="btn btn-outline-secondary btn-sm" 
                                                        onclick="validateModule('<?php echo htmlspecialchars($modName); ?>')"
                                                        title="Validate Module">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (!$isCore && $modName !== 'admin' && $modName !== 'home') : ?>
                                                <button type="button" 
                                                        class="btn btn-outline-danger btn-sm" 
                                                        onclick="confirmDeleteModule('<?php echo htmlspecialchars($modName); ?>')"
                                                        title="Delete Module">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i>Save Changes
                    </button>
                </div>
                </table>
            </div>

            <!-- Card View -->
            <div id="cardViewContainer" style="display: none;">
                <?php if (empty($modules)) : ?>
                    <div class="text-center py-5">
                        <div class="text-muted">
                            <i class="fas fa-box-open fa-3x mb-3"></i>
                            <h4>No modules installed</h4>
                            <p class="mb-0">Get started by installing your first module!</p>
                            <a href="/admin/module-installer" class="btn btn-primary mt-3">
                                <i class="fas fa-download me-2"></i>Install Module
                            </a>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="row" id="moduleCardsContainer">
                        <?php foreach ($modules as $modName => $modInfo) : ?>
                            <?php
                            // Get module metadata (reuse same logic as table)
                            $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
                            if (empty($documentRoot)) {
                                $modulePath = dirname(__FILE__, 4) . '/modules/' . $modName;
                            } else {
                                $modulePath = $documentRoot . '/modules/' . $modName;
                            }
                            
                            $metadata = [];
                            if (file_exists($modulePath . '/index.php')) {
                                try {
                                    $metadata = include $modulePath . '/index.php';
                                } catch (Exception $e) {
                                    $metadata = [];
                                }
                            }
                            
                            $category = $metadata['category'] ?? 'Uncategorized';
                            $description = $metadata['description'] ?? '';
                            $version = $metadata['version'] ?? '1.0.0';
                            $author = $metadata['author'] ?? '';
                            $adminUrl = $metadata['admin_url'] ?? '';
                            $adminTitle = $metadata['title'] ?? $modName;
                            
                            // Get validation status
                            $validationStatus = null;
                            if ($moduleValidator) {
                                if (is_dir($modulePath)) {
                                    $validationResults = $moduleValidator->validateModule($modulePath);
                                    $validationStatus = $validationResults['valid'];
                                }
                            }
                            
                            $isCore = ($modName === 'admin' || $modName === 'home');
                            $isEnabled = !empty($modInfo['enabled']);
                            
                            // Get category badge class
                            $badgeClass = 'bg-secondary';
                            switch ($category) {
                                case 'Utility':
                                    $badgeClass = 'bg-primary';
                                    break;
                                case 'Content':
                                    $badgeClass = 'bg-success';
                                    break;
                                case 'Admin':
                                    $badgeClass = 'bg-danger';
                                    break;
                                case 'Security':
                                    $badgeClass = 'bg-warning text-dark';
                                    break;
                                case 'API':
                                    $badgeClass = 'bg-info text-dark';
                                    break;
                                case 'Social':
                                    $badgeClass = 'bg-primary';
                                    break;
                                case 'E-commerce':
                                    $badgeClass = 'bg-dark';
                                    break;
                                case 'Analytics':
                                    $badgeClass = 'bg-info';
                                    break;
                                case 'SEO':
                                    $badgeClass = 'bg-success';
                                    break;
                                case 'Media':
                                    $badgeClass = 'bg-warning text-dark';
                                    break;
                                case 'Development':
                                    $badgeClass = 'bg-dark';
                                    break;
                                case 'Marketing':
                                    $badgeClass = 'bg-danger';
                                    break;
                                default:
                                    $badgeClass = 'bg-secondary';
                                    break;
                            }
                            ?>
                            <div class="col-md-6 col-lg-4 mb-4 module-card" 
                                 data-module-name="<?= htmlspecialchars(strtolower($modName)) ?>"
                                 data-category="<?= htmlspecialchars($category) ?>"
                                 data-status="<?= $isEnabled ? 'enabled' : 'disabled' ?>"
                                 data-core="<?= $isCore ? 'true' : 'false' ?>"
                                 data-description="<?= htmlspecialchars(strtolower($description)) ?>">
                                <div class="card h-100 <?= $isEnabled ? 'border-success' : 'border-secondary' ?>">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">
                                                <strong><?= htmlspecialchars($modName) ?></strong>
                                                <?php if ($isCore) : ?>
                                                    <span class="badge bg-secondary ms-2">Core</span>
                                                <?php endif; ?>
                                            </h6>
                                        </div>
                                        <div class="form-check">
                                            <?php if ($modName === 'admin' || $modName === 'home') : ?>
                                                <input class="form-check-input card-view-checkbox" type="checkbox" checked disabled>
                                                <input type="hidden" class="card-view-input" name="enabled[]" value="<?= htmlspecialchars($modName) ?>">
                                            <?php else : ?>
                                                <input class="form-check-input card-view-checkbox" type="checkbox" name="enabled[]" value="<?= htmlspecialchars($modName) ?>" <?= $isEnabled ? 'checked' : '' ?>>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <span class="badge <?= $badgeClass ?> mb-2">
                                                <?= htmlspecialchars($category) ?>
                                            </span>
                                            <?php if ($isEnabled) : ?>
                                                <span class="badge bg-success mb-2">Active</span>
                                            <?php else : ?>
                                                <span class="badge bg-secondary mb-2">Inactive</span>
                                            <?php endif; ?>
                                            <?php if (!empty($modInfo['suitable_as_default'])) : ?>
                                                <span class="badge bg-info mb-2">Can be Default</span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if ($description) : ?>
                                            <p class="card-text text-muted small"><?= htmlspecialchars($description) ?></p>
                                        <?php endif; ?>
                                        
                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <?php if ($version) : ?>
                                                    <i class="fas fa-tag me-1"></i>v<?= htmlspecialchars($version) ?>
                                                <?php endif; ?>
                                                <?php if ($author) : ?>
                                                    <br><i class="fas fa-user me-1"></i><?= htmlspecialchars($author) ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <strong>Validation:</strong>
                                            <?php if ($validationStatus === true) : ?>
                                                <span class="badge bg-success" title="All validations passed">
                                                    <i class="fas fa-check-circle"></i> Valid
                                                </span>
                                            <?php elseif ($validationStatus === false) : ?>
                                                <span class="badge bg-warning" title="Some validation issues found">
                                                    <i class="fas fa-exclamation-triangle"></i> Issues
                                                </span>
                                            <?php else : ?>
                                                <span class="badge bg-secondary" title="Validation not available">
                                                    <i class="fas fa-question-circle"></i> Unknown
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <div class="btn-group btn-group-sm w-100" role="group">
                                            <a href="/admin/modules/details/<?= urlencode($modName) ?>" 
                                               class="btn btn-outline-primary">
                                                <i class="fas fa-info-circle me-1"></i>Details
                                            </a>
                                            <?php if ($isEnabled && !empty($adminUrl)) : ?>
                                                <a href="<?= htmlspecialchars($adminUrl) ?>" class="btn btn-outline-success" title="Open <?= htmlspecialchars($adminTitle) ?>">
                                                    <i class="fas fa-external-link-alt me-1"></i>Open Module
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($moduleValidator) : ?>
                                                <button type="button" 
                                                        class="btn btn-outline-secondary" 
                                                        onclick="validateModuleCard('<?= htmlspecialchars($modName) ?>', this)"
                                                        title="Validate Module">
                                                    <i class="fas fa-check me-1"></i>Validate
                                                </button>
                                            <?php endif; ?>
                                            <?php if (!$isCore && $modName !== 'admin' && $modName !== 'home') : ?>
                                                <button type="button" 
                                                        class="btn btn-outline-danger" 
                                                        onclick="confirmDeleteModule('<?= htmlspecialchars($modName) ?>')"
                                                        title="Delete Module">
                                                    <i class="fas fa-trash me-1"></i>Delete
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- No results message for card view -->
                    <div id="noResultsCard" class="text-center py-5" style="display: none;">
                        <div class="text-muted">
                            <i class="fas fa-search fa-3x mb-3"></i>
                            <h4>No modules found</h4>
                            <p class="mb-0">Try adjusting your filters or search terms.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                
                <label for="default_module" class="form-label"><strong>Default Module (Root Page)</strong></label>
                <select id="default_module" name="default_module" class="form-select">
                    <?php
                    // Only show modules that are enabled AND suitable_as_default in config
                    foreach ($modules as $modName => $modInfo) :
                        if (!empty($modInfo['enabled']) && !empty($modInfo['suitable_as_default'])) :
                            ?>
                        <option value="<?php echo htmlspecialchars($modName); ?>" <?php if (isset($siteConfig['default_module']) && $siteConfig['default_module'] === $modName) {
                            echo 'selected';
                                       } ?>><?php echo htmlspecialchars($modName); ?></option>
                            <?php
                        endif;
                    endforeach;
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Save Changes</button>
        </form>
        
        

        
        <!-- Bulk Actions -->
        <div class="mt-4">
            <div class="row">
                <div class="col-md-6">
                    <a href="/admin/modules/validate-all" class="btn btn-outline-info">
                        <i class="fas fa-check-double me-2"></i>Validate All Modules
                    </a>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="/admin/module-installer" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Install New Module
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Module filtering and search functionality
document.addEventListener('DOMContentLoaded', function() {
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    const searchFilter = document.getElementById('searchFilter');
    const clearSearch = document.getElementById('clearSearch');
    const tableView = document.getElementById('tableView');
    const cardView = document.getElementById('cardView');
    const modulesTable = document.getElementById('modulesTable');
    
    // Filter functions
    function filterModules() {
        const categoryValue = categoryFilter.value.toLowerCase();
        const statusValue = statusFilter.value.toLowerCase();
        const searchValue = searchFilter.value.toLowerCase();
        const isCardView = cardView.checked;
        
        let visibleCount = 0;
        
        if (isCardView) {
            // Filter card view
            const cards = document.querySelectorAll('.module-card');
            cards.forEach(card => {
                const category = card.dataset.category.toLowerCase();
                const status = card.dataset.status.toLowerCase();
                const core = card.dataset.core === 'true';
                const moduleName = card.dataset.moduleName.toLowerCase();
                const description = card.dataset.description.toLowerCase();
                
                let show = true;
                
                // Category filter
                if (categoryValue && category !== categoryValue) {
                    show = false;
                }
                
                // Status filter
                if (statusValue) {
                    if (statusValue === 'core' && !core) {
                        show = false;
                    } else if (statusValue !== 'core' && status !== statusValue) {
                        show = false;
                    }
                }
                
                // Search filter
                if (searchValue && !moduleName.includes(searchValue) && !description.includes(searchValue)) {
                    show = false;
                }
                
                card.style.display = show ? '' : 'none';
                if (show) visibleCount++;
            });
            
            // Show/hide "no results" message for card view
            const noResultsCard = document.getElementById('noResultsCard');
            if (visibleCount === 0) {
                noResultsCard.style.display = 'block';
            } else {
                noResultsCard.style.display = 'none';
            }
        } else {
            // Filter table view
            const rows = document.querySelectorAll('.module-row');
            rows.forEach(row => {
                const category = row.dataset.category.toLowerCase();
                const status = row.dataset.status.toLowerCase();
                const core = row.dataset.core === 'true';
                const moduleName = row.dataset.moduleName.toLowerCase();
                const description = row.dataset.description.toLowerCase();
                
                let show = true;
                
                // Category filter
                if (categoryValue && category !== categoryValue) {
                    show = false;
                }
                
                // Status filter
                if (statusValue) {
                    if (statusValue === 'core' && !core) {
                        show = false;
                    } else if (statusValue !== 'core' && status !== statusValue) {
                        show = false;
                    }
                }
                
                // Search filter
                if (searchValue && !moduleName.includes(searchValue) && !description.includes(searchValue)) {
                    show = false;
                }
                
                row.style.display = show ? '' : 'none';
                if (show) visibleCount++;
            });
            
            // Show/hide "no results" message for table view
            updateNoResultsMessage(visibleCount);
        }
    }
    
    function updateNoResultsMessage(visibleCount) {
        let noResultsRow = document.querySelector('.no-results-row');
        
        if (visibleCount === 0) {
            if (!noResultsRow) {
                noResultsRow = document.createElement('tr');
                noResultsRow.className = 'no-results-row';
                noResultsRow.innerHTML = `
                    <td colspan="6" class="text-center py-4">
                        <div class="text-muted">
                            <i class="fas fa-search fa-2x mb-3"></i>
                            <h6>No modules found</h6>
                            <p class="mb-0">Try adjusting your filters or search terms.</p>
                        </div>
                    </td>
                `;
                modulesTable.querySelector('tbody').appendChild(noResultsRow);
            }
            noResultsRow.style.display = '';
        } else if (noResultsRow) {
            noResultsRow.style.display = 'none';
        }
    }
    
    function resetFilters() {
        categoryFilter.value = '';
        statusFilter.value = '';
        searchFilter.value = '';
        filterModules();
    }
    
    // View mode toggle functionality
    function switchToTableView() {
        document.getElementById('tableViewContainer').style.display = 'block';
        document.getElementById('cardViewContainer').style.display = 'none';
        // Re-apply filters to table view
        filterModules();
    }
    
    function switchToCardView() {
        document.getElementById('tableViewContainer').style.display = 'none';
        document.getElementById('cardViewContainer').style.display = 'block';
        // Re-apply filters to card view
        filterModules();
    }
    
    // Event listeners
    categoryFilter.addEventListener('change', filterModules);
    statusFilter.addEventListener('change', filterModules);
    searchFilter.addEventListener('input', filterModules);
    clearSearch.addEventListener('click', () => {
        searchFilter.value = '';
        filterModules();
    });
    
    // View mode toggle
    tableView.addEventListener('change', function() {
        if (this.checked) {
            switchToTableView();
        }
    });
    
    cardView.addEventListener('change', function() {
        if (this.checked) {
            switchToCardView();
        }
    });
    
    // Make functions available globally
    window.resetFilters = resetFilters;
    window.switchToTableView = switchToTableView;
    window.switchToCardView = switchToCardView;
});

// Validation function for table view
function validateModule(moduleName) {
    // Find the validation badge for this module
    const row = event.target.closest('tr');
    const validationCell = row.querySelector('td:nth-child(5)'); // Updated for new column order
    const originalContent = validationCell.innerHTML;
    
    // Show loading state
    validationCell.innerHTML = '<span class="badge bg-secondary"><i class="fas fa-spinner fa-spin"></i> Validating...</span>';
    
    fetch(`/admin/modules/validate/${moduleName}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update validation status based on results
            if (data.valid) {
                validationCell.innerHTML = '<span class="badge bg-success" title="All validations passed"><i class="fas fa-check-circle"></i> Valid</span>';
            } else {
                validationCell.innerHTML = '<span class="badge bg-warning" title="Some validation issues found"><i class="fas fa-exclamation-triangle"></i> Issues</span>';
            }
        } else {
            // Restore original content on error
            validationCell.innerHTML = originalContent;
            alert('Validation failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        // Restore original content on error
        validationCell.innerHTML = originalContent;
        alert('Error validating module: ' + error.message);
    });
}

// Validation function for card view
function validateModuleCard(moduleName, buttonElement) {
    // Find the validation badge in the card
    const card = buttonElement.closest('.card');
    const validationSection = card.querySelector('.card-body > div:nth-last-child(2)');
    const validationBadge = validationSection.querySelector('.badge');
    const originalContent = validationBadge.outerHTML;
    
    // Show loading state
    validationBadge.outerHTML = '<span class="badge bg-secondary"><i class="fas fa-spinner fa-spin"></i> Validating...</span>';
    
    fetch(`/admin/modules/validate/${moduleName}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        const newValidationBadge = validationSection.querySelector('.badge');
        if (data.success) {
            // Update validation status based on results
            if (data.valid) {
                newValidationBadge.outerHTML = '<span class="badge bg-success" title="All validations passed"><i class="fas fa-check-circle"></i> Valid</span>';
            } else {
                newValidationBadge.outerHTML = '<span class="badge bg-warning" title="Some validation issues found"><i class="fas fa-exclamation-triangle"></i> Issues</span>';
            }
        } else {
            // Restore original content on error
            newValidationBadge.outerHTML = originalContent;
            alert('Validation failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        // Restore original content on error
        const newValidationBadge = validationSection.querySelector('.badge');
        newValidationBadge.outerHTML = originalContent;
        alert('Error validating module: ' + error.message);
    });
}

// Module deletion functions
function confirmDeleteModule(moduleName) {
    // Show the confirmation modal
    const modal = document.getElementById('deleteConfirmModal');
    const moduleNameSpan = document.getElementById('deleteModuleName');
    const confirmInput = document.getElementById('deleteConfirmInput');
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    
    moduleNameSpan.textContent = moduleName;
    confirmInput.value = '';
    deleteBtn.disabled = true;
    
    // Store module name for deletion
    modal.dataset.moduleName = moduleName;
    
    // Show modal
    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();
}

function validateDeleteInput() {
    const modal = document.getElementById('deleteConfirmModal');
    const moduleName = modal.dataset.moduleName;
    const confirmInput = document.getElementById('deleteConfirmInput');
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    
    deleteBtn.disabled = confirmInput.value !== moduleName;
}

function executeModuleDeletion() {
    const modal = document.getElementById('deleteConfirmModal');
    const moduleName = modal.dataset.moduleName;
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    
    // Show loading state
    deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
    deleteBtn.disabled = true;
    
    fetch(`/admin/modules/delete/${moduleName}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            const bootstrapModal = bootstrap.Modal.getInstance(modal);
            bootstrapModal.hide();
            
            // Show success message
            alert('Module deleted successfully!');
            
            // Reload the page to update the module list
            window.location.reload();
        } else {
            // Show error message
            alert('Error deleting module: ' + (data.message || 'Unknown error'));
            
            // Reset button
            deleteBtn.innerHTML = '<i class="fas fa-trash me-1"></i>Delete Module';
            deleteBtn.disabled = false;
        }
    })
    .catch(error => {
        alert('Error deleting module: ' + error.message);
        
        // Reset button
        deleteBtn.innerHTML = '<i class="fas fa-trash me-1"></i>Delete Module';
        deleteBtn.disabled = false;
    });
}
</script>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteConfirmModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Module Deletion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" role="alert">
                    <h6><i class="fas fa-warning me-2"></i>Warning: This action cannot be undone!</h6>
                    <p class="mb-0">
                        You are about to permanently delete the module <strong><span id="deleteModuleName"></span></strong>.
                        This will remove all module files, database tables, and associated data.
                    </p>
                </div>
                <div class="mb-3">
                    <label for="deleteConfirmInput" class="form-label">
                        To confirm deletion, type the module name <strong><span id="deleteModuleName2"></span></strong>:
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="deleteConfirmInput" 
                           placeholder="Enter module name" 
                           oninput="validateDeleteInput()">
                </div>
                <div class="text-muted small">
                    <i class="fas fa-info-circle me-1"></i>
                    A backup will be created before deletion and stored in the storage/backups directory.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" 
                        class="btn btn-danger" 
                        id="confirmDeleteBtn" 
                        onclick="executeModuleDeletion()"
                        disabled>
                    <i class="fas fa-trash me-1"></i>Delete Module
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Update the second module name span when modal is shown
document.getElementById('deleteConfirmModal').addEventListener('show.bs.modal', function () {
    const moduleName = this.dataset.moduleName;
    document.getElementById('deleteModuleName2').textContent = moduleName;
});

// View switching logic - only enable checkboxes for active view
function switchActiveView(viewType) {
    const tableCheckboxes = document.querySelectorAll('.table-view-checkbox');
    const tableInputs = document.querySelectorAll('.table-view-input');
    const cardCheckboxes = document.querySelectorAll('.card-view-checkbox');
    const cardInputs = document.querySelectorAll('.card-view-input');
    if (viewType === 'table') {
        tableCheckboxes.forEach(cb => cb.disabled = false);
        tableInputs.forEach(input => input.disabled = false);
        cardCheckboxes.forEach(cb => cb.disabled = true);
        cardInputs.forEach(input => input.disabled = true);
    } else {
        cardCheckboxes.forEach(cb => cb.disabled = false);
        cardInputs.forEach(input => input.disabled = false);
        tableCheckboxes.forEach(cb => cb.disabled = true);
        tableInputs.forEach(input => input.disabled = true);
    }
}

// Listen for view mode changes
document.getElementById('tableView').addEventListener('change', function() {
    if (this.checked) {
        switchActiveView('table');
    }
});

document.getElementById('cardView').addEventListener('change', function() {
    if (this.checked) {
        switchActiveView('card');
    }
});

// Always enable checkboxes for the default (table) view on page load
document.addEventListener('DOMContentLoaded', function() {
    switchActiveView('table');
    // Also ensure all checkboxes for the active view are enabled
    setTimeout(function() {
        const tableCheckboxes = document.querySelectorAll('.table-view-checkbox');
        tableCheckboxes.forEach(cb => cb.disabled = false);
    }, 100);
});
</script>

<?php 
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/views/partials/footer.php')) {
    require $_SERVER['DOCUMENT_ROOT'] . '/views/partials/footer.php';
} else {
    echo '<!-- Missing footer.php -->';
}
?>
