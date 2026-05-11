=== Base47 HTML Editor ===
Contributors: base47
Tags: html editor, html templates, code editor, live preview, shortcode templates
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 3.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Base47 HTML Editor is a powerful HTML template editor for WordPress. Build, preview, and convert HTML templates into shortcodes automatically.

== Description ==

Base47 HTML Editor is a professional HTML editor for WordPress that gives you direct control over your HTML templates without the bloat of visual page builders. Build clean, semantic HTML, preview changes in real time, and render templates as shortcodes with controlled asset loading.

Unlike visual page builders, Base47 HTML Editor focuses on pure HTML workflows, giving developers and advanced users full control over structure, performance, and output.

The free version includes a built-in classic HTML editor, live preview, shortcode generation, theme-set management, optimized asset loading, and reusable widget support. Base47 HTML Editor Pro unlocks advanced developer tools including the Monaco Editor experience.

Whether you are building landing pages, reusable sections, template packs, or complete HTML-based layouts, Base47 HTML Editor helps you keep your workflow fast, organized, and developer-friendly.

= Why Base47 HTML Editor? =

* Full control over your HTML output
* No page builder lock-in or layout bloat
* Live preview while editing templates
* Automatic shortcode generation
* Optimized asset loading for better performance
* Built for developers, power users, and custom workflows
* Works with any WordPress theme and page builder

= Free Features =

**Classic HTML Editor**
* Edit template files directly inside WordPress
* Clean and simple editing experience
* Built for speed and reliability

**Live Preview**
* Preview changes in real time
* Responsive preview modes for desktop, tablet, and mobile
* Side-by-side editing workflow

**Template & Shortcode System**
* Every HTML file automatically becomes a shortcode based on the filename
* Example shortcodes: `[base47-home]`, `[base47-hero]`
* Nested shortcodes supported
* Works with unlimited template folders ending in `-templates`

**Theme Manager**
* Activate or deactivate template sets
* Set a default theme
* Manage multiple template packs from one dashboard

**Base47 Loader**
* Loads only the CSS and JS required by the specific template
* Helps prevent conflicts between template sets and active theme CSS
* Designed for lightweight and reliable template rendering

**Special Widgets**
* Add reusable HTML components such as sliders, hero sections, and content blocks
* Auto-detection from the `special-widgets/` folder
* Shortcode usage: `[base47_widget slug="widget-name"]`

**Backups & Restore**
* Automatic backups before saving templates
* Restore previous versions when needed

= Pro Features =

Upgrade to Base47 HTML Editor Pro to unlock a full developer-grade editing experience:

* Monaco Editor [Pro] – VS Code-powered editing experience
* IntelliSense and advanced syntax support [Pro]
* Smart Loader [Pro] – Advanced asset optimization with lazy loading and preloading
* Manifest Loader [Pro] – JSON-based asset control for advanced users
* Additional editor themes [Pro]
* Advanced coding workflow enhancements [Pro]

== Installation ==

1. Upload the plugin or install it via **Plugins → Add New**.
2. Activate the plugin.
3. Place your template folders inside:
   * `wp-content/uploads/base47-themes/` (recommended)
   * Or use the built-in template installer to add and manage template sets
4. Visit **Base47 → Theme Manager** and activate your template sets.
5. Use the generated shortcodes anywhere in your pages, posts, or page builders.

== External Services & Privacy ==

This plugin connects to external services only in the following cases:

= License Validation (Optional) =

When activating a Pro license key, the plugin connects to the Base47 License API to validate your license.

* Service: Base47 License API
* URL: https://base47.art/wp-json/base47-core/v1/license/activate
* Purpose: Validate Pro license keys and enable premium features
* Data Sent: License key, site URL, home URL, site UUID, product slug, WordPress version, PHP version, plugin version
* Privacy: No personal user data is transmitted. Only technical site information required for license validation is sent
* When: Only when you manually activate a Pro license in the License page
* Required: No. The free version works without this service
* Terms: https://base47.art/privacy/

= Free Version Privacy =

The free version of this plugin operates locally on your WordPress installation:

* All template processing, editing, and management happens locally
* No tracking, analytics, or phone-home functionality
* Template files remain on your server
* No data transmission occurs except when you explicitly activate a Pro license
* Admin pages use system fonts — no remote font loading

= Third-Party Libraries =

This plugin bundles the following third-party libraries. See `THIRD-PARTY-LICENSES.md` for full details.

* Font Awesome Free v6.5.1 (Font Awesome Free License) – icon library. Source: https://fontawesome.com
* jQuery v3.7.1 (MIT License) – JavaScript library. Source: https://jquery.com

Note: Monaco Editor is bundled in the Pro plugin only and is not included in this free plugin.

All bundled libraries are GPL-compatible and properly licensed.

= Source Code =

The source code for this plugin is publicly available at: https://github.com/base47hq/base47-html-editor

== Frequently Asked Questions ==

= Can I use my own HTML templates? =

Yes. Any folder ending with `-templates` is detected automatically.

= Do CSS and JS load automatically? =

Yes. Assets inside `assets/css/` and `assets/js/` load only when the related shortcode is used.

= Can I edit templates in the free version? =

Yes. The free version includes a built-in classic HTML editor with live preview.

= Is Monaco Editor included in the free version? =

No. Monaco Editor is available in Base47 HTML Editor Pro.

= Is it compatible with page builders? =

Yes. Tested with Elementor, Bricks, Beaver Builder, Gutenberg, and WPBakery.

= Does this plugin work with WooCommerce? =

Yes. The Base47 Loader is designed to reduce conflicts with WooCommerce styling.

= Where do I find my shortcodes? =

Go to **Base47 → Shortcodes** to browse all available shortcodes with live previews.

= What are Special Widgets? =

Special Widgets are reusable HTML components stored inside each template set under `/special-widgets/`. Each widget can be used with `[base47_widget slug="widget-name"]`.

= Can I manage multiple template sets? =

Yes. Use the Theme Manager to activate or deactivate unlimited template sets.

= Does it create backups? =

Yes. The editor automatically creates backups before each save. Use the Restore function to view previous versions.

= What happens when I uninstall? =

On uninstall, all plugin options and generated data are removed. Template files stored in `wp-content/uploads/base47-themes/` are preserved and can be manually deleted if desired.

= Do I need coding knowledge to use Base47 HTML Editor? =

Basic HTML knowledge is recommended. Base47 HTML Editor is designed primarily for developers and advanced users who want direct control over HTML templates.

== Screenshots ==

1. Dashboard overview
2. Classic HTML Editor with Live Preview
3. Theme Manager
4. Shortcodes preview
5. Special Widgets
6. Settings panel
7. Logs view
8. Changelog page

== Changelog ==

= 3.0.2 =
* Improved editor stability and internal architecture
* Improved AJAX handling and admin reliability
* Added Validation Warnings panel in the editor with detailed issue display
* Added keyboard shortcuts bar with visual reference
* Added template duplication in the editor toolbar
* General compatibility improvements
* Internal code cleanup and maintenance
* Fixed minor UI and workflow inconsistencies
* Improved plugin structure and maintainability

= 3.0.1 =
* Improved loader architecture and template rendering stability
* Restored full Free backup functionality
* Added optional Strict Sanitization Mode for multi-user environments
* Improved asset loading reliability and compatibility
* Improved editor security and permission handling
* Fixed AJAX handler conflicts and cleanup issues
* Improved template rendering consistency
* General stability and performance improvements

= 3.0.0 – February 6, 2026 =
* Initial public release
* Complete internationalization (i18n) support with translation files
* Enhanced security with proper escaping, sanitization, and nonce validation
* Improved file deletion security with capability checks and path validation
* Updated compatibility testing with WordPress 6.9.1
* Enhanced documentation and privacy disclosures
* Third-party library licensing documentation
* Improved update and compatibility architecture
* Built-in classic HTML editor with live preview
* Automatic shortcode generation for HTML templates
* Base47 Loader system for optimized template asset loading
* Theme-set management with activation and deactivation
* Special Widgets system with auto-discovery
* Backup and restore functionality
* Soft UI admin dashboard
* General stability, compatibility, and security improvements
* Clean uninstall procedures
* Comprehensive logging system
* Mobile-responsive admin interface

== Upgrade Notice ==

= 3.0.0 =
Initial public release with internationalization, enhanced security, live preview, shortcode generation, and HTML template management tools.

== License ==

GPL v2 or later.
