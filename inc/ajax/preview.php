<?php
/**
 * Preview AJAX Handlers
 * 
 * Handles template preview operations
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AJAX: Lazy preview for shortcodes page
 */
function base47_he_ajax_lazy_preview() {
    check_ajax_referer( 'base47_he', 'nonce' );

    if ( ! base47_he_current_user_can_edit_templates() ) {
        base47_he_log( 'Capability check failed: base47_he_lazy_preview', 'warn' );
        wp_send_json_error( [ 'message' => 'Insufficient permissions.' ] );
    }

    $file = isset( $_POST['file'] ) ? sanitize_text_field( wp_unslash( $_POST['file'] ) ) : '';
    $set  = isset( $_POST['set'] )  ? sanitize_key( wp_unslash( $_POST['set'] ) )         : '';

    if ( ! $file ) wp_send_json_error( [ 'message' => 'Missing file parameter.' ] );

    // Use shared resolver for path validation
    $full_path = base47_he_resolve_template_path( $set, $file );
    if ( is_wp_error( $full_path ) ) {
        wp_send_json_error( [ 'message' => $full_path->get_error_message() ] );
    }

    $sets     = base47_he_get_template_sets();
    $base_url = isset( $sets[ $set ]['url'] ) ? $sets[ $set ]['url'] : '';

    if ( ! base47_he_init_filesystem() ) {
        wp_send_json_error( [ 'message' => 'Filesystem unavailable.' ] );
    }
    global $wp_filesystem;
    $html = $wp_filesystem->get_contents( $full_path );
    if ( false === $html ) {
        wp_send_json_error( [ 'message' => 'Failed reading template.' ] );
    }

    $html = base47_he_rewrite_assets( $html, $base_url, true );

    // Pre-scan and enqueue special widget assets for preview
    base47_he_enqueue_detected_special_widget_assets( $html );
    
    // Process shortcodes in template HTML
    $html = do_shortcode( $html );

    wp_send_json_success( [
        'html' => $html,
        'set'  => $set,
        'file' => $file,
    ] );
}
add_action( 'wp_ajax_base47_he_lazy_preview', 'base47_he_ajax_lazy_preview' );
// nopriv removed: preview requires authentication (WordPress.org compliance)

/**
 * AJAX: Full template preview
 */
function base47_he_ajax_preview() {
    check_ajax_referer( 'base47_he', 'nonce' );

    if ( ! base47_he_current_user_can_edit_templates() ) {
        base47_he_log( 'Capability check failed: base47_he_preview', 'warn' );
        wp_die( 'Insufficient permissions.', '', [ 'response' => 403 ] );
    }

    $file = isset( $_GET['file'] ) ? sanitize_text_field( wp_unslash( $_GET['file'] ) ) : '';
    $set  = isset( $_GET['set'] )  ? sanitize_key( wp_unslash( $_GET['set'] ) )         : '';

    if ( ! $file ) wp_die( 'Template not specified.' );

    // Use shared resolver for path validation
    $full_path = base47_he_resolve_template_path( $set, $file );
    if ( is_wp_error( $full_path ) ) {
        wp_die( esc_html( $full_path->get_error_message() ) );
    }

    $sets     = base47_he_get_template_sets();
    $base_url = isset( $sets[ $set ]['url'] ) ? $sets[ $set ]['url'] : '';

    if ( ! base47_he_init_filesystem() ) {
        wp_die( 'Filesystem unavailable.' );
    }
    global $wp_filesystem;
    $html = $wp_filesystem->get_contents( $full_path );
    if ( false === $html ) wp_die( 'Template not found.' );

    $html = base47_he_rewrite_assets( $html, $base_url, true );

    // Pre-scan and enqueue special widget assets for preview
    base47_he_enqueue_detected_special_widget_assets( $html );
    
    // Process shortcodes in template HTML
    $html = do_shortcode( $html );

    echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Intentional raw HTML template preview, admin-only
    exit;
}
add_action( 'wp_ajax_base47_he_preview', 'base47_he_ajax_preview' );
// nopriv removed: preview requires authentication (WordPress.org compliance)

/**
 * Helper: Detect default theme (for JS/editor)
 */
function base47_he_detect_default_theme() {
    $sets = base47_he_get_template_sets();
    return array_key_first($sets) ?: '';
}
