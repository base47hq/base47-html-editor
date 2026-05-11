/**
 * License Page Admin JS
 *
 * @package Base47_HTML_Editor
 * @since 3.0.0
 */
jQuery( document ).ready( function( $ ) {

	// Global showMessage function
	function showMessage( type, text ) {
		var $message = $( '#license-message' );
		$message.removeClass( 'success error info' ).addClass( type ).html( text ).show();
	}

	// Activate License
	$( '#base47-license-form' ).on( 'submit', function( e ) {
		e.preventDefault();

		var licenseKey = $( '#base47-license-key-input' ).val().trim();
		var $message   = $( '#license-message' );
		var $btn       = $( '.activate-btn' );
		var $btnText   = $btn.find( '.btn-text' );
		var $btnIcon   = $btn.find( '.btn-icon .dashicons' );

		if ( ! licenseKey ) {
			showMessage( 'error', 'Please enter a license key' );
			return;
		}

		$btn.prop( 'disabled', true ).addClass( 'loading' );
		$btnText.text( 'Activating...' );
		$btnIcon.removeClass( 'dashicons-yes' ).addClass( 'dashicons-update' );
		showMessage( 'info', 'Validating license key...' );

		$.post( BASE47_HE.ajax_url, {
			action:      'base47_activate_license',
			license_key: licenseKey,
			nonce:       BASE47_HE.nonce
		}, function( response ) {
			if ( response.success ) {
				showMessage( 'success', '✓ License activated successfully! Reloading page...' );
				setTimeout( function() { location.reload(); }, 1500 );
			} else {
				showMessage( 'error', '✗ ' + response.data.message );
				resetButton();
			}
		} ).fail( function() {
			showMessage( 'error', '✗ Network error. Please try again.' );
			resetButton();
		} );

		function resetButton() {
			$btn.prop( 'disabled', false ).removeClass( 'loading' );
			$btnText.text( 'Activate License' );
			$btnIcon.removeClass( 'dashicons-update' ).addClass( 'dashicons-yes' );
		}
	} );

	// Deregister License
	$( '#base47-deregister-license' ).on( 'click', function() {
		if ( ! confirm( 'Are you sure you want to deactivate this license key? All Pro features will be disabled.' ) ) return;

		var $btn = $( this );
		var $message = $( '#license-message' );
		var originalText = $btn.html();
		
		$btn.prop( 'disabled', true ).html( '<span class="dashicons dashicons-update"></span> Deactivating...' );

		// Set a failsafe reload in case AJAX doesn't complete
		var reloadTimer = setTimeout( function() {
			location.reload();
		}, 5000 );

		$.post( BASE47_HE.ajax_url, {
			action: 'base47_deactivate_license',
			nonce:  BASE47_HE.nonce
		}, function( response ) {
			clearTimeout( reloadTimer );
			// Always treat deactivation as success since local cleanup always works
			showMessage( 'success', '✓ License deactivated successfully. Reloading page...' );
			setTimeout( function() { location.reload(); }, 800 );
		} ).fail( function( xhr, status, error ) {
			clearTimeout( reloadTimer );
			
			// Handle nonce failures properly - never treat 403 as success
			if ( xhr.status === 403 || xhr.responseText === '-1' ) {
				showMessage( 'error', '✗ Security check failed. Please refresh the page and try again.' );
				$btn.prop( 'disabled', false ).html( originalText );
				setTimeout( function() { location.reload(); }, 2000 );
				return;
			}
			
			// For other AJAX failures, show proper error message
			showMessage( 'error', '✗ License deactivation failed. Please try again.' );
			$btn.prop( 'disabled', false ).html( originalText );
		} );

		function showMessage( type, text ) {
			$message.removeClass( 'success error info' ).addClass( type ).html( text ).show();
		}
	} );

} );
