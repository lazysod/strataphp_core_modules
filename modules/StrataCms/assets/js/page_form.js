// Alignment logic for selected image
function alignSelectedImage(alignment)
{
    if (!window.richEditor) {
        return;
    }
    let selected = document.querySelector(".editor-image-wrapper.selected");
    if (!selected) {
        return;
    }
    const img = selected.querySelector("img");
    if (!img) {
        return;
    }
  // Remove all Bootstrap alignment classes and reset display
    img.classList.remove("float-start", "float-end", "mx-auto", "d-block");
    selected.classList.remove("text-center", "text-start", "text-end");
  // For left/right, REMOVE wrapper and float the image directly
    if (alignment === "left" || alignment === "right") {
      // If image is wrapped, unwrap it
        if (selected && selected.classList.contains("editor-image-wrapper")) {
            selected.replaceWith(img);
        }
      // Remove all float/alignment classes from image
        img.classList.remove("float-start", "float-end", "mx-auto", "d-block");
      // Apply float class
        if (alignment === "left") {
            img.classList.add("float-start");
        } else {
            img.classList.add("float-end");
        }
      // Place image directly in the parent element (no wrapper)
      // No wrapper for left/right
    } else if (alignment === "center") {
        if (selected.tagName !== "DIV") {
            const div = document.createElement("div");
            div.className = selected.className;
            div.classList.add("editor-image-wrapper");
            div.contentEditable = "false";
            div.appendChild(img);
            selected.replaceWith(div);
            selected = div;
        }
        selected.style.display = "block";
    } else {
      // For inline, use span
        if (selected.tagName !== "SPAN") {
            const span = document.createElement("span");
            span.className = selected.className;
            span.classList.add("editor-image-wrapper");
            span.contentEditable = "false";
            span.appendChild(img);
            selected.replaceWith(span);
            selected = span;
        }
        selected.style.display = "";
    }
  // Remove all float and alignment classes from image
    img.classList.remove("float-start", "float-end", "mx-auto", "d-block");
  // Apply new alignment
    switch (alignment) {
        case "left":
            selected.classList.remove("text-center", "text-end");
            selected.classList.add("text-start");
            img.classList.add("float-start");
        break;
        case "center":
            selected.classList.remove("text-start", "text-end");
            selected.classList.add("text-center");
            img.classList.add("mx-auto", "d-block");
        break;
        case "right":
            selected.classList.remove("text-center", "text-start");
            selected.classList.add("text-end");
            img.classList.add("float-end");
        break;
        default:
          // inline, no extra classes
        break;
    }
    window.richEditor.textarea.value = window.richEditor.element.innerHTML;
}

// Simple Rich Text Editor Implementation
function initializeRichTextEditor()
{
    const textarea = document.getElementById("content");
    if (!textarea) {
        console.error("Content textarea not found");
        return;
    }

  // Create editor container
    const editorContainer = document.createElement("div");
    editorContainer.className = "rich-editor-container";

  // Create toolbar
    const toolbar = document.createElement("div");
    toolbar.className = "rich-editor-toolbar";

  // Add Media Manager button to the toolbar
    const toolbarHtml = `
                <button type="button" data-cmd="bold" title="Bold"><b>B</b></button>
                <button type="button" data-cmd="italic" title="Italic"><i>I</i></button>
                <button type="button" data-cmd="underline" title="Underline"><u>U</u></button>
                <button type="button" data-cmd="formatBlock" data-value="h2" title="Heading 2">H2</button>
                <button type="button" data-cmd="formatBlock" data-value="h3" title="Heading 3">H3</button>
                <button type="button" data-cmd="formatBlock" data-value="p" title="Paragraph">P</button>
                <button type="button" data-cmd="insertUnorderedList" title="Bullet List">• List</button>
                <button type="button" data-cmd="insertOrderedList" title="Numbered List">1. List</button>
                <button type="button" data-cmd="createLink" title="Link">🔗</button>
                <button type="button" id="insertImageBtn" title="Insert Image">📷</button>
                <button type="button" id="mediaManagerBtn" title="Open Media Manager">🖼️</button>
                <button type="button" id="alignLeftBtn" title="Align Left">⬅️</button>
                <button type="button" id="alignCenterBtn" title="Align Center">↔️</button>
                <button type="button" id="alignRightBtn" title="Align Right">➡️</button>
                <button type="button" data-cmd="removeFormat" title="Clear Format">Clear</button>
                <button type="button" id="toggleSourceBtn" title="HTML Source">&lt;&gt;</button>
            `;
    toolbar.innerHTML = toolbarHtml;

  // Add event listener for Media Manager button
    toolbar
    .querySelector("#mediaManagerBtn")
    .addEventListener("click", function () {
        window.open("/admin/media/media-library", "_blank", "noopener");
    });

  // Add event listeners for toolbar buttons
    Array.from(toolbar.querySelectorAll("button[data-cmd]")).forEach((btn) => {
        btn.addEventListener("click", function () {
            const cmd = btn.getAttribute("data-cmd");
            const value = btn.getAttribute("data-value") || null;
            execCmd(cmd, value);
        });
    });

    toolbar
    .querySelector("#insertImageBtn")
    .addEventListener("click", insertImage);
    toolbar.querySelector("#alignLeftBtn").addEventListener("click", function () {
        alignSelectedImage("left");
    });
    toolbar
    .querySelector("#alignCenterBtn")
    .addEventListener("click", function () {
        alignSelectedImage("center");
    });
    toolbar
    .querySelector("#alignRightBtn")
    .addEventListener("click", function () {
        alignSelectedImage("right");
    });
    toolbar
    .querySelector("#toggleSourceBtn")
    .addEventListener("click", toggleSource);

  // Create editor div (instead of iframe)
    const editor = document.createElement("div");
    editor.className = "rich-editor-content";
    editor.contentEditable = true;
    editor.style.width = "100%";
    editor.style.height = "400px";
    editor.style.border = "1px solid #ddd";
    editor.style.borderTop = "none";
    editor.style.padding = "10px";
    editor.style.fontSize = "14px";
    editor.style.fontFamily =
    '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif';
    editor.style.lineHeight = "1.6";
    editor.style.backgroundColor = "white";
    editor.style.overflowY = "auto";
    editor.style.outline = "none";

  // Set initial content
    editor.innerHTML =
    textarea.value || "<p>Start writing your content here...</p>";

  // Hide original textarea
    textarea.style.display = "none";

  // Insert editor before textarea
    textarea.parentNode.insertBefore(editorContainer, textarea);
    editorContainer.appendChild(toolbar);
    editorContainer.appendChild(editor);

  // Update textarea when content changes
    editor.addEventListener("input", function () {
        textarea.value = editor.innerHTML;
    });

    editor.addEventListener("blur", function () {
        textarea.value = editor.innerHTML;
    });

  // Handle paste events
    editor.addEventListener("paste", function (e) {
        e.preventDefault();
        const text = e.clipboardData.getData("text/plain");
        document.execCommand("insertText", false, text);
        textarea.value = editor.innerHTML;
    });

  // Add drag and drop functionality
    editor.addEventListener("dragover", function (e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = "copy";
        editor.classList.add("drag-over");
    });

    editor.addEventListener("dragleave", function (e) {
        e.preventDefault();
        editor.classList.remove("drag-over");
    });

    editor.addEventListener("drop", function (e) {
        e.preventDefault();
        editor.classList.remove("drag-over");

        const files = Array.from(e.dataTransfer.files);
        const imageFiles = files.filter((file) => file.type.startsWith("image/"));

        if (imageFiles.length === 0) {
            alert("Please drop image files only.");
            return;
        }

      // Save current position for insertion
        const range = document.createRange();
        const selection = window.getSelection();

      // Try to get the position where the drop occurred
        if (document.caretPositionFromPoint) {
            const caretPos = document.caretPositionFromPoint(e.clientX, e.clientY);
            if (caretPos) {
                range.setStart(caretPos.offsetNode, caretPos.offset);
                range.collapse(true);
            }
        } else if (document.caretRangeFromPoint) {
            const caretRange = document.caretRangeFromPoint(e.clientX, e.clientY);
            if (caretRange) {
                range.setStart(caretRange.startContainer, caretRange.startOffset);
                range.collapse(true);
            }
        }

        selection.removeAllRanges();
        selection.addRange(range);

      // Upload each image
        imageFiles.forEach((file) => {
            if (file.size > 5 * 1024 * 1024) {
                alert(`File ${file.name} is too large. Maximum size is 5MB.`);
                return;
            }
            uploadImageForEditor(file, range);
        });
    });

  // Store reference for later use
    window.richEditor = {
        element: editor,
        textarea: textarea,
        sourceMode: false,
    };


  // Focus the editor
    setTimeout(() => {
        editor.focus();
    }, 100);
}

// Editor command functions
function execCmd(cmd, value = null)
{
    console.trace(); // This will show us the call stack

    if (window.richEditor && !window.richEditor.sourceMode) {
        if (cmd === "createLink") {
            console.log("createLink command detected - this is what causes the URL prompt");
            value = prompt("Enter URL:");
            if (!value) {
                return;
            }
        }

      // Focus the editor first
        window.richEditor.element.focus();

      // Execute the command
        document.execCommand(cmd, false, value);

      // Update textarea
        window.richEditor.textarea.value = window.richEditor.element.innerHTML;
    }
}

function insertImage()
{
    if (!window.richEditor || window.richEditor.sourceMode) {
        return;
    }

  // Create file input
    const input = document.createElement("input");
    input.type = "file";
    input.accept = "image/*";
    input.style.display = "none";

    input.addEventListener("change", function (e) {
        const file = e.target.files[0];
        if (!file) {
            return;
        }

      // Validate file
        if (!file.type.startsWith("image/")) {
            alert("Please select an image file.");
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            alert("File size must be less than 5MB.");
            return;
        }

      // Save current selection
        const selection = window.getSelection();
        const range = selection.rangeCount > 0 ? selection.getRangeAt(0) : null;

      // Upload image
        uploadImageForEditor(file, range);
    });

    document.body.appendChild(input);
    input.click();
    document.body.removeChild(input);
}

function uploadImageForEditor(file, range)
{
    const formData = new FormData();
    formData.append("image", file);

  // Show loading in editor
    if (range) {
        const loadingSpan = document.createElement("span");
        loadingSpan.textContent = "Uploading image...";
        loadingSpan.style.color = "#666";
        loadingSpan.style.fontStyle = "italic";

        range.deleteContents();
        range.insertNode(loadingSpan);

        window.getSelection().removeAllRanges();
    }

    fetch("/admin/media/upload/image", {
        method: "POST",
        body: formData,
        credentials: "same-origin",
    })
    .then(async(response) => {
        const text = await response.text();
        try {
            const data = JSON.parse(text);
            if (data.success) {
              // Create image wrapper as inline span by default
                const imageWrapper = document.createElement("span");
                imageWrapper.className = "editor-image-wrapper";
                imageWrapper.contentEditable = "false";
              // Create image element with Bootstrap classes
                const img = document.createElement("img");
                img.src = data.url;
                img.alt = "Uploaded image";
                img.className = "editor-image img-fluid";
                img.style.maxWidth = "100%";
                img.style.height = "auto";
              // Add click handler for selection
                img.addEventListener("click", function (e) {
                    e.preventDefault();
                    selectImage(imageWrapper);
                });
                imageWrapper.appendChild(img);
              // Replace loading text with image
                if (range) {
                      const loadingSpan = window.richEditor.element.querySelector("span");
                    if (
                        loadingSpan &&
                        loadingSpan.textContent === "Uploading image..."
                      ) {
                          loadingSpan.replaceWith(imageWrapper);
                    } else {
                  // Fallback: append to end
                        window.richEditor.element.appendChild(imageWrapper);
                    }
                } else {
                    window.richEditor.element.appendChild(imageWrapper);
                }
              // Update textarea
                window.richEditor.textarea.value =
                window.richEditor.element.innerHTML;
            } else {
              // Remove loading text
                const loadingSpan = window.richEditor.element.querySelector("span");
                if (loadingSpan && loadingSpan.textContent === "Uploading image...") {
                    loadingSpan.remove();
                }
                alert("Upload failed: " + data.error);
            }
        } catch (e) {
          // Remove loading text
            const loadingSpan = window.richEditor.element.querySelector("span");
            if (loadingSpan && loadingSpan.textContent === "Uploading image...") {
                loadingSpan.remove();
            }
            alert(
                "Upload failed: Server returned non-JSON response. See console for details."
            );
            console.error("Upload response (not JSON):", text);
        }
    })
    .catch((error) => {
      // Remove loading text
        const loadingSpan = window.richEditor.element.querySelector("span");
        if (loadingSpan && loadingSpan.textContent === "Uploading image...") {
            loadingSpan.remove();
        }
        alert("Upload failed: " + error.message);
        console.error("Upload error:", error);
    });
}

function toggleSource()
{
    if (!window.richEditor) {
        return;
    }

    const editor = window.richEditor;
    const button = event.target;

    if (editor.sourceMode) {
      // Switch back to visual mode
        const textarea = document.querySelector(".source-textarea");
        editor.element.innerHTML = textarea.value;
        editor.textarea.value = textarea.value;
        textarea.remove();
        editor.element.style.display = "block";
        button.textContent = "<>";
        button.title = "HTML Source";
        editor.sourceMode = false;
        editor.element.focus();
    } else {
      // Switch to source mode
        const sourceTextarea = document.createElement("textarea");
        sourceTextarea.className = "source-textarea";
        sourceTextarea.style.width = "100%";
        sourceTextarea.style.height = "400px";
        sourceTextarea.style.border = "1px solid #ddd";
        sourceTextarea.style.borderTop = "none";
        sourceTextarea.style.fontFamily = "Monaco, Consolas, monospace";
        sourceTextarea.style.fontSize = "12px";
        sourceTextarea.style.padding = "10px";
        sourceTextarea.style.resize = "vertical";
        sourceTextarea.value = editor.element.innerHTML;

        sourceTextarea.addEventListener("input", function () {
            editor.textarea.value = this.value;
        });

        editor.element.style.display = "none";
        editor.element.parentNode.insertBefore(
            sourceTextarea,
            editor.element.nextSibling
        );
        button.textContent = "Visual";
        button.title = "Visual Editor";
        editor.sourceMode = true;
        sourceTextarea.focus();
    }
}

// Tab functionality
document.addEventListener("DOMContentLoaded", function () {

  // Initialize rich text editor
    initializeRichTextEditor();

    const tabButtons = document.querySelectorAll(".tab-button");
    const tabPanes = document.querySelectorAll(".tab-pane");


    tabButtons.forEach((button, index) => {
        button.addEventListener("click", function () {
            const targetId = this.getAttribute("data-target");

          // Remove active class from all buttons and panes
            tabButtons.forEach((btn) => btn.classList.remove("active"));
            tabPanes.forEach((pane) => pane.classList.remove("active"));

          // Add active class to clicked button and corresponding pane
            this.classList.add("active");
            const targetPane = document.getElementById(targetId);
            if (targetPane) {
                targetPane.classList.add("active");
            } else {
                console.error("Target pane not found:", targetId);
            }

          // Store active tab
            localStorage.setItem("pageFormActiveTab", targetId);
        });
    });

  // Restore active tab
    const activeTab = localStorage.getItem("pageFormActiveTab");
    if (activeTab && activeTab !== "content-pane") {
        const targetButton = document.querySelector(`[data-target="${activeTab}"]`);
        if (targetButton) {
            targetButton.click();
        }
    }
});

// Auto-generate slug from title
document.getElementById("title").addEventListener("input", function () {
    const title = this.value;
    const slug = title
    .toLowerCase()
    .replace(/[^\w\s\-]/g, "") // Remove special characters (escaped hyphen)
    .replace(/\s+/g, "-") // Replace spaces with hyphens
    .replace(/-+/g, "-") // Replace multiple hyphens with single
    .trim();
    document.getElementById("slug").value = slug;
});

// Character counters for meta fields
function updateCharacterCount(inputId, maxLength)
{
    const input = document.getElementById(inputId);
    const helpText = input.nextElementSibling;

    input.addEventListener("input", function () {
        const currentLength = this.value.length;
        const remaining = maxLength - currentLength;

        if (helpText && helpText.classList.contains("form-text")) {
            helpText.textContent = `${currentLength}/${maxLength} characters`;

            if (remaining < 10) {
                helpText.className = "form-text text-warning";
            } else if (remaining < 0) {
                helpText.className = "form-text text-danger";
            } else {
                helpText.className = "form-text text-muted";
            }
        }
    });
}

// Initialize character counters
updateCharacterCount("meta_title", 60);
updateCharacterCount("meta_description", 160);

// Form submission handling
document.getElementById("pageForm").addEventListener("submit", function (e) {
  // Ensure rich editor content is saved to textarea before submission
    if (window.richEditor && !window.richEditor.sourceMode) {
        window.richEditor.textarea.value = window.richEditor.element.innerHTML;
    } else if (window.richEditor && window.richEditor.sourceMode) {
        const sourceTextarea = document.querySelector(".source-textarea");
        if (sourceTextarea) {
            window.richEditor.textarea.value = sourceTextarea.value;
        }
    }


  // Log all form data
    const formData = new FormData(this);
    for (let [key, value] of formData.entries()) {
    }
});

// Simple OG image upload handler
function uploadOgImageButtonClick(event)
{
  // Prevent any default action or event bubbling
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    const fileInput = document.getElementById("og_image_file");
    if (fileInput) {
        fileInput.click();
    } else {
        console.error("File input not found");
    }

    return false; // Prevent any form submission or other actions
}

// Image Upload Functionality
document.addEventListener("DOMContentLoaded", function () {
    const ogImageFileInput = document.getElementById("og_image_file");
    if (ogImageFileInput) {
        ogImageFileInput.addEventListener("change", function (e) {
            const file = e.target.files[0];
            if (!file) {
                return;
            }

          // Validate file type
            if (!file.type.startsWith("image/")) {
                alert("Please select an image file.");
                return;
            }

          // Validate file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                alert("File size must be less than 5MB.");
                return;
            }

            uploadOgImage(file);
        });
    } else {
        console.error("og_image_file input not found");
    }
});

function uploadOgImage(file)
{
    const formData = new FormData();
    formData.append("image", file);

  // Show upload progress
    const preview = document.getElementById("og_image_preview");
    preview.innerHTML = '<div class="upload-progress">Uploading...</div>';

    fetch("/admin/media/upload/image", {
        method: "POST",
        body: formData,
        credentials: "same-origin",
    })
    .then((response) => {
        return response.text();
    })
    .then((text) => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
              // Update the URL field with the full-size image
                document.getElementById("og_image").value = data.url;

              // Show preview using thumbnail (better for admin UI)
                preview.innerHTML =
                '<img src="' + data.thumbnail + '" alt="OG Image Preview">';
            } else {
                preview.innerHTML = "";
                alert("Upload failed: " + data.error);
            }
        } catch (e) {
            preview.innerHTML = "";
            console.error("JSON parse error:", e);
            console.error("Server returned:", text);
            alert(
                "Upload failed: Server returned invalid response. Check console for details."
            );
        }
    })
    .catch((error) => {
        preview.innerHTML = "";
        alert("Upload failed: " + error.message);
        console.error("OG Upload error:", error);
    });
}

function clearOgImage()
{
    document.getElementById("og_image").value = "";
    document.getElementById("og_image_preview").innerHTML = "";
    document.getElementById("og_image_file").value = "";
}

// Fallback: define addResizeHandles if not present
if (typeof addResizeHandles !== "function") {
    function addResizeHandles(wrapper, img)
    {
      // Remove existing handles
        wrapper.querySelectorAll(".resize-handle").forEach((h) => h.remove());
      // Only add if not already resizing
        const handles = ["se", "sw", "ne", "nw"];
        handles.forEach((dir) => {
            const handle = document.createElement("span");
            handle.className = "resize-handle resize-" + dir;
            handle.dataset.dir = dir;
            handle.style.position = "absolute";
            handle.style.width = "12px";
            handle.style.height = "12px";
            handle.style.background = "#fff";
            handle.style.border = "2px solid #3498db";
            handle.style.borderRadius = "50%";
            handle.style.boxShadow = "0 1px 4px rgba(52,152,219,0.15)";
            handle.style.zIndex = "10000";
            handle.style.cursor = dir + "-resize";
            handle.style.userSelect = "none";
            handle.style.display = "block";
          // Position handle
            if (dir === "se") {
                handle.style.right = "-6px";
                handle.style.bottom = "-6px";
            }
            if (dir === "sw") {
                handle.style.left = "-6px";
                handle.style.bottom = "-6px";
            }
            if (dir === "ne") {
                handle.style.right = "-6px";
                handle.style.top = "-6px";
            }
            if (dir === "nw") {
                handle.style.left = "-6px";
                handle.style.top = "-6px";
            }
            handle.addEventListener("mousedown", function (e) {
                e.preventDefault();
                startImageResize(e, wrapper, img, dir);
            });
        wrapper.style.position = "relative";
        wrapper.appendChild(handle);
        });
    }
}

// Fallback: define startImageResize if not present
if (typeof startImageResize !== "function") {
    function startImageResize(e, wrapper, img, dir)
    {
        e.preventDefault();
        e.stopPropagation();
        const startX = e.clientX;
        const startY = e.clientY;
        const startWidth = img.offsetWidth;
        const startHeight = img.offsetHeight;
        const aspect = startWidth / startHeight;
        function onMove(ev)
        {
            let dx = ev.clientX - startX;
            let dy = ev.clientY - startY;
            let newWidth = startWidth,
            newHeight = startHeight;
            if (dir === "se") {
                newWidth = startWidth + dx;
                newHeight = newWidth / aspect;
            } else if (dir === "sw") {
                newWidth = startWidth - dx;
                newHeight = newWidth / aspect;
            } else if (dir === "ne") {
                newWidth = startWidth + dx;
                newHeight = newWidth / aspect;
            } else if (dir === "nw") {
                newWidth = startWidth - dx;
                newHeight = newWidth / aspect;
            }
            if (newWidth < 32) {
                newWidth = 32;
                newHeight = newWidth / aspect;
            }
            img.style.width = newWidth + "px";
            img.style.height = newHeight + "px";
        }
        function onUp(ev)
        {
            document.removeEventListener("mousemove", onMove);
            document.removeEventListener("mouseup", onUp);
          // Sync textarea after resize
            const editor = document.querySelector(".rich-editor-content");
            const textarea = document.getElementById("content");
            if (editor && textarea) {
                textarea.value = editor.innerHTML;
            }
        }
        document.addEventListener("mousemove", onMove);
        document.addEventListener("mouseup", onUp);
    }
}

// Expose functions to window for toolbar and image handlers
window.execCmd = execCmd;
window.insertImage = insertImage;
window.alignSelectedImage = alignSelectedImage;
window.addResizeHandles = addResizeHandles;
window.startImageResize = startImageResize;
window.selectImage = selectImage;

// Ensure selectImage is always called on image click (delegated)
document.addEventListener("DOMContentLoaded", function () {
    const editor = document.querySelector(".rich-editor-content");
    if (editor) {
        editor.addEventListener("click", function (e) {
            if (
            e.target &&
            e.target.tagName === "IMG" &&
            e.target.classList.contains("editor-image")
            ) {
                const wrapper = e.target.closest(".editor-image-wrapper");
                if (wrapper) {
                    selectImage(wrapper);
                }
            }
        });
    }
});

// Floating image toolbar
(function () {
    const toolbar = document.createElement("div");
    toolbar.id = "image-float-toolbar";
    toolbar.style.position = "absolute";
    toolbar.style.display = "none";
    toolbar.style.zIndex = "9999";
    toolbar.style.background = "#fff";
    toolbar.style.border = "1px solid #3498db";
    toolbar.style.borderRadius = "6px";
    toolbar.style.boxShadow = "0 2px 8px rgba(52,152,219,0.15)";
    toolbar.style.padding = "4px 8px";
    toolbar.style.gap = "4px";
    toolbar.style.alignItems = "center";
    toolbar.style.transition = "opacity 0.15s";
    toolbar.style.fontSize = "15px";
    toolbar.style.userSelect = "none";
    toolbar.innerHTML = `
                <button type="button" class="img-toolbar-btn" data-action="left" title="Align Left">⬅️</button>
                <button type="button" class="img-toolbar-btn" data-action="center" title="Align Center">↔️</button>
                <button type="button" class="img-toolbar-btn" data-action="right" title="Align Right">➡️</button>
                <button type="button" class="img-toolbar-btn" data-action="alt" title="Edit Alt Text">📝</button>
                <button type="button" class="img-toolbar-btn" data-action="delete" title="Remove Image">🗑️</button>
            `;
    document.body.appendChild(toolbar);

  // Toolbar button actions
    toolbar.addEventListener("click", function (e) {
        if (!window._selectedImageWrapper) {
            return;
        }
        const action = e.target.getAttribute("data-action");
        if (!action) {
            return;
        }
        if (action === "left" || action === "center" || action === "right") {
            alignSelectedImage(action);
        } else if (action === "alt") {
            const img = window._selectedImageWrapper.querySelector("img");
            if (img) {
                const newAlt = prompt("Alt text for image:", img.alt || "");
                if (newAlt !== null) {
                    img.alt = newAlt;
                }
            }
        } else if (action === "delete") {
            window._selectedImageWrapper.remove();
            toolbar.style.display = "none";
            window._selectedImageWrapper = null;
            window.richEditor.textarea.value = window.richEditor.element.innerHTML;
        }
        window.richEditor.textarea.value = window.richEditor.element.innerHTML;
    });

  // Hide toolbar on click outside
    document.addEventListener("mousedown", function (e) {
        if (
        !toolbar.contains(e.target) &&
        (!window._selectedImageWrapper ||
        !window._selectedImageWrapper.contains(e.target))
        ) {
            toolbar.style.display = "none";
            if (window._selectedImageWrapper) {
                window._selectedImageWrapper.classList.remove("selected");
            }
            window._selectedImageWrapper = null;
        }
    });

  // Position toolbar above selected image (with debug and fallback)
    window.showImageToolbar = function (wrapper) {
        const toolbar = document.getElementById("image-float-toolbar");
        const rect = wrapper.getBoundingClientRect();
        const editor = document.querySelector(".rich-editor-content");
        const editorRect = editor
        ? editor.getBoundingClientRect()
        : { left: 0, top: 0, width: window.innerWidth };
        let left = rect.left + window.scrollX;
        let top = rect.top + window.scrollY - toolbar.offsetHeight - 8;
      // Clamp toolbar within editor horizontally
        if (left < editorRect.left + window.scrollX) {
            left = editorRect.left + window.scrollX + 8;
        }
        if (
        left + toolbar.offsetWidth >
        editorRect.left + window.scrollX + editorRect.width
        ) {
            left =
            editorRect.left +
            window.scrollX +
            editorRect.width -
            toolbar.offsetWidth -
            8;
        }
      // Clamp toolbar to top of editor if needed
        if (top < editorRect.top + window.scrollY) {
            top = rect.bottom + window.scrollY + 8;
        }
        toolbar.style.left = left + "px";
        toolbar.style.top = top + "px";
        toolbar.style.display = "flex";
        toolbar.style.background = "#fff";
        toolbar.style.border = "2px solid #e67e22";
        toolbar.style.zIndex = "99999";
        toolbar.style.opacity = "1";
        toolbar.style.visibility = "visible";
        toolbar.style.boxShadow = "0 0 10px 2px #e67e22";
        toolbar.style.pointerEvents = "auto";
        window._selectedImageWrapper = wrapper;
    };
})();

// Enhance selectImage to show floating toolbar
function selectImage(wrapper)
{
  // Deselect others
    document
    .querySelectorAll(".editor-image-wrapper.selected")
    .forEach((el) => el.classList.remove("selected"));
    wrapper.classList.add("selected");
  // Show resize handles
    const img = wrapper.querySelector("img");
    if (img) {
        addResizeHandles(wrapper, img);
    }
  // Show floating toolbar
    if (window.showImageToolbar) {
        window.showImageToolbar(wrapper);
    }
}

// Ensure all images are wrapped in .editor-image-wrapper (on load and mutation)
function wrapAllEditorImages()
{
    const editor = document.querySelector(".rich-editor-content");
    if (!editor) {
        return;
    }
    editor.querySelectorAll("img").forEach((img) => {
      // Always add editor-image class
        img.classList.add("editor-image");
        if (!img.closest(".editor-image-wrapper")) {
            const wrapper = document.createElement("span");
            wrapper.className = "editor-image-wrapper";
            wrapper.contentEditable = "false";
            img.parentNode.insertBefore(wrapper, img);
            wrapper.appendChild(img);
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
    const editor = document.querySelector(".rich-editor-content");
    if (editor) {
      // Initial wrap
        wrapAllEditorImages();
      // Observe for DOM changes (e.g. paste, undo, etc)
        const observer = new MutationObserver(() => {
            wrapAllEditorImages();
        });
        observer.observe(editor, { childList: true, subtree: true });
      // Event delegation for image selection
        editor.addEventListener("click", function (e) {
            if (e.button !== 0) {
                return;
            }
            let img = null;
            if (
            e.target.tagName === "IMG" &&
            e.target.classList.contains("editor-image")
            ) {
                img = e.target;
            }
            if (img) {
                let wrapper = img.closest(".editor-image-wrapper");
                if (wrapper) {
                    selectImage(wrapper);
                } else {
                  // Fallback: select the image directly and show toolbar
                    img.classList.add("selected");
                    if (window.showImageToolbar) {
                        window.showImageToolbar(img);
                    }
                }
                e.stopPropagation();
            }
        });
    }
});
