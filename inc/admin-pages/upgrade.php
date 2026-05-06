<?php
/**
 * Upgrade Page — Base47 Soft UI
 *
 * @package Base47_HTML_Editor
 * @since 3.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function base47_he_upgrade_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    $pro_url = base47_he_get_pro_url();
    $license_url = admin_url( 'admin.php?page=base47-he-license' );

    $header_buttons = '<a href="' . esc_url( $license_url ) . '" class="b47-btn b47-btn-secondary b47-btn-sm"><span class="dashicons dashicons-shield" style="font-size:16px;width:16px;height:16px;"></span> Already have a license?</a>';
    ?>
    <div class="wrap">
    <div class="b47-wrap">
        <?php base47_he_render_header_nav( 'pro', 'Unlock the full power of your workflow', $header_buttons ); ?>

        <!-- Hero -->
        <div class="b47-upgrade-hero b47-mb-24">
            <h2>Unlock the Full Power of Base47 HTML Editor</h2>
            <p>Transform your workflow with Monaco Editor, unlimited templates, auto-backups, and premium features.</p>
            <a href="<?php echo esc_url( $pro_url ); ?>" class="b47-upgrade-btn" target="_blank">
                <span class="dashicons dashicons-cart" style="font-size:18px;width:18px;height:18px;"></span> Get Pro Now
            </a>
        </div>

        <!-- Trust Badges -->
        <div class="b47-grid b47-grid-4 b47-mb-24">
            <div class="b47-card b47-stat-card-v2" style="text-align:center;">
                <div class="b47-stat-card-icon blue" style="margin:0 auto 14px;"><span class="dashicons dashicons-admin-tools" style="font-size:20px;width:20px;height:20px;"></span></div>
                <div class="b47-stat-card-value" style="font-size:16px;">Built for</div>
                <div class="b47-stat-card-label">Creators</div>
            </div>
            <div class="b47-card b47-stat-card-v2" style="text-align:center;">
                <div class="b47-stat-card-icon green" style="margin:0 auto 14px;"><span class="dashicons dashicons-update" style="font-size:20px;width:20px;height:20px;"></span></div>
                <div class="b47-stat-card-value" style="font-size:16px;">1 Year</div>
                <div class="b47-stat-card-label">Updates</div>
            </div>
            <div class="b47-card b47-stat-card-v2" style="text-align:center;">
                <div class="b47-stat-card-icon purple" style="margin:0 auto 14px;"><span class="dashicons dashicons-sos" style="font-size:20px;width:20px;height:20px;"></span></div>
                <div class="b47-stat-card-value" style="font-size:16px;">Priority</div>
                <div class="b47-stat-card-label">Support</div>
            </div>
            <div class="b47-card b47-stat-card-v2" style="text-align:center;">
                <div class="b47-stat-card-icon amber" style="margin:0 auto 14px;"><span class="dashicons dashicons-performance" style="font-size:20px;width:20px;height:20px;"></span></div>
                <div class="b47-stat-card-value" style="font-size:16px;">Pro</div>
                <div class="b47-stat-card-label">Workflow</div>
            </div>
        </div>

        <!-- Feature Cards -->
        <div class="b47-grid b47-grid-3 b47-mb-24">
            <?php
            $features = [
                [ 'icon' => 'dashicons-editor-code', 'color' => 'blue', 'title' => 'Monaco Editor', 'desc' => 'A VS Code-style editing experience with syntax highlighting, cleaner navigation, and a more focused workflow.' ],
                [ 'icon' => 'dashicons-smartphone', 'color' => 'green', 'title' => 'Responsive Preview', 'desc' => 'Preview templates across desktop, tablet, and mobile layouts while you work.' ],
                [ 'icon' => 'dashicons-backup', 'color' => 'amber', 'title' => 'Auto-Backups', 'desc' => 'Create automatic backups before saves so you can restore previous versions with confidence.' ],
                [ 'icon' => 'dashicons-admin-page', 'color' => 'purple', 'title' => 'Unlimited Templates', 'desc' => 'Work with unlimited template packs and expand your HTML library without Free version limits.' ],
                [ 'icon' => 'dashicons-performance', 'color' => 'indigo', 'title' => 'Smart Asset Loading', 'desc' => 'Load template assets more efficiently with advanced Pro asset controls.' ],
                [ 'icon' => 'dashicons-sos', 'color' => 'red', 'title' => 'Priority Support', 'desc' => 'Get faster help for Pro workflow issues and advanced setup questions.' ],
            ];
            foreach ( $features as $f ) : ?>
            <div class="b47-card b47-feature-card">
                <div class="b47-feature-card-icon b47-section-icon <?php echo esc_attr( $f['color'] ); ?>" style="width:48px;height:48px;border-radius:12px;font-size:24px;">
                    <span class="dashicons <?php echo esc_attr( $f['icon'] ); ?>" style="font-size:24px;width:24px;height:24px;"></span>
                </div>
                <div class="b47-feature-card-title"><?php echo esc_html( $f['title'] ); ?></div>
                <div class="b47-feature-card-desc"><?php echo esc_html( $f['desc'] ); ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Comparison Table -->
        <div class="b47-card b47-mb-24">
            <div class="b47-card-header">
                <span class="b47-card-title">Free vs Pro Comparison</span>
            </div>
            <table class="b47-compare-table">
                <thead><tr><th>Feature</th><th>Free</th><th>Pro</th></tr></thead>
                <tbody>
                    <tr><td>Classic Editor</td><td class="b47-compare-check">✓</td><td class="b47-compare-check">✓</td></tr>
                    <tr><td>Monaco Editor (VS Code)</td><td class="b47-compare-cross">✕</td><td class="b47-compare-check">✓</td></tr>
                    <tr><td>Template Packs</td><td>1 Pack</td><td class="b47-compare-check">Unlimited</td></tr>
                    <tr><td>Responsive Preview</td><td class="b47-compare-cross">✕</td><td class="b47-compare-check">✓</td></tr>
                    <tr><td>Auto-Backups & Restore</td><td class="b47-compare-cross">✕</td><td class="b47-compare-check">✓</td></tr>
                    <tr><td>Priority Support</td><td class="b47-compare-cross">✕</td><td class="b47-compare-check">✓</td></tr>
                </tbody>
            </table>
        </div>

        <!-- Pricing Cards -->
        <div class="b47-grid b47-grid-3 b47-mb-24">
            <?php
            $plans = [
                [ 'name' => 'Starter', 'desc' => 'For individual developers', 'price' => '69', 'monthly' => '9', 'sites' => '1 Website', 'popular' => false ],
                [ 'name' => 'Pro', 'desc' => 'Best for agencies', 'price' => '139', 'monthly' => '19', 'sites' => '5 Websites', 'popular' => true ],
                [ 'name' => 'Agency', 'desc' => 'For teams and large projects', 'price' => '249', 'monthly' => '29', 'sites' => 'Unlimited', 'popular' => false ],
            ];
            foreach ( $plans as $plan ) : ?>
            <div class="b47-card" style="<?php echo $plan['popular'] ? 'border:2px solid var(--b47-primary);position:relative;' : ''; ?>">
                <?php if ( $plan['popular'] ) : ?>
                <div style="position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:var(--b47-primary);color:white;padding:3px 14px;border-radius:20px;font-size:11px;font-weight:600;">Most Popular</div>
                <?php endif; ?>
                <div style="text-align:center;padding:8px 0 16px;">
                    <h3 style="font-size:18px;font-weight:700;color:var(--b47-text);margin:0 0 4px;"><?php echo esc_html( $plan['name'] ); ?></h3>
                    <p style="font-size:13px;color:var(--b47-text-secondary);margin:0 0 16px;"><?php echo esc_html( $plan['desc'] ); ?></p>
                    <div style="font-size:36px;font-weight:700;color:var(--b47-text);">$<?php echo esc_html( $plan['price'] ); ?><span style="font-size:14px;font-weight:400;color:var(--b47-text-muted);">/year</span></div>
                    <div style="font-size:13px;color:var(--b47-text-muted);margin-top:4px;">or $<?php echo esc_html( $plan['monthly'] ); ?>/month</div>
                </div>
                <div style="border-top:1px solid var(--b47-border);padding:16px 0;font-size:13px;color:var(--b47-text-secondary);">
                    <div style="padding:6px 0;">✓ <?php echo esc_html( $plan['sites'] ); ?></div>
                    <div style="padding:6px 0;">✓ All Pro Features</div>
                    <div style="padding:6px 0;">✓ Monaco Editor</div>
                    <div style="padding:6px 0;">✓ Auto-Backups</div>
                    <div style="padding:6px 0;">✓ Priority Support</div>
                </div>
                <a href="<?php echo esc_url( $pro_url ); ?>" class="b47-btn <?php echo $plan['popular'] ? 'b47-btn-primary' : 'b47-btn-secondary'; ?>" style="width:100%;justify-content:center;" target="_blank">Get Started</a>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Final CTA -->
        <div class="b47-card" style="text-align:center;padding:40px;">
            <h2 style="font-size:22px;font-weight:700;color:var(--b47-text);margin:0 0 8px;">Ready to Upgrade Your Workflow?</h2>
            <p style="font-size:14px;color:var(--b47-text-secondary);margin:0 0 20px;">Build faster with Monaco Editor, unlimited templates, auto-backups, and Pro support.</p>
            <a href="<?php echo esc_url( $pro_url ); ?>" class="b47-btn b47-btn-primary" target="_blank" style="font-size:15px;padding:12px 28px;">
                <span class="dashicons dashicons-cart" style="font-size:18px;width:18px;height:18px;"></span> Get Pro Now — Starting at $69/year
            </a>
            <p style="font-size:12px;color:var(--b47-text-muted);margin-top:12px;">
                <a href="<?php echo esc_url( $license_url ); ?>" style="color:var(--b47-primary);">Already purchased? Activate your license</a>
            </p>
        </div>

    </div>
    </div>
    <?php
}
