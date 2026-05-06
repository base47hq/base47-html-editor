<?php
/**
 * Logs Admin Page — Base47 Soft UI (Free Version)
 *
 * @package Base47_HTML_Editor
 * @since 3.2.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function base47_he_render_logs_page() {
    $raw_logs    = base47_he_get_logs();
    if ( empty( $raw_logs ) ) {
        base47_he_log( 'Logs page accessed - logging system initialized', 'info' );
        $raw_logs = base47_he_get_logs();
    }

    $log_entries = base47_he_parse_logs( $raw_logs );
    $log_file    = base47_he_get_log_file();
    $log_size    = file_exists( $log_file ) ? size_format( filesize( $log_file ) ) : '0 B';
    $log_count   = count( $log_entries );
    $error_count = 0;
    foreach ( $log_entries as $e ) {
        if ( strtoupper( $e['level'] ) === 'ERROR' ) $error_count++;
    }
    $status_label = $error_count > 0 ? 'Warning' : 'Healthy';

    // Header buttons (Pro features when Pro is active)
    $header_buttons = '<button type="button" id="base47-clear-logs" class="b47-btn b47-btn-danger b47-btn-sm"><span class="dashicons dashicons-trash" style="font-size:16px;width:16px;height:16px;"></span> Clear Logs</button>';
    
    // Add Download ZIP if Pro is active (filter buttons moved below stat cards)
    if ( apply_filters( 'base47_he_is_pro_active', false ) ) {
        $header_buttons .= '<button type="button" id="base47-download-logs" class="b47-btn b47-btn-secondary b47-btn-sm" style="margin-left:8px;"><span class="dashicons dashicons-download" style="font-size:16px;width:16px;height:16px;"></span> Download ZIP</button>';
    }
    
    $header_buttons .= '<span id="base47-logs-status"></span>';

    $has_pro = apply_filters( 'base47_he_is_pro_active', false );
    ?>
    <div class="wrap">
    <div class="b47-wrap">

        <?php base47_he_render_header_nav( 'logs', 'Monitor plugin activity and system events', $header_buttons ); ?>

        <!-- Stat Cards -->
        <div class="b47-grid b47-grid-4 b47-mb-24">
            <div class="b47-card b47-stat-card-v2">
                <div class="b47-stat-card-icon blue"><span class="dashicons dashicons-media-text" style="font-size:20px;width:20px;height:20px;"></span></div>
                <div class="b47-stat-card-label">Log Entries</div>
                <div class="b47-stat-card-value"><?php echo esc_html( $log_count ); ?></div>
            </div>
            <div class="b47-card b47-stat-card-v2">
                <div class="b47-stat-card-icon indigo"><span class="dashicons dashicons-database" style="font-size:20px;width:20px;height:20px;"></span></div>
                <div class="b47-stat-card-label">Log Size</div>
                <div class="b47-stat-card-value"><?php echo esc_html( $log_size ); ?></div>
            </div>
            <div class="b47-card b47-stat-card-v2">
                <div class="b47-stat-card-icon <?php echo $error_count > 0 ? 'red' : 'green'; ?>"><span class="dashicons dashicons-warning" style="font-size:20px;width:20px;height:20px;"></span></div>
                <div class="b47-stat-card-label">Errors</div>
                <div class="b47-stat-card-value"><?php echo esc_html( $error_count ); ?></div>
            </div>
            <div class="b47-card b47-stat-card-v2">
                <div class="b47-stat-card-icon <?php echo $error_count > 0 ? 'amber' : 'green'; ?>"><span class="dashicons dashicons-yes-alt" style="font-size:20px;width:20px;height:20px;"></span></div>
                <div class="b47-stat-card-label">Status</div>
                <div class="b47-stat-card-value"><?php echo esc_html( $status_label ); ?></div>
            </div>
        </div>

        <!-- Filter Bar (Pro feature - below stat cards) -->
        <?php if ( $has_pro ) : ?>
        <div class="b47-log-filter-bar" style="display:flex;align-items:center;gap:8px;margin-bottom:20px;padding:12px 16px;background:var(--b47-card,#fff);border-radius:var(--b47-radius,12px);border:1px solid var(--b47-border,#E5E7EB);">
            <span style="font-size:13px;font-weight:600;color:var(--b47-text-muted);margin-right:8px;">Filter:</span>
            <button type="button" class="b47-log-filter-btn active" data-level="all" style="padding:6px 14px;border-radius:6px;border:none;font-size:13px;font-weight:500;cursor:pointer;background:var(--b47-primary);color:white;transition:all 0.15s;">All</button>
            <button type="button" class="b47-log-filter-btn" data-level="error" style="padding:6px 14px;border-radius:6px;border:none;font-size:13px;font-weight:500;cursor:pointer;background:white;color:var(--b47-text);border:1px solid var(--b47-border,#E5E7EB);transition:all 0.15s;">Errors</button>
            <button type="button" class="b47-log-filter-btn" data-level="warning" style="padding:6px 14px;border-radius:6px;border:none;font-size:13px;font-weight:500;cursor:pointer;background:white;color:var(--b47-text);border:1px solid var(--b47-border,#E5E7EB);transition:all 0.15s;">Warnings</button>
            <button type="button" class="b47-log-filter-btn" data-level="info" style="padding:6px 14px;border-radius:6px;border:none;font-size:13px;font-weight:500;cursor:pointer;background:white;color:var(--b47-text);border:1px solid var(--b47-border,#E5E7EB);transition:all 0.15s;">Info</button>
        </div>
        <?php endif; ?>

        <!-- Log Table -->
        <?php if ( empty( $log_entries ) ) : ?>
        <div class="b47-card">
            <div class="b47-empty-state">
                <div class="b47-empty-icon"><span class="dashicons dashicons-info" style="font-size:48px;width:48px;height:48px;"></span></div>
                <div class="b47-empty-title">No Logs Available</div>
                <div class="b47-empty-desc">System logs will appear here as events occur.</div>
            </div>
        </div>
        <?php else : ?>
        <div class="b47-card" style="padding:0;overflow:hidden;">
            <!-- Table Header -->
            <div class="b47-log-row" style="background:#FAFBFC;border-bottom:2px solid var(--b47-border);font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:0.3px;color:var(--b47-text-muted);">
                <span class="b47-log-time">Time</span>
                <span class="b47-log-level" style="background:none;color:var(--b47-text-muted);">Level</span>
                <span class="b47-log-msg">Message</span>
            </div>
            <?php foreach ( array_reverse( $log_entries ) as $entry ) :
                $level_lower = strtolower( $entry['level'] );
            ?>
            <div class="b47-log-row" data-level="<?php echo esc_attr( $level_lower ); ?>">
                <span class="b47-log-time"><?php echo esc_html( $entry['time'] ); ?></span>
                <span class="b47-log-level <?php echo esc_attr( $level_lower ); ?>"><?php echo esc_html( $entry['level'] ); ?></span>
                <span class="b47-log-msg"><?php echo esc_html( $entry['message'] ); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Footer Note -->
        <div style="margin-top:16px;padding:12px 16px;font-size:12px;color:var(--b47-text-muted);">
            <span class="dashicons dashicons-info" style="font-size:14px;width:14px;height:14px;"></span>
            Logs are stored in <code><?php echo esc_html( str_replace( ABSPATH, '', $log_file ?? '' ) ); ?></code> and persist through plugin updates.
        </div>

        <!-- Pro Features Notice -->
        <?php do_action( 'base47_he_logs_page_footer' ); ?>

    </div>
    </div>
    <?php
}

/**
 * Parse raw logs into structured array
 */
function base47_he_parse_logs( $raw_logs ) {
    if ( empty( $raw_logs ) ) return [];
    $entries = [];
    $lines = explode( "\n", $raw_logs );
    foreach ( $lines as $line ) {
        $line = trim( $line );
        if ( empty( $line ) ) continue;
        if ( preg_match( '/^\[([^\]]+)\]\s*\[([^\]]+)\]\s*(.+)$/', $line, $matches ) ) {
            $entries[] = [ 'time' => $matches[1], 'level' => strtoupper( trim( $matches[2] ) ), 'message' => trim( $matches[3] ) ];
        } else {
            $entries[] = [ 'time' => gmdate( 'Y-m-d H:i:s' ), 'level' => 'INFO', 'message' => $line ];
        }
    }
    return $entries;
}
