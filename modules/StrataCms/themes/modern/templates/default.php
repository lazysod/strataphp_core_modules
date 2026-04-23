<?php
/**
 * Default Page Template (Bootstrap 5 Only)
 *
 * This template is for the new CMS theme system. No embedded CSS or JS.
 * All layout uses Bootstrap 5 utility classes. All custom styles in theme.css.
 *
 * Error handling: Wraps meta/data preparation in try-catch and logs errors.
 */
try {
    // Get page meta data, fallback to home page meta if missing
    if (!isset($themeManager)) {
        $themeManager = isset($theme) ? new App\Modules\StrataCms\ThemeManager() : null;
    }
    if (!isset($meta)) {
        $meta = $themeManager ? $themeManager->getPageMeta($page) : [];
    }
    // If any of the key OG meta fields are missing, fallback to home page meta
    if ($themeManager && (
        empty($meta['og_image']) || empty($meta['og_title']) || empty($meta['og_description'])
    )) {
        $homePage = (new App\Modules\StrataCms\Models\Page())->getHomePage();
        if ($homePage) {
            $homeMeta = $themeManager->getPageMeta($homePage);
            if (empty($meta['og_image']) && !empty($homeMeta['og_image'])) {
                $meta['og_image'] = $homeMeta['og_image'];
            }
            if (empty($meta['og_title']) && !empty($homeMeta['og_title'])) {
                $meta['og_title'] = $homeMeta['og_title'];
            }
            if (empty($meta['og_description']) && !empty($homeMeta['og_description'])) {
                $meta['og_description'] = $homeMeta['og_description'];
            }
            if (empty($meta['description']) && !empty($homeMeta['description'])) {
                $meta['description'] = $homeMeta['description'];
            }
            if (empty($meta['twitter_image']) && !empty($homeMeta['twitter_image'])) {
                $meta['twitter_image'] = $homeMeta['twitter_image'];
            }
            if (empty($meta['twitter_title']) && !empty($homeMeta['twitter_title'])) {
                $meta['twitter_title'] = $homeMeta['twitter_title'];
            }
            if (empty($meta['twitter_description']) && !empty($homeMeta['twitter_description'])) {
                $meta['twitter_description'] = $homeMeta['twitter_description'];
            }
        }
    }
} catch (\Throwable $e) {
    echo '<div class="alert alert-danger">An error occurred preparing the page meta data.</div>';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- CMS HEADER TEMPLATE: modern/templates/default.php -->
    <?php
    $isProfile = isset($page['slug']) && $page['slug'] === 'user/profile';
    $noindex = !empty($meta['noindex']) || !empty($page['noindex']) || $isProfile;
    ?>
    <?php if ($noindex) : ?>
        <meta name="robots" content="noindex, nofollow">
    <?php else : ?>
        <meta name="robots" content="index, follow">
    <?php endif; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($meta['title'] ?? $page['title'] ?? 'Page') ?></title>
    <?php if (isset($meta['description'])) : ?>
        <meta name="description" content="<?= htmlspecialchars($meta['description']) ?>">
    <?php endif; ?>
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= htmlspecialchars($meta['title'] ?? $page['title'] ?? ($theme['config']['site_name'] ?? 'Page')) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($meta['description'] ?? ($theme['config']['description'] ?? 'oops')) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>">
    <?php
    // Determine image for OG/Twitter
    $siteImage = $meta['og_image'] ?? ($theme['config']['site_image'] ?? '/assets/site-image.png');
    ?>
    <meta property="og:image" content="<?= htmlspecialchars($siteImage) ?>">
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($meta['title'] ?? $page['title'] ?? ($theme['config']['site_name'] ?? 'Page')) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($meta['description'] ?? ($theme['config']['description'] ?? '')) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($siteImage) ?>">
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/favicon.ico" />
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Font Awesome (optional, for icons) -->
    <link rel="stylesheet" href="/themes/default/assets/fontawesome/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/modules/cms/themes/modern/assets/css/style.css">
</head>

<body>
    <header class="cms-header mb-4">
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand" href="/"><?= htmlspecialchars($theme['config']['site_name'] ?? $theme['config']['name'] ?? 'CMS') ?></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#cmsNavbar" aria-controls="cmsNavbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="cmsNavbar">
                    <?php
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    $sessionPrefix = $config['session_prefix'] ?? 'app_';
                    $userId = $_SESSION[$sessionPrefix . 'user_id'] ?? null;
                    $userName = $_SESSION[$sessionPrefix . 'first_name'] ?? 'User';
                    // Recursive function to render nav items with correct Bootstrap classes
                    function renderNav($items, $level = 0, $userId = null)
                    {
                        $ulClass = $level === 0 ? 'navbar-nav ms-auto mb-2 mb-lg-0' : 'dropdown-menu';
                        if ($level === 0) {
                            foreach ($items as $nav) {
                                $hasChildren = !empty($nav['children']);
                                $isActive = ($_SERVER['REQUEST_URI'] === $nav['url']) || ($nav['is_home'] && ($_SERVER['REQUEST_URI'] === '/' || $_SERVER['REQUEST_URI'] === '/index.php'));
                                $titleLower = strtolower($nav['title']);
                                if ($userId && ($titleLower === 'logout' || $titleLower === 'profile')) {
                                    continue;
                                }
                                if ($hasChildren) {
                                    $liClass = 'nav-item dropdown';
                                    $aClass = 'nav-link dropdown-toggle';
                                    echo '<li class="' . $liClass . '">';
                                    echo '<a class="' . $aClass . ($isActive ? ' active' : '') . '" href="' . htmlspecialchars($nav['url']) . '" id="dropdown-' . htmlspecialchars($nav['slug']) . '" role="button" data-bs-toggle="dropdown" aria-expanded="false">' . htmlspecialchars($nav['title']) . '</a>';
                                    foreach ($nav['children'] as &$child) {
                                        $child['parent_slug'] = $nav['slug'];
                                    }
                                    unset($child);
                                    renderNav($nav['children'], $level + 1, $userId);
                                    echo '</li>';
                                } else {
                                    echo '<li class="nav-item">';
                                    echo '<a class="nav-link' . ($isActive ? ' active' : '') . '" href="' . htmlspecialchars($nav['url']) . '">' . htmlspecialchars($nav['title']) . '</a>';
                                    echo '</li>';
                                }
                            }
                        } else {
                            $parentSlug = isset($items[0]['parent_slug']) ? $items[0]['parent_slug'] : (isset($items[0]['parent']) ? $items[0]['parent'] : '');
                            $ariaLabelledBy = $parentSlug ? ' aria-labelledby="dropdown-' . htmlspecialchars($parentSlug) . '"' : '';
                            echo '<ul class="dropdown-menu"' . $ariaLabelledBy . '>';
                            foreach ($items as $nav) {
                                $hasChildren = !empty($nav['children']);
                                $isActive = ($_SERVER['REQUEST_URI'] === $nav['url']);
                                $titleLower = strtolower($nav['title']);
                                if ($userId && ($titleLower === 'logout' || $titleLower === 'profile')) {
                                    continue;
                                }
                                if ($hasChildren) {
                                    echo '<li class="dropdown-submenu">';
                                    echo '<a class="dropdown-item dropdown-toggle' . ($isActive ? ' active' : '') . '" href="' . htmlspecialchars($nav['url']) . '" id="dropdown-' . htmlspecialchars($nav['slug']) . '" role="button" data-bs-toggle="dropdown" aria-expanded="false">' . htmlspecialchars($nav['title']) . '</a>';
                                    foreach ($nav['children'] as &$child) {
                                        $child['parent_slug'] = $nav['slug'];
                                    }
                                    unset($child);
                                    renderNav($nav['children'], $level + 1, $userId);
                                    echo '</li>';
                                } else {
                                    echo '<li>';
                                    echo '<a class="dropdown-item' . ($isActive ? ' active' : '') . '" href="' . htmlspecialchars($nav['url']) . '">' . htmlspecialchars($nav['title']) . '</a>';
                                    echo '</li>';
                                }
                            }
                            echo '</ul>';
                        }
                    }
                    ?>
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <?php if (isset($navigation) && is_array($navigation)) {
                            renderNav($navigation, 0, $userId);
                        } ?>
                        <?php if ($userId) : ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Welcome <?= htmlspecialchars($userName) ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <?php if ($_SESSION[$sessionPrefix . 'admin']) : ?>
                                        <li><a class="dropdown-item" href="/admin/dashboard">Admin Dashboard</a></li>
                                    <?php endif; ?>
                                    <li><a class="dropdown-item" href="/user/profile">Profile</a></li>
                                    <li><a class="dropdown-item" href="/logout.php">Logout</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container py-4">
            <h1 class="h2 mb-0"><?= htmlspecialchars($meta['title'] ?? $page['title'] ?? 'Page') ?></h1>
        </div>
    </header>
    <main class="container">
        <article class="cms-content">
            <?= $page['content'] ?? '' ?>
        </article>
    </main>
    <footer class="cms-footer mt-5 py-4 bg-light">
        <div class="container text-center">
            <p class="mb-0">&copy; <?= date('Y') ?> <?= htmlspecialchars($theme['config']['site_name'] ?? 'CMS') ?>. All rights reserved.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/modules/cms/themes/modern/assets/js/theme.js"></script>
</body>

</html>