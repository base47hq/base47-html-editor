<?php
/**
 * Live Editor Admin Page — Base47 Soft UI
 *
 * @package Base47_HTML_Editor
 * @since 3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function base47_he_editor_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $sets_all   = base47_he_get_template_sets();
    $active     = base47_he_get_active_sets();

    if ( empty( $active ) ) {
        echo '<div class="wrap"><div class="b47-wrap"><p>No active themes. Enable at least one in <strong>Theme Manager</strong>.</p></div></div>';
        return;
    }

    // GET parameters for file/set selection - no nonce needed for display purposes
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET params for display only, no data modification
    $current_set = isset( $_GET['set'] ) ? sanitize_text_field( wp_unslash( $_GET['set'] ) ) : $active[0];
    if ( ! in_array( $current_set, $active, true ) ) $current_set = $active[0];

    $files = [];
    if ( isset( $sets_all[ $current_set ] ) && is_dir( $sets_all[ $current_set ]['path'] ) ) {
        foreach ( new DirectoryIterator( $sets_all[ $current_set ]['path'] ) as $f ) {
            if ( $f->isFile() && in_array( strtolower( pathinfo( $f->getFilename(), PATHINFO_EXTENSION ) ), [ 'html', 'htm' ], true ) ) {
                $files[] = $f->getFilename();
            }
        }
    }
    sort( $files, SORT_NATURAL | SORT_FLAG_CASE );

    // GET parameter for file selection - no nonce needed for display purposes
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET param for display only, no data modification
    $selected = isset( $_GET['file'] ) ? sanitize_text_field( wp_unslash( $_GET['file'] ) ) : ( $files[0] ?? '' );
    $content  = '';
    if ( $selected && isset( $sets_all[ $current_set ] ) && file_exists( $sets_all[ $current_set ]['path'] . $selected ) ) {
        $content = '';
        if ( base47_he_init_filesystem() ) {
            global $wp_filesystem;
            $content = $wp_filesystem->get_contents( $sets_all[ $current_set ]['path'] . $selected );
        }
    }

    $preview = $selected
        ? admin_url( 'admin-ajax.php?action=base47_he_preview&file=' . rawurlencode( $selected ) . '&set=' . rawurlencode( $current_set ) . '&_wpnonce=' . wp_create_nonce( 'base47_he' ) )
        : '';

    $has_pro = apply_filters( 'base47_he_is_pro_active', false );
    ?>
    <div class="wrap">
    <div class="b47-wrap">

        <style>
        #base47-he-mode-classic:hover,
        #base47-he-mode-advanced:hover {
            background: var(--b47-primary) !important;
            color: white !important;
            border-color: var(--b47-primary) !important;
        }
        #base47-he-mode-classic.active,
        #base47-he-mode-advanced.active {
            background: var(--b47-primary) !important;
            color: white !important;
        }
        </style>

        <?php base47_he_render_header_nav( 'editor', 'Edit your templates safely' ); ?>

        <!-- Status Strip -->
        <div class="b47-status-strip">
            <div class="b47-status-item"><div class="b47-status-dot green"></div> Protected Save enabled</div>
            <div class="b47-status-item"><div class="b47-status-dot green"></div> Auto backup on save</div>
            <div class="b47-status-item"><div class="b47-status-dot blue"></div> Validation warnings: <span id="b47-warning-count">0</span></div>
        </div>

        <!-- Selectors Row -->
        <div class="b47-flex b47-items-center b47-justify-between b47-mb-20" style="flex-wrap:wrap;gap:12px;">
            <form method="get" class="b47-flex b47-gap-12" style="flex-wrap:wrap;">
                <input type="hidden" name="page" value="base47-he-editor">
                <select name="set" onchange="this.form.submit()" class="b47-select" style="width:auto;min-width:180px;">
                    <?php foreach ( $active as $s ) : ?>
                        <option value="<?php echo esc_attr( $s ); ?>" <?php selected( $s, $current_set ); ?>><?php echo esc_html( $s ); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="file" onchange="this.form.submit()" class="b47-select" style="width:auto;min-width:200px;">
                    <?php foreach ( $files as $f ) : ?>
                        <option value="<?php echo esc_attr( $f ); ?>" <?php selected( $f, $selected ); ?>><?php echo esc_html( $f ); ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <?php
        if ( ! $has_pro ) :
            $dismissed_monaco = get_user_meta( get_current_user_id(), 'base47_he_dismiss_monaco_notice', true );
            if ( ! $dismissed_monaco ) :
        ?>
        <!-- Pro Notice -->
        <div id="base47-monaco-notice" style="background:var(--b47-primary-bg);border:1px solid #DDE1FC;border-radius:var(--b47-radius);padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;gap:16px;">
            <span class="dashicons dashicons-star-filled" style="color:var(--b47-primary);font-size:24px;width:24px;height:24px;"></span>
            <div style="flex:1;">
                <strong style="color:var(--b47-text);">Monaco Editor is available in Base47 HTML Editor Pro</strong>
                <p style="color:var(--b47-text-secondary);font-size:13px;margin:4px 0 0;">You are using the Classic Editor. The Monaco Editor (VS Code experience) is provided by the separate Pro add-on.</p>
            </div>
            <a href="<?php echo esc_url( base47_he_get_pro_url() ); ?>" class="b47-btn b47-btn-primary b47-btn-sm" target="_blank">Get Pro Add-on</a>
            <button id="base47-dismiss-monaco-notice" class="b47-btn b47-btn-ghost b47-btn-sm">Dismiss</button>
        </div>
        <?php 
            endif; 
        endif; 
        ?>

        <!-- Editor Layout -->
        <div class="b47-editor-layout">
            <!-- Main Editor -->
            <div class="b47-editor-main">
                <div class="b47-toolbar">
                    <div class="b47-toolbar-left">
                        <div class="b47-filename">
                            <span class="dashicons dashicons-media-code" style="font-size:16px;width:16px;height:16px;color:var(--b47-primary);"></span>
                            <?php echo esc_html( $selected ); ?>
                        </div>
                        <span class="b47-badge b47-badge-green" id="b47-save-status">Ready</span>
                        <?php if ( $has_pro ) : ?>
                        <div class="b47-flex b47-gap-8" style="margin-left:12px;">
                            <button type="button" id="base47-he-mode-classic" class="b47-btn b47-btn-sm b47-btn-secondary active" style="font-size:12px;">Classic</button>
                            <button type="button" id="base47-he-mode-advanced" class="b47-btn b47-btn-sm b47-btn-ghost" style="font-size:12px;">Monaco</button>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="b47-toolbar-right">
                        <?php if ( $selected ) : ?>
                        <button id="base47-he-duplicate" class="b47-btn b47-btn-ghost b47-btn-sm">
                            <span class="dashicons dashicons-admin-page" style="font-size:15px;width:15px;height:15px;"></span> Duplicate
                        </button>
                        <button id="base47-he-restore" class="b47-btn b47-btn-ghost b47-btn-sm">
                            <span class="dashicons dashicons-backup" style="font-size:15px;width:15px;height:15px;"></span> Backups
                        </button>
                        <button id="base47-he-open-preview" class="b47-btn b47-btn-secondary b47-btn-sm">
                            <span class="dashicons dashicons-visibility" style="font-size:15px;width:15px;height:15px;"></span> Preview
                        </button>
                        <button id="base47-he-save" class="b47-btn b47-btn-primary b47-btn-sm">
                            <span class="dashicons dashicons-saved" style="font-size:15px;width:15px;height:15px;"></span> Save
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="b47-editor-textarea-wrap">
                    <!-- Monaco Container (hidden by default) -->
                    <div id="base47-monaco-editor" style="display:none;flex:1;min-height:400px;"></div>
                    <!-- Classic Textarea -->
                    <textarea id="base47-he-code" class="b47-editor-textarea"><?php echo esc_textarea( $content ); ?></textarea>
                </div>
            </div>

            <!-- Resizer -->
            <div id="base47-he-resizer" class="b47-editor-resizer"></div>

            <!-- Preview Panel -->
            <div class="b47-editor-preview">
                <div class="b47-editor-preview-header">
                    <span class="dashicons dashicons-visibility" style="font-size:14px;width:14px;height:14px;"></span>
                    Live Preview
                    <div style="margin-left:auto;" class="b47-flex b47-gap-8">
                        <button type="button" class="b47-btn b47-btn-ghost b47-btn-xs preview-size-btn active" data-size="100%">Full</button>
                        <button type="button" class="b47-btn b47-btn-ghost b47-btn-xs preview-size-btn" data-size="768">Tablet</button>
                        <button type="button" class="b47-btn b47-btn-ghost b47-btn-xs preview-size-btn" data-size="375">Mobile</button>
                    </div>
                </div>
                <div class="b47-editor-preview-body">
                    <div class="base47-he-preview-wrap" style="height:100%;">
                        <iframe id="base47-he-preview" src="<?php echo esc_url( $preview ); ?>" class="b47-preview-frame"></iframe>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden Fields -->
        <input type="hidden" id="base47-he-current-file" value="<?php echo esc_attr( $selected ); ?>">
        <input type="hidden" id="base47-he-current-set" value="<?php echo esc_attr( $current_set ); ?>">
        <?php wp_nonce_field( 'base47_he', 'nonce' ); ?>

        <!-- Editor Footer: Validation + Shortcuts (side by side) -->
        <div class="b47-editor-footer">
            <!-- Left: Validation Warnings -->
            <div class="b47-editor-footer-left" id="b47-validation-panel">
                <div class="b47-validation-header">
                    <span class="b47-validation-icon" id="b47-validation-icon">✓</span>
                    <span class="b47-validation-title" id="b47-validation-title">Validation Warnings (<span id="b47-validation-count">0</span>)</span>
                </div>
                <ul id="b47-validation-list" class="b47-validation-list">
                    <li class="b47-validation-ok">No issues detected. Save to validate.</li>
                </ul>
            </div>
            <!-- Right: Keyboard Shortcuts -->
            <div class="b47-editor-footer-right">
                <div class="b47-shortcuts-title">
                    <span class="dashicons dashicons-editor-help" style="font-size:14px;width:14px;height:14px;"></span>
                    Keyboard Shortcuts
                </div>
                <div class="b47-shortcuts-list">
                    <span class="b47-shortcut"><kbd>⌘</kbd><kbd>S</kbd> Save</span>
                    <span class="b47-shortcut"><kbd>⌘</kbd><kbd>Z</kbd> Undo</span>
                    <span class="b47-shortcut"><kbd>⌘</kbd><kbd>⇧</kbd><kbd>Z</kbd> Redo</span>
                    <span class="b47-shortcut"><kbd>⌘</kbd><kbd>A</kbd> Select All</span>
                    <span class="b47-shortcut"><kbd>⌘</kbd><kbd>C</kbd> Copy</span>
                    <span class="b47-shortcut"><kbd>⌘</kbd><kbd>V</kbd> Paste</span>
                    <span class="b47-shortcut"><kbd>⌘</kbd><kbd>F</kbd> Find</span>
                    <span class="b47-shortcut"><kbd>⌘</kbd><kbd>D</kbd> Duplicate Line</span>
                </div>
            </div>
        </div>

        <style>
        /* Editor Footer: Two-column layout */
        .b47-editor-footer {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 16px;
        }
        @media (max-width: 900px) {
            .b47-editor-footer { grid-template-columns: 1fr; }
        }

        /* Left: Validation Panel */
        .b47-editor-footer-left {
            padding: 14px 18px;
            background: #F0FDF4;
            border: 1px solid #BBF7D0;
            border-radius: var(--b47-radius, 12px);
            min-height: 60px;
            transition: background 0.2s, border-color 0.2s;
        }
        .b47-editor-footer-left.has-warnings {
            background: #FFFBEB;
            border-color: #FDE68A;
        }
        .b47-validation-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }
        .b47-validation-icon {
            font-size: 16px;
            line-height: 1;
            color: #16A34A;
        }
        .b47-editor-footer-left.has-warnings .b47-validation-icon {
            color: #D97706;
        }
        .b47-validation-title {
            font-size: 13px;
            font-weight: 700;
            color: #166534;
        }
        .b47-editor-footer-left.has-warnings .b47-validation-title {
            color: #92400E;
        }
        .b47-validation-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .b47-validation-list li {
            font-size: 12px;
            color: #15803D;
            padding: 2px 0;
            line-height: 1.5;
        }
        .b47-editor-footer-left.has-warnings .b47-validation-list li {
            color: #78350F;
            list-style: disc;
            margin-left: 16px;
        }
        .b47-validation-list li code {
            background: rgba(0,0,0,0.05);
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 11px;
            font-family: 'JetBrains Mono', monospace;
        }

        /* Right: Shortcuts */
        .b47-editor-footer-right {
            padding: 14px 18px;
            background: var(--b47-bg, #F9FAFB);
            border: 1px solid var(--b47-border, #E5E7EB);
            border-radius: var(--b47-radius, 12px);
        }
        .b47-shortcuts-title {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 700;
            color: var(--b47-text, #1F2937);
            margin-bottom: 8px;
        }
        .b47-shortcuts-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .b47-shortcut {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            font-size: 12px;
            color: var(--b47-text-secondary, #6B7280);
        }
        .b47-shortcut kbd {
            display: inline-block;
            padding: 2px 5px;
            font-size: 11px;
            font-family: -apple-system, BlinkMacSystemFont, sans-serif;
            background: #fff;
            border: 1px solid var(--b47-border, #D1D5DB);
            border-radius: 4px;
            box-shadow: 0 1px 1px rgba(0,0,0,0.05);
            color: var(--b47-text, #374151);
            line-height: 1;
        }
        </style>

    </div>
    </div>

    <!-- Duplicate Template Modal -->
    <div id="base47-he-duplicate-modal" class="base47-he-modal" style="display:none;">
        <div class="base47-he-modal-content" style="background:var(--b47-card,#fff);border-radius:var(--b47-radius,12px);max-width:480px;margin:80px auto;padding:0;box-shadow:var(--b47-shadow-lg);">
            <div style="padding:20px 24px;border-bottom:1px solid var(--b47-border,#E5E7EB);display:flex;align-items:center;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <span class="dashicons dashicons-admin-page" style="font-size:20px;width:20px;height:20px;color:var(--b47-primary);"></span>
                    <h2 style="margin:0;font-size:18px;font-weight:600;color:var(--b47-text);">Duplicate Template</h2>
                </div>
                <span class="base47-he-modal-close" style="cursor:pointer;font-size:24px;color:var(--b47-text-muted);line-height:1;">&times;</span>
            </div>
            <div style="padding:20px 24px;">
                <p style="color:var(--b47-text-secondary);font-size:13px;margin:0 0 16px;">Create a copy of the current template with a new name.</p>
                <label for="base47-he-duplicate-name" style="font-size:13px;font-weight:600;color:var(--b47-text);display:block;margin-bottom:6px;">New Template Name</label>
                <input type="text" id="base47-he-duplicate-name" placeholder="e.g., my-custom-page.html" style="width:100%;padding:10px 12px;border:1px solid var(--b47-border,#D1D5DB);border-radius:8px;font-size:14px;">
                <p style="font-size:12px;color:var(--b47-text-muted);margin:8px 0 0;">Use letters, numbers, hyphens, underscores. Include .html extension.</p>
                <div id="base47-he-duplicate-error" style="display:none;margin-top:12px;padding:10px 14px;background:#FEF2F2;border:1px solid #FECACA;border-radius:8px;">
                    <p style="color:#DC2626;margin:0;font-size:13px;"></p>
                </div>
            </div>
            <div style="padding:16px 24px;border-top:1px solid var(--b47-border,#E5E7EB);display:flex;gap:8px;justify-content:flex-end;">
                <button id="base47-he-duplicate-confirm" class="b47-btn b47-btn-primary b47-btn-sm">
                    <span class="dashicons dashicons-admin-page" style="font-size:14px;width:14px;height:14px;"></span> Create Duplicate
                </button>
                <button class="b47-btn b47-btn-ghost b47-btn-sm base47-he-modal-close">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Backup & Restore Modal -->
    <div id="base47-he-restore-modal" class="base47-he-modal" style="display:none;">
        <div class="base47-he-modal-content" style="background:var(--b47-card,#fff);border-radius:var(--b47-radius,12px);max-width:640px;margin:60px auto;padding:0;box-shadow:var(--b47-shadow-lg);">
            <!-- Header -->
            <div style="padding:20px 24px;border-bottom:1px solid var(--b47-border,#E5E7EB);display:flex;align-items:center;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <span class="dashicons dashicons-backup" style="font-size:20px;width:20px;height:20px;color:var(--b47-primary);"></span>
                    <h2 style="margin:0;font-size:18px;font-weight:600;color:var(--b47-text);">Backup & Restore</h2>
                </div>
                <span class="base47-he-modal-close" style="cursor:pointer;font-size:24px;color:var(--b47-text-muted);line-height:1;">&times;</span>
            </div>
            
            <!-- Actions Bar -->
            <div style="padding:16px 24px;background:var(--b47-bg,#F9FAFB);border-bottom:1px solid var(--b47-border,#E5E7EB);display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <button type="button" id="base47-he-create-backup" class="b47-btn b47-btn-primary b47-btn-sm">
                    <span class="dashicons dashicons-plus-alt" style="font-size:14px;width:14px;height:14px;"></span> Create Backup
                </button>
                <button type="button" id="base47-he-refresh-backups" class="b47-btn b47-btn-ghost b47-btn-sm">
                    <span class="dashicons dashicons-update" style="font-size:14px;width:14px;height:14px;"></span> Refresh
                </button>
                <span style="margin-left:auto;font-size:12px;color:var(--b47-text-secondary);">Auto-backup on every save</span>
            </div>
            
            <!-- Backup List -->
            <div style="padding:20px 24px;max-height:350px;overflow-y:auto;">
                <div id="base47-he-backup-list" style="min-height:80px;">
                    <p style="text-align:center;color:var(--b47-text-secondary);padding:20px;margin:0;">Loading backups...</p>
                </div>
                <div id="base47-he-backup-preview" style="display:none;margin-top:16px;">
                    <div style="font-size:13px;font-weight:600;color:var(--b47-text);margin-bottom:8px;">Preview</div>
                    <textarea id="base47-he-backup-preview-content" readonly style="width:100%;height:200px;font-family:'JetBrains Mono',monospace;font-size:12px;border:1px solid var(--b47-border);border-radius:8px;padding:12px;background:var(--b47-bg,#F9FAFB);color:var(--b47-text);resize:none;"></textarea>
                </div>
            </div>
            
            <!-- Footer -->
            <div style="padding:16px 24px;border-top:1px solid var(--b47-border,#E5E7EB);display:flex;gap:8px;justify-content:flex-end;">
                <button id="base47-he-restore-selected" class="b47-btn b47-btn-primary b47-btn-sm" disabled>
                    <span class="dashicons dashicons-image-rotate" style="font-size:14px;width:14px;height:14px;"></span> Restore
                </button>
                <button id="base47-he-download-selected" class="b47-btn b47-btn-secondary b47-btn-sm" disabled>
                    <span class="dashicons dashicons-download" style="font-size:14px;width:14px;height:14px;"></span> Download
                </button>
                <button class="b47-btn b47-btn-ghost b47-btn-sm base47-he-modal-close">Cancel</button>
            </div>
        </div>
    </div>
    <?php
}
