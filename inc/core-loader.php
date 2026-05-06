<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Core Loader + Manifest Engine for Base47
 * - HTML shell stripping
 * - Asset URL rewriting
 * - Smart Loader++
 * - Manifest engine
 * - Template render
 * - Shortcodes
 */


/* -------------------------------------------------
| MANIFEST DISCOVERY (REQUIRED)
--------------------------------------------------*/

if ( ! function_exists( 'base47_he_get_all_manifests' ) ) {

    function base47_he_get_all_manifests() {

        $sets = base47_he_get_template_sets();
        $manifests = [];

        foreach ( $sets as $slug => $set ) {

            $manifest_file = trailingslashit( $set['path'] ) . 'manifest.json';

            if ( ! file_exists( $manifest_file ) ) {
                continue;
            }

            $json = false;
            if ( base47_he_init_filesystem() ) {
                global $wp_filesystem;
                $json = $wp_filesystem->get_contents( $manifest_file );
            }
            if ( ! $json ) continue;

            $data = json_decode( $json, true );
            if ( ! is_array( $data ) ) continue;

            /* Auto-build helper fields (old behaviour) */
            $manifests[ $slug ] = array_merge( $data, [
                '_set_slug'      => $slug,
                '_base_url'      => trailingslashit( $set['url'] ) . 'assets/',
                '_base_path'     => trailingslashit( $set['path'] ) . 'assets/',
                '_handle_prefix' => 'base47-' . sanitize_key( $slug ),
            ]);
        }

        return $manifests;
    }
}

if ( ! function_exists( 'base47_he_get_manifest_for_set' ) ) {
    function base47_he_get_manifest_for_set( $slug ) {
        $all = base47_he_get_all_manifests();
        return $all[ $slug ] ?? null;
    }
}

/* -------------------------------------------------
| MANIFEST + FLAGS
--------------------------------------------------*/

function base47_he_theme_uses_manifest( $slug ) {
    $use = get_option( BASE47_HE_OPT_USE_MANIFEST, [] );
    return in_array( $slug, $use, true );
}

// Hook for Pro Smart Loader functionality
function base47_he_theme_uses_smart( $slug ) {
    return apply_filters( 'base47_he_theme_uses_smart', false, $slug );
}

function base47_he_load_manifest( $slug ) {
    $sets = base47_he_get_template_sets();
    if ( ! isset( $sets[$slug] ) ) return false;

    $path = trailingslashit( $sets[$slug]['path'] ) . 'manifest.json';
    if ( ! file_exists( $path ) ) return false;

    $json = false;
    if ( base47_he_init_filesystem() ) {
        global $wp_filesystem;
        $json = $wp_filesystem->get_contents( $path );
    }
    $data  = json_decode( $json, true );

    return is_array( $data ) ? $data : false;
}


/* -------------------------------------------------
| REWRITE ASSETS
--------------------------------------------------*/

function base47_he_rewrite_assets( $html, $base_url, $add_ver = true ) {

    $base = trailingslashit( $base_url );

    $patterns = [
        '#src="/assets/#i',     '#src=\'/assets/#i',
        '#href="/assets/#i',    '#href=\'/assets/#i',
        '#src="assets/#i',      '#src=\'assets/#i',
        '#href="assets/#i',     '#href=\'assets/#i',
        '#url\("/assets/#i',    '#url\(\'/assets/#i',  '#url\(/assets/#i',
        '#url\("assets/#i',     '#url\(\'assets/#i',   '#url\(assets/#i',
        '#data-background="/assets/#i', '#data-background=\'/assets/#i',
        '#data-background="assets/#i',  '#data-background=\'assets/#i',
    ];

    $replacements = [
        'src="' . $base . 'assets/',   "src='" . $base . 'assets/',
        'href="' . $base . 'assets/',  "href='" . $base . 'assets/',
        'src="' . $base . 'assets/',   "src='" . $base . 'assets/',
        'href="' . $base . 'assets/',  "href='" . $base . 'assets/',
        'url("' . $base . 'assets/',   "url('" . $base . 'assets/',   'url(' . $base . 'assets/',
        'url("' . $base . 'assets/',   "url('" . $base . 'assets/',   'url(' . $base . 'assets/',
        'data-background="' . $base . 'assets/',  "data-background='" . $base . 'assets/',
        'data-background="' . $base . 'assets/',  "data-background='" . $base . 'assets/',
    ];

    $html = preg_replace( $patterns, $replacements, $html );

    // Hook for Pro asset processing
    $html = apply_filters( 'base47_he_process_pro_assets', $html, $base_url );

    if ( $add_ver ) {
        $ver = time();
        $html = preg_replace_callback(
            '#\b(src|href)=["\'](' . preg_quote( $base, '#' ) . 'assets/[^"\']+)#i',
            function( $m ) use ( $ver ) {
                $url = $m[2];
                $url .= ( strpos( $url ?? '', '?' ) === false ? '?ver=' : '&ver=' ) . $ver;
                return $m[1] . '="' . $url . '"';
            },
            $html
        );
    }

    return $html;
}


/* -------------------------------------------------
| STRIP SHELL
--------------------------------------------------*/

function base47_he_strip_shell( $html ) {

    $head = '';
    if ( preg_match( '#<head\b[^>]*>(.*?)</head>#is', $html, $m ) ) {
        $head = $m[1];
    }

    $body = $html;
    if ( preg_match( '#<body\b[^>]*>(.*?)</body>#is', $html, $m2 ) ) {
        $body = $m2[1];
    } else {
        $body = preg_replace( '#^.*?<html\b[^>]*>#is', '', $body );
        $body = preg_replace( '#</html>.*$#is', '', $body );
    }

    $inline = [];

    // Extract inline styles and scripts from HEAD
    if ( $head ) {
        if ( preg_match_all('#<style\b[^>]*>.*?</style>#is', $head, $ms )) {
            $inline = array_merge( $inline, $ms[0] );
        }
        if ( preg_match_all('#<script(?![^>]*\bsrc=)[^>]*>.*?</script>#is', $head, $ms )) {
            $inline = array_merge( $inline, $ms[0] );
        }
    }

    // Extract inline styles and scripts from BODY (important for layout)
    if ( preg_match_all('#<style\b[^>]*>.*?</style>#is', $body, $ms )) {
        $inline = array_merge( $inline, $ms[0] );
    }
    if ( preg_match_all('#<script(?![^>]*\bsrc=)[^>]*>.*?</script>#is', $body, $ms )) {
        $inline = array_merge( $inline, $ms[0] );
    }

    // Remove external asset links (these will be enqueued by WordPress)
    $body = preg_replace( '#<link[^>]+href=["\']/?assets/[^>]+>#i', '', $body );
    $body = preg_replace( '#<script[^>]+src=["\']/?assets/[^>]+></script>#i', '', $body );
    
    // Remove inline styles and scripts from body (we already extracted them)
    $body = preg_replace( '#<style\b[^>]*>.*?</style>#is', '', $body );
    $body = preg_replace( '#<script(?![^>]*\bsrc=)[^>]*>.*?</script>#is', '', $body );
    
    // Remove HTML shell tags
    $body = preg_replace( '#<(?:!DOCTYPE|/?html|/?head|/?body)[^>]*>#i', '', $body );

    return implode("\n", $inline) . "\n" . $body;
}


/* -------------------------------------------------
| SMART LOADER++, MANIFEST, BASE47 LOADER
--------------------------------------------------*/

function base47_he_enqueue_assets_for_set( $set_slug ) {

    $default = get_option( 'base47_default_theme', '' );
    if ( empty( $set_slug ) && $default ) {
        $set_slug = $default;
    }

    if ( ! base47_he_is_set_active( $set_slug ) ) {
        return;
    }

    $sets = base47_he_get_template_sets();
    if ( ! isset( $sets[ $set_slug ] ) ) {
        return;
    }

    $theme_path = trailingslashit( $sets[ $set_slug ]['path'] );
    $theme_url  = trailingslashit( $sets[ $set_slug ]['url'] );

    // Common directories
    $css_dir   = $theme_path . 'assets/css/';
    $js_dir    = $theme_path . 'assets/js/';
    $libs_dir  = $theme_path . 'assets/libs/';

    /**
     * Helper: enqueue assets/libs (one level deep)
     */
    $enqueue_libs = function( $prefix ) use ( $libs_dir, $theme_path, $theme_url ) {

        if ( ! is_dir( $libs_dir ) ) {
            return;
        }

        // CSS: assets/libs/*/*.css
        foreach ( glob( $libs_dir . '*/*.css' ) as $file ) {
            $relative = str_replace( $theme_path, '', $file );
            wp_enqueue_style(
                $prefix . '-lib-css-' . md5( $relative ),
                $theme_url . $relative,
                [],
                filemtime( $file )
            );
        }

        // JS: assets/libs/*/*.js
        foreach ( glob( $libs_dir . '*/*.js' ) as $file ) {
            $relative = str_replace( $theme_path, '', $file );
            wp_enqueue_script(
                $prefix . '-lib-js-' . md5( $relative ),
                $theme_url . $relative,
                [ 'jquery' ],
                filemtime( $file ),
                true
            );
        }
    };

    /* ----------------------------
       BASE47 LOADER (Free Version)
    -----------------------------*/
    if ( is_dir( $css_dir ) ) {
        foreach ( glob( $css_dir . '*.css' ) as $file ) {
            wp_enqueue_style(
                'base47-fallback-css-' . md5( $file ),
                $theme_url . 'assets/css/' . basename( $file ),
                [],
                filemtime( $file )
            );
        }
    }

    if ( is_dir( $js_dir ) ) {
        foreach ( glob( $js_dir . '*.js' ) as $file ) {
            wp_enqueue_script(
                'base47-fallback-js-' . md5( $file ),
                $theme_url . 'assets/js/' . basename( $file ),
                [ 'jquery' ],
                filemtime( $file ),
                true
            );
        }
    }

    // Also enqueue assets/libs/*
    $enqueue_libs( 'base47-fallback' );
    
    // Hook for Pro asset loaders
    do_action( 'base47_he_enqueue_pro_assets', $set_slug, $sets[ $set_slug ] );
}

/* -------------------------------------------------
| RENDER TEMPLATE
--------------------------------------------------*/

function base47_he_render_template( $filename, $set_slug = '' ) {

    $sets = base47_he_get_template_sets();

    if ( empty( $set_slug ) ) {
        $default = get_option('base47_default_theme', '');
        if ( $default ) $set_slug = $default;
    }

    if ( empty( $set_slug ) ) {
        $info = base47_he_locate_template( $filename );
        if ( ! $info ) return '';
        $set_slug = $info['set'];
        $full     = $info['path'];
        $base_url = $info['url'];
    } else {
        if ( ! isset( $sets[$set_slug] ) ) return '';
        $full     = $sets[$set_slug]['path'] . $filename;
        $base_url = $sets[$set_slug]['url'];
        if ( ! file_exists( $full ) ) return '';
    }

    if ( ! base47_he_is_set_active( $set_slug ) ) {
        return '<!-- Base47: theme inactive -->';
    }

    $GLOBALS['base47_current_set_slug'] = $set_slug;

    $html = '';
    if ( base47_he_init_filesystem() ) {
        global $wp_filesystem;
        $html = $wp_filesystem->get_contents( $full );
    }
    if ( false === $html || '' === $html ) return '';
    
    // Check if we're in PURE canvas mode
    $pure_canvas = isset( $GLOBALS['base47_pure_canvas_mode'] ) && $GLOBALS['base47_pure_canvas_mode'];
    
    if ( $pure_canvas ) {
        // PURE CANVAS MODE: Keep complete HTML structure with all assets
        // Just rewrite asset URLs and process shortcodes
        
        // Rewrite asset URLs
        $html = base47_he_rewrite_assets( $html, $base_url, true );
        
        // Pre-scan and enqueue special widget assets
        base47_he_enqueue_detected_special_widget_assets( $html );
        
        // Process nested shortcodes if any
        $html = do_shortcode( $html );
        
    } else {
        // NORMAL MODE: Full WordPress integration with shell stripping
        $html = base47_he_strip_shell( $html );
        $html = base47_he_rewrite_assets( $html, $base_url, true );
        
        // Pre-scan and enqueue special widget assets
        base47_he_enqueue_detected_special_widget_assets( $html );
        
        $html = do_shortcode( $html );
    }

    base47_he_enqueue_assets_for_set( $set_slug );

    // Templates are admin-authored trusted content (only manage_options users can edit).
    // Security is enforced at save-time via capability checks (unfiltered_html).
    // 
    // Optional: "Strict Sanitization Mode" applies wp_kses when explicitly enabled.
    // Default OFF — enabling it may break scripts, styles, sliders, animations, external assets.
    $settings = base47_he_get_settings();
    if ( ! empty( $settings['sanitize_output'] ) ) {
        $html = base47_he_sanitize_template_html( $html );
    }

    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Admin-authored template HTML intentionally rendered raw. Editing restricted to manage_options capability. Optional Strict Sanitization Mode available.
    return $html;
}


/* -------------------------------------------------
| SHORTCODES - MOVED TO shortcodes.php
--------------------------------------------------*/

// Shortcode registration moved to inc/shortcodes.php to avoid duplicates