<?php
/**
 * Marketplace AJAX Handlers
 * 
 * Handles template loading, installation, and preview
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.9.4
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Load marketplace templates
 */
add_action( 'wp_ajax_base47_he_load_marketplace', 'base47_he_load_marketplace' );
function base47_he_load_marketplace() {
    check_ajax_referer( 'base47_he_nonce', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Insufficient permissions' );
    }
    
    // Load templates from JSON file
    $upload_dir = wp_upload_dir();
    $json_file = $upload_dir['basedir'] . '/base47-downloads/metadata/templates.json';
    
    $templates = array();
    
    if ( file_exists( $json_file ) ) {
        $json_content = false;
        if ( base47_he_init_filesystem() ) {
            global $wp_filesystem;
            $json_content = $wp_filesystem->get_contents( $json_file );
        }
        $data = json_decode( $json_content, true );
        
        if ( $data && isset( $data['templates'] ) ) {
            // Replace placeholders with actual URLs
            $site_url = get_site_url();
            $uploads_url = $upload_dir['baseurl'];
            
            // Get list of installed themes
            $installed_themes = base47_he_get_installed_theme_slugs();
            
            foreach ( $data['templates'] as $template ) {
                // Replace URL placeholders
                $template['thumbnail'] = str_replace( '{UPLOADS_URL}', $uploads_url, $template['thumbnail'] ?? '' );
                $template['preview_url'] = str_replace( '{SITE_URL}', $site_url, $template['preview_url'] ?? '' );
                $template['download_url'] = str_replace( '{UPLOADS_URL}', $uploads_url, $template['download_url'] ?? '' );
                
                // Check if theme is already installed
                $theme_slug = $template['slug'] ?? $template['id'];
                $is_installed = in_array( $theme_slug, $installed_themes ) || 
                               in_array( $theme_slug . '-templates', $installed_themes );
                
                // Convert to format expected by JavaScript
                $templates[] = array(
                    'id' => $template['id'],
                    'name' => $template['name'],
                    'description' => $template['description'],
                    'category' => strtolower( str_replace( ' ', '', $template['category'] ?? '' ) ),
                    'type' => $template['type'],
                    'price' => $template['price'] > 0 ? '$' . $template['price'] : 'Free',
                    'rating' => $template['rating'],
                    'reviews' => $template['reviews'],
                    'downloads' => $template['downloads'],
                    'thumbnail' => $template['thumbnail'],
                    'preview_url' => $template['preview_url'],
                    'download_url' => $template['download_url'],
                    'installed' => $is_installed
                );
            }
        }
    }
    
    // If no templates loaded, provide default fallback templates
    if ( empty( $templates ) ) {
        $templates = base47_he_get_default_marketplace_templates();
    }
    
    wp_send_json_success( array(
        'templates' => $templates
    ) );
}

/**
 * Get installed theme slugs
 */
function base47_he_get_installed_theme_slugs() {
    $themes_root = base47_he_get_themes_root();
    $installed = array();
    
    if ( ! is_dir( $themes_root ) ) {
        return $installed;
    }
    
    $dirs = scandir( $themes_root );
    foreach ( $dirs as $dir ) {
        if ( $dir === '.' || $dir === '..' ) {
            continue;
        }
        
        $theme_path = $themes_root . $dir;
        if ( is_dir( $theme_path ) && file_exists( $theme_path . '/theme.json' ) ) {
            // Remove '-templates' suffix if present
            $slug = str_replace( '-templates', '', $dir );
            $installed[] = $slug;
        }
    }
    
    return $installed;
}

/**
 * Get default marketplace templates (fallback)
 */
function base47_he_get_default_marketplace_templates() {
    return array(
        array(
            'id' => 'coming-soon-1',
            'name' => 'More Templates Coming Soon',
            'description' => 'We are constantly adding new professional templates to the marketplace. Check back soon for SaaS, Agency, E-commerce, and more!',
            'category' => 'all',
            'type' => 'info',
            'price' => 'Free',
            'rating' => 5,
            'reviews' => 0,
            'downloads' => 0,
            'thumbnail' => BASE47_HE_URL . 'admin-assets/images/base47-icon-gradient.svg',
            'preview_url' => 'https://base47.art/marketplace/',
            'download_url' => ''
        ),
        array(
            'id' => 'visit-marketplace',
            'name' => 'Visit Our Website',
            'description' => 'Browse our complete template collection on base47.art. Download templates and upload them via Theme Manager.',
            'category' => 'all',
            'type' => 'link',
            'price' => 'Free',
            'rating' => 5,
            'reviews' => 0,
            'downloads' => 0,
            'thumbnail' => BASE47_HE_URL . 'admin-assets/images/base47-icon-gradient.svg',
            'preview_url' => 'https://base47.art/marketplace/',
            'download_url' => ''
        )
    );
}

/**
 * Install marketplace template
 */
add_action( 'wp_ajax_base47_he_install_marketplace_template', 'base47_he_install_marketplace_template' );
function base47_he_install_marketplace_template() {
    check_ajax_referer( 'base47_he_nonce', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Insufficient permissions' );
    }
    
    $template_id = isset( $_POST['template_id'] ) ? sanitize_text_field( wp_unslash( $_POST['template_id'] ) ) : '';
    
    if ( ! $template_id ) {
        wp_send_json_error( 'Invalid template ID' );
    }
    
    // Get upload directory
    $upload_dir = wp_upload_dir();
    $templates_dir = $upload_dir['basedir'] . '/base47-downloads/templates/';
    $zip_file = $templates_dir . $template_id . '.zip';
    
    // Debug info
    $debug_info = array(
        'template_id' => $template_id,
        'templates_dir' => $templates_dir,
        'zip_file' => $zip_file,
        'file_exists' => file_exists( $zip_file ),
        'file_size' => file_exists( $zip_file ) ? filesize( $zip_file ) : 0,
        'is_readable' => file_exists( $zip_file ) ? is_readable( $zip_file ) : false,
        'ziparchive_available' => class_exists( 'ZipArchive' )
    );
    
    // Check if ZIP file exists
    if ( ! file_exists( $zip_file ) ) {
        wp_send_json_error( array(
            'message' => 'Template ZIP file not found: ' . basename( $zip_file ),
            'debug' => $debug_info
        ) );
    }
    
    // Check if file is readable
    if ( ! is_readable( $zip_file ) ) {
        wp_send_json_error( array(
            'message' => 'Template ZIP file is not readable. Please check file permissions.',
            'debug' => $debug_info
        ) );
    }
    
    // Check ZipArchive availability
    if ( ! class_exists( 'ZipArchive' ) ) {
        wp_send_json_error( array(
            'message' => 'ZipArchive extension is not available on this server.',
            'debug' => $debug_info
        ) );
    }
    
    // Load theme installation functions
    if ( ! function_exists( 'base47_he_install_theme_from_zip' ) ) {
        $theme_install_file = plugin_dir_path( __FILE__ ) . '../operations/theme-install.php';
        if ( ! file_exists( $theme_install_file ) ) {
            wp_send_json_error( array(
                'message' => 'Theme installation file not found: ' . $theme_install_file,
                'debug' => $debug_info
            ) );
        }
        require_once $theme_install_file;
    }
    
    // Install the template using existing installation function
    $result = base47_he_install_theme_from_zip( $zip_file );
    
    if ( is_wp_error( $result ) ) {
        wp_send_json_error( array(
            'message' => $result->get_error_message(),
            'error_code' => $result->get_error_code(),
            'debug' => $debug_info
        ) );
    }
    
    // Success - template installed
    wp_send_json_success( array(
        'message' => 'Template installed successfully! You can now use it in the Live Editor.',
        'theme_slug' => $result,
        'redirect_url' => admin_url( 'admin.php?page=base47-he-theme-manager' ),
        'debug' => $debug_info
    ) );
}

/**
 * Download marketplace template
 */
add_action( 'wp_ajax_base47_he_download_marketplace_template', 'base47_he_download_marketplace_template' );
function base47_he_download_marketplace_template() {
    check_ajax_referer( 'base47_he_nonce', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Insufficient permissions' );
    }
    
    $template_id = isset( $_POST['template_id'] ) ? sanitize_text_field( wp_unslash( $_POST['template_id'] ) ) : '';
    
    if ( ! $template_id ) {
        wp_send_json_error( 'Invalid template ID' );
    }
    
    // Get upload directory
    $upload_dir = wp_upload_dir();
    $templates_dir = $upload_dir['basedir'] . '/base47-downloads/templates/';
    $zip_file = $templates_dir . $template_id . '.zip';
    
    // Check if ZIP file exists
    if ( ! file_exists( $zip_file ) ) {
        wp_send_json_error( 'Template ZIP file not found: ' . $template_id . '.zip' );
    }
    
    // Get file info
    $file_size = filesize( $zip_file );
    $file_name = basename( $zip_file );
    
    // Send download URL
    $download_url = $upload_dir['baseurl'] . '/base47-downloads/templates/' . $template_id . '.zip';
    
    wp_send_json_success( array(
        'download_url' => $download_url,
        'file_name' => $file_name,
        'file_size' => $file_size,
        'message' => 'Download ready'
    ) );
}
add_action( 'wp_ajax_base47_he_get_template_preview', 'base47_he_get_template_preview' );
function base47_he_get_template_preview() {
    check_ajax_referer( 'base47_he_nonce', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Insufficient permissions' );
    }
    
    $template_id = isset( $_POST['template_id'] ) ? sanitize_text_field( wp_unslash( $_POST['template_id'] ) ) : '';
    
    if ( ! $template_id ) {
        wp_send_json_error( 'Invalid template ID' );
    }
    
    // Get uploads directory
    $upload_dir = wp_upload_dir();
    $uploads_url = $upload_dir['baseurl'];
    
    // Build preview URL - point directly to the template's index.html file
    $preview_url = $uploads_url . '/base47-downloads/templates/' . $template_id . '/index.html';
    
    wp_send_json_success( array(
        'preview_url' => $preview_url
    ) );
}
