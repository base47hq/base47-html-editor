<?php
/**
 * Central Security Helpers
 *
 * All security checks for the Base47 HTML Editor go through this file.
 * No AJAX handler or shortcode callback should perform capability checks,
 * path resolution, or filesystem initialization inline — use these helpers.
 *
 * @package Base47_HTML_Editor
 * @since 3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Capability check for all editor operations.
 *
 * Wraps manage_options. Use this everywhere — never call current_user_can() directly
 * in AJAX handlers. This wrapper allows future expansion of permissions without
 * rewriting every handler.
 *
 * @return bool
 */
function base47_he_current_user_can_edit_templates(): bool {
    return current_user_can( 'manage_options' );
}

/**
 * Resolve and validate a template path.
 *
 * Validates set slug against known sets, validates file name pattern,
 * and performs realpath() containment check to prevent path traversal.
 *
 * For existing files: uses realpath() on the full path.
 * For new/temp files: uses realpath() on the parent directory + validates filename separately.
 *
 * @param string $set  Set slug (from user input — will be sanitized internally).
 * @param string $file File name (from user input — will be validated internally).
 * @return string|WP_Error Full validated path on success, WP_Error on failure.
 */
function base47_he_resolve_template_path( string $set, string $file ) {
    // 1. Sanitize set slug
    $set = sanitize_key( $set );

    if ( empty( $set ) ) {
        base47_he_log( 'Path validation failed: empty set slug', 'warn' );
        return new WP_Error( 'invalid_set', 'Invalid template set.' );
    }

    // 2. Confirm set exists in known sets
    $sets = base47_he_get_template_sets();
    if ( ! isset( $sets[ $set ] ) ) {
        base47_he_log( "Path validation failed: unknown set '{$set}'", 'warn' );
        return new WP_Error( 'invalid_set', 'Template set not found.' );
    }

    $set_path = trailingslashit( $sets[ $set ]['path'] );

    // 3. Validate file name — only allow [a-zA-Z0-9_-].html
    if ( ! preg_match( '/^[a-zA-Z0-9_\-]+\.html$/i', $file ) ) {
        base47_he_log( "Path validation failed: invalid filename '{$file}' in set '{$set}'", 'warn' );
        return new WP_Error( 'invalid_file', 'Invalid file name.' );
    }

    // 4. Build candidate path
    $candidate = $set_path . $file;

    // 5. Resolve the approved set directory
    $real_set_path = realpath( $set_path );
    if ( false === $real_set_path ) {
        base47_he_log( "Path validation failed: set directory does not exist '{$set_path}'", 'warn' );
        return new WP_Error( 'invalid_set', 'Template set directory not found.' );
    }
    $real_set_path = trailingslashit( $real_set_path );

    // 6. For existing files: realpath() the full path and check containment
    if ( file_exists( $candidate ) ) {
        $real_candidate = realpath( $candidate );
        if ( false === $real_candidate || strpos( $real_candidate, $real_set_path ) !== 0 ) {
            base47_he_log( "Path traversal attempt blocked: '{$file}' in set '{$set}'", 'warn' );
            return new WP_Error( 'path_traversal', 'Invalid file path.' );
        }
        return $real_candidate;
    }

    // 7. For new/temp files: realpath() the parent directory and check containment
    $real_parent = realpath( dirname( $candidate ) );
    if ( false === $real_parent ) {
        base47_he_log( "Path validation failed: parent directory not found for '{$file}' in set '{$set}'", 'warn' );
        return new WP_Error( 'invalid_path', 'Invalid file path.' );
    }
    $real_parent = trailingslashit( $real_parent );

    if ( strpos( $real_parent, $real_set_path ) !== 0 ) {
        base47_he_log( "Path traversal attempt blocked (new file): '{$file}' in set '{$set}'", 'warn' );
        return new WP_Error( 'path_traversal', 'Invalid file path.' );
    }

    return $real_parent . $file;
}

/**
 * Initialize WP_Filesystem.
 *
 * Returns true on success, false on failure (logs the failure).
 * Never falls back to raw PHP file functions on failure.
 *
 * @return bool
 */
function base47_he_init_filesystem(): bool {
    global $wp_filesystem;

    if ( $wp_filesystem instanceof WP_Filesystem_Base ) {
        return true; // Already initialized
    }

    if ( ! function_exists( 'WP_Filesystem' ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }

    $result = WP_Filesystem();

    if ( ! $result || ! ( $wp_filesystem instanceof WP_Filesystem_Base ) ) {
        base47_he_log( 'WP_Filesystem initialization failed', 'error' );
        return false;
    }

    return true;
}

/**
 * Validate HTML content and return advisory warnings.
 *
 * Does NOT modify content. Returns array of warning strings.
 * Warnings are advisory only — they never block a save.
 *
 * @param string $html Raw HTML content.
 * @return string[]
 */
function base47_he_validate_html_content( string $html ): array {
    $warnings = [];

    // Check content size (> 500 KB)
    if ( strlen( $html ) > 512000 ) {
        $warnings[] = 'Content exceeds 500 KB. Large templates may affect page load performance.';
    }

    // Check for external script src tags
    if ( preg_match( '/<script[^>]+src=["\']https?:\/\//i', $html ) ) {
        $warnings[] = 'External script detected: script src="..." references an external URL.';
    }

    // Check for <iframe> elements
    if ( stripos( $html, '<iframe' ) !== false ) {
        $warnings[] = 'Iframe element detected: <iframe> elements may pose security or compatibility risks.';
    }

    // Check for inline event handler attributes
    if ( preg_match( '/\s(on[a-z]+)\s*=/i', $html ) ) {
        $warnings[] = 'Inline event handler detected: attributes like onclick=, onload=, onerror= were found.';
    }

    // Check for duplicate id attributes
    preg_match_all( '/\sid=["\']([^"\']+)["\']/', $html, $id_matches );
    if ( ! empty( $id_matches[1] ) ) {
        $ids      = $id_matches[1];
        $dupes    = array_filter( array_count_values( $ids ), fn( $count ) => $count > 1 );
        if ( ! empty( $dupes ) ) {
            $dupe_list = implode( ', ', array_keys( $dupes ) );
            $warnings[] = "Duplicate id attributes found: {$dupe_list}";
        }
    }

    // Check for unclosed structural tags (basic heuristic)
    $structural_tags = [ 'div', 'section', 'article', 'header', 'footer', 'main', 'nav', 'aside' ];
    foreach ( $structural_tags as $tag ) {
        $open_count  = substr_count( strtolower( $html ), "<{$tag}" );
        $close_count = substr_count( strtolower( $html ), "</{$tag}>" );
        if ( $open_count > $close_count ) {
            $diff = $open_count - $close_count;
            $warnings[] = "Unclosed <{$tag}> tag: {$diff} opening tag(s) without matching closing tag.";
        }
    }

    return $warnings;
}
