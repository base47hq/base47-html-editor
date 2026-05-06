<?php
/**
 * Dashboard Admin Page — Base47 Soft UI
 *
 * @package Base47_HTML_Editor
 * @since 3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function base47_he_dashboard_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $sets    = base47_he_get_template_sets();
    $active  = base47_he_get_active_sets();
    $all     = base47_he_get_all_templates( true );
    $has_pro = base47_he_is_pro_active();
    $version = BASE47_HE_VERSION;

    $total_templates = count( $all );
    $total_sets      = count( $sets );
    $total_shortcodes = $total_templates;
    ?>
    <div class="wrap">
    <div class="b47-wrap">

        <?php
        $header_buttons = '<a href="' . esc_url( admin_url( 'admin.php?page=base47-he-templates' ) ) . '" class="b47-btn b47-btn-secondary b47-btn-sm"><span class="dashicons dashicons-shortcode" style="font-size:16px;width:16px;height:16px;"></span> View Shortcodes</a>'
            . '<a href="' . esc_url( admin_url( 'admin.php?page=base47-he-editor' ) ) . '" class="b47-btn b47-btn-primary b47-btn-sm"><span class="dashicons dashicons-edit" style="font-size:16px;width:16px;height:16px;"></span> Open Editor</a>';
        base47_he_render_header_nav( 'dashboard', 'Professional template management for WordPress', $header_buttons );
        ?>

        <!-- Stats Row -->
        <div class="b47-grid b47-grid-4 b47-mb-24">
            <div class="b47-card b47-stat-card-v2">
                <div class="b47-stat-card-icon blue">
                    <span class="dashicons dashicons-portfolio" style="font-size:20px;width:20px;height:20px;"></span>
                </div>
                <div class="b47-stat-card-label">Template Sets</div>
                <div class="b47-stat-card-value"><?php echo esc_html( $total_sets ); ?></div>
            </div>
            <div class="b47-card b47-stat-card-v2">
                <div class="b47-stat-card-icon indigo">
                    <span class="dashicons dashicons-media-default" style="font-size:20px;width:20px;height:20px;"></span>
                </div>
                <div class="b47-stat-card-label">Total Templates</div>
                <div class="b47-stat-card-value"><?php echo esc_html( $total_templates ); ?></div>
            </div>
            <div class="b47-card b47-stat-card-v2">
                <div class="b47-stat-card-icon green">
                    <span class="dashicons dashicons-shortcode" style="font-size:20px;width:20px;height:20px;"></span>
                </div>
                <div class="b47-stat-card-label">Active Sets</div>
                <div class="b47-stat-card-value"><?php echo esc_html( count( $active ) ); ?></div>
            </div>
            <div class="b47-card b47-stat-card-v2">
                <div class="b47-stat-card-icon amber">
                    <span class="dashicons dashicons-info" style="font-size:20px;width:20px;height:20px;"></span>
                </div>
                <div class="b47-stat-card-label">Version</div>
                <div class="b47-stat-card-value" style="font-size:16px;"><?php echo esc_html( $version ); ?></div>
                <div class="b47-stat-card-meta"><?php echo $has_pro ? 'Pro Active' : 'Free'; ?></div>
            </div>
        </div>

        <!-- Main Content: 2/3 + 1/3 -->
        <div class="b47-grid b47-grid-main b47-mb-24">

            <!-- Left Column -->
            <div>
                <!-- Safety & Protection -->
                <div class="b47-card b47-mb-20">
                    <div class="b47-card-header">
                        <div class="b47-flex b47-items-center b47-gap-8">
                            <div class="b47-section-icon green">
                                <span class="dashicons dashicons-shield" style="font-size:16px;width:16px;height:16px;"></span>
                            </div>
                            <span class="b47-card-title">Safety &amp; Protection</span>
                        </div>
                        <span class="b47-badge b47-badge-green">All Systems Active</span>
                    </div>
                    <div class="b47-safety-grid">
                        <div class="b47-safety-item">
                            <div class="b47-safety-dot green"></div>
                            <div>
                                <div class="b47-safety-title">Protected Save</div>
                                <div class="b47-safety-desc">Atomic writes with temp files</div>
                            </div>
                        </div>
                        <div class="b47-safety-item">
                            <div class="b47-safety-dot green"></div>
                            <div>
                                <div class="b47-safety-title">Backup System</div>
                                <div class="b47-safety-desc">Auto-backup before every save</div>
                            </div>
                        </div>
                        <div class="b47-safety-item">
                            <div class="b47-safety-dot green"></div>
                            <div>
                                <div class="b47-safety-title">Revisions Active</div>
                                <div class="b47-safety-desc">Up to 5 versions per template</div>
                            </div>
                        </div>
                        <div class="b47-safety-item">
                            <div class="b47-safety-dot green"></div>
                            <div>
                                <div class="b47-safety-title">Validation System</div>
                                <div class="b47-safety-desc">Advisory warnings on save</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Templates -->
                <div class="b47-card">
                    <div class="b47-card-header">
                        <div class="b47-flex b47-items-center b47-gap-8">
                            <div class="b47-section-icon blue">
                                <span class="dashicons dashicons-clock" style="font-size:16px;width:16px;height:16px;"></span>
                            </div>
                            <span class="b47-card-title">Templates</span>
                        </div>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=base47-he-editor' ) ); ?>" class="b47-btn b47-btn-ghost b47-btn-xs">View All →</a>
                    </div>
                    <div class="b47-recent-list">
                        <?php
                        $shown = 0;
                        foreach ( $all as $item ) :
                            if ( $shown >= 6 ) break;
                            $shown++;
                            $is_active = in_array( $item['set'], $active, true );
                        ?>
                        <div class="b47-recent-item">
                            <div class="b47-recent-icon">
                                <span class="dashicons dashicons-editor-code" style="font-size:16px;width:16px;height:16px;"></span>
                            </div>
                            <div class="b47-flex-1">
                                <div class="b47-recent-name"><?php echo esc_html( $item['file'] ); ?></div>
                                <div class="b47-recent-meta"><?php echo esc_html( $item['set'] ); ?></div>
                            </div>
                            <span class="b47-badge <?php echo $is_active ? 'b47-badge-green' : 'b47-badge-gray'; ?>" style="font-size:11px;">
                                <?php echo $is_active ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                        <?php if ( empty( $all ) ) : ?>
                        <p style="color:var(--b47-text-muted);font-size:14px;padding:12px 0;">No templates found. Install a theme from the Theme Manager.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div>
                <!-- Quick Actions -->
                <div class="b47-card b47-mb-20">
                    <div class="b47-card-header">
                        <div class="b47-flex b47-items-center b47-gap-8">
                            <div class="b47-section-icon indigo">
                                <span class="dashicons dashicons-lightning" style="font-size:16px;width:16px;height:16px;"></span>
                            </div>
                            <span class="b47-card-title">Quick Actions</span>
                        </div>
                    </div>
                    <div class="b47-actions-list">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=base47-he-editor' ) ); ?>" class="b47-action-item">
                            <div class="b47-action-icon blue"><span class="dashicons dashicons-editor-code" style="font-size:18px;width:18px;height:18px;"></span></div>
                            <div><div class="b47-action-title">Open Editor</div><div class="b47-action-desc">Edit your HTML templates</div></div>
                            <span class="dashicons dashicons-arrow-right-alt2 b47-action-arrow" style="font-size:16px;width:16px;height:16px;"></span>
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=base47-he-templates' ) ); ?>" class="b47-action-item">
                            <div class="b47-action-icon green"><span class="dashicons dashicons-shortcode" style="font-size:18px;width:18px;height:18px;"></span></div>
                            <div><div class="b47-action-title">View Shortcodes</div><div class="b47-action-desc">Copy and manage shortcodes</div></div>
                            <span class="dashicons dashicons-arrow-right-alt2 b47-action-arrow" style="font-size:16px;width:16px;height:16px;"></span>
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=base47-he-theme-manager' ) ); ?>" class="b47-action-item">
                            <div class="b47-action-icon amber"><span class="dashicons dashicons-admin-appearance" style="font-size:18px;width:18px;height:18px;"></span></div>
                            <div><div class="b47-action-title">Theme Manager</div><div class="b47-action-desc">Install and manage template sets</div></div>
                            <span class="dashicons dashicons-arrow-right-alt2 b47-action-arrow" style="font-size:16px;width:16px;height:16px;"></span>
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=base47-he-settings' ) ); ?>" class="b47-action-item">
                            <div class="b47-action-icon purple"><span class="dashicons dashicons-admin-generic" style="font-size:18px;width:18px;height:18px;"></span></div>
                            <div><div class="b47-action-title">Settings</div><div class="b47-action-desc">Configure plugin options</div></div>
                            <span class="dashicons dashicons-arrow-right-alt2 b47-action-arrow" style="font-size:16px;width:16px;height:16px;"></span>
                        </a>
                    </div>
                </div>

                <?php if ( ! $has_pro ) :
                    $dismissed = get_user_meta( get_current_user_id(), 'base47_he_dismiss_pro_upgrade_notice', true );
                    if ( ! $dismissed ) :
                ?>
                <!-- Pro Card -->
                <div class="b47-card b47-pro-card-v2" id="pro-upgrade-notice">
                    <div class="b47-pro-header-v2">
                        <span class="b47-pro-badge-v2">PRO</span>
                        <div class="b47-pro-title-v2">Unlock Pro Features</div>
                        <div class="b47-pro-subtitle-v2">Take your workflow to the next level</div>
                    </div>
                    <div class="b47-pro-features-v2">
                        <div class="b47-pro-feature-item"><span class="dashicons dashicons-yes" style="font-size:16px;width:16px;height:16px;color:var(--b47-primary);"></span> Monaco Editor (VS Code)</div>
                        <div class="b47-pro-feature-item"><span class="dashicons dashicons-yes" style="font-size:16px;width:16px;height:16px;color:var(--b47-primary);"></span> Advanced Revisions</div>
                        <div class="b47-pro-feature-item"><span class="dashicons dashicons-yes" style="font-size:16px;width:16px;height:16px;color:var(--b47-primary);"></span> Import / Export</div>
                        <div class="b47-pro-feature-item"><span class="dashicons dashicons-yes" style="font-size:16px;width:16px;height:16px;color:var(--b47-primary);"></span> Premium Template Packs</div>
                    </div>
                    <a href="<?php echo esc_url( base47_he_get_pro_url() ); ?>" class="b47-pro-btn-v2" target="_blank">Learn More →</a>
                </div>
                <?php 
                    endif; 
                endif; 
                ?>
            </div>
        </div>

    </div>
    </div>
    <?php
}
