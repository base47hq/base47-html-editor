<?php
/*
Plugin Name: Base47 HTML Editor
Description: A professional HTML editor for WordPress. Write pure, clean HTML with live preview, canvas mode, and smart asset loading. Perfect for developers who want full control.
Version: 3.0.2
Author: Base47
Author URI: https://base47.art
Plugin URI: https://base47.art/base47-html-editor
Text Domain: base47-html-editor
Domain Path: /languages
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 5.0
Tested up to: 6.9
Requires PHP: 7.4
*/

if ( ! defined( 'ABSPATH' ) ) exit;

/* --------------------------------------------------------------------------
| CONSTANTS
-------------------------------------------------------------------------- */
define( 'BASE47_HE_VERSION', '3.0.2' );
define( 'BASE47_HE_PATH', plugin_dir_path( __FILE__ ) );
define( 'BASE47_HE_URL',  plugin_dir_url( __FILE__ ) );

/* --------------------------------------------------------------------------
| OPTIONS
-------------------------------------------------------------------------- */
const BASE47_HE_OPT_ACTIVE_THEMES  = 'base47_active_themes';     // array of active set slugs
const BASE47_HE_OPT_USE_MANIFEST   = 'base47_use_manifest';      // array of sets using manifest
const BASE47_HE_OPT_SETTINGS_NONCE = 'base47_he_settings_nonce';


function base47_he_get_nonce() {
    return wp_create_nonce('base47_he');
}

/**
 * Central storage location for user themes.
 * /wp-content/uploads/base47-themes/{set}/
 */

function base47_he_get_themes_root() {
    static $root = null;
    if ( $root !== null ) {
        return $root;
    }

    $uploads = wp_upload_dir();
    $dir     = trailingslashit( $uploads['basedir'] ) . 'base47-themes/';
    $url     = trailingslashit( $uploads['baseurl'] ) . 'base47-themes/';

    if ( ! is_dir( $dir ) ) {
        wp_mkdir_p( $dir );
    }

    $root = [
        'dir' => $dir,
        'url' => $url,
    ];

    return $root;
}

/* --------------------------------------------------------------------------
| INCLUDES
-------------------------------------------------------------------------- */

// Core loader + manifest engine
require_once BASE47_HE_PATH . 'inc/core-loader.php';

// Discovery & caching
require_once BASE47_HE_PATH . 'inc/discovery.php';

// Error handling & fallback mode (v2.9.8)
require_once BASE47_HE_PATH . 'inc/helpers/error-handler.php';
require_once BASE47_HE_PATH . 'inc/helpers/fallback-mode.php';
require_once BASE47_HE_PATH . 'inc/helpers/woocommerce-compat.php';

// Backup system
require_once BASE47_HE_PATH . 'inc/helpers/backups.php';

// Systems (must load before shortcodes)
require_once BASE47_HE_PATH . 'inc/systems/special-widgets.php';

// Shortcode registration
require_once BASE47_HE_PATH . 'inc/shortcodes.php';

// Activation & migration
require_once BASE47_HE_PATH . 'inc/activation.php';

// Helpers
require_once BASE47_HE_PATH . 'inc/helpers/feature-detection.php';
require_once BASE47_HE_PATH . 'inc/helpers/settings.php';
require_once BASE47_HE_PATH . 'inc/helpers/logs.php';
require_once BASE47_HE_PATH . 'inc/helpers/security.php'; // Central security helpers (must load before AJAX)
require_once BASE47_HE_PATH . 'inc/helpers/templates.php';
require_once BASE47_HE_PATH . 'inc/helpers/metadata.php';
require_once BASE47_HE_PATH . 'inc/helpers/tooltips.php';
require_once BASE47_HE_PATH . 'inc/helpers/admin-ui.php';

// Operations
require_once BASE47_HE_PATH . 'inc/operations/theme-install.php';
require_once BASE47_HE_PATH . 'inc/operations/theme-delete.php';

// Systems
// (special-widgets.php already loaded above)

// AJAX Handlers
require_once BASE47_HE_PATH . 'inc/ajax/preview.php';
require_once BASE47_HE_PATH . 'inc/ajax/editor.php';
require_once BASE47_HE_PATH . 'inc/ajax/theme-manager.php';
require_once BASE47_HE_PATH . 'inc/ajax/cache.php';
require_once BASE47_HE_PATH . 'inc/ajax/settings.php';
require_once BASE47_HE_PATH . 'inc/ajax/license.php';
require_once BASE47_HE_PATH . 'inc/ajax/marketplace.php';
require_once BASE47_HE_PATH . 'inc/ajax/support.php';

// Admin Pages
require_once BASE47_HE_PATH . 'inc/admin-pages/dashboard.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/onboarding.php';  // V3 Feature
require_once BASE47_HE_PATH . 'inc/admin-pages/shortcodes.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/editor.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/theme-manager.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/marketplace.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/support.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/widgets.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/settings.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/changelog.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/logs.php';
require_once BASE47_HE_PATH . 'inc/admin-pages/upgrade.php';  
require_once BASE47_HE_PATH . 'inc/admin-pages/license.php';   

// Admin initialization (MUST be after admin pages so functions exist)
require_once BASE47_HE_PATH . 'inc/admin-init.php';

/* --------------------------------------------------------------------------
| ONBOARDING REDIRECT (V3 FEATURE)
-------------------------------------------------------------------------- */

/**
 * Redirect new users to onboarding wizard
 */
function base47_he_onboarding_redirect() {
    // Only run in admin
    if ( ! is_admin() ) {
        return;
    }
    
    // Don't redirect during AJAX requests
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        return;
    }
    
    // Handle onboarding dismissal
    if ( isset( $_GET['dismiss_onboarding'] ) && sanitize_text_field( wp_unslash( $_GET['dismiss_onboarding'] ) ) === '1' ) {
        if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'base47_dismiss_onboarding' ) ) {
            update_user_meta( get_current_user_id(), 'base47_he_onboarding_dismissed', true );
            return;
        }
    }
    
    // Don't redirect if already on onboarding page
    $page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
    if ( $page === 'base47-he-onboarding' ) {
        return;
    }
    
    // Check if user should see onboarding
    if ( ! base47_he_should_show_onboarding() ) {
        return;
    }
    
    // Only redirect on Base47 pages or dashboard
    $base47_pages = array(
        'base47-he-dashboard',
        'base47-he-templates',
        'base47-he-editor',
        'base47-he-theme-manager',
        'base47-he-marketplace',
        'base47-special-widgets',
        'base47-he-support',
        'base47-he-settings',
        'base47-he-logs',
        'base47-he-changelog',
        'base47-he-license',
        'base47-he-upgrade'
    );
    
    // Redirect if on a Base47 page
    if ( in_array( $page, $base47_pages, true ) ) {
        wp_safe_redirect( admin_url( 'admin.php?page=base47-he-onboarding' ) );
        exit;
    }
}
add_action( 'admin_init', 'base47_he_onboarding_redirect' );

/* --------------------------------------------------------------------------
| CANVAS MODE FRONTEND RENDERER
-------------------------------------------------------------------------- */

/**
 * Canvas Mode Frontend Renderer
 * Only activates for pages with explicit Base47 canvas/template meta enabled.
 * 
 * This takes full control of the page output — provides its own document shell.
 * Works regardless of which theme template is assigned.
 */
function base47_canvas_mode_takeover( $template ) {
    if ( ! is_singular() ) {
        return $template;
    }

    $post = get_queried_object();
    if ( ! $post || empty( $post->ID ) ) {
        return $template;
    }

    $canvas_mode = get_post_meta( $post->ID, '_base47_canvas_mode', true );
    if ( ! $canvas_mode ) {
        return $template;
    }

    $template_file = get_post_meta( $post->ID, '_base47_template_file', true );
    $set_slug      = get_post_meta( $post->ID, '_base47_template_set', true );

    if ( empty( $template_file ) || empty( $set_slug ) ) {
        return $template;
    }

    $GLOBALS['base47_pure_canvas_mode'] = true;

    $html = base47_he_render_template( $template_file, $set_slug );

    if ( empty( $html ) ) {
        return $template;
    }

    ?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html( get_the_title( $post ) ); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'base47-canvas' ); ?>>
    <?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Intentional raw HTML template output ?>
    <?php wp_footer(); ?>
</body>
</html><?php
    exit;
}
add_filter( 'template_include', 'base47_canvas_mode_takeover' );

/* --------------------------------------------------------------------------
| HOOK REGISTRATIONS
-------------------------------------------------------------------------- */

// Plugin activation (handled in inc/activation.php)
register_activation_hook( __FILE__, 'base47_he_activate' );

// Note: Shortcode registration happens via add_action('init') inside inc/shortcodes.php
// Note: Admin menu registration happens via add_action('admin_menu') inside inc/admin-init.php
// Note: Admin assets enqueuing happens via add_action('admin_enqueue_scripts') inside inc/admin-init.php