<?php
/**
 * Settings Page - Soft UI
 * 
 * Global plugin settings with Soft UI design
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.7.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Render Settings Page
 */
function base47_he_settings_page() {
    
    // Handle form submission
    if ( isset( $_POST['base47_he_save_settings'] ) ) {
        check_admin_referer( 'base47_he_settings' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions.' );
        }
        
        $new_settings = [
            // General
            'debug_mode'                  => isset( $_POST['debug_mode'] ),
            'enable_cache'                => isset( $_POST['enable_cache'] ),
            'cache_lifetime'              => absint( $_POST['cache_lifetime'] ?? 60 ),
            
            // Support
            'support_email'               => sanitize_email( wp_unslash( $_POST['support_email'] ?? 'support@base47.art' ) ),
            
            // Live Editor
            'editor_theme'                => sanitize_key( $_POST['editor_theme'] ?? 'light' ),
            'editor_font_size'            => sanitize_text_field( wp_unslash( $_POST['editor_font_size'] ?? '14px' ) ),
            'editor_mode'                 => sanitize_key( $_POST['editor_mode'] ?? 'advanced' ),
            'line_numbers'                => isset( $_POST['line_numbers'] ),
            'line_wrap'                   => isset( $_POST['line_wrap'] ),
            'autosave_interval'           => absint( $_POST['autosave_interval'] ?? 0 ),
            
            // Logging
            'logging_enabled'             => isset( $_POST['logging_enabled'] ),
            'log_level'                   => sanitize_key( $_POST['log_level'] ?? 'warnings' ),
            'log_retention'               => absint( $_POST['log_retention'] ?? 14 ),
            'max_log_size'                => absint( $_POST['max_log_size'] ?? 5 ),
            
            // Developer Tools
            'show_file_paths'             => isset( $_POST['show_file_paths'] ),
            'show_asset_map'              => isset( $_POST['show_asset_map'] ),
            'experimental_features'       => isset( $_POST['experimental_features'] ),
            'show_performance_metrics'    => isset( $_POST['show_performance_metrics'] ),
            
            // Security
            'restrict_editor_admins'      => isset( $_POST['restrict_editor_admins'] ),
            'disable_upload_editors'      => isset( $_POST['disable_upload_editors'] ),
            'sanitize_output'             => isset( $_POST['sanitize_output'] ),
        ];
        
        // Track if user explicitly set strict sanitization mode
        update_option( 'base47_he_strict_mode_user_set', true );
        
        if ( base47_he_update_settings( $new_settings ) ) {
            // Log settings save
            $user = wp_get_current_user();
            $username = $user->user_login ?? 'Unknown';
            $changed_settings = [];
            $old_settings = base47_he_get_settings();
            foreach ( $new_settings as $key => $value ) {
                if ( isset( $old_settings[$key] ) && $old_settings[$key] !== $value ) {
                    $changed_settings[] = $key;
                }
            }
            if ( ! empty( $changed_settings ) ) {
                base47_he_log( "Settings updated: " . implode( ', ', $changed_settings ) . " by {$username}", 'info' );
            }
            
            echo '<div class="base47-notice base47-notice-success"><span class="dashicons dashicons-yes-alt"></span> Settings saved successfully.</div>';
        } else {
            echo '<div class="base47-notice base47-notice-error"><span class="dashicons dashicons-warning"></span> Failed to save settings.</div>';
        }
    }
    
    $settings = base47_he_get_settings();
    
    ?>
    <div class="wrap base47-settings-soft-ui">
        <div class="b47-wrap" style="padding-bottom:0;">
        <?php base47_he_render_header_nav( 'settings', 'Configure plugin behavior and preferences' ); ?>
        </div>
        
        <!-- SOFT UI HEADER (replaced by b47 header above) -->
        
        <form method="post" action="" class="base47-settings-form">
            <?php wp_nonce_field( 'base47_he_settings' ); ?>
            
            <div class="base47-settings-grid">
                
                <!-- GENERAL SECTION -->
                <div class="base47-settings-card">
                    <div class="card-header">
                        <span class="dashicons dashicons-admin-generic"></span>
                        <h2>General</h2>
                    </div>
                    <div class="card-body">
                        
                        <!-- Debug Mode -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Debug Mode</label>
                                <p class="description">Disables caching and shows developer information</p>
                            </div>
                            <div class="setting-control">
                                <label class="base47-toggle">
                                    <input type="checkbox" name="debug_mode" value="1" <?php checked( $settings['debug_mode'] ); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Enable Caching -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Enable Caching</label>
                                <p class="description">Cache theme discovery and template scans</p>
                            </div>
                            <div class="setting-control">
                                <label class="base47-toggle">
                                    <input type="checkbox" name="enable_cache" value="1" <?php checked( $settings['enable_cache'] ); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Cache Lifetime -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Cache Lifetime</label>
                                <p class="description">How long to cache theme data</p>
                            </div>
                            <div class="setting-control">
                                <div class="input-group">
                                    <input type="number" name="cache_lifetime" value="<?php echo esc_attr( $settings['cache_lifetime'] ); ?>" min="1" max="720" class="form-control">
                                    <span class="input-suffix">minutes</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Support Email -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Support Email</label>
                                <p class="description">Email address to receive support ticket notifications</p>
                            </div>
                            <div class="setting-control">
                                <input type="email" name="support_email" value="<?php echo esc_attr( $settings['support_email'] ?? 'support@base47.art' ); ?>" class="form-control" placeholder="support@base47.art">
                            </div>
                        </div>
                        
                        <!-- Clear Caches -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Clear Caches</label>
                                <p class="description">Remove all cached data</p>
                            </div>
                            <div class="setting-control">
                                <button type="button" class="btn-soft-secondary" id="base47-clear-all-caches">
                                    <span class="dashicons dashicons-trash"></span>
                                    Clear All Caches
                                </button>
                                <span id="base47-cache-status" class="status-message"></span>
                            </div>
                        </div>

                        <!-- Pro Asset Loaders Hook -->
                        <?php if ( apply_filters( 'base47_he_is_pro_active', false ) ) : ?>
                            <?php do_action( 'base47_he_settings_asset_loaders' ); ?>
                        <?php else : ?>
                            <!-- Smart Loader++ (Pro Feature) -->
                            <div class="base47-pro-info-card">
                                <div class="pro-info-header">
                                    <span class="dashicons dashicons-performance"></span>
                                    <h4>Smart Loader++ (Pro)</h4>
                                </div>
                                <div class="pro-info-content">
                                    <p>Advanced asset loading optimization with intelligent caching, minification, and performance monitoring.</p>
                                    <div class="pro-info-cta">
                                        <span class="pro-badge">Available in Base47 HTML Editor Pro</span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                </div>

                <!-- LIVE EDITOR SECTION -->
                <div class="base47-settings-card">
                    <div class="card-header">
                        <span class="dashicons dashicons-edit"></span>
                        <h2>Live Editor</h2>
                    </div>
                    <div class="card-body">
                        
                        <!-- Default Editor Mode -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Default Editor Mode</label>
                                <p class="description">Which editor loads by default</p>
                            </div>
                            <div class="setting-control">
                                <select name="editor_mode" class="form-select">
                                    <option value="classic" <?php selected( $settings['editor_mode'] ?? 'classic', 'classic' ); ?>>Classic (Textarea)</option>
                                    <?php if ( apply_filters( 'base47_he_is_pro_active', false ) ) : ?>
                                    <option value="advanced" <?php selected( $settings['editor_mode'] ?? 'classic', 'advanced' ); ?>>Monaco (VS Code)</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Monaco Editor (Pro Feature) - only show when Pro is NOT active -->
                        <?php if ( ! apply_filters( 'base47_he_is_pro_active', false ) ) : ?>
                        <div class="base47-pro-info-card">
                            <div class="pro-info-header">
                                <span class="dashicons dashicons-editor-code"></span>
                                <h4>Monaco Editor (Pro)</h4>
                            </div>
                            <div class="pro-info-content">
                                <p>Advanced VS Code-style editor with syntax highlighting, IntelliSense, and advanced editing features.</p>
                                <div class="pro-info-cta">
                                    <span class="pro-badge">Available in Base47 HTML Editor Pro</span>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Line Wrapping (Free) -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Line Wrapping</label>
                                <p class="description">Wrap long lines instead of scroll</p>
                            </div>
                            <div class="setting-control">
                                <label class="base47-toggle">
                                    <input type="checkbox" name="line_wrap" value="1" <?php checked( $settings['line_wrap'] ); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Auto-save Interval (Basic in Free) -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Auto-save Interval</label>
                                <p class="description">Automatically save changes</p>
                            </div>
                            <div class="setting-control">
                                <select name="autosave_interval" class="form-select">
                                    <option value="0" <?php selected( $settings['autosave_interval'], 0 ); ?>>Disabled</option>
                                    <option value="30" <?php selected( $settings['autosave_interval'], 30 ); ?>>30 seconds</option>
                                    <option value="60" <?php selected( $settings['autosave_interval'], 60 ); ?>>1 minute</option>
                                    <option value="120" <?php selected( $settings['autosave_interval'], 120 ); ?>>2 minutes</option>
                                </select>
                            </div>
                        </div>

                        <!-- Pro Editor Features Hook -->
                        <?php if ( apply_filters( 'base47_he_is_pro_active', false ) ) : ?>
                            <?php do_action( 'base47_he_settings_editor_features' ); ?>
                        <?php else : ?>
                            <!-- Monaco Editor Theme (Pro Feature) -->
                            <div class="base47-pro-info-card">
                                <div class="pro-info-header">
                                    <span class="dashicons dashicons-admin-appearance"></span>
                                    <h4>Monaco Editor Themes (Pro)</h4>
                                </div>
                                <div class="pro-info-content">
                                    <p>Choose from multiple Monaco Editor themes including VS Dark, Light, and High Contrast modes.</p>
                                    <div class="pro-info-cta">
                                        <span class="pro-badge">Available in Base47 HTML Editor Pro</span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                </div>

                <!-- LOGGING SECTION -->
                <div class="base47-settings-card">
                    <div class="card-header">
                        <span class="dashicons dashicons-media-text"></span>
                        <h2>Logging</h2>
                    </div>
                    <div class="card-body">
                        
                        <!-- Enable Logging (Free) -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Enable Logging</label>
                                <p class="description">Log plugin actions and errors</p>
                            </div>
                            <div class="setting-control">
                                <label class="base47-toggle">
                                    <input type="checkbox" name="logging_enabled" value="1" <?php checked( $settings['logging_enabled'] ); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Pro Advanced Logs Hook -->
                        <?php if ( apply_filters( 'base47_he_is_pro_active', false ) ) : ?>
                            <?php do_action( 'base47_he_settings_advanced_logs' ); ?>
                        <?php else : ?>
                            <!-- Log Level (Free: Errors/Warnings, Pro: Info/Debug) -->
                            <div class="setting-row">
                                <div class="setting-label">
                                    <label>Log Level</label>
                                    <p class="description">Minimum severity to log</p>
                                </div>
                                <div class="setting-control">
                                    <select name="log_level" class="form-select">
                                        <option value="errors" <?php selected( $settings['log_level'], 'errors' ); ?>>Errors Only</option>
                                        <option value="warnings" <?php selected( $settings['log_level'], 'warnings' ); ?>>Warnings & Errors</option>
                                        <option value="notices" <?php selected( $settings['log_level'], 'notices' ); ?>>All Notices</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Advanced Log Levels (Pro Feature) -->
                            <div class="base47-pro-info-card">
                                <div class="pro-info-header">
                                    <span class="dashicons dashicons-admin-tools"></span>
                                    <h4>Advanced Log Levels (Pro)</h4>
                                </div>
                                <div class="pro-info-content">
                                    <p>Additional logging levels including Info and Debug for comprehensive development and troubleshooting.</p>
                                    <div class="pro-info-cta">
                                        <span class="pro-badge">Available in Base47 HTML Editor Pro</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Log Retention (Free: 7/14 days, Pro: up to 90) -->
                            <div class="setting-row">
                                <div class="setting-label">
                                    <label>Log Retention</label>
                                    <p class="description">Auto-delete old logs</p>
                                </div>
                                <div class="setting-control">
                                    <select name="log_retention" class="form-select">
                                        <option value="3" <?php selected( $settings['log_retention'], 3 ); ?>>3 days</option>
                                        <option value="7" <?php selected( $settings['log_retention'], 7 ); ?>>7 days</option>
                                        <option value="14" <?php selected( $settings['log_retention'], 14 ); ?>>14 days</option>
                                        <option value="30" <?php selected( $settings['log_retention'], 30 ); ?>>30 days</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Extended Log Retention (Pro Feature) -->
                            <div class="base47-pro-info-card">
                                <div class="pro-info-header">
                                    <span class="dashicons dashicons-calendar-alt"></span>
                                    <h4>Extended Log Retention (Pro)</h4>
                                </div>
                                <div class="pro-info-content">
                                    <p>Keep logs for up to 90 days with flexible retention policies for long-term analysis and compliance.</p>
                                    <div class="pro-info-cta">
                                        <span class="pro-badge">Available in Base47 HTML Editor Pro</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Max Log Size (Pro Feature Info) -->
                            <div class="base47-pro-info-card">
                                <div class="pro-info-header">
                                    <span class="dashicons dashicons-admin-settings"></span>
                                    <h4>Configurable Log Size (Pro)</h4>
                                </div>
                                <div class="pro-info-content">
                                    <p>Set custom maximum log file sizes from 1MB to 50MB. Free version uses a fixed 5MB limit.</p>
                                    <div class="pro-info-cta">
                                        <span class="pro-badge">Available in Base47 HTML Editor Pro</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Log Actions -->
                            <div class="setting-row">
                                <div class="setting-label">
                                    <label>Log Actions</label>
                                    <p class="description">Manage log files</p>
                                </div>
                                <div class="setting-control">
                                    <div class="button-group">
                                        <button type="button" class="btn-soft-secondary" id="base47-clear-logs">
                                            <span class="dashicons dashicons-trash"></span>
                                            Clear Logs
                                        </button>
                                    </div>
                                    <span id="base47-log-status" class="status-message"></span>
                                </div>
                            </div>
                            
                            <!-- Log Download (Pro Feature) -->
                            <div class="base47-pro-info-card">
                                <div class="pro-info-header">
                                    <span class="dashicons dashicons-download"></span>
                                    <h4>Download Logs (Pro)</h4>
                                </div>
                                <div class="pro-info-content">
                                    <p>Download all log files as a ZIP archive for detailed analysis and debugging.</p>
                                    <div class="pro-info-cta">
                                        <span class="pro-badge">Available in Base47 HTML Editor Pro</span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                </div>

                <!-- DEVELOPER TOOLS SECTION -->
                <div class="base47-settings-card">
                    <div class="card-header">
                        <span class="dashicons dashicons-admin-tools"></span>

<style>
/* Pro Info Cards - WordPress.org Compliant */
.base47-pro-info-card {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px;
    margin: 12px 0;
    position: relative;
    overflow: hidden;
}

.base47-pro-info-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
}

.pro-info-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.pro-info-header .dashicons {
    color: #6366f1;
    font-size: 18px;
    width: 18px;
    height: 18px;
}

.pro-info-header h4 {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
}

.pro-info-content p {
    margin: 0 0 12px 0;
    font-size: 13px;
    color: #64748b;
    line-height: 1.5;
}

.pro-info-cta {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.pro-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: white;
    font-size: 11px;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.pro-badge::before {
    content: '🔒';
    font-size: 10px;
}

/* Remove old disabled styles */
.base47-pro-disabled {
    display: none !important;
}

.base47-pro-notice {
    display: none !important;
}
</style>
                        <h2>Developer Tools</h2>
                    </div>
                    <div class="card-body">
                        
                        <!-- Pro Developer Tools Hook -->
                        <?php if ( apply_filters( 'base47_he_is_pro_active', false ) ) : ?>
                            <?php do_action( 'base47_he_settings_developer_tools' ); ?>
                        <?php else : ?>
                            <!-- Show File Paths (Pro Feature) -->
                            <div class="base47-pro-info-card">
                                <div class="pro-info-header">
                                    <span class="dashicons dashicons-lock"></span>
                                    <h4>Show File Paths (Pro)</h4>
                                </div>
                                <div class="pro-info-content">
                                    <p>Display template file paths in preview mode for easier debugging and development workflow.</p>
                                    <div class="pro-info-cta">
                                        <span class="pro-badge">Available in Base47 HTML Editor Pro</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Show Loaded Assets (Pro Feature) -->
                            <div class="base47-pro-info-card">
                                <div class="pro-info-header">
                                    <span class="dashicons dashicons-lock"></span>
                                    <h4>Show Loaded Assets (Pro)</h4>
                                </div>
                                <div class="pro-info-content">
                                    <p>Display detailed asset loading information including CSS/JS files, load times, and optimization suggestions.</p>
                                    <div class="pro-info-cta">
                                        <span class="pro-badge">Available in Base47 HTML Editor Pro</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Experimental Features (Pro Feature) -->
                            <div class="base47-pro-info-card">
                                <div class="pro-info-header">
                                    <span class="dashicons dashicons-lock"></span>
                                    <h4>Experimental Features (Pro)</h4>
                                </div>
                                <div class="pro-info-content">
                                    <p>Enable beta features and experimental functionality for advanced users. Use with caution in production environments.</p>
                                    <div class="pro-info-cta">
                                        <span class="pro-badge">Available in Base47 HTML Editor Pro</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Performance Metrics (Pro Feature) -->
                            <div class="base47-pro-info-card">
                                <div class="pro-info-header">
                                    <span class="dashicons dashicons-lock"></span>
                                    <h4>Performance Metrics (Pro)</h4>
                                </div>
                                <div class="pro-info-content">
                                    <p>Display detailed performance data in admin including load times, memory usage, and optimization recommendations.</p>
                                    <div class="pro-info-cta">
                                        <span class="pro-badge">Available in Base47 HTML Editor Pro</span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                </div>

                <!-- SECURITY SECTION -->
                <div class="base47-settings-card">
                    <div class="card-header">
                        <span class="dashicons dashicons-shield"></span>
                        <h2>Security</h2>
                    </div>
                    <div class="card-body">
                        
                        <!-- Restrict Editor to Admins -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Restrict Editor to Admins</label>
                                <p class="description">Only administrators can use Live Editor</p>
                            </div>
                            <div class="setting-control">
                                <label class="base47-toggle">
                                    <input type="checkbox" name="restrict_editor_admins" value="1" <?php checked( $settings['restrict_editor_admins'] ); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Disable Theme Upload for Editors -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Disable Theme Upload</label>
                                <p class="description">Only administrators can upload themes</p>
                            </div>
                            <div class="setting-control">
                                <label class="base47-toggle">
                                    <input type="checkbox" name="disable_upload_editors" value="1" <?php checked( $settings['disable_upload_editors'] ); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Strict Sanitization Mode -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Strict Sanitization Mode</label>
                                <p class="description" style="color:#d63638;font-weight:500;">⚠️ May break scripts, styles, sliders, animations, and external assets. Only enable on multi-user sites where non-admins edit templates.</p>
                            </div>
                            <div class="setting-control">
                                <label class="base47-toggle">
                                    <input type="checkbox" name="sanitize_output" value="1" <?php checked( $settings['sanitize_output'] ); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        
                    </div>
                </div>

                <!-- ADVANCED SECTION -->
                <div class="base47-settings-card">
                    <div class="card-header">
                        <span class="dashicons dashicons-admin-settings"></span>
                        <h2>Advanced</h2>
                    </div>
                    <div class="card-body">
                        
                        <!-- Reset Settings (Free) -->
                        <div class="setting-row">
                            <div class="setting-label">
                                <label>Reset to Defaults</label>
                                <p class="description">Restore all settings to default values</p>
                            </div>
                            <div class="setting-control">
                                <button type="button" id="base47-reset-settings" class="btn-soft-danger">
                                    <span class="dashicons dashicons-undo"></span>
                                    Reset to Defaults
                                </button>
                            </div>
                        </div>
                        
                        <!-- Export/Import Settings (Pro Hook) -->
                        <?php if ( apply_filters( 'base47_he_is_pro_active', false ) ) : ?>
                            <?php do_action( 'base47_he_settings_export_import' ); ?>
                        <?php else : ?>
                            <!-- Export Settings (Pro Feature) -->
                            <div class="base47-pro-info-card">
                                <div class="pro-info-header">
                                    <span class="dashicons dashicons-download"></span>
                                    <h4>Export Settings (Pro)</h4>
                                </div>
                                <div class="pro-info-content">
                                    <p>Download all plugin settings as a JSON file for backup or migration to other sites.</p>
                                    <div class="pro-info-cta">
                                        <span class="pro-badge">Available in Base47 HTML Editor Pro</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Import Settings (Pro Feature) -->
                            <div class="base47-pro-info-card">
                                <div class="pro-info-header">
                                    <span class="dashicons dashicons-upload"></span>
                                    <h4>Import Settings (Pro)</h4>
                                </div>
                                <div class="pro-info-content">
                                    <p>Upload and restore previously exported settings from JSON files with validation and conflict resolution.</p>
                                    <div class="pro-info-cta">
                                        <span class="pro-badge">Available in Base47 HTML Editor Pro</span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                </div>

            </div>
            
            <!-- SAVE BUTTON -->
            <div class="base47-settings-footer">
                <button type="submit" name="base47_he_save_settings" class="btn-soft-primary btn-large">
                    <span class="dashicons dashicons-yes"></span>
                    Save All Settings
                </button>
            </div>
        </form>
    </div>
    
    <?php
}
