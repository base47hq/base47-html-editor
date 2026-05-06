<?php
/**
 * License Page — Base47 Soft UI
 *
 * @package Base47_HTML_Editor
 * @since 3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function base47_he_license_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $is_pro         = base47_he_is_pro_active();
    $license_key    = get_option( 'base47_he_license_key', '' );
    $license_status = get_option( 'base47_he_license_status', 'inactive' );
    $license_data   = get_option( 'base47_he_license_data', [] );
    $pro_url        = base47_he_get_pro_url();

    $mask_key = function( $key ) {
        if ( strlen( $key ) <= 8 ) return $key;
        $parts = explode( '-', $key );
        if ( count( $parts ) >= 4 ) return $parts[0] . '-****-****-' . end( $parts );
        return substr( $key, 0, 4 ) . '-****-****-' . substr( $key, -4 );
    };

    // Header buttons (only when active)
    $header_buttons = '';
    if ( $license_status === 'active' ) {
        $header_buttons .= '<button type="button" id="base47-deregister-license" class="b47-btn b47-btn-secondary b47-btn-sm"><span class="dashicons dashicons-dismiss" style="font-size:16px;width:16px;height:16px;"></span> Deactivate</button>';
        $header_buttons .= '<a href="' . esc_url( $pro_url ) . '/account" target="_blank" class="b47-btn b47-btn-primary b47-btn-sm"><span class="dashicons dashicons-admin-users" style="font-size:16px;width:16px;height:16px;"></span> Manage Account</a>';
    }
    ?>
    <div class="wrap">
    <div class="b47-wrap">
        <?php base47_he_render_header_nav( 'license', 'Manage your license and subscription', $header_buttons ); ?>

        <?php if ( $license_status === 'active' ) : ?>
        <!-- Active License Status -->
        <div class="b47-card b47-license-status active b47-mb-24">
            <div class="b47-flex b47-items-center b47-gap-16">
                <span class="dashicons dashicons-yes-alt" style="font-size:40px;width:40px;height:40px;color:var(--b47-success);"></span>
                <div>
                    <div class="b47-license-title">License Active</div>
                    <div class="b47-license-subtitle">Your Pro license is active and all premium features are unlocked.</div>
                </div>
                <span class="b47-badge b47-badge-green" style="margin-left:auto;">Pro License</span>
            </div>
        </div>

        <!-- License Info Grid -->
        <div class="b47-card b47-mb-24">
            <div class="b47-card-header">
                <div class="b47-flex b47-items-center b47-gap-8">
                    <div class="b47-section-icon blue"><span class="dashicons dashicons-shield" style="font-size:16px;width:16px;height:16px;"></span></div>
                    <span class="b47-card-title">License Information</span>
                </div>
            </div>
            <div class="b47-license-grid">
                <div class="b47-license-field">
                    <div class="b47-license-field-label">License Key</div>
                    <div class="b47-license-field-value"><code><?php echo esc_html( $mask_key( $license_key ) ); ?></code></div>
                </div>
                <div class="b47-license-field">
                    <div class="b47-license-field-label">Status</div>
                    <div class="b47-license-field-value"><span class="b47-badge b47-badge-green">Active</span></div>
                </div>
                <div class="b47-license-field">
                    <div class="b47-license-field-label">Plan</div>
                    <div class="b47-license-field-value"><?php echo esc_html( ucfirst( $license_data['plan'] ?? 'Pro' ) ); ?></div>
                </div>
                <div class="b47-license-field">
                    <div class="b47-license-field-label">Sites Used</div>
                    <div class="b47-license-field-value"><?php
                        $used = $license_data['activations_count'] ?? 1;
                        $max  = $license_data['max_activations'] ?? 1;
                        echo esc_html( $max == -1 ? $used . ' / Unlimited' : $used . ' / ' . $max );
                    ?></div>
                </div>
                <div class="b47-license-field">
                    <div class="b47-license-field-label">Expires</div>
                    <div class="b47-license-field-value"><?php echo esc_html( $license_data['expires'] ?? 'Never' ); ?></div>
                </div>
            </div>
            
            <!-- Pro Plugin Section (Full Width) -->
            <?php if ( ! $is_pro ) : ?>
            <div style="margin-top:20px;padding:24px;background:linear-gradient(135deg,#F0F9FF,#E0F2FE);border:1px solid #7DD3FC;border-radius:12px;color:#0C4A6E;">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                    <span style="font-size:24px;">🚀</span>
                    <div>
                        <h3 style="margin:0;color:#0C4A6E;font-size:18px;font-weight:700;">Activate Pro Features</h3>
                        <p style="margin:4px 0 0;color:#0369A1;font-size:14px;">Your license is active! Complete setup to unlock all Pro features.</p>
                    </div>
                    <span class="b47-badge b47-badge-amber" style="margin-left:auto;">Ready to Install</span>
                </div>
                
                <div style="display:grid;grid-template-columns:1fr auto;gap:24px;align-items:start;">
                    <div>
                        <h4 style="margin:0 0 12px;color:#0C4A6E;font-size:15px;font-weight:600;">Setup Instructions:</h4>
                        <ol style="margin:0;padding-left:20px;color:#0369A1;line-height:1.6;font-size:14px;">
                            <li style="margin-bottom:6px;">Download the PRO plugin from your Base47 dashboard</li>
                            <li style="margin-bottom:6px;">Go to <strong>Plugins → Add New → Upload Plugin</strong></li>
                            <li>Upload and activate the PRO plugin</li>
                        </ol>
                    </div>
                    
                    <div style="display:flex;flex-direction:column;gap:8px;min-width:180px;">
                        <a href="https://base47.art/dashboard/" target="_blank" class="b47-btn b47-btn-primary" style="background:#0284C7;border-color:#0284C7;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 20px;border-radius:8px;font-weight:600;white-space:nowrap;font-size:14px;">
                            <span class="dashicons dashicons-download" style="font-size:16px;width:16px;height:16px;"></span>
                            Download PRO Plugin
                        </a>
                        <div style="text-align:center;color:#0369A1;font-size:12px;">
                            or <a href="https://base47.art/login" target="_blank" style="color:#0284C7;text-decoration:none;font-weight:500;">login to your account</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php else : ?>
            <div class="b47-license-field">
                <div class="b47-license-field-label">Pro Plugin</div>
                <div class="b47-license-field-value">
                    <span class="b47-badge b47-badge-green">Installed</span>
                </div>
            </div>
            <?php endif; ?>
            </div>
        </div>

        <?php else : ?>
        <!-- Inactive License -->
        <div class="b47-card b47-license-status inactive b47-mb-24">
            <div class="b47-flex b47-items-center b47-gap-16">
                <span class="dashicons dashicons-lock" style="font-size:40px;width:40px;height:40px;color:var(--b47-text-muted);"></span>
                <div>
                    <div class="b47-license-title">Activate Your License</div>
                    <div class="b47-license-subtitle">Enter your license key to unlock all Pro features and get priority support.</div>
                </div>
            </div>
        </div>

        <!-- Activation Form -->
        <div class="b47-card b47-mb-24">
            <div class="b47-card-header">
                <div class="b47-flex b47-items-center b47-gap-8">
                    <div class="b47-section-icon blue"><span class="dashicons dashicons-shield" style="font-size:16px;width:16px;height:16px;"></span></div>
                    <span class="b47-card-title">License Activation</span>
                </div>
            </div>
            <form id="base47-license-form">
                <p style="font-size:13px;color:var(--b47-text-secondary);margin-bottom:16px;">Enter the license key you received after purchase.</p>
                <div style="margin-bottom:16px;">
                    <label for="base47-license-key-input" style="font-size:13px;font-weight:600;color:var(--b47-text);display:block;margin-bottom:6px;">License Key</label>
                    <input type="text" id="base47-license-key-input" name="license_key" class="b47-license-key-input" placeholder="B47-XXXX-XXXX-XXXX-XXXX" value="<?php echo esc_attr( $license_key ); ?>">
                    <p style="font-size:12px;color:var(--b47-text-muted);margin-top:6px;"><span class="dashicons dashicons-info" style="font-size:14px;width:14px;height:14px;"></span> Your license key is in your purchase confirmation email</p>
                </div>
                <button type="submit" class="activate-btn b47-btn b47-btn-primary">
                    <span class="btn-icon"><span class="dashicons dashicons-yes" style="font-size:16px;width:16px;height:16px;"></span></span>
                    <span class="btn-text">Activate License</span>
                </button>
                <div id="license-message" class="license-message" style="margin-top:12px;display:none;padding:10px 14px;border-radius:var(--b47-radius-sm);font-size:13px;"></div>
            </form>
        </div>

        <!-- Purchase Card -->
        <div class="b47-card b47-mb-24" style="background:linear-gradient(135deg,#F8F9FF,#EEF0FE);border-color:#DDE1FC;">
            <div class="b47-flex b47-items-center b47-gap-16" style="flex-wrap:wrap;">
                <span class="dashicons dashicons-cart" style="font-size:32px;width:32px;height:32px;color:var(--b47-primary);"></span>
                <div style="flex:1;">
                    <h3 style="font-size:16px;font-weight:700;color:var(--b47-text);margin:0 0 4px;">Need a License?</h3>
                    <p style="font-size:13px;color:var(--b47-text-secondary);margin:0;">Get instant access to all Pro features.</p>
                </div>
                <a href="<?php echo esc_url( $pro_url ); ?>" class="b47-btn b47-btn-primary" target="_blank">Purchase License</a>
            </div>
        </div>
        <?php endif; ?>

    </div>
    </div>
    <?php
}
