<?php
/**
 * Changelog Page — Base47 Soft UI
 *
 * @package Base47_HTML_Editor
 * @since 3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function base47_he_changelog_page() {
    $versions = base47_he_get_changelog_versions();
    $stats    = base47_he_calculate_stats( $versions );
    ?>
    <div class="wrap">
    <div class="b47-wrap">
        <?php base47_he_render_header_nav( 'changelog', 'Version history and release notes' ); ?>

        <!-- Stats -->
        <div class="b47-grid b47-grid-4 b47-mb-24">
            <div class="b47-card b47-stat-card-v2">
                <div class="b47-stat-card-icon blue"><span class="dashicons dashicons-editor-code" style="font-size:20px;width:20px;height:20px;"></span></div>
                <div class="b47-stat-card-label">Current Version</div>
                <div class="b47-stat-card-value"><?php echo esc_html( BASE47_HE_VERSION ); ?></div>
            </div>
            <div class="b47-card b47-stat-card-v2">
                <div class="b47-stat-card-icon indigo"><span class="dashicons dashicons-backup" style="font-size:20px;width:20px;height:20px;"></span></div>
                <div class="b47-stat-card-label">Total Releases</div>
                <div class="b47-stat-card-value"><?php echo esc_html( count( $versions ) ); ?></div>
            </div>
            <div class="b47-card b47-stat-card-v2">
                <div class="b47-stat-card-icon green"><span class="dashicons dashicons-star-filled" style="font-size:20px;width:20px;height:20px;"></span></div>
                <div class="b47-stat-card-label">New Features</div>
                <div class="b47-stat-card-value"><?php echo esc_html( $stats['features'] ); ?></div>
            </div>
            <div class="b47-card b47-stat-card-v2">
                <div class="b47-stat-card-icon amber"><span class="dashicons dashicons-admin-tools" style="font-size:20px;width:20px;height:20px;"></span></div>
                <div class="b47-stat-card-label">Bug Fixes</div>
                <div class="b47-stat-card-value"><?php echo esc_html( $stats['fixes'] ); ?></div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="b47-card">
            <div class="b47-card-header">
                <div class="b47-flex b47-items-center b47-gap-8">
                    <div class="b47-section-icon blue"><span class="dashicons dashicons-backup" style="font-size:16px;width:16px;height:16px;"></span></div>
                    <span class="b47-card-title">Release History</span>
                </div>
            </div>

            <?php foreach ( $versions as $version ) :
                $is_current = ( $version['version'] === BASE47_HE_VERSION );
            ?>
            <div class="b47-changelog-item <?php echo $is_current ? 'current' : ''; ?>">
                <div class="b47-changelog-dot"></div>
                <div class="b47-flex b47-items-center b47-gap-8" style="margin-bottom:4px;">
                    <span class="b47-changelog-version">v<?php echo esc_html( $version['version'] ); ?></span>
                    <?php if ( $is_current ) : ?>
                        <span class="b47-badge b47-badge-green" style="font-size:11px;">Latest</span>
                    <?php endif; ?>
                </div>
                <div class="b47-changelog-date"><?php echo esc_html( $version['date'] ); ?></div>
                <div class="b47-changelog-title"><?php echo esc_html( $version['title'] ); ?></div>
                <ul class="b47-changelog-list">
                    <?php foreach ( $version['changes'] as $change ) :
                        $change_lower = strtolower( $change );
                        $tag = 'improve';
                        if ( strpos( $change_lower, 'fixed' ) === 0 || strpos( $change_lower, 'bug' ) !== false || strpos( $change_lower, 'resolved' ) === 0 ) {
                            $tag = 'fix';
                        } elseif ( strpos( $change_lower, 'added' ) === 0 || strpos( $change_lower, 'new' ) === 0 || strpos( $change_lower, 'complete' ) === 0 || strpos( $change_lower, 'initial' ) === 0 || strpos( $change_lower, 'launched' ) !== false ) {
                            $tag = 'new';
                        }
                    ?>
                    <li class="b47-changelog-entry">
                        <span class="b47-changelog-tag <?php echo esc_attr( $tag ); ?>"><?php echo esc_html( strtoupper( $tag ) ); ?></span>
                        <?php echo esc_html( $change ); ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
    </div>
    <?php
}

/**
 * Get changelog versions data
 */
function base47_he_get_changelog_versions() {
    return [
        [
            'version' => '3.0.1',
            'date' => 'May 2026',
            'title' => 'Core Stability Update',
            'summary' => 'Improved loader architecture, enhanced backup workflow, and better template rendering.',
            'changes' => [
                'Improved loader architecture and template rendering stability',
                'Enhanced backup and restore workflow',
                'Added optional Strict Sanitization Mode for multi-user environments',
                'Improved asset loading reliability and compatibility',
                'Improved editor security and permission handling',
                'General stability and performance improvements',
            ]
        ],
        [
            'version' => '3.0.0',
            'date' => 'February 6, 2026',
            'title' => 'Major Release',
            'summary' => 'Complete platform overhaul with new design system and enhanced security.',
            'changes' => [
                'New Base47 Soft UI design system for all admin pages',
                'Improved template save reliability',
                'Advisory HTML validation warnings',
                'Comprehensive audit logging',
                'Improved filesystem compatibility and stability',
                'Improved admin performance and compatibility',
                'Added backup retention (5 per template)',
            ]
        ],
        [
            'version' => '2.9.9.3.12',
            'date' => 'January 12, 2026',
            'title' => 'Changelog UI Refresh',
            'summary' => 'Clean changelog design with stats and version timeline.',
            'changes' => [
                'Clean header with title and description',
                'Added stats bubbles showing key metrics',
                'Simple version rows with changes',
                'Current version highlighting',
            ]
        ],
        [
            'version' => '2.9.9.3.9',
            'date' => 'January 12, 2026',
            'title' => 'Critical Bug Fixes',
            'summary' => 'Major bug fixes and UI improvements across all admin pages.',
            'changes' => [
                'Fixed changelog page showing empty rows',
                'Added missing marketplace styles',
                'Enhanced dashboard support system',
                'Improved responsive layouts',
            ]
        ],
        [
            'version' => '2.9.9.3.1',
            'date' => 'January 11, 2026',
            'title' => 'Marketplace Launch',
            'summary' => 'Template marketplace with one-click installations.',
            'changes' => [
                'Complete marketplace interface',
                'Added one-click template installation',
                'Added live preview system',
                'Advanced filtering system',
            ]
        ],
        [
            'version' => '2.9.8',
            'date' => 'November 2025',
            'title' => 'New Dashboard Experience',
            'summary' => 'Professional dashboard redesign with Soft UI components.',
            'changes' => [
                'New Soft UI dashboard design',
                'Added welcome banner system',
                'Added statistics overview cards',
                'Added quick actions grid',
            ]
        ],
        [
            'version' => '2.9.0',
            'date' => 'July 2025',
            'title' => 'Base47 Transformation',
            'summary' => 'Complete rebranding and major feature overhaul.',
            'changes' => [
                'Complete rebranding to Base47',
                'Added live HTML editor with Monaco',
                'New template management system',
                'Enhanced security features',
            ]
        ],
        [
            'version' => '2.0.0',
            'date' => 'November 2024',
            'title' => 'Genesis',
            'summary' => 'The beginning of the editor journey.',
            'changes' => [
                'Initial public release',
                'Core shortcode system',
                'Basic admin interface',
                'Template rendering foundation',
            ]
        ]
    ];
}

/**
 * Calculate statistics from versions
 */
function base47_he_calculate_stats( $versions ) {
    $features = 0;
    $fixes = 0;
    foreach ( $versions as $version ) {
        foreach ( $version['changes'] as $change ) {
            $cl = strtolower( $change );
            if ( strpos( $cl, 'added' ) === 0 || strpos( $cl, 'new' ) === 0 || strpos( $cl, 'complete' ) === 0 || strpos( $cl, 'initial' ) === 0 ) {
                $features++;
            } elseif ( strpos( $cl, 'fixed' ) === 0 || strpos( $cl, 'bug' ) !== false || strpos( $cl, 'resolved' ) === 0 ) {
                $fixes++;
            }
        }
    }
    return [ 'features' => $features, 'fixes' => $fixes ];
}
