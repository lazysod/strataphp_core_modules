# StrataPHP Core Modules Repository

This repository contains the core modules for the StrataPHP framework. Each module is self-contained and follows a standard structure to ensure compatibility with the StrataPHP module importer and updater systems.

## Repository Structure

- `modules/` — Contains all core modules, each in its own subdirectory.
    - Each module typically includes:
        - `index.php` — Module metadata and bootstrap.
        - `module.json` — Module manifest (name, version, etc.).
        - `Controllers/`, `Models/`, `views/`, `assets/` — Standard MVC structure.
        - Additional folders as needed (e.g., `config/`, `Helpers/`).

## Module Updates & Importing

- Modules are updated via the StrataPHP admin interface or CLI tools.
- The update/importer system expects each module to:
    - Reside in its own folder under `modules/`
    - Provide an `index.php` and `module.json` with correct metadata
    - Follow the standard folder structure
- To update a module, replace its folder with the new version and ensure metadata is correct.
- For bulk updates, use the main StrataPHP update tools. For single-module updates, a specialized script or manual replacement may be used.

## Best Practices

- Always validate module metadata before importing or updating.
- Test modules in a staging environment before deploying to production.
- Keep modules decoupled and avoid cross-module dependencies unless documented.

## More Information

For full documentation, guides, and the main StrataPHP project, visit:

[https://strataphp.org](https://strataphp.org)
