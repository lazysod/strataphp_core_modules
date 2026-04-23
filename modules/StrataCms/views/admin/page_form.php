<?php
// Enhanced CMS Page Form Template with Tabbed Interface
if (!defined('STRPHP_ROOT')) {
    require_once dirname(__DIR__, 4) . '/bootstrap.php';
}
if (!isset($config)) {
    $config = \App\App::config('modules') ? ['modules' => \App\App::config('modules')] : [];
}
$isEdit = isset($page) && $page;
$pageTitle = $isEdit ? 'Edit Page' : 'Create New Page';
$formAction = $isEdit ? "/admin/strata-cms/pages/{$page['id']}/edit" : "/admin/strata-cms/pages/create";

// Check for session messages
$success_message = isset($_SESSION['success']) ? $_SESSION['success'] : null;
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : null;
if (isset($_SESSION['success'])) {
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    unset($_SESSION['error']);
}

// Warn if Media module is not enabled
$mediaEnabled = false;
if (isset($config['modules']['media'])) {
    $mediaInfo = $config['modules']['media'];
    $mediaEnabled = is_array($mediaInfo) ? !empty($mediaInfo['enabled']) : (bool)$mediaInfo;
}
if (!$mediaEnabled) {
    echo '<div style="background:#ffe0e0;color:#c0392b;padding:12px 18px;border-radius:6px;margin-bottom:18px;font-weight:bold;">Media Manager module is not enabled. Media features will be unavailable.</div>';
}
require __DIR__ . '/../partials/admin_header.php';
?>
<div class="container">
    <div class="breadcrumb">
        <a href="/admin">Admin</a> > <a href="/admin/strata-cms">StrataCMS</a> > <a href="/admin/strata-cms/pages">Pages</a> > <?= $pageTitle ?>
    </div>

    <?php if ($success_message) : ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <?php if ($error_message) : ?>
        <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <div class="header">
        <h1><?= htmlspecialchars($pageTitle) ?> <small style="color: #27ae60; font-size: 14px;">[Enhanced v2.0 - <?= date('H:i:s') ?>]</small></h1>
        <a href="/admin/strata-cms/pages" class="btn btn-secondary">← Back to Pages</a>
    </div>

    <!-- Tab Navigation -->
    <ul class="nav-tabs" id="pageFormTabs">
        <li>
            <button type="button" class="tab-button active" data-target="content-pane">
                <i class="fas fa-file-alt"></i> Content
            </button>
        </li>
        <li>
            <button type="button" class="tab-button" data-target="seo-pane">
                <i class="fas fa-search"></i> SEO & Social
            </button>
        </li>
        <li>
            <button type="button" class="tab-button" data-target="settings-pane">
                <i class="fas fa-cog"></i> Settings
            </button>
        </li>
    </ul>

    <form method="POST" action="<?= $formAction ?>" id="pageForm">
        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Content Tab -->
            <div class="tab-pane active" id="content-pane">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="title">Title <span class="required">*</span></label>
                            <input type="text" id="title" name="title"
                                value="<?= isset($page) ? htmlspecialchars($page['title']) : '' ?>"
                                required maxlength="255">
                        </div>

                        <div class="form-group">
                            <label for="slug">Slug <span class="required">*</span></label>
                            <input type="text" id="slug" name="slug"
                                value="<?= isset($page) ? htmlspecialchars($page['slug']) : '' ?>"
                                required maxlength="255">
                            <div class="form-text">URL-friendly version of the title</div>
                        </div>

                        <div class="form-group">
                            <label for="excerpt">Excerpt</label>
                            <textarea id="excerpt" name="excerpt" rows="3" maxlength="500"><?= isset($page) ? htmlspecialchars($page['excerpt']) : '' ?></textarea>
                            <div class="form-text">Brief description of the page content</div>
                        </div>

                        <div class="form-group">
                            <label for="content">Content</label>
                            <textarea id="content" name="content" rows="15"><?= isset($page) ? htmlspecialchars($page['content']) : '' ?></textarea>
                            <button type="button" class="btn btn-outline-info mt-2" id="openMediaManagerBtn" style="margin-bottom:6px;">
                                <i class="fas fa-photo-video"></i> Open Media Manager
                            </button>
                            <div class="form-text">Browse and select media to insert directly into your content.</div>
                            <!-- Modal for Media Manager -->
                            <div id="mediaManagerModal" style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;flex-direction:row;">
                                <div style="background:#fff;max-width:900px;width:90vw;height:80vh;overflow:auto;position:relative;border-radius:8px;box-shadow:0 4px 32px rgba(0,0,0,0.2);">
                                    <button type="button" id="closeMediaManagerModal" style="position:absolute;top:10px;right:10px;font-size:1.5rem;background:none;border:none;">&times;</button>
                                    <iframe src="/admin/strata-cms/media-library?embed=1" style="width:100%;height:75vh;border:none;border-radius:8px;"></iframe>
                                </div>
                            </div>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    // Cleanup resize handles before form submit
                                    var form = document.querySelector('form');
                                    if (form) {
                                        form.addEventListener('submit', function(e) {
                                            var editor = document.querySelector('.rich-editor-content');
                                            var textarea = document.getElementById('content');
                                            if (editor) {
                                                var handles = editor.querySelectorAll('.resize-handle');
                                                handles.forEach(function(h) {
                                                    h.remove();
                                                });
                                                if (textarea) textarea.value = editor.innerHTML;
                                            }
                                        });
                                    }
                                    // Modal open/close logic
                                    var mediaModal = document.getElementById('mediaManagerModal');
                                    var openBtn = document.getElementById('openMediaManagerBtn');
                                    var closeBtn = document.getElementById('closeMediaManagerModal');
                                    if (openBtn && mediaModal) {
                                        openBtn.onclick = function() {
                                            mediaModal.style.display = 'flex';
                                            mediaModal.style.alignItems = 'center';
                                            mediaModal.style.justifyContent = 'center';
                                            mediaModal.style.flexDirection = 'row';
                                        };
                                    }
                                    if (closeBtn && mediaModal) {
                                        closeBtn.onclick = function() {
                                            mediaModal.style.display = 'none';
                                        };
                                    }
                                    // Listen for messages from iframe (media selection)
                                    window.addEventListener('message', function(event) {
                                        if (event.origin !== window.location.origin) return;
                                        if (event.data && event.data.mediaUrl) {
                                            var url = event.data.mediaUrl;
                                            var tag = url.match(/\.(jpg|jpeg|png|gif|webp|svg)$/i) ? '<img src="' + url + '" alt="" />' : url;
                                            // Insert into rich text editor at cursor
                                            var editor = document.querySelector('.rich-editor-content');
                                            if (editor && editor.isContentEditable) {
                                                // Insert HTML at cursor
                                                var sel = window.getSelection();
                                                if (sel && sel.rangeCount > 0 && editor.contains(sel.anchorNode)) {
                                                    var range = sel.getRangeAt(0);
                                                    var el = document.createElement('span');
                                                    el.innerHTML = tag;
                                                    var frag = document.createDocumentFragment(),
                                                        node, lastNode;
                                                    while ((node = el.firstChild)) {
                                                        lastNode = frag.appendChild(node);
                                                    }
                                                    range.deleteContents();
                                                    range.insertNode(frag);
                                                    // Move cursor after inserted node
                                                    if (lastNode) {
                                                        range.setStartAfter(lastNode);
                                                        range.collapse(true);
                                                        sel.removeAllRanges();
                                                        sel.addRange(range);
                                                    }
                                                } else {
                                                    // Fallback: append to end
                                                    editor.innerHTML += tag;
                                                }
                                                // Sync textarea
                                                var textarea = document.getElementById('content');
                                                if (editor) {
                                                    var handles = editor.querySelectorAll('.resize-handle');
                                                    handles.forEach(function(h) {
                                                        h.remove();
                                                    });
                                                }
                                                if (textarea) textarea.value = editor.innerHTML;
                                            } else {
                                                // Fallback: insert into textarea
                                                var textarea = document.getElementById('content');
                                                if (textarea) {
                                                    var start = textarea.selectionStart,
                                                        end = textarea.selectionEnd;
                                                    var before = textarea.value.substring(0, start),
                                                        after = textarea.value.substring(end);
                                                    textarea.value = before + tag + after;
                                                    textarea.selectionStart = textarea.selectionEnd = before.length + tag.length;
                                                    textarea.focus();
                                                }
                                            }
                                            // Cleanup: remove any leftover resize handles
                                            if (editor) {
                                                var handles = editor.querySelectorAll('.resize-handle');
                                                handles.forEach(function(h) {
                                                    h.remove();
                                                });
                                                // Sync textarea again after cleanup
                                                if (textarea) textarea.value = editor.innerHTML;
                                            }
                                            mediaModal.style.display = 'none';
                                        }
                                    });
                                });
                            </script>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h6>Publishing</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select id="status" name="status">
                                        <option value="draft" <?= (isset($page) && $page['status'] === 'draft') ? 'selected' : '' ?>>Draft</option>
                                        <option value="published" <?= (isset($page) && $page['status'] === 'published') ? 'selected' : '' ?>>Published</option>
                                        <option value="private" <?= (isset($page) && $page['status'] === 'private') ? 'selected' : '' ?>>Private</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="site_id">Site <span class="required">*</span></label>
                                    <select id="site_id" name="site_id" required>
                                        <option value="">-- Select Site --</option>
                                        <?php if (isset($sites) && is_array($sites)) : ?>
                                            <?php foreach ($sites as $site) : ?>
                                                <option value="<?= htmlspecialchars($site['id']) ?>" <?= (isset($page) && isset($page['site_id']) && $page['site_id'] == $site['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($site['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="form-text">Assign this page to a site.</div>
                                </div>

                                <div class="form-group">
                                    <!-- Template dropdown removed: not used in this CMS -->
                                </div>

                                <div class="form-group">
                                    <label for="parent_id">Parent Page</label>
                                    <select id="parent_id" name="parent_id">
                                        <option value="">-- None (Top Level) --</option>
                                        <?php
                                        // Helper to build a flat list with indentation for hierarchy
                                        function renderParentOptions($pages, $currentId = null, $parentId = null, $level = 0, $excludeIds = [])
                                        {
                                            foreach ($pages as $p) {
                                                if ($p['id'] == $currentId || in_array($p['id'], $excludeIds)) {
                                                    continue;
                                                }
                                                if ($p['parent_id'] != $parentId) {
                                                    continue;
                                                }
                                                $indent = str_repeat('&nbsp;&nbsp;&nbsp;', $level);
                                                $selected = (isset($page) && isset($page['parent_id']) && $page['parent_id'] == $p['id']) ? 'selected' : '';
                                                echo '<option value="' . htmlspecialchars($p['id']) . '" ' . $selected . '>' . $indent . htmlspecialchars($p['title']) . '</option>';
                                                // Prevent circular reference by adding this id to excludeIds
                                                renderParentOptions($pages, $currentId, $p['id'], $level + 1, array_merge($excludeIds, [$currentId]));
                                            }
                                        }
                                        if (isset($allPages)) {
                                            renderParentOptions($allPages, isset($page['id']) ? $page['id'] : null);
                                        }
                                        ?>
                                    </select>
                                    <div class="form-text">Select a parent page to nest this page under another. Leave blank for top-level.</div>
                                </div>
                                <div class="form-group">
                                    <label for="menu_order">Menu Order</label>
                                    <input type="number" id="menu_order" name="menu_order"
                                        value="<?= isset($page) ? htmlspecialchars($page['menu_order']) : '0' ?>"
                                        min="0" max="999">
                                    <div class="form-text">Order in navigation menu (0 = hidden)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO & Social Tab -->
            <div class="tab-pane" id="seo-pane">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6>Search Engine Optimization</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="meta_title">Meta Title</label>
                                    <input type="text" id="meta_title" name="meta_title"
                                        value="<?= isset($page) ? htmlspecialchars($page['meta_title'] ?? '') : '' ?>"
                                        maxlength="60">
                                    <div class="form-text">Recommended: 50-60 characters</div>
                                </div>

                                <div class="form-group">
                                    <label for="meta_description">Meta Description</label>
                                    <textarea id="meta_description" name="meta_description"
                                        rows="3" maxlength="160"><?= isset($page) ? htmlspecialchars($page['meta_description'] ?? '') : '' ?></textarea>
                                    <div class="form-text">Recommended: 150-160 characters</div>
                                </div>

                                <div class="form-group">
                                    <label for="canonical_url">Canonical URL</label>
                                    <input type="url" id="canonical_url" name="canonical_url"
                                        value="<?= isset($page) ? htmlspecialchars($page['canonical_url'] ?? '') : '' ?>"
                                        placeholder="https://example.com/page">
                                    <div class="form-text">Leave empty to use page URL</div>
                                </div>

                                <div class="form-check">
                                    <input type="checkbox" id="noindex" name="noindex" value="1"
                                        <?= (isset($page) && ($page['noindex'] ?? 0)) ? 'checked' : '' ?>>
                                    <label for="noindex">No Index (hide from search engines)</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6>Social Media</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="og_type">Open Graph Type</label>
                                    <select id="og_type" name="og_type">
                                        <option value="">Select type...</option>
                                        <option value="website" <?= (isset($page) && ($page['og_type'] ?? '') === 'website') ? 'selected' : '' ?>>Website</option>
                                        <option value="article" <?= (isset($page) && ($page['og_type'] ?? '') === 'article') ? 'selected' : '' ?>>Article</option>
                                        <option value="product" <?= (isset($page) && ($page['og_type'] ?? '') === 'product') ? 'selected' : '' ?>>Product</option>
                                        <option value="profile" <?= (isset($page) && ($page['og_type'] ?? '') === 'profile') ? 'selected' : '' ?>>Profile</option>
                                    </select>
                                    <div class="form-text">For Facebook, LinkedIn sharing</div>
                                </div>

                                <div class="form-group">
                                    <label for="og_image">Open Graph Image</label>
                                    <div class="image-upload-container">
                                        <input type="url" id="og_image" name="og_image"
                                            value="<?= isset($page) ? htmlspecialchars($page['og_image'] ?? '') : '' ?>"
                                            placeholder="https://example.com/image.jpg">
                                        <div class="upload-controls">
                                            <input type="file" id="og_image_file" accept="image/*" style="display: none;">
                                            <button type="button" onclick="uploadOgImageButtonClick(event); return false;">Upload Image</button>
                                            <button type="button" onclick="clearOgImage()">Clear</button>
                                        </div>
                                        <div id="og_image_preview" class="image-preview">
                                            <?php if (!empty($page['og_image'])) : ?>
                                                <img src="<?= htmlspecialchars($page['og_image']) ?>" alt="OG Image Preview">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="form-text">Recommended: 1200x630px. You can upload an image or enter a URL manually.</div>
                                </div>

                                <div class="form-group">
                                    <label for="twitter_card">Twitter Card Type</label>
                                    <select id="twitter_card" name="twitter_card">
                                        <option value="">Select type...</option>
                                        <option value="summary" <?= (isset($page) && ($page['twitter_card'] ?? '') === 'summary') ? 'selected' : '' ?>>Summary</option>
                                        <option value="summary_large_image" <?= (isset($page) && ($page['twitter_card'] ?? '') === 'summary_large_image') ? 'selected' : '' ?>>Summary Large Image</option>
                                        <option value="app" <?= (isset($page) && ($page['twitter_card'] ?? '') === 'app') ? 'selected' : '' ?>>App</option>
                                        <option value="player" <?= (isset($page) && ($page['twitter_card'] ?? '') === 'player') ? 'selected' : '' ?>>Player</option>
                                    </select>
                                    <div class="form-text">For Twitter sharing</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Tab -->
            <div class="tab-pane" id="settings-pane">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6>Advanced Settings</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Page ID</label>
                                    <input type="text" value="<?= isset($page) ? $page['id'] : 'Auto-generated' ?>" readonly>
                                    <div class="form-text">Unique identifier for this page</div>
                                </div>

                                <div class="form-group">
                                    <label>Created</label>
                                    <input type="text"
                                        value="<?= isset($page) ? date('M j, Y g:i A', strtotime($page['created_at'])) : 'Not yet created' ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Last Modified</label>
                                    <input type="text"
                                        value="<?= isset($page) ? date('M j, Y g:i A', strtotime($page['updated_at'])) : 'Not yet created' ?>" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6>Preview</h6>
                            </div>
                            <div class="card-body">
                                <?php if (isset($page)) : ?>
                                    <div class="form-group">
                                        <label>Page URL</label>
                                        <div class="input-group">
                                            <input type="text" value="/<?= htmlspecialchars($page['slug']) ?>" readonly>
                                            <a href="/<?= htmlspecialchars($page['slug']) ?>" target="_blank" class="btn btn-outline">View</a>
                                        </div>
                                    </div>
                                <?php else : ?>
                                    <p style="color: #666;">Save the page to see preview options</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-success">
                <?= $isEdit ? 'Update Page' : 'Create Page' ?>
            </button>
            <a href="/admin/strata-cms/pages" class="btn btn-secondary">Cancel</a>
            <?php if (isset($page)) : ?>
                <a href="/<?= htmlspecialchars($page['slug']) ?>" target="_blank" class="btn btn-outline" style="float: right;">Preview Page</a>
            <?php endif; ?>
        </div>
    </form>
</div>
</div>


<!-- Simple Rich Text Editor (Self-hosted) -->
<script src="/modules/StrataCms/assets/js/page_form.js"></script>
<?php require __DIR__ . '/../partials/admin_footer.php'; ?>