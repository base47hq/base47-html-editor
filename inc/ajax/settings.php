<?php
/**
 * Settings AJAX Handlers
 * 
 * Handles AJAX requests for settings page actions.
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.4.5
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AJAX: Clear all caches
 */
function base47_he_ajax_clear_all_caches() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Insufficient permissions.' ] );
    }
    
    // Clear theme caches
    base47_he_refresh_theme_caches();
    
    // Clear WordPress transients
    delete_transient( 'base47_he_cache_template_sets' );
    delete_transient( 'base47_he_cache_templates' );
    
    // Log cache clear
    $user = wp_get_current_user();
    $username = $user->user_login ?? 'Unknown';
    base47_he_log( "All caches cleared by {$username}", 'info' );
    
    wp_send_json_success( [ 'message' => 'All caches cleared successfully.' ] );
}
add_action( 'wp_ajax_base47_clear_all_caches', 'base47_he_ajax_clear_all_caches' );

/**
 * AJAX: Clear logs
 */
function base47_he_ajax_clear_logs() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Insufficient permissions.' ] );
    }
    
    // Log before clearing (will be cleared too, but good practice)
    $user = wp_get_current_user();
    $username = $user->user_login ?? 'Unknown';
    base47_he_log( "Logs cleared by {$username}", 'warning' );
    
    // Clear using the helper function
    base47_he_clear_logs();
    
    wp_send_json_success( [ 
        'message' => 'Logs cleared successfully.'
    ] );
}
add_action( 'wp_ajax_base47_clear_logs', 'base47_he_ajax_clear_logs' );

/**
 * AJAX: Download logs - PRO ONLY (stub)
 * Only registered when Pro is NOT active. When Pro is active, the real handler in
 * class-advanced-logs.php takes over.
 */
function base47_he_ajax_download_logs() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Insufficient permissions.' );
    }
    
    wp_send_json_error( [
        'message' => 'Log download is available in Base47 HTML Editor Pro.',
        'upgrade_url' => 'https://base47.art/base47-html-editor/',
        'pro_feature' => true,
    ] );
}
// Only register stub when Pro is not active — Pro registers its own real handler.
// Deferred to 'init' because Pro registers its filter at plugins_loaded priority 20.
add_action( 'init', function() {
    if ( ! apply_filters( 'base47_he_is_pro_active', false ) ) {
        add_action( 'wp_ajax_base47_download_logs', 'base47_he_ajax_download_logs' );
    }
}, 5 );

/**
 * AJAX: Reset settings to defaults
 */
function base47_he_ajax_reset_settings() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Insufficient permissions.' ] );
    }
    
    if ( base47_he_reset_settings() ) {
        // Log settings reset
        $user = wp_get_current_user();
        $username = $user->user_login ?? 'Unknown';
        base47_he_log( "Settings reset to defaults by {$username}", 'warning' );
        
        wp_send_json_success( [ 'message' => 'Settings reset to defaults.' ] );
    } else {
        wp_send_json_error( [ 'message' => 'Failed to reset settings.' ] );
    }
}
add_action( 'wp_ajax_base47_reset_settings', 'base47_he_ajax_reset_settings' );

/**
 * AJAX: Export settings - PRO ONLY (stub)
 * Only registered when Pro is NOT active. When Pro is active, the real handler in
 * class-settings-manager.php takes over.
 */
function base47_he_ajax_export_settings() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Insufficient permissions.' );
    }
    
    wp_send_json_error( [
        'message' => 'Settings export is available in Base47 HTML Editor Pro.',
        'upgrade_url' => 'https://base47.art/base47-html-editor/',
        'pro_feature' => true,
    ] );
}

/**
 * AJAX: Import settings - PRO ONLY (stub)
 * Only registered when Pro is NOT active. When Pro is active, the real handler in
 * class-settings-manager.php takes over.
 */
function base47_he_ajax_import_settings() {
    check_ajax_referer( 'base47_he', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Insufficient permissions.' ] );
    }
    
    wp_send_json_error( [
        'message' => 'Settings import is available in Base47 HTML Editor Pro.',
        'upgrade_url' => 'https://base47.art/base47-html-editor/',
        'pro_feature' => true,
    ] );
}

// Only register export/import stubs when Pro is not active — Pro registers its own real handlers.
// Deferred to 'init' because Pro registers its filter at plugins_loaded priority 20.
add_action( 'init', function() {
    if ( ! apply_filters( 'base47_he_is_pro_active', false ) ) {
        add_action( 'wp_ajax_base47_export_settings', 'base47_he_ajax_export_settings' );
        add_action( 'wp_ajax_base47_import_settings', 'base47_he_ajax_import_settings' );
    }
}, 5 );
