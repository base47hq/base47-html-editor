/**
 * Logs Admin JS
 *
 * @package Base47_HTML_Editor
 * @since 3.0.0
 */
jQuery( document ).ready( function( $ ) {

	// Clear logs
	$( '#base47-clear-logs' ).on( 'click', function() {
		if ( ! confirm( 'Are you sure you want to clear all logs? This action cannot be undone.' ) ) return;

		var btn = $( this );
		var originalText = btn.html();
		btn.prop( 'disabled', true ).html( '<span class="dashicons dashicons-update"></span> Clearing...' );

		$.post( BASE47_HE.ajax_url, {
			action: 'base47_clear_logs',
			nonce:  BASE47_HE.nonce
		}, function( response ) {
			if ( response.success ) {
				$( '#base47-logs-status' ).html( '<span class="status-success">✓ Logs cleared successfully</span>' );
				setTimeout( function() { location.reload(); }, 1000 );
			} else {
				$( '#base47-logs-status' ).html( '<span class="status-error">✗ Failed to clear logs</span>' );
				btn.prop( 'disabled', false ).html( originalText );
			}
		} ).fail( function() {
			$( '#base47-logs-status' ).html( '<span class="status-error">✗ Network error</span>' );
			btn.prop( 'disabled', false ).html( originalText );
		} );
	} );

	// Download logs
	$( '#base47-download-logs' ).on( 'click', function() {
		window.location.href = BASE47_HE.ajax_url + '?action=base47_download_logs&nonce=' + BASE47_HE.nonce;
	} );

} );
