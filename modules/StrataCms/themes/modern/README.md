# Modern CMS Theme

This theme is designed for StrataPHP CMS with strict Bootstrap 5 usage.

## Structure

- `templates/` — All PHP page templates (use only Bootstrap 5 classes)
- `assets/css/theme.css` — All custom theme styles (no inline/embedded CSS)
- `assets/js/theme.js` — All custom JS (no inline scripts)
- `assets/images/` — Theme images
- `theme.json` — Theme metadata and asset entry points

## Usage

- To override styles, edit `theme.css` only.
- To add JS, use `theme.js` only.
- Do not add `<style>` or `<script>` tags to templates.
- Use Bootstrap 5 utility classes for all layout and spacing.

## Customization

Copy this folder to create a new theme, or override files as needed.

---

For more, see the StrataPHP CMS theming guide.
