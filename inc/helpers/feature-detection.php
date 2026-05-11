<?php
/**
 * Pro Plugin Detection (WordPress.org Compliant)
 * 
 * Simple detection for Pro plugin presence - NO FEATURE GATING
 * Free version is fully functional on its own
 * 
 * @package Base47_HTML_Editor
 * @since 3.2.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Check if Pro plugin is installed (for hooks/actions only)
 * 
 * @return bool
 */
function base47_he_is_pro_installed() {
    // Check if Pro plugin constant is defined
    if ( defined( 'BASE47_HE_PRO_VERSION' ) ) {
        return true;
    }
    
    // Check if Pro plugin class exists
    if ( class_exists( 'Base47_HTML_Editor_Pro' ) ) {
        return true;
    }
    
    return false;
}

/**
 * Check if Pro plugin is active.
 * 
 * Uses filter system for WordPress.org compliance.
 * Pro plugin hooks into this filter to return true when installed and licensed.
 * 
 * @return bool
 */
function base47_he_is_pro_active() {
    return apply_filters( 'base47_he_is_pro_active', false );
}

/**
 * Get Pro upgrade URL
 * 
 * @return string
 */
function base47_he_get_pro_url() {
    return apply_filters( 'base47_he_pro_url', 'https://base47.art/base47-html-editor/' );
}

/**
 * Get feature comparison data for Free vs Pro
 * 
 * @return array
 */
function base47_he_get_feature_comparison() {
    return array(
        'Editor' => array(
            array(
                'name' => 'Classic Textarea Editor',
                'free' => true,
                'pro' => true,
            ),
            array(
                'name' => 'Monaco Editor (VS Code)',
                'free' => false,
                'pro' => true,
            ),
            array(
                'name' => 'Editor Mode Switcher',
                'free' => false,
                'pro' => true,
            ),
            array(
                'name' => 'Basic Live Preview',
                'free' => true,
                'pro' => true,
            ),
            array(
                'name' => 'Advanced Preview (Responsive)',
                'free' => false,
                'pro' => true,
            ),
        ),
        'Templates' => array(
            array(
                'name' => 'Template Discovery',
                'free' => true,
                'pro' => true,
            ),
            array(
                'name' => 'Included Template Packs',
                'free' => '1 (Base47 Minimal)',
                'pro' => 'Unlimited',
            ),
            array(
                'name' => 'Template Marketplace',
                'free' => false,
                'pro' => true,
            ),
            array(
                'name' => 'Export/Import Sets',
                'free' => false,
                'pro' => true,
            ),
        ),
        'Widgets' => array(
            array(
                'name' => 'Widget Discovery',
                'free' => true,
                'pro' => true,
            ),
            array(
                'name' => 'Included Widgets',
                'free' => '1 (Hero Slider)',
                'pro' => 'Unlimited',
            ),
            array(
                'name' => 'Widget Marketplace',
                'free' => false,
                'pro' => true,
            ),
            array(
                'name' => 'Custom Widget Builder',
                'free' => false,
                'pro' => true,
            ),
        ),
        'Asset Loading' => array(
            array(
                'name' => 'Base47 Loader',
                'free' => true,
                'pro' => true,
            ),
            array(
                'name' => 'Smart Loader++',
                'free' => false,
                'pro' => true,
            ),
            array(
                'name' => 'Manifest Loader',
                'free' => true,
                'pro' => true,
            ),
        ),
        'Backups' => array(
            array(
                'name' => 'Auto-Backups',
                'free' => false,
                'pro' => true,
            ),
            array(
                'name' => 'Manual Backups',
                'free' => false,
                'pro' => true,
            ),
            array(
                'name' => 'One-Click Restore',
                'free' => false,
                'pro' => true,
            ),
        ),
        'Support' => array(
            array(
                'name' => 'Community Support',
                'free' => true,
                'pro' => true,
            ),
            array(
                'name' => 'Priority Support',
                'free' => false,
                'pro' => true,
            ),
            array(
                'name' => 'Email Support',
                'free' => false,
                'pro' => true,
            ),
        ),
    );
}
