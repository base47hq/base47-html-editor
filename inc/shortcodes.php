<?php
/**
 * Shortcode Registration
 * 
 * Registers all template shortcodes with unified format and backward compatibility.
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.3
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register shortcodes using unified format:
 * [base47-{theme}-{template}]
 *
 * Plus backward compatibility for legacy shortcodes:
 *  - [base47-{template}]  (legacy format)
 *  - Legacy shortcodes from previous versions are also supported
 */
add_action( 'init', function() {

    $sets = base47_he_get_template_sets();
    if ( empty( $sets ) ) return;

    foreach ( $sets as $set_slug => $set ) {

        $theme_prefix = str_replace( [ '-templates', '-templetes' ], '', $set_slug ?? '' );

        foreach ( glob( $set['path'] . '*.html' ) as $file_path ) {

            $file = basename( $file_path );
            $slug = base47_he_filename_to_slug( $file );

            // FINAL shortcode format — always theme + template
            $shortcode = 'base47-' . $theme_prefix . '-' . $slug;

            add_shortcode( $shortcode, function() use ( $set_slug, $file ) {
                return base47_he_render_template_safe( $file, $set_slug );
            } );

            // BACKWARD COMPATIBILITY — legacy shortcodes only
            if ( $theme_prefix === 'mivon' ) {
                add_shortcode( 'mivon-' . $slug, function() use ( $set_slug, $file ) {
                    return base47_he_render_template_safe( $file, $set_slug );
                } );
            }
        }
    }
} );

/**
 * Convert filename to shortcode slug.
 */
function base47_he_filename_to_slug( $filename ) {
    $base = pathinfo( $filename, PATHINFO_FILENAME );
    $slug = sanitize_title_with_dashes( $base );
    return $slug ?: ( 'tpl-' . md5( $filename ) );
}

/**
 * Safe template renderer.
 * 
 * Templates are admin-authored trusted content. Security is enforced at save-time
 * via capability checks (manage_options + unfiltered_html).
 * No wp_kses on render — it destroys template functionality (styles, scripts, data attributes, SVG, iframes).
 */
function base47_he_render_template_safe( $filename, $set_slug = '' ) {
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Admin-authored template HTML intentionally rendered raw. Optional Strict Sanitization Mode is available.
    return base47_he_render_template( $filename, $set_slug );
}

/**
 * Sanitize template HTML with comprehensive allowlist
 * 
 * Templates are admin-created trusted content, but we sanitize for WordPress.org compliance.
 * This allowlist covers all common HTML elements and attributes needed for templates.
 */
function base47_he_sanitize_template_html( $html ) {
    // Comprehensive HTML allowlist for templates
    $allowed_html = [
        // Document structure
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
    ];
    
    // Apply sanitization with our comprehensive allowlist
    return wp_kses( $html, $allowed_html );
}
