# Repository Guidelines

## Project Structure & Module Organization
- `cf7-custom-validation.php` bootstraps the plugin, registers hooks, and loads the rest of the code.
- `includes/` houses PHP classes (`CF7CV_Loader`, `CF7CV_Admin`) that encapsulate admin UI logic, hook registration, and validation handling.
- `views/panel.php` renders the Custom Validation tab inside Contact Form 7; keep markup minimal and escape output on print.
- `assets/css` and `assets/js` deliver the admin styles and scripts enqueued by `CF7CV_Admin`; update these files directly as no build pipeline runs by default.
- `languages/` stores the translation template; regenerate it via `wp i18n make-pot . languages/cf7-custom-validation.pot` before releasing new strings.
- `uninstall.php` removes plugin data on deletion—mirror its patterns when adding new meta keys.

## Build, Test, and Development Commands
- `wp plugin activate cf7-custom-validation` enables the plugin inside your local WordPress install; pair it with the Contact Form 7 plugin before testing.
- `wp plugin deactivate cf7-custom-validation` toggles the plugin off when inspecting clean states.
- `php -l cf7-custom-validation.php includes/*.php` provides a quick syntax check; run it before committing.
- `wp i18n make-pot . languages/cf7-custom-validation.pot` refreshes localisation files after string changes.

## Coding Style & Naming Conventions
- Follow WordPress PHP coding standards: 4-space indentation, snake_case for function names, Yoda conditions where it improves clarity, and translated strings with the `cf7-custom-validation` text domain.
- Prefix new functions, filters, and option keys with `cf7cv_` to avoid collisions (e.g., `cf7cv_get_validation_messages()`).
- Sanitize user input on receipt and escape on output; reuse the helper patterns already present in `CF7CV_Admin`.

## Testing Guidelines
- Validate changes manually inside wp-admin: activate the plugin, edit a Contact Form 7 form, enable Custom Validation, and confirm that required/invalid messages save and display on the front end.
- Clear browser cache or use a private window when verifying script or style tweaks; assets are versioned via `CF7CV_VERSION`.
- If you touch translations, switch the site language and confirm strings load from `languages/`.

## Commit & Pull Request Guidelines
- Write focused, imperative commit subjects such as `Add toggle for custom date message` and describe the why in the body when necessary.
- Reference support tickets or issue IDs in commit bodies; include screenshots or screen recordings for any admin UI change.
- For pull requests, note manual verification steps (form save, validation errors, uninstall flow) and mention any follow-up tasks required for deployment.

## Security & Configuration Tips
- Never store credentials in the repository; rely on the environment-specific `wp-config.php` for sensitive settings.
- Honour the nonce and capability checks already in place; replicate them for new AJAX handlers or admin pages.
- When adding new meta keys, register them for sanitisation and cleanup so `uninstall.php` can purge data safely.
