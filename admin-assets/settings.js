/**
 * Settings Admin JS
 *
 * @package Base47_HTML_Editor
 * @since 3.0.0
 */
jQuery( document ).ready( function( $ ) {

	// Clear all caches
	$( '#base47-clear-all-caches' ).on( 'click', function() {
		var btn = $( this );
		var originalText = btn.html();
		btn.prop( 'disabled', true ).html( '<span class="dashicons dashicons-update"></span> Clearing...' );

		$.post( BASE47_HE.ajax_url, {
			action: 'base47_clear_all_caches',
			nonce:  BASE47_HE.nonce
		}, function( response ) {
			if ( response.success ) {
				$( '#base47-cache-status' ).html( '<span class="status-success">✓ Cleared</span>' );
			} else {
				$( '#base47-cache-status' ).html( '<span class="status-error">✗ Failed</span>' );
			}
			btn.prop( 'disabled', false ).html( originalText );
			setTimeout( function() { $( '#base47-cache-status' ).html( '' ); }, 3000 );
		} );
	} );

	// Clear logs
	$( '#base47-clear-logs' ).on( 'click', function() {
		if ( ! confirm( 'Are you sure you want to delete all logs?' ) ) return;

		var btn = $( this );
		var originalText = btn.html();
		btn.prop( 'disabled', true ).html( '<span class="dashicons dashicons-update"></span> Clearing...' );

		$.post( BASE47_HE.ajax_url, {
			action: 'base47_clear_logs',
			nonce:  BASE47_HE.nonce
		}, function( response ) {
			if ( response.success ) {
				$( '#base47-log-status' ).html( '<span class="status-success">✓ Cleared</span>' );
			} else {
				$( '#base47-log-status' ).html( '<span class="status-error">✗ Failed</span>' );
			}
			btn.prop( 'disabled', false ).html( originalText );
			setTimeout( function() { $( '#base47-log-status' ).html( '' ); }, 3000 );
		} );
	} );

	// Download logs
	$( '#base47-download-logs' ).on( 'click', function() {
		window.location.href = BASE47_HE.ajax_url + '?action=base47_download_logs&nonce=' + BASE47_HE.nonce;
	} );

	// Reset settings
	$( '#base47-reset-settings' ).on( 'click', function() {
		if ( ! confirm( 'Are you sure you want to reset all settings to defaults? This cannot be undone.' ) ) return;

		var btn = $( this );
		var originalText = btn.html();
		btn.prop( 'disabled', true ).html( '<span class="dashicons dashicons-update"></span> Resetting...' );

		$.post( BASE47_HE.ajax_url, {
			action: 'base47_reset_settings',
			nonce:  BASE47_HE.nonce
		}, function( response ) {
			if ( response.success ) {
				location.reload();
			} else {
				$( '#base47-reset-status' ).html( '<span class="status-error">✗ Failed to reset settings</span>' );
				btn.prop( 'disabled', false ).html( originalText );
				setTimeout( function() { $( '#base47-reset-status' ).html( '' ); }, 3000 );
			}
		} );
	} );

	// Export settings
	$( '#base47-export-settings' ).on( 'click', function() {
		window.location.href = BASE47_HE.ajax_url + '?action=base47_export_settings&nonce=' + BASE47_HE.nonce;
	} );

	// Import settings
	$( '#base47-import-settings' ).on( 'click', function() {
		$( '#base47-import-file' ).click();
	} );

	$( '#base47-import-file' ).on( 'change', function() {
		var file = this.files[0];
		if ( ! file ) return;

		if ( ! confirm( 'Import settings from "' + file.name + '"? This will overwrite your current settings.' ) ) {
			$( this ).val( '' );
			return;
		}

		var formData = new FormData();
		formData.append( 'action', 'base47_import_settings' );
		formData.append( 'nonce', BASE47_HE.nonce );
		formData.append( 'settings_file', file );

		$( '#base47-import-status' ).html( '<span class="status-info">Importing...</span>' );

		$.ajax( {
			url:         BASE47_HE.ajax_url,
			type:        'POST',
			data:        formData,
			processData: false,
			contentType: false,
			success: function( response ) {
				if ( response.success ) {
					$( '#base47-import-status' ).html( '<span class="status-success">✓ Imported!</span>' );
					setTimeout( function() { location.reload(); }, 1500 );
				} else {
					$( '#base47-import-status' ).html( '<span class="status-error">✗ ' + response.data.message + '</span>' );
				}
			},
			error: function() {
				$( '#base47-import-status' ).html( '<span class="status-error">✗ Import failed</span>' );
			}
		} );

		$( this ).val( '' );
	} );

} );
