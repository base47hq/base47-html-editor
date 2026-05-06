<?php
/**
 * Special Widgets System
 * 
 * Auto-discovery and shortcode registration for special widgets
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Scan /special-widgets/ for folders with widget.json
 * Returns array of widgets, keyed by slug.
 *
 * Structure:
 * [
 *   'hero-slider' => [
 *      'name'        => 'Hero Slider (Base47)',
 *      'slug'        => 'hero-slider',
 *      'description' => '...',
 *      'folder'      => 'hero-slider',
 *      'html'        => 'hero-slider.html',
 *      'css'         => [...],
 *      'js'          => [...],
 *   ],
 *   ...
 * ]
 */
function base47_he_get_special_widgets_registry() {
    static $cache = null;

    if ( $cache !== null ) {
        return $cache;
    }

    $cache = [];

    $base_dir = BASE47_HE_PATH . 'special-widgets/';
    if ( ! is_dir( $base_dir ) ) {
        return $cache;
    }

    $folders = scandir( $base_dir );
    if ( ! $folders ) {
        return $cache;
    }

    foreach ( $folders as $folder ) {
        if ( $folder === '.' || $folder === '..' || $folder === '.DS_Store' ) {
            continue;
        }

        $widget_dir = $base_dir . $folder . '/';
        if ( ! is_dir( $widget_dir ) ) {
            continue;
        }

        $json_file = $widget_dir . 'widget.json';
        if ( ! file_exists( $json_file ) ) {
            // No widget.json => ignore this folder completely
            continue;
        }

        $json_raw = false;
        if ( base47_he_init_filesystem() ) {
            global $wp_filesystem;
            $json_raw = $wp_filesystem->get_contents( $json_file );
        }
        if ( ! $json_raw ) {
            continue;
        }

        $data = json_decode( $json_raw, true );
        if ( ! is_array( $data ) ) {
            continue;
        }

        // Minimal required fields
        if ( empty( $data['html'] ) ) {
            continue;
        }

        // Derive slug if missing
        $slug = ! empty( $data['slug'] ) ? sanitize_title( $data['slug'] ) : sanitize_title( $folder );

        $cache[ $slug ] = [
            'name'        => isset( $data['name'] ) ? $data['name'] : $slug,
            'slug'        => $slug,
            'description' => isset( $data['description'] ) ? $data['description'] : '',
            'folder'      => $folder,
            'html'        => $data['html'],
            'css'         => isset( $data['css'] ) && is_array( $data['css'] ) ? $data['css'] : [],
            'js'          => isset( $data['js'] ) && is_array( $data['js'] ) ? $data['js'] : [],
        ];
    }

    return $cache;
}

/**
 * Unified Special Widget Renderer
 * All widgets use this single render pipeline
 */
function base47_he_render_special_widget( $slug, $atts = [], $content = '' ) {
    $slug = sanitize_title( $slug );
    $widgets = base47_he_get_special_widgets_registry();
    if ( empty( $widgets[ $slug ] ) ) {
        return '';
    }

    $widget = $widgets[ $slug ];
    $folder = $widget['folder'];

    $plugin_url = BASE47_HE_URL;
    $plugin_dir = BASE47_HE_PATH;

    $widget_dir_url  = $plugin_url . 'special-widgets/' . $folder . '/';
    $widget_dir_path = $plugin_dir . 'special-widgets/' . $folder . '/';

    // Collect inline assets for preview/AJAX contexts
    $inline_assets = '';
    
    // Check if we're in a preview context (read-only check, no state changes)
    $is_preview_context = false;
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only check for preview context, no state change
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_REQUEST['action'] ) ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only check for preview context, no state change
        $action = sanitize_key( wp_unslash( $_REQUEST['action'] ) );
        $is_preview_context = ( $action === 'base47_he_preview' || $action === 'base47_he_lazy_preview' );
    }

    // Handle CSS assets
    foreach ( $widget['css'] as $index => $css_rel ) {
        $css_path = $widget_dir_path . $css_rel;
        if ( ! file_exists( $css_path ) ) {
            continue;
        }

        $handle = 'base47-sw-' . $slug . '-css-' . $index;

        if ( $is_preview_context ) {
            // Collect inline CSS for preview
            if ( base47_he_init_filesystem() ) {
                global $wp_filesystem;
                $css_content = $wp_filesystem->get_contents( $css_path );
                if ( $css_content ) {
                    $inline_assets .= '<style id="' . esc_attr( $handle ) . '">' . $css_content . '</style>';
                }
            }
        } else {
            // Normal enqueue
            if ( ! wp_style_is( $handle, 'enqueued' ) ) {
                wp_enqueue_style(
                    $handle,
                    $widget_dir_url . $css_rel,
                    [],
                    filemtime( $css_path )
                );
            }
        }
    }

    // Handle JS assets
    foreach ( $widget['js'] as $index => $js_rel ) {
        $js_path = $widget_dir_path . $js_rel;
        if ( ! file_exists( $js_path ) ) {
            continue;
        }

        $handle = 'base47-sw-' . $slug . '-js-' . $index;

        if ( $is_preview_context ) {
            // Collect inline JS for preview
            if ( base47_he_init_filesystem() ) {
                global $wp_filesystem;
                $js_content = $wp_filesystem->get_contents( $js_path );
                if ( $js_content ) {
                    $inline_assets .= '<script id="' . esc_attr( $handle ) . '">' . $js_content . '</script>';
                }
            }
        } else {
            // Normal enqueue
            if ( ! wp_script_is( $handle, 'enqueued' ) ) {
                wp_enqueue_script(
                    $handle,
                    $widget_dir_url . $js_rel,
                    [],
                    filemtime( $js_path ),
                    true
                );
            }
        }
    }

    // Load HTML template
    $html_file = $widget_dir_path . $widget['html'];
    if ( ! file_exists( $html_file ) ) {
        return '';
    }

    $html = false;
    if ( base47_he_init_filesystem() ) {
        global $wp_filesystem;
        $html = $wp_filesystem->get_contents( $html_file );
    }
    if ( ! $html ) {
        return '';
    }

    // Path fix: replace old plugin paths
    $html = str_replace(
        '/wp-content/plugins/base47-html-editor/special-widgets/' . $folder . '/',
        $widget_dir_url,
        $html ?? ''
    );
    // Backward compatibility: replace legacy plugin path
    $html = str_replace(
        '/wp-content/plugins/mivon-html-editor/special-widgets/' . $folder . '/',
        $widget_dir_url,
        $html ?? ''
    );

    // Return HTML with inline assets prepended for preview contexts
    return $inline_assets . $html;
}
/**
 * Register Special Widget Shortcodes on init
 */
function base47_he_register_special_widget_shortcodes() {
    add_shortcode( 'base47_widget', 'base47_he_special_widget_shortcode' );
    add_shortcode( 'mivon_widget', 'base47_he_special_widget_shortcode' ); // Backward compatibility
    add_shortcode( 'base47_b47_hero_video', 'base47_b47_hero_video_shortcode' );
}
add_action( 'init', 'base47_he_register_special_widget_shortcodes', 5 );

/**
 * Generic Special Widget Shortcode: [base47_widget slug="widget-name"]
 */
function base47_he_special_widget_shortcode( $atts = [], $content = '' ) {
    $atts = shortcode_atts([
        'slug' => '',
    ], $atts, 'base47_widget' );

    $slug = sanitize_title( $atts['slug'] );
    if ( ! $slug ) {
        return '';
    }

    $html = base47_he_render_special_widget( $slug, $atts, $content );
    
    // Templates/widgets are admin-authored trusted content — no wp_kses on render
    return $html;
}

/**
 * B47 Hero Video Widget Shortcode: [base47_b47_hero_video]
 * Uses unified render pipeline
 */
function base47_b47_hero_video_shortcode( $atts = [], $content = '' ) {
    $html = base47_he_render_special_widget( 'b47-hero-video', $atts, $content );
    
    // Templates/widgets are admin-authored trusted content — no wp_kses on render
    return $html;
}
/**
 * Pre-scan HTML and enqueue detected special widget assets
 * For Base47 HTML Editor preview/editor iframe
 */
function base47_he_enqueue_detected_special_widget_assets( $html ) {
    if ( empty( $html ) ) {
        return;
    }

    // Detect B47 Hero Video widget
    if ( strpos( $html, '[base47_b47_hero_video' ) !== false || 
         strpos( $html, 'base47-widget-b47-hero-video' ) !== false ) {
        base47_he_enqueue_widget_assets_by_slug( 'b47-hero-video' );
    }

    // Detect generic widgets
    if ( preg_match_all( '/\[base47_widget\s+slug=["\']([^"\']+)["\']/', $html, $matches ) ) {
        foreach ( $matches[1] as $slug ) {
            base47_he_enqueue_widget_assets_by_slug( $slug );
        }
    }
}

/**
 * Enqueue widget assets by slug (for generic widgets and pre-scanning)
 */
function base47_he_enqueue_widget_assets_by_slug( $slug ) {
    $slug = sanitize_title( $slug );
    $widgets = base47_he_get_special_widgets_registry();
    if ( empty( $widgets[ $slug ] ) ) {
        return;
    }

    $widget = $widgets[ $slug ];
    $folder = $widget['folder'];
    $widget_url = BASE47_HE_URL . 'special-widgets/' . $folder . '/';
    $widget_path = BASE47_HE_PATH . 'special-widgets/' . $folder . '/';

    // Enqueue CSS files
    foreach ( $widget['css'] as $index => $css_rel ) {
        $css_path = $widget_path . $css_rel;
        if ( file_exists( $css_path ) ) {
            $handle = 'base47-sw-' . $slug . '-css-' . $index;
            if ( ! wp_style_is( $handle, 'enqueued' ) ) {
                wp_enqueue_style(
                    $handle,
                    $widget_url . $css_rel,
                    [],
                    filemtime( $css_path )
                );
            }
        }
    }

    // Enqueue JS files
    foreach ( $widget['js'] as $index => $js_rel ) {
        $js_path = $widget_path . $js_rel;
        if ( file_exists( $js_path ) ) {
            $handle = 'base47-sw-' . $slug . '-js-' . $index;
            if ( ! wp_script_is( $handle, 'enqueued' ) ) {
                wp_enqueue_script(
                    $handle,
                    $widget_url . $js_rel,
                    [],
                    filemtime( $js_path ),
                    true
                );
            }
        }
    }
}
