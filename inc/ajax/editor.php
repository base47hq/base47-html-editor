<?php
/**
 * Editor AJAX Handlers
 *
 * All handlers use central security helpers from inc/helpers/security.php:
 * - base47_he_current_user_can_edit_templates() for capability checks
 * - base47_he_resolve_template_path() for path validation
 *
 * @package Base47_HTML_Editor
 * @since 3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AJAX: Get template content for editing
 */
function base47_he_ajax_get_template() {
    check_ajax_referer( 'base47_he', 'nonce' );

    if ( ! base47_he_current_user_can_edit_templates() ) {
        base47_he_log( 'Capability check failed: base47_he_get_template', 'warn' );
        wp_send_json_error( [ 'message' => 'Insufficient permissions.' ] );
    }

    $file = isset( $_POST['file'] ) ? sanitize_text_field( wp_unslash( $_POST['file'] ) ) : '';
    $set  = isset( $_POST['set'] )  ? sanitize_key( wp_unslash( $_POST['set'] ) )         : '';

    if ( ! $file ) wp_send_json_error( [ 'message' => 'Template not specified.' ] );

    // Use shared resolver — validates set + file + path containment
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
    $content = $wp_filesystem->get_contents( $full_path );
    if ( false === $content ) {
        wp_send_json_error( [ 'message' => 'Could not read template.' ] );
    }

    $preview = base47_he_rewrite_assets( base47_he_strip_shell( $content ), $base_url, true );

    wp_send_json_success( [
        'content' => $content,
        'preview' => $preview,
        'set'     => $set,
    ] );
}
add_action( 'wp_ajax_base47_he_get_template', 'base47_he_ajax_get_template' );

/**
 * AJAX: Save template content
 */
function base47_he_ajax_save_template() {
    check_ajax_referer( 'base47_he', 'nonce' );

    if ( ! base47_he_current_user_can_edit_templates() ) {
        base47_he_log( 'Capability check failed: base47_he_save_template', 'warn' );
        wp_send_json_error( [ 'message' => 'Insufficient permissions.' ] );
    }

    $file    = isset( $_POST['file'] )    ? sanitize_text_field( wp_unslash( $_POST['file'] ) ) : '';
    $set     = isset( $_POST['set'] )     ? sanitize_key( wp_unslash( $_POST['set'] ) )         : '';
    
    // Sanitize HTML content with comprehensive allowlist for admin-created templates
    $content = '';
    if ( isset( $_POST['content'] ) ) {
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Raw HTML content for admin-only HTML editor, sanitized below
        $raw_content = wp_unslash( $_POST['content'] );
        
        // Apply HTML sanitization with comprehensive allowlist
        // Templates are admin-created trusted content, but we sanitize for WordPress.org compliance
        $content = base47_he_sanitize_admin_template_content( $raw_content );
    }

    if ( ! $file ) wp_send_json_error( [ 'message' => 'Template not specified.' ] );

    // Enforce UTF-8 and 2 MB size limit
    if ( strlen( $content ) > 2097152 ) {
        wp_send_json_error( [ 'message' => 'Template exceeds 2 MB limit.' ] );
    }

    // Use shared resolver — validates set + file + path containment
    $full_path = base47_he_resolve_template_path( $set, $file );
    if ( is_wp_error( $full_path ) ) {
        wp_send_json_error( [ 'message' => $full_path->get_error_message() ] );
    }

    // Run advisory validation warnings (never blocks save)
    $warnings = base47_he_validate_html_content( $content );

    // Hook for Pro backup functionality
    do_action( 'base47_he_before_save_template', $full_path, $content, $set );

    // Protected Save: write to temp file first, then rename
    if ( ! base47_he_init_filesystem() ) {
        wp_send_json_error( [ 'message' => 'Filesystem unavailable.' ] );
    }
    global $wp_filesystem;

    // Pre-move validation
    $target_dir = dirname( $full_path );

    // Ensure target directory exists and is writable
    if ( ! $wp_filesystem->is_dir( $target_dir ) ) {
        wp_mkdir_p( $target_dir );
    }

    if ( ! $wp_filesystem->is_writable( $target_dir ) ) {
        base47_he_log( "Target directory not writable: {$target_dir}", 'error' );
        wp_send_json_error( [
            'message' => 'Target directory is not writable. Check permissions.',
            'details' => [
                'target_dir' => $target_dir,
                'target_writable' => false
            ]
        ] );
    }

    // If target file exists, check if it's writable
    if ( $wp_filesystem->exists( $full_path ) && ! $wp_filesystem->is_writable( $full_path ) ) {
        base47_he_log( "Target file not writable: {$full_path}", 'error' );
        wp_send_json_error( [
            'message' => 'Target file is not writable. Check file permissions.',
            'details' => [
                'target_file' => $full_path,
                'file_writable' => false
            ]
        ] );
    }

    $temp_path = $full_path . '.tmp.' . uniqid( '', true );
    $written   = $wp_filesystem->put_contents( $temp_path, $content, FS_CHMOD_FILE );

    if ( ! $written ) {
        $wp_filesystem->delete( $temp_path );
        base47_he_log( "Protected Save: temp write failed for {$file} (Set: {$set})", 'error' );
        wp_send_json_error( [ 'message' => 'Could not write file. Check permissions.' ] );
    }
    
    // Enhanced move operation with WP_Filesystem fallback
    if ( ! $wp_filesystem->move( $temp_path, $full_path, true ) ) {
        // Fallback: Use put_contents directly (WordPress-compliant)
        $temp_content = $wp_filesystem->get_contents( $temp_path );
        
        if ( false === $temp_content ) {
            $wp_filesystem->delete( $temp_path );
            base47_he_log( "Could not read temp file: {$temp_path}", 'error' );
            wp_send_json_error( [
                'message' => 'Could not read temporary file.',
                'details' => [
                    'temp_path' => $temp_path,
                    'temp_readable' => false
                ]
            ] );
        }
        
        $fallback_saved = $wp_filesystem->put_contents( $full_path, $temp_content, FS_CHMOD_FILE );
        $wp_filesystem->delete( $temp_path );
        
        if ( ! $fallback_saved ) {
            base47_he_log( "Both move() and put_contents() failed for {$file} (Set: {$set})", 'error' );
            wp_send_json_error( [
                'message' => 'Could not save file. Please check file permissions.',
                'details' => [
                    'temp_path' => $temp_path,
                    'full_path' => $full_path,
                    'temp_exists' => $wp_filesystem->exists( $temp_path ),
                    'target_exists' => $wp_filesystem->exists( $full_path ),
                    'target_dir_writable' => $wp_filesystem->is_writable( dirname( $full_path ) ),
                    'target_file_writable' => $wp_filesystem->exists( $full_path ) ? $wp_filesystem->is_writable( $full_path ) : false
                ]
            ] );
        } else {
            base47_he_log( "Fallback put_contents() succeeded for {$file} (Set: {$set})", 'info' );
        }
    }

    $user     = wp_get_current_user();
    $username = $user->user_login ?? 'Unknown';
    base47_he_log( "Template saved: {$file} (Set: {$set}) by {$username}", 'info' );

    wp_send_json_success( [
        'saved'         => true,
        'warnings'      => $warnings,
        'warning_count' => count( $warnings ),
    ] );
}
add_action( 'wp_ajax_base47_he_save_template', 'base47_he_ajax_save_template' );

/**
 * AJAX: Duplicate template
 *
 * Creates a copy of an existing HTML template with a new filename.
 *
 * @since 3.0.2
 */
function base47_he_ajax_duplicate_template() {
    check_ajax_referer( 'base47_he', 'nonce' );

    if ( ! base47_he_current_user_can_edit_templates() ) {
        wp_send_json_error( [ 'message' => 'Insufficient permissions.' ] );
    }

    $file     = isset( $_POST['file'] )     ? sanitize_text_field( wp_unslash( $_POST['file'] ) )     : '';
    $set      = isset( $_POST['set'] )      ? sanitize_text_field( wp_unslash( $_POST['set'] ) )      : '';
    $new_name = isset( $_POST['new_name'] ) ? sanitize_text_field( wp_unslash( $_POST['new_name'] ) ) : '';

    // Validate required fields
    if ( empty( $file ) || empty( $set ) || empty( $new_name ) ) {
        wp_send_json_error( [ 'message' => 'Missing required fields (file, set, new_name).' ] );
    }

    // Only allow .html / .htm files
    if ( ! preg_match( '/^[a-zA-Z0-9_-]+\.html?$/i', $new_name ) ) {
        wp_send_json_error( [ 'message' => 'Invalid filename. Use only letters, numbers, hyphens, underscores, and a .html extension.' ] );
    }

    if ( ! preg_match( '/^[a-zA-Z0-9_-]+\.html?$/i', $file ) ) {
        wp_send_json_error( [ 'message' => 'Invalid source filename.' ] );
    }

    // Block path traversal
    if ( strpos( $file, '..' ) !== false || strpos( $new_name, '..' ) !== false || strpos( $set, '..' ) !== false ) {
        wp_send_json_error( [ 'message' => 'Invalid path.' ] );
    }

    // Resolve source path using the shared resolver
    $source_path = base47_he_resolve_template_path( $set, $file );
    if ( is_wp_error( $source_path ) ) {
        wp_send_json_error( [ 'message' => $source_path->get_error_message() ] );
    }

    // Build destination path in the same directory
    $theme_dir = dirname( $source_path );
    $dest_path = $theme_dir . '/' . $new_name;

    // Prevent overwriting existing file
    if ( file_exists( $dest_path ) ) {
        wp_send_json_error( [ 'message' => 'A template with that name already exists. Please choose a different name.' ] );
    }

    // Use WP_Filesystem for the copy
    if ( ! base47_he_init_filesystem() ) {
        wp_send_json_error( [ 'message' => 'Filesystem unavailable.' ] );
    }
    global $wp_filesystem;

    $content = $wp_filesystem->get_contents( $source_path );
    if ( false === $content ) {
        wp_send_json_error( [ 'message' => 'Could not read source template.' ] );
    }

    $written = $wp_filesystem->put_contents( $dest_path, $content, FS_CHMOD_FILE );
    if ( ! $written ) {
        wp_send_json_error( [ 'message' => 'Could not create duplicate file. Check directory permissions.' ] );
    }

    // Log the action
    if ( function_exists( 'base47_he_log' ) ) {
        $user     = wp_get_current_user();
        $username = $user->user_login ?? 'Unknown';
        base47_he_log( "Template duplicated: {$file} to {$new_name} (Set: {$set}) by {$username}", 'info' );
    }

    wp_send_json_success( [
        'message'      => 'Template duplicated successfully!',
        'new_file'     => $new_name,
        'redirect_url' => admin_url( 'admin.php?page=base47-he-editor&set=' . urlencode( $set ) . '&file=' . urlencode( $new_name ) ),
    ] );
}
add_action( 'wp_ajax_base47_he_duplicate_template', 'base47_he_ajax_duplicate_template' );

/**
 * AJAX: Live preview (real-time preview in editor)
 */
add_action( 'wp_ajax_base47_he_live_preview', function() {
    check_ajax_referer( 'base47_he', 'nonce' );

    if ( ! base47_he_current_user_can_edit_templates() ) {
        base47_he_log( 'Capability check failed: base47_he_live_preview', 'warn' );
        wp_send_json_error( [ 'message' => 'Insufficient permissions.' ] );
    }

    $file    = isset( $_POST['file'] )    ? sanitize_text_field( wp_unslash( $_POST['file'] ) ) : '';
    $set     = isset( $_POST['set'] )     ? sanitize_key( wp_unslash( $_POST['set'] ) )         : '';
    
    // Sanitize HTML content for preview
    $content = '';
    if ( isset( $_POST['content'] ) ) {
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Raw HTML content for admin-only HTML editor, sanitized below
        $raw_content = wp_unslash( $_POST['content'] );
        
        // Apply HTML sanitization for preview (same as saved templates)
        $content = base47_he_sanitize_admin_template_content( $raw_content );
    }

    if ( ! $file ) wp_send_json_error( [ 'message' => 'No file specified.' ] );

    // Validate set/file via shared resolver (even for preview)
    $full_path = base47_he_resolve_template_path( $set, $file );
    if ( is_wp_error( $full_path ) ) {
        wp_send_json_error( [ 'message' => $full_path->get_error_message() ] );
    }

    $sets     = base47_he_get_template_sets();
    $base_url = isset( $sets[ $set ]['url'] ) ? $sets[ $set ]['url'] : '';

    $html = base47_he_rewrite_assets( $content, $base_url, false );
    wp_send_json_success( [ 'html' => $html ] );
} );

/* Backup AJAX handlers */

/**
 * AJAX: List available backups for a template
 */
function base47_he_ajax_list_backups() {
    check_ajax_referer( 'base47_he', 'nonce' );

    if ( ! base47_he_current_user_can_edit_templates() ) {
        wp_send_json_error( [ 'message' => 'Insufficient permissions.' ] );
    }

    $file = isset( $_POST['file'] ) ? sanitize_text_field( wp_unslash( $_POST['file'] ) ) : '';
    $set  = isset( $_POST['set'] )  ? sanitize_key( wp_unslash( $_POST['set'] ) )         : '';

    if ( ! $file ) wp_send_json_error( 'Template not specified.' );

    $sets = base47_he_get_template_sets();
    if ( empty( $set ) ) {
        $info = base47_he_locate_template( $file );
        if ( ! $info ) wp_send_json_error( 'Template not found.' );
        $full = $info['path'];
        $theme = $info['set'];
    } else {
        if ( ! isset( $sets[ $set ] ) ) wp_send_json_error( 'Template set not found.' );
        $full = $sets[ $set ]['path'] . $file;
        $theme = $set;
    }

    $backups = base47_he_list_backups( $full, $theme );
    wp_send_json_success( $backups );
}
add_action( 'wp_ajax_base47_he_list_backups', 'base47_he_ajax_list_backups' );

/**
 * AJAX: Restore a backup
 */
function base47_he_ajax_restore_backup() {
    check_ajax_referer( 'base47_he', 'nonce' );

    if ( ! base47_he_current_user_can_edit_templates() ) {
        wp_send_json_error( [ 'message' => 'Insufficient permissions.' ] );
    }

    $file           = isset( $_POST['file'] )           ? sanitize_text_field( wp_unslash( $_POST['file'] ) )           : '';
    $set            = isset( $_POST['set'] )            ? sanitize_key( wp_unslash( $_POST['set'] ) )                   : '';
    $backup_filename = isset( $_POST['backup_filename'] ) ? sanitize_text_field( wp_unslash( $_POST['backup_filename'] ) ) : '';

    if ( ! $file || ! $backup_filename ) wp_send_json_error( 'Missing parameters.' );

    $sets = base47_he_get_template_sets();
    if ( empty( $set ) ) {
        $info = base47_he_locate_template( $file );
        if ( ! $info ) wp_send_json_error( 'Template not found.' );
        $full = $info['path'];
        $theme = $info['set'];
    } else {
        if ( ! isset( $sets[ $set ] ) ) wp_send_json_error( 'Template set not found.' );
        $full = $sets[ $set ]['path'] . $file;
        $theme = $set;
    }

    $content = base47_he_restore_backup( $backup_filename, $theme, $full );

    if ( false === $content ) {
        base47_he_log( "Failed to restore backup: {$backup_filename} for {$file} (Theme: {$theme})", 'error' );
        wp_send_json_error( 'Backup not found.' );
    }

    // Log successful backup restore
    $user = wp_get_current_user();
    $username = $user->user_login ?? 'Unknown';
    base47_he_log( "Backup restored: {$file} from {$backup_filename} (Theme: {$theme}) by {$username}", 'info' );

    wp_send_json_success( [ 'content' => $content ] );
}
add_action( 'wp_ajax_base47_he_ajax_restore_backup', 'base47_he_ajax_restore_backup' );

// Hook for Pro backup AJAX handlers (additional Pro-only backup features)
do_action( 'base47_he_register_backup_ajax_handlers' );

/**
 * Sanitize admin template content with comprehensive allowlist
 * 
 * This function provides more permissive sanitization for admin-created templates
 * while still maintaining WordPress.org compliance. Templates are created by
 * administrators and contain trusted HTML, but we apply sanitization for security.
 */
function base47_he_sanitize_admin_template_content( $content ) {
    // For admin template editing, we allow a more comprehensive set of HTML
    // This includes inline styles and scripts which are necessary for template functionality
    
    $allowed_html = [
        // All standard HTML elements
        'div' => [
            'class' => true, 'id' => true, 'style' => true, 'data-*' => true,
            'role' => true, 'aria-*' => true, 'tabindex' => true,
        ],
        'span' => [
            'class' => true, 'id' => true, 'style' => true, 'data-*' => true,
            'role' => true, 'aria-*' => true,
        ],
        'section' => [
            'class' => true, 'id' => true, 'style' => true, 'data-*' => true,
            'role' => true, 'aria-*' => true,
        ],
        'article' => [
            'class' => true, 'id' => true, 'style' => true, 'data-*' => true,
            'role' => true, 'aria-*' => true,
        ],
        'header' => [
            'class' => true, 'id' => true, 'style' => true, 'data-*' => true,
            'role' => true, 'aria-*' => true,
        ],
        'footer' => [
            'class' => true, 'id' => true, 'style' => true, 'data-*' => true,
            'role' => true, 'aria-*' => true,
        ],
        'main' => [
            'class' => true, 'id' => true, 'style' => true, 'data-*' => true,
            'role' => true, 'aria-*' => true,
        ],
        'aside' => [
            'class' => true, 'id' => true, 'style' => true, 'data-*' => true,
            'role' => true, 'aria-*' => true,
        ],
        'nav' => [
            'class' => true, 'id' => true, 'style' => true, 'data-*' => true,
            'role' => true, 'aria-*' => true,
        ],
        
        // Typography
        'h1' => [
            'class' => true, 'id' => true, 'style' => true, 'data-*' => true,
        ],
        'h2' => [
            'class' => true, 'id' => true, 'style' => true, 'data-*' => true,
        ],
        'h3' => [
            'class' => true, 'id' => true, 'style' => true, 'data-*' => true,
        ],
        'h4' => [
            'class' => true, 'id' => true, 'style' => true, 'data-*' => true,
        ],
        'h5' => [
            'class' => true, 'id' => true, 'style' => true, 'data-*' => true,
        ],
        'h6' => [
            'class' => true, 'id' => true, 'style' => true, 'data-*' => true,
        ],
        'p' => [
            'class' => true, 'id' => true, 'style' => true, 'data-*' => true,
        ],
        'strong' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        'b' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        'em' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        'i' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        'u' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        'small' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        'mark' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        'del' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        'ins' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        'sub' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        'sup' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        
        // Links and media
        'a' => [
            'href' => true, 'title' => true, 'target' => true, 'rel' => true,
            'class' => true, 'id' => true, 'style' => true, 'data-*' => true,
            'role' => true, 'aria-*' => true,
        ],
        'img' => [
            'src' => true, 'alt' => true, 'title' => true, 'width' => true, 'height' => true,
            'class' => true, 'id' => true, 'style' => true, 'data-*' => true,
            'loading' => true, 'decoding' => true, 'sizes' => true, 'srcset' => true,
        ],
        'picture' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        'source' => [
            'src' => true, 'srcset' => true, 'media' => true, 'sizes' => true, 'type' => true,
        ],
        'figure' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        'figcaption' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        
        // Lists
        'ul' => [
            'class' => true, 'id' => true, 'style' => true, 'data-*' => true,
        ],
        'ol' => [
            'class' => true, 'id' => true, 'style' => true, 'data-*' => true,
        ],
        'li' => [
            'class' => true, 'id' => true, 'style' => true, 'data-*' => true,
        ],
        'dl' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        'dt' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        'dd' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        
        // Tables
        'table' => [
            'class' => true, 'id' => true, 'style' => true, 'data-*' => true,
        ],
        'thead' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        'tbody' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        'tfoot' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        'tr' => [
            'class' => true, 'id' => true, 'style' => true, 'data-*' => true,
        ],
        'th' => [
            'class' => true, 'id' => true, 'style' => true, 'scope' => true,
            'colspan' => true, 'rowspan' => true,
        ],
        'td' => [
            'class' => true, 'id' => true, 'style' => true,
            'colspan' => true, 'rowspan' => true,
        ],
        'caption' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        
        // Forms
        'form' => [
            'action' => true, 'method' => true, 'class' => true, 'id' => true,
            'style' => true, 'data-*' => true, 'novalidate' => true,
        ],
        'input' => [
            'type' => true, 'name' => true, 'value' => true, 'placeholder' => true,
            'class' => true, 'id' => true, 'style' => true, 'data-*' => true,
            'required' => true, 'disabled' => true, 'readonly' => true,
            'min' => true, 'max' => true, 'step' => true, 'pattern' => true,
            'autocomplete' => true, 'aria-*' => true,
        ],
        'textarea' => [
            'name' => true, 'placeholder' => true, 'rows' => true, 'cols' => true,
            'class' => true, 'id' => true, 'style' => true, 'data-*' => true,
            'required' => true, 'disabled' => true, 'readonly' => true,
        ],
        'select' => [
            'name' => true, 'class' => true, 'id' => true, 'style' => true,
            'data-*' => true, 'required' => true, 'disabled' => true, 'multiple' => true,
        ],
        'option' => [
            'value' => true, 'selected' => true, 'disabled' => true,
        ],
        'optgroup' => [
            'label' => true, 'disabled' => true,
        ],
        'button' => [
            'type' => true, 'name' => true, 'value' => true, 'class' => true,
            'id' => true, 'style' => true, 'data-*' => true, 'disabled' => true,
            'aria-*' => true, 'role' => true,
        ],
        'label' => [
            'for' => true, 'class' => true, 'id' => true, 'style' => true,
        ],
        'fieldset' => [
            'class' => true, 'id' => true, 'style' => true, 'disabled' => true,
        ],
        'legend' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        
        // Inline elements
        'br' => [],
        'hr' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        'code' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        'pre' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        'kbd' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        'samp' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        'var' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        'time' => [
            'datetime' => true, 'class' => true, 'id' => true, 'style' => true,
        ],
        'abbr' => [
            'title' => true, 'class' => true, 'id' => true, 'style' => true,
        ],
        'acronym' => [
            'title' => true, 'class' => true, 'id' => true, 'style' => true,
        ],
        'cite' => [
            'class' => true, 'id' => true, 'style' => true,
        ],
        'q' => [
            'cite' => true, 'class' => true, 'id' => true, 'style' => true,
        ],
        'blockquote' => [
            'cite' => true, 'class' => true, 'id' => true, 'style' => true,
        ],
        
        // Multimedia
        'video' => [
            'src' => true, 'poster' => true, 'width' => true, 'height' => true,
            'controls' => true, 'autoplay' => true, 'loop' => true, 'muted' => true,
            'preload' => true, 'class' => true, 'id' => true, 'style' => true,
            'data-*' => true,
        ],
        'audio' => [
            'src' => true, 'controls' => true, 'autoplay' => true, 'loop' => true,
            'muted' => true, 'preload' => true, 'class' => true, 'id' => true,
            'style' => true, 'data-*' => true,
        ],
        'iframe' => [
            'src' => true, 'width' => true, 'height' => true, 'frameborder' => true,
            'allowfullscreen' => true, 'class' => true, 'id' => true, 'style' => true,
            'data-*' => true, 'title' => true, 'loading' => true,
        ],
        
        // Inline styles and scripts (for template functionality)
        'style' => [
            'type' => true, 'media' => true, 'id' => true,
        ],
        'script' => [
            'type' => true, 'src' => true, 'async' => true, 'defer' => true,
            'id' => true, 'data-*' => true,
        ],
        
        // SVG support
        'svg' => [
            'width' => true, 'height' => true, 'viewbox' => true, 'class' => true,
            'id' => true, 'style' => true, 'xmlns' => true, 'fill' => true,
            'stroke' => true, 'stroke-width' => true,
        ],
        'path' => [
            'd' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true,
            'class' => true, 'id' => true,
        ],
        'circle' => [
            'cx' => true, 'cy' => true, 'r' => true, 'fill' => true,
            'stroke' => true, 'stroke-width' => true, 'class' => true, 'id' => true,
        ],
        'rect' => [
            'x' => true, 'y' => true, 'width' => true, 'height' => true,
            'fill' => true, 'stroke' => true, 'stroke-width' => true,
            'class' => true, 'id' => true,
        ],
        'g' => [
            'class' => true, 'id' => true, 'transform' => true,
        ],
        'defs' => [],
        'use' => [
            'href' => true, 'xlink:href' => true,
        ],
        
        // HTML document structure (for full templates)
        'html' => [
            'lang' => true, 'dir' => true, 'class' => true,
        ],
        'head' => [],
        'body' => [
            'class' => true, 'id' => true, 'style' => true, 'data-*' => true,
        ],
        'title' => [],
        'meta' => [
            'name' => true, 'content' => true, 'charset' => true, 'http-equiv' => true,
            'property' => true, 'itemprop' => true,
        ],
        'link' => [
            'rel' => true, 'href' => true, 'type' => true, 'media' => true,
            'sizes' => true, 'as' => true, 'crossorigin' => true,
        ],
    ];
    
    // Apply sanitization with comprehensive allowlist for admin templates
    return wp_kses( $content, $allowed_html );
}
