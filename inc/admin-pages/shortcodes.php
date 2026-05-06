<?php
/**
 * Shortcodes Admin Page — Base47 Soft UI
 *
 * @package Base47_HTML_Editor
 * @since 3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function base47_he_templates_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $active = base47_he_get_active_sets();
    $sets   = base47_he_get_template_sets();

    if ( empty( $active ) ) {
        echo '<div class="wrap"><div class="b47-wrap"><p>No active themes. Go to <strong>Theme Manager</strong> to enable one.</p></div></div>';
        return;
    }

    $by_set = [];
    foreach ( base47_he_get_all_templates( false ) as $item ) {
        $by_set[ $item['set'] ][] = $item['file'];
    }

    $total = 0;
    foreach ( $active as $s ) { $total += count( $by_set[ $s ] ?? [] ); }
    ?>
    <div class="wrap">
    <div class="b47-wrap">

        <?php
        $header_buttons = '<a href="' . esc_url( admin_url( 'admin.php?page=base47-he-editor' ) ) . '" class="b47-btn b47-btn-primary b47-btn-sm"><span class="dashicons dashicons-edit" style="font-size:16px;width:16px;height:16px;"></span> Open Editor</a>';
        base47_he_render_header_nav( 'shortcodes', 'Copy shortcodes and embed templates anywhere', $header_buttons );
        ?>

        <!-- Title + Stats -->
        <div class="b47-sc-topbar b47-mb-24">
            <div>
                <div class="b47-sc-page-title">Template Shortcodes</div>
                <div class="b47-sc-page-desc">Click any shortcode to copy it to your clipboard, then paste into any WordPress page or post.</div>
            </div>
            <div class="b47-sc-stats">
                <div class="b47-sc-stat">
                    <span class="b47-sc-stat-num"><?php echo esc_html( $total ); ?></span>
                    <span class="b47-sc-stat-label">Total</span>
                </div>
                <div class="b47-sc-stat-divider"></div>
                <div class="b47-sc-stat">
                    <span class="b47-sc-stat-num" style="color:#059669;"><?php echo esc_html( count( $active ) ); ?></span>
                    <span class="b47-sc-stat-label">Active Sets</span>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="b47-sc-toolbar b47-mb-20">
            <div class="b47-input-icon" style="max-width:340px;flex:1;">
                <span class="dashicons dashicons-search" style="font-size:16px;width:16px;height:16px;position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--b47-text-muted);pointer-events:none;"></span>
                <input type="text" class="b47-input" style="padding-left:38px;" placeholder="Search templates by name..." id="b47-sc-search" autocomplete="off">
            </div>
        </div>

        <?php foreach ( $active as $set_slug ) :
            $files       = $by_set[ $set_slug ] ?? [];
            $theme_label = $sets[ $set_slug ]['label'] ?? $set_slug;
        ?>
        <!-- Set Label -->
        <div class="b47-sc-set-label b47-mb-16">
            <span class="dashicons dashicons-portfolio" style="font-size:14px;width:14px;height:14px;"></span>
            <?php echo esc_html( $theme_label ); ?>
        </div>

        <?php if ( empty( $files ) ) : ?>
            <p style="color:var(--b47-text-muted);font-size:14px;margin-bottom:24px;">No templates found in this set.</p>
        <?php else : ?>
        <div class="b47-sc-list b47-mb-24" id="b47-sc-list-<?php echo esc_attr( $set_slug ); ?>">
            <?php foreach ( $files as $file ) :
                $slug      = base47_he_filename_to_slug( $file );
                $set_clean = str_replace( [ '-templates', '-templetes' ], '', $set_slug );
                $shortcode = '[base47-' . $set_clean . '-' . $slug . ']';
                $editor_url = admin_url( 'admin.php?page=base47-he-editor&set=' . rawurlencode( $set_slug ) . '&file=' . rawurlencode( $file ) );
                $preview_url = admin_url( 'admin-ajax.php?action=base47_he_preview&file=' . rawurlencode( $file ) . '&set=' . rawurlencode( $set_slug ) . '&_wpnonce=' . wp_create_nonce( 'base47_he' ) );
            ?>
            <div class="b47-sc-card-v2" data-name="<?php echo esc_attr( strtolower( $file ) ); ?>">
                <div class="b47-sc-card-top">
                    <div>
                        <div class="b47-sc-card-name"><?php echo esc_html( ucwords( str_replace( [ '-', '_' ], ' ', $slug ) ) ); ?></div>
                        <div class="b47-sc-card-file"><?php echo esc_html( $file ); ?></div>
                    </div>
                    <span class="b47-badge b47-badge-green" style="font-size:11px;">Active</span>
                </div>
                <div class="b47-sc-card-code" onclick="b47CopyShortcode(this, '<?php echo esc_js( $shortcode ); ?>')">
                    <code><?php echo esc_html( $shortcode ); ?></code>
                    <span class="b47-sc-copy-icon"><span class="dashicons dashicons-admin-page" style="font-size:14px;width:14px;height:14px;"></span></span>
                </div>
                <div class="b47-sc-card-actions">
                    <button type="button" class="b47-sc-action-btn" onclick="b47CopyShortcode(this, '<?php echo esc_js( $shortcode ); ?>')">
                        <span class="dashicons dashicons-admin-page" style="font-size:14px;width:14px;height:14px;"></span> Copy
                    </button>
                    <a class="b47-sc-action-btn" href="<?php echo esc_url( $editor_url ); ?>">
                        <span class="dashicons dashicons-edit" style="font-size:14px;width:14px;height:14px;"></span> Edit
                    </a>
                    <a class="b47-sc-action-btn" href="<?php echo esc_url( $preview_url ); ?>" target="_blank">
                        <span class="dashicons dashicons-visibility" style="font-size:14px;width:14px;height:14px;"></span> Preview
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>

    </div>
    </div>


    <?php
}
