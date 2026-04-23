<?php
// CMS Page Template
if (!defined('STRPHP_ROOT')) {
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($meta_description ?? '') ?>">
    <title><?= htmlspecialchars($meta_title ?? $title ?? 'Page') ?></title>
    
    <?php if (!empty($canonical_url)) : ?>
    <link rel="canonical" href="<?= htmlspecialchars($canonical_url) ?>">
    <?php endif; ?>
    
    <?php if (!empty($noindex)) : ?>
    <meta name="robots" content="noindex, nofollow">
    <?php endif; ?>
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="<?= htmlspecialchars($meta_title ?? $title ?? 'Page') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($meta_description ?? '') ?>">
    <meta property="og:type" content="<?= htmlspecialchars($og_type ?? 'article') ?>">
    <?php if (!empty($og_image)) : ?>
    <meta property="og:image" content="<?= htmlspecialchars($og_image) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:type" content="image/png">
        <?php
    // Generate thumbnail URL for additional meta tag
        $thumbnailUrl = str_replace('/storage/uploads/cms/', '/storage/uploads/cms/thumbs/', $og_image);
        ?>
    <meta property="og:image:thumbnail" content="<?= htmlspecialchars($thumbnailUrl) ?>">
    <?php endif; ?>
    <meta property="og:url" content="<?= htmlspecialchars($canonical_url ?? $_SERVER['REQUEST_URI'] ?? '') ?>">
    <meta property="og:site_name" content="<?= htmlspecialchars($site_name ?? 'StrataPHP CMS') ?>">
    
    <!-- Twitter Cards -->
    <meta name="twitter:card" content="<?= htmlspecialchars($twitter_card ?? 'summary_large_image') ?>">
    <meta name="twitter:title" content="<?= htmlspecialchars($meta_title ?? $title ?? 'Page') ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($meta_description ?? '') ?>">
    <?php if (!empty($og_image)) : ?>
    <meta name="twitter:image" content="<?= htmlspecialchars($og_image) ?>">
    <meta name="twitter:image:alt" content="<?= htmlspecialchars($meta_title ?? $title ?? 'Page image') ?>">
    <?php endif; ?>
    
    <style>
    /* Bootstrap float utility fallbacks for CMS images */
    .float-start { float: left !important; margin-right: 1.5rem !important; }
    .float-end { float: right !important; margin-left: 1.5rem !important; }
    .mx-auto { margin-left: auto !important; margin-right: auto !important; }
    .d-block { display: block !important; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2, h3, h4, h5, h6 {
            color: #2c3e50;
            margin-top: 1.5em;
            margin-bottom: 0.5em;
        }
        h1 {
            border-bottom: 2px solid #3498db;
            padding-bottom: 0.3em;
        }
        .page-meta {
            color: #7f8c8d;
            font-size: 0.9em;
            margin-bottom: 2em;
        }
        .page-content {
            line-height: 1.8;
        }
        .page-content img {
            max-width: 100%;
            height: auto;
        }
        .page-content pre {
            background: #f8f9fa;
            padding: 1em;
            border-radius: 4px;
            overflow-x: auto;
        }
        .page-content blockquote {
            border-left: 4px solid #3498db;
            margin: 1em 0;
            padding: 0.5em 0 0.5em 1em;
            background: #f8f9fa;
        }
        .float-start { float: left !important; margin-right: 1.5rem !important; }
.float-end { float: right !important; margin-left: 1.5rem !important; }
.mx-auto { margin-left: auto !important; margin-right: auto !important; }
.d-block { display: block !important; }
    </style>
</head>
<body>
    <main class="page-content">
        <?php
        // Process content to handle line breaks and paragraphs properly
        $processedContent = $content ?? '';
        
        // If content contains HTML tags, preserve them; otherwise convert line breaks
        if (strip_tags($processedContent) === $processedContent) {
            // No HTML tags found, process as plain text
            $processedContent = htmlspecialchars($processedContent);
            
            // Split by double line breaks to create paragraphs
            $paragraphs = preg_split('/\n\s*\n/', $processedContent);
            $processedParagraphs = [];
            
            foreach ($paragraphs as $paragraph) {
                $paragraph = trim($paragraph);
                if (!empty($paragraph)) {
                    // Convert single line breaks to <br> within paragraphs
                    $paragraph = nl2br($paragraph);
                    $processedParagraphs[] = '<p>' . $paragraph . '</p>';
                }
            }
            
            $processedContent = implode("\n", $processedParagraphs);
        }
        
        echo $processedContent;
        ?>
    </main>
    
    <?php if (isset($page) && $page) : ?>
    <div class="page-meta">
        <?php if (!empty($page['created_at'])) : ?>
            <p>Published: <?= date('F j, Y', strtotime($page['created_at'])) ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</body>
</html>