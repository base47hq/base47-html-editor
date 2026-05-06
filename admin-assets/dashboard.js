/**
 * Dashboard Admin JS
 *
 * @package Base47_HTML_Editor
 * @since 3.0.0
 */
jQuery( document ).ready( function( $ ) {

	// Dismiss Pro Upgrade Notice
	window.dismissProUpgradeNotice = function() {
		$( '#pro-upgrade-notice' ).fadeOut( 300, function() {
			$( this ).remove();
		} );

		$.post( BASE47_HE.ajax_url, {
			action: 'base47_he_dismiss_pro_notice',
			nonce:  BASE47_HE.nonce
		} );
	};

} );
