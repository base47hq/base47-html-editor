<?php
/**
 * Special Widgets Admin Page — Base47 Soft UI
 *
 * @package Base47_HTML_Editor
 * @since 3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function base47_special_widgets_page() {
    $widgets      = base47_he_get_special_widgets_registry();
    $widget_count = count( $widgets );
    ?>
    <div class="wrap">
    <div class="b47-wrap">
        <?php base47_he_render_header_nav( 'widgets', 'Reusable components for your templates' ); ?>

        <?php
        $dismissed_widgets = get_user_meta( get_current_user_id(), 'base47_he_dismiss_widgets_notice', true );
        if ( ! $dismissed_widgets ) :
        ?>
        <div id="widgets-pro-notice" style="background:var(--b47-primary-bg);border:1px solid #DDE1FC;border-radius:var(--b47-radius);padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;gap:16px;">
            <span class="dashicons dashicons-admin-plugins" style="color:var(--b47-primary);font-size:24px;width:24px;height:24px;"></span>
            <div style="flex:1;">
                <strong>1 Widget (Free Version)</strong>
                <p style="color:var(--b47-text-secondary);font-size:13px;margin:4px 0 0;">The Pro add-on provides unlimited widgets and the Base47 Widget Library.</p>
            </div>
            <a href="<?php echo esc_url( base47_he_get_pro_url() ); ?>" class="b47-btn b47-btn-primary b47-btn-sm" target="_blank">Get Pro Add-on</a>
            <button type="button" id="dismiss-widgets-notice" class="b47-btn b47-btn-ghost b47-btn-sm">Dismiss</button>
        </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="b47-grid b47-grid-4 b47-mb-24">
            <div class="b47-card b47-stat-card-v2">
                <div class="b47-stat-card-icon blue"><span class="dashicons dashicons-screenoptions" style="font-size:20px;width:20px;height:20px;"></span></div>
                <div class="b47-stat-card-label">Available Widgets</div>
                <div class="b47-stat-card-value"><?php echo esc_html( $widget_count ); ?></div>
            </div>
            <div class="b47-card b47-stat-card-v2">
                <div class="b47-stat-card-icon green"><span class="dashicons dashicons-yes-alt" style="font-size:20px;width:20px;height:20px;"></span></div>
                <div class="b47-stat-card-label">Active</div>
                <div class="b47-stat-card-value"><?php echo esc_html( $widget_count ); ?></div>
            </div>
            <div class="b47-card b47-stat-card-v2">
                <div class="b47-stat-card-icon purple"><span class="dashicons dashicons-star-filled" style="font-size:20px;width:20px;height:20px;"></span></div>
                <div class="b47-stat-card-label">Pro Widgets</div>
                <div class="b47-stat-card-value">Coming</div>
            </div>
            <div class="b47-card b47-stat-card-v2">
                <div class="b47-stat-card-icon amber"><span class="dashicons dashicons-shortcode" style="font-size:20px;width:20px;height:20px;"></span></div>
                <div class="b47-stat-card-label">Integration</div>
                <div class="b47-stat-card-value">Shortcode</div>
            </div>
        </div>

        <?php if ( empty( $widgets ) ) : ?>
        <div class="b47-card">
            <div class="b47-empty-state">
                <div class="b47-empty-icon"><span class="dashicons dashicons-admin-plugins" style="font-size:48px;width:48px;height:48px;"></span></div>
                <div class="b47-empty-title">No Special Widgets Found</div>
                <div class="b47-empty-desc">To add a widget, create a folder in <code>special-widgets/</code> with a <code>widget.json</code> file.</div>
            </div>
        </div>
        <?php else : ?>
        <div class="b47-grid b47-grid-3 b47-mb-24">
            <?php
            $plugin_url = plugin_dir_url( BASE47_HE_PATH . 'base47-html-editor.php' );
            foreach ( $widgets as $w ) :
                $folder    = $w['folder'];
                $html      = $w['html'];
                $name      = $w['name'];
                $desc      = $w['description'];
                $slug      = $w['slug'];
                $shortcode = '[base47_widget slug="' . esc_attr( $slug ) . '"]';
                $preview   = $plugin_url . 'special-widgets/' . $folder . '/' . $html;

                $type = 'Component';
                if ( stripos( $name, 'hero' ) !== false || stripos( $slug, 'hero' ) !== false ) $type = 'Hero';
                elseif ( stripos( $name, 'slider' ) !== false || stripos( $slug, 'slider' ) !== false ) $type = 'Slider';
                elseif ( stripos( $name, 'contact' ) !== false || stripos( $slug, 'contact' ) !== false ) $type = 'Contact';
                elseif ( stripos( $name, 'form' ) !== false || stripos( $slug, 'form' ) !== false ) $type = 'Form';
            ?>
            <div class="b47-card" style="padding:0;overflow:hidden;">
                <div style="background:linear-gradient(135deg,var(--b47-primary-bg),#EDE9FE);padding:20px;display:flex;align-items:center;justify-content:space-between;">
                    <span class="dashicons dashicons-admin-customizer" style="font-size:28px;width:28px;height:28px;color:var(--b47-primary);"></span>
                    <span class="b47-badge b47-badge-blue" style="font-size:11px;"><?php echo esc_html( $type ); ?></span>
                </div>
                <div style="padding:20px;">
                    <h3 style="font-size:16px;font-weight:600;color:var(--b47-text);margin:0 0 6px;"><?php echo esc_html( $name ); ?></h3>
                    <p style="font-size:13px;color:var(--b47-text-secondary);margin:0 0 14px;"><?php echo esc_html( $desc ?: 'A reusable widget component for your templates.' ); ?></p>
                    <div class="b47-sc-card-code" style="margin-bottom:14px;" onclick="b47CopyShortcode(this, '<?php echo esc_js( $shortcode ); ?>')">
                        <code><?php echo esc_html( $shortcode ); ?></code>
                        <span class="b47-sc-copy-icon"><span class="dashicons dashicons-admin-page" style="font-size:14px;width:14px;height:14px;"></span></span>
                    </div>
                    <div class="b47-flex b47-gap-8">
                        <button class="b47-sc-action-btn btn-widget-copy" data-shortcode="<?php echo esc_attr( $shortcode ); ?>">
                            <span class="dashicons dashicons-admin-page" style="font-size:14px;width:14px;height:14px;"></span> Copy
                        </button>
                        <a href="<?php echo esc_url( $preview ); ?>" target="_blank" class="b47-sc-action-btn">
                            <span class="dashicons dashicons-visibility" style="font-size:14px;width:14px;height:14px;"></span> Open Example
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="padding:12px 16px;font-size:12px;color:var(--b47-text-muted);">
            <span class="dashicons dashicons-info" style="font-size:14px;width:14px;height:14px;"></span>
            Widgets are auto-discovered from <code>special-widgets/</code> folder. Only folders with <code>widget.json</code> are shown.
        </div>
        <?php endif; ?>

    </div>
    </div>
    <?php
}
