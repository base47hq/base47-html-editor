<?php
/**
 * License AJAX Handlers
 * 
 * Handles license activation and deactivation via AJAX.
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.9.2.6
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AJAX: Activate License
 * 
 * Activates license in free version, then triggers Pro plugin installation.
 */
add_action( 'wp_ajax_base47_activate_license', 'base47_he_ajax_activate_license' );
function base47_he_ajax_activate_license() {
    // Verify nonce
    check_ajax_referer( 'base47_he', 'nonce' );
    
    // Check permissions
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Permission denied' ] );
    }
    
    $license_key = sanitize_text_field( wp_unslash( $_POST['license_key'] ?? '' ) );
    
    if ( empty( $license_key ) ) {
        wp_send_json_error( [ 'message' => 'License key is required' ] );
    }
    
    // Check if Pro plugin is already active
    if ( class_exists( 'Base47_HE_License_Manager' ) ) {
        // Delegate to Pro license manager
        $license_manager = Base47_HE_License_Manager::instance();
        $result = $license_manager->activate_license( $license_key );
        
        if ( $result['success'] ) {
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( $result );
        }
    }
    
    // Free version - validate license with server and install Pro plugin
    $validation_result = base47_he_validate_license_with_server( $license_key );
    
    if ( ! $validation_result['valid'] ) {
        wp_send_json_error( [ 
            'message' => $validation_result['message'] ?? 'Invalid license key. Please check and try again.'
        ] );
    }
    
    // Save license data INCLUDING activation token
    update_option( 'base47_he_license_key', $license_key );
    update_option( 'base47_he_license_status', 'active' );
    update_option( 'base47_he_license_data', $validation_result['data'] ?? [] );
    update_option( 'base47_he_activation_token', $validation_result['activation_token'] ?? '' );
    update_option( 'base47_he_license_checked', current_time( 'mysql' ) );
    
    // License activated — user must install Pro plugin manually
    wp_send_json_success( [
        'message' => 'License activated! Please install the Pro plugin manually from your account downloads.',
        'data' => $validation_result['data'],
        'pro_installed' => false,
        'download_url' => 'https://base47.art/account/downloads/'
    ] );
}

/**
 * Validate license with Base47 licensing server
 * 
 * Uses Base47 Core API on base47.art to validate license
 */
function base47_he_validate_license_with_server( $license_key ) {
    // Use Base47 Core customer API endpoint (v2)
    $api_url = 'https://base47.art/wp-json/base47-core/v1/license/activate';
    
    $response = wp_remote_post( $api_url, [
        'body' => json_encode([
            'license_key' => $license_key,
            'site_url' => get_site_url(),
            'home_url' => home_url(),
            'wp_site_uuid' => base47_he_get_site_uuid(),
            'product_slug' => 'base47-html-editor-pro',
            'wp_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'plugin_version' => BASE47_HE_VERSION
        ]),
        'headers' => [
            'Content-Type' => 'application/json'
        ],
        'timeout' => 15,
        'sslverify' => true
    ] );
    
    if ( is_wp_error( $response ) ) {
        return [
            'valid' => false,
            'message' => 'Could not connect to license server: ' . $response->get_error_message()
        ];
    }
    
    $status_code = wp_remote_retrieve_response_code( $response );
    $body = wp_remote_retrieve_body( $response );
    
    // Log for debugging
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        error_log( 'License validation response: ' . $body );
    }
    
    $data = json_decode( $body, true );
    
    if ( $status_code !== 200 ) {
        $error_message = $data['message'] ?? 'License validation failed. Please check your license key.';
        
        // If activation limit reached, show active sites
        if (isset($data['data']['active_sites']) && is_array($data['data']['active_sites'])) {
            $sites_list = "\n\nActive sites:\n";
            foreach ($data['data']['active_sites'] as $site) {
                $sites_list .= "• " . $site['site_url'] . " (last seen: " . $site['last_seen'] . ")\n";
            }
            $sites_list .= "\nYou can deactivate sites from your dashboard at https://base47.art/base47/dashboard/";
            $error_message .= $sites_list;
        }
        
        return [
            'valid' => false,
            'message' => $error_message,
            'active_sites' => $data['data']['active_sites'] ?? []
        ];
    }
    
    if ( ! $data || ! isset( $data['success'] ) || ! $data['success'] ) {
        return [
            'valid' => false,
            'message' => $data['message'] ?? 'Invalid response from license server. Please try again or contact support.'
        ];
    }
    
    // Extract activation token from response
    $activation_token = $data['data']['activation_token'] ?? '';
    
    return [
        'valid' => true,
        'data' => $data['data'],
        'activation_token' => $activation_token
    ];
}

/**
 * Get or generate site UUID
 */
function base47_he_get_site_uuid() {
    $uuid = get_option('base47_site_uuid');
    
    if (empty($uuid)) {
        $uuid = wp_generate_uuid4();
        update_option('base47_site_uuid', $uuid, false);
    }
    
    return $uuid;
}

/**
 * Install Pro plugin automatically
 */
/**
 * Install Pro plugin automatically
 *
 * REMOVED: Auto-install of Pro plugin is not permitted per WordPress.org guidelines.
 * Users must install the Pro plugin manually.
 * This stub is kept for backward compatibility only.
 */
function base47_he_install_pro_plugin() {
    return [ 'success' => false, 'message' => 'Auto-install not available. Please install the Pro plugin manually.' ];
}

/**
 * AJAX: Install Pro Plugin — REMOVED
 *
 * This action has been removed for WordPress.org compliance.
 * The free plugin must not install or activate other plugins automatically.
 * Users must install the Pro plugin manually from their account downloads.
 */
add_action( 'wp_ajax_base47_install_pro_plugin', 'base47_he_ajax_install_pro_plugin' );
function base47_he_ajax_install_pro_plugin() {
    // Verify nonce
    check_ajax_referer( 'base47_he', 'nonce' );
    
    // Check permissions
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Permission denied' ] );
    }
    
    wp_send_json_error( [
        'message' => 'Automatic PRO installation is not available yet. Please download the PRO ZIP from your Base47 account and upload it via Plugins → Add New → Upload Plugin.',
        'download_url' => 'https://base47.art/account/downloads/'
    ] );
}

/**
 * AJAX: Deactivate License
 * 
 * Free plugin handler - Pro plugin will override this when active.
 * Always works locally for Free-only installations.
 */
add_action( 'wp_ajax_base47_deactivate_license', 'base47_he_ajax_deactivate_license' );
function base47_he_ajax_deactivate_license() {
    // DEBUG: Log which handler is running
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        error_log( 'FREE: ajax_deactivate_license() called in Free plugin' );
    }
    
    // Verify nonce
    check_ajax_referer( 'base47_he', 'nonce' );
    
    // DEBUG: Log nonce verification success
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        error_log( 'FREE: Nonce verification passed' );
    }
    
    // Check permissions
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Permission denied' ] );
    }
    
    // List of license options to clean up
    $options_to_delete = [
        'base47_he_license_key',
        'base47_he_license_status',
        'base47_he_license_data',
        'base47_he_license_checked',
        'base47_he_activation_token',
    ];
    
    // DEBUG: Log options before deletion
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
        foreach ( $options_to_delete as $option ) {
            $value = get_option( $option, 'NOT_SET' );
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log( "FREE: Before delete - {$option}: " . ( is_array( $value ) ? 'ARRAY' : $value ) );
        }
    }
    
    // Free plugin only - clean up local license data
    foreach ( $options_to_delete as $option ) {
        delete_option( $option );
    }
    
    // DEBUG: Verify options were deleted
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
        foreach ( $options_to_delete as $option ) {
            $value = get_option( $option, 'NOT_SET' );
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log( "FREE: After delete - {$option}: " . ( is_array( $value ) ? 'ARRAY' : $value ) );
        }
    }
    
    // Free plugin - local cleanup only
    wp_send_json_success( [
        'message' => 'License removed from this site. If needed, you can also manage activations from your Base47 account.',
        'account_url' => 'https://base47.art/account/downloads/'
    ] );
}

/**
 * Daily License Verification (Cron)
 * 
 * WordPress.org Free Version: No license verification needed.
 */
function base47_he_verify_license() {
    // WordPress.org free version - no license verification
    return;
}

