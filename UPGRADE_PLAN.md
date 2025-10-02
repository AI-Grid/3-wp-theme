# WordPress Upgrade Plan for the Redmond Inspired Theme

## 1. Establish a Baseline
- Confirm the site is currently running the Redmond Inspired theme version `0.0.1` and capture a full database and `wp-content` backup before touching production. The theme header in `style.css` documents the legacy version we are starting from.【F:style.css†L1-L12】
- Stand up a local WordPress environment that mirrors production (PHP version, active plugins, multisite state) so the upgrade and fixes can be rehearsed safely.
- Import a recent production database snapshot and media library into the sandbox. Validate that the theme renders correctly to ensure we have a reliable “before” reference.

## 2. Update the Core WordPress Version
- Identify the target WordPress release (e.g., 6.4.x or newer) and review the official release notes for breaking changes affecting themes (block editor expectations, `wp_nav_menu` updates, script loading changes, etc.).
- Upgrade the sandbox site incrementally through any major version gaps (e.g., 4.x → 5.x → 6.x) to catch migration tasks such as database upgrades or Gutenberg-related configuration.
- After each core upgrade, run the WordPress database upgrade routine and check the site for fatal errors, warnings, or layout breakages.

## 3. Theme Compatibility Audit
- Run automated checks: `wp theme validate`, Theme Check, and WordPress Coding Standards (PHPCS) to surface deprecated API usage, escaping issues, and coding-standard regressions in files like `functions.php` and `header.php`.
- Manually browse the site in the sandbox, especially AJAX-driven UI such as the “Start” menu and modal windows controlled by `functions.js`, to confirm interactions still work under the new core JavaScript and jQuery versions.【F:functions.js†L1-L89】

## 4. Replace Deprecated WordPress APIs
- Update `header.php` to rely on modern authentication globals (`wp_get_current_user()`) instead of the deprecated `get_currentuserinfo()` helper.【F:header.php†L1-L24】
- Replace the custom `<title>` tag generation and the `wp_title` filter in `functions.php` with the `title-tag` theme support introduced in WordPress 4.1.【F:functions.php†L36-L63】【F:header.php†L8-L16】
- Review AJAX endpoints in `bin/ajax.php` to ensure they validate nonces and sanitize payloads, and modernize responses away from `WP_Ajax_Response` if needed. Adopt `wp_send_json_success()` / `wp_send_json_error()` for consistency with current best practices.【F:bin/ajax.php†L1-L73】

## 5. Modernize Theme Supports and Assets
- In `functions.php`, add support for features expected by recent WordPress releases (block editor styles, responsive embeds, custom logos) while keeping the theme’s classic aesthetic.【F:functions.php†L36-L63】
- Audit script and style registration in `redmond_theme_add_scripts_and_styles()` (located in the `bin` directory) to ensure dependencies are enqueued with proper versioning and compatibility with the bundled jQuery UI from modern WordPress.【F:functions.php†L36-L63】
- Confirm all static assets referenced via `REDMONDURI` still exist and consider migrating icon resources to SVG or modern formats if practical.【F:header.php†L24-L78】

## 6. Hardening and Performance
- Review the numerous `remove_action( 'wp_head', ... )` calls in `functions.php` to ensure we only strip the elements we truly do not need, balancing performance with modern WordPress features (e.g., RSS feeds for discoverability).【F:functions.php†L64-L86】
- Add nonce checks and capability validations to AJAX actions (`redmond_getpost_callback`, `redmond_getarchive_callback`, `redmond_getauthor_callback`) before trusting `$_POST` data.【F:bin/ajax.php†L1-L110】
- Evaluate whether the New Relic integrations are still required; guard them behind feature flags so the theme functions without the extension when running on hosts without New Relic.【F:functions.php†L8-L35】

## 7. Block Editor (Gutenberg) Compatibility
- Test creating and editing posts in the block editor to see how the theme’s CSS applies to blocks. Extend `editor-style.css` support or add block-specific styles so default blocks match the theme’s Windows XP aesthetic.
- Provide template parts or block patterns, if feasible, that mimic the existing layout to ease content creation while maintaining the retro feel.

## 8. Quality Assurance and Rollout
- Regression test all front-end interactions, including AJAX-driven dialogs, login/logout flows, and customizer options supplied via `bin/customizer.php`.
- Verify responsive behavior and cross-browser support, paying special attention to fixed-position UI like the taskbar implemented in `style.css` and the clock controlled by `functions.js`.【F:style.css†L13-L123】【F:functions.js†L1-L89】
- Once confidence is high, deploy the upgraded theme and core to a staging environment, secure stakeholder sign-off, and then schedule the production release during a low-traffic window. Keep the previous version snapshot for quick rollback.

## 9. Post-Upgrade Monitoring
- Monitor application logs, browser console output, and (if enabled) New Relic APM dashboards for errors related to the theme.
- Capture user feedback and address any regressions promptly, committing fixes back into version control.

Following this plan will let us upgrade WordPress to the latest release while preserving the Redmond Inspired theme’s distinctive UX and ensuring it meets current platform expectations.
