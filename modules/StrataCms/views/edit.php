<?php
$title = $data['title'] ?? 'Create Cms';
$showNav = true;
require __DIR__ . '/../../../views/partials/header.php';
?>

<section class="py-5">
    <div class="container px-5">
        <div class="row gx-5 justify-content-center">
            <div class="col-lg-8">
                <h1 class="fw-bolder mb-4">Create Cms</h1>
                
                <form method="post" action="/cms/create">
                    <div class="form-floating mb-3">
                        <input class="form-control" id="title" name="title" type="text" value="<?= htmlspecialchars($data['item']['title']) ?>" required>
                        <label for="title">Title</label>
                    </div>

                    <div class="form-floating mb-3">
                        <textarea class="form-control" id="content" name="content" style="height: 200px" required><?= htmlspecialchars($data['item']['content']) ?></textarea>
                        <label for="content">Content</label>
                    </div>

                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-info" id="openMediaManagerBtn" style="margin-bottom:6px;">
                            <i class="bi bi-images"></i> Open Media Manager
                        </button>
                        <div class="form-text">Browse and select media to insert directly into your content.</div>
                        <!-- Modal for Media Manager -->
                        <div id="mediaManagerModal" style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;flex-direction:row;">
                            <div style="background:#fff;max-width:900px;width:90vw;height:80vh;overflow:auto;position:relative;border-radius:8px;box-shadow:0 4px 32px rgba(0,0,0,0.2);">
                                <button type="button" id="closeMediaManagerModal" style="position:absolute;top:10px;right:10px;font-size:1.5rem;background:none;border:none;">&times;</button>
                                <iframe src="/admin/media/media-library?embed=1" style="width:100%;height:75vh;border:none;border-radius:8px;"></iframe>
                            </div>
                        </div>
                        <script>
                        // Modal open/close logic
                        var mediaModal = document.getElementById('mediaManagerModal');
                        document.getElementById('openMediaManagerBtn').onclick = function() {
                            mediaModal.style.display = 'flex';
                            // Ensure flex is always set for centering
                            mediaModal.style.alignItems = 'center';
                            mediaModal.style.justifyContent = 'center';
                            mediaModal.style.flexDirection = 'row';
                        };
                        document.getElementById('closeMediaManagerModal').onclick = function() {
                            mediaModal.style.display = 'none';
                        };
                        // Listen for messages from iframe (media selection)
                        window.addEventListener('message', function(event) {
                            // Only accept messages from our own origin
                            if (event.origin !== window.location.origin) return;
                            if (event.data && event.data.mediaUrl) {
                                // Insert media URL at cursor position in textarea
                                var textarea = document.getElementById('content');
                                if (textarea) {
                                    var url = event.data.mediaUrl;
                                    var tag = url.match(/\.(jpg|jpeg|png|gif|webp|svg)$/i) ? '<img src="'+url+'" alt="" />' : url;
                                    // Insert at cursor
                                    var start = textarea.selectionStart, end = textarea.selectionEnd;
                                    var before = textarea.value.substring(0, start), after = textarea.value.substring(end);
                                    textarea.value = before + tag + after;
                                    // Move cursor after inserted tag
                                    textarea.selectionStart = textarea.selectionEnd = before.length + tag.length;
                                    textarea.focus();
                                }
                                document.getElementById('mediaManagerModal').style.display = 'none';
                            }
                        });
                        </script>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="/cms" class="btn btn-secondary">Cancel</a>
                        <button class="btn btn-primary" type="submit">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../../../views/partials/footer.php'; ?>