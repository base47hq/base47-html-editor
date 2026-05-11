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
 * AJAX: Download logs
 *
 * Pro plugin provides an advanced log download handler.
 * Free version does not register this endpoint — no stub needed.
 */

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
 * Settings export/import are provided by the Pro add-on.
 * Free version does not register stub endpoints — no trialware pattern.
 * Pro plugin registers its own wp_ajax handlers when active.
 */
