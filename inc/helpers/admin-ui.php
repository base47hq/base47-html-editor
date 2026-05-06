<?php
/**
 * Admin UI Helper — Shared Header & Navigation
 *
 * Renders the b47 header and navigation bar used by ALL admin pages.
 *
 * @package Base47_HTML_Editor
 * @since 3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Render the b47 header and navigation bar.
 *
 * @param string $active_page  The current page slug (e.g. 'dashboard', 'editor', 'themes').
 * @param string $subtitle     Contextual subtitle shown under "Base47 HTML Editor".
 * @param string $header_right Optional HTML for right-side header buttons.
 */
function base47_he_render_header_nav( $active_page = '', $subtitle = '', $header_right = '' ) {

    // --- Header ---
    ?>
    <div class="b47-header">
        <div class="b47-header-left">
            <div class="b47-logo">B</div>
            <div>
                <h1>Base47 HTML Editor</h1>
                <?php if ( $subtitle ) : ?>
                    <p><?php echo esc_html( $subtitle ); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php if ( $header_right ) : ?>
            <div class="b47-flex b47-gap-8">
                <?php echo $header_right; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Pre-escaped by caller ?>
            </div>
        <?php endif; ?>
    </div>
    <?php

    // --- Navigation ---
    $nav_links = array(
        // Primary group
        array(
            'slug'  => 'dashboard',
            'label' => 'Dashboard',
            'icon'  => 'dashicons-grid-view',
            'page'  => 'base47-he-dashboard',
        ),
        array(
            'slug'  => 'editor',
            'label' => 'Editor',
            'icon'  => 'dashicons-editor-code',
            'page'  => 'base47-he-editor',
        ),
        array(
            'slug'  => 'shortcodes',
            'label' => 'Shortcodes',
            'icon'  => 'dashicons-shortcode',
            'page'  => 'base47-he-templates',
        ),
        array(
            'slug'  => 'themes',
            'label' => 'Themes',
            'icon'  => 'dashicons-admin-appearance',
            'page'  => 'base47-he-theme-manager',
        ),
        array(
            'slug'  => 'marketplace',
            'label' => 'Marketplace',
            'icon'  => 'dashicons-cart',
            'page'  => 'base47-he-marketplace',
        ),
        array(
            'slug'  => 'widgets',
            'label' => 'Widgets',
            'icon'  => 'dashicons-screenoptions',
            'page'  => 'base47-special-widgets',
        ),
        // Divider
        'divider',
        // Secondary group
        array(
            'slug'  => 'support',
            'label' => 'Support',
            'icon'  => 'dashicons-sos',
            'page'  => 'base47-he-support',
        ),
        array(
            'slug'  => 'settings',
            'label' => 'Settings',
            'icon'  => 'dashicons-admin-settings',
            'page'  => 'base47-he-settings',
        ),
        array(
            'slug'  => 'logs',
            'label' => 'Logs',
            'icon'  => 'dashicons-media-text',
            'page'  => 'base47-he-logs',
        ),
        array(
            'slug'  => 'changelog',
            'label' => 'Changelog',
            'icon'  => 'dashicons-backup',
            'page'  => 'base47-he-changelog',
        ),
        array(
            'slug'  => 'license',
            'label' => 'License',
            'icon'  => 'dashicons-shield',
            'page'  => 'base47-he-license',
        ),
    );

    // Conditionally add Pro link
    if ( ! base47_he_is_pro_active() ) {
        $nav_links[] = array(
            'slug'  => 'pro',
            'label' => 'Pro',
            'icon'  => 'dashicons-star-filled',
            'page'  => 'base47-he-upgrade',
        );
    }

    ?>
    <nav class="b47-nav">
        <?php foreach ( $nav_links as $link ) :
            if ( $link === 'divider' ) : ?>
                <span class="b47-nav-divider"></span>
            <?php else :
                $is_active = ( $active_page === $link['slug'] );
                $url       = esc_url( admin_url( 'admin.php?page=' . $link['page'] ) );
            ?>
                <a href="<?php echo esc_url( $url ); ?>"<?php echo $is_active ? ' class="active"' : ''; ?>>
                    <span class="dashicons <?php echo esc_attr( $link['icon'] ); ?>" style="font-size:16px;width:16px;height:16px;"></span>
                    <?php echo esc_html( $link['label'] ); ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
    <?php
}
