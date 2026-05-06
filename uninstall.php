<?php
/**
 * Uninstall Script for Base47 HTML Editor
 * 
 * This file is executed when the plugin is deleted via WordPress admin.
 * It cleans up all plugin data from the database.
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.8
 */

// Exit if accessed directly or not uninstalling
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

/**
 * Clean up plugin options
 */
function base47_he_uninstall_cleanup() {
    
    // List of all plugin options
    $options = [
        'base47_active_themes',
        'base47_use_manifest',
        'base47_default_theme',
        'base47_he_editor_mode',
        'base47_he_editor_theme',
        'base47_he_cache_enabled',
        'base47_he_cache_ttl',
        'base47_he_minify_enabled',
        'base47_he_version',
        'base47_he_license_key',
        'base47_he_license_status',
        'base47_he_license_data',
        'base47_he_license_checked',
        'base47_he_activation_token',
    ];
    
    // Delete each option
    foreach ( $options as $option ) {
        delete_option( $option );
    }
    
    // Clean up transients (cached data)
    global $wpdb;
    
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional cleanup during uninstall
    $wpdb->query(
        "DELETE FROM {$wpdb->options} 
         WHERE option_name LIKE '_transient_base47_he_%' 
         OR option_name LIKE '_transient_timeout_base47_he_%'"
    );
    
    // Clean up user meta (if any)
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Intentional cleanup during uninstall
    $wpdb->query(
        "DELETE FROM {$wpdb->usermeta} 
         WHERE meta_key LIKE 'base47_he_%'"
    );
}

/**
 * Recursively delete directory using WP_Filesystem
 */
function base47_he_uninstall_rmdir_recursive( $dir ) {
    global $wp_filesystem;

    if ( ! function_exists( 'WP_Filesystem' ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    if ( ! WP_Filesystem() ) {
        return false;
    }

    return $wp_filesystem->delete( $dir, true );
}

/**
 * Optional: Clean up log files
 */
function base47_he_uninstall_delete_logs() {
    
    $upload_dir = wp_upload_dir();
    $log_file = trailingslashit( $upload_dir['basedir'] ) . 'base47-logs/base47-he.log';
    
    if ( file_exists( $log_file ) ) {
        wp_delete_file( $log_file );
    }
    
    $log_dir = trailingslashit( $upload_dir['basedir'] ) . 'base47-logs';
    if ( is_dir( $log_dir ) && count( scandir( $log_dir ) ) === 2 ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Checking if directory is empty
        global $wp_filesystem;
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        if ( WP_Filesystem() ) {
            $wp_filesystem->rmdir( $log_dir );
        }
    }
}

// Execute cleanup
base47_he_uninstall_cleanup();

// Delete logs on uninstall
base47_he_uninstall_delete_logs();

// Backup cleanup moved to PRO plugin
