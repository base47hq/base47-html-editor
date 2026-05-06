/**
 * Special Widgets Admin JS
 *
 * @package Base47_HTML_Editor
 * @since 3.0.0
 */
jQuery( document ).ready( function( $ ) {

	// Copy shortcode to clipboard
	$( '.btn-widget-copy' ).on( 'click', function( e ) {
		e.preventDefault();

		var shortcode = $( this ).data( 'shortcode' );
		var $btn = $( this );

		// Use Clipboard API if available, fallback to execCommand
		if ( navigator.clipboard && window.isSecureContext ) {
			navigator.clipboard.writeText( shortcode ).then( function() {
				showCopied( $btn );
			} );
		} else {
			var $temp = $( '<input>' );
			$( 'body' ).append( $temp );
			$temp.val( shortcode ).select();
			document.execCommand( 'copy' );
			$temp.remove();
			showCopied( $btn );
		}
	} );

	function showCopied( $btn ) {
		var originalHtml = $btn.html();
		$btn.html( '<span class="dashicons dashicons-yes"></span> Copied!' ).addClass( 'copied' );
		setTimeout( function() {
			$btn.html( originalHtml ).removeClass( 'copied' );
		}, 2000 );
		showToast( 'Shortcode Copied!', 'success' );
	}

	function showToast( message, type ) {
		var toast = $( '<div class="base47-toast base47-toast-' + type + '">' + message + '</div>' );
		$( 'body' ).append( toast );
		setTimeout( function() { toast.addClass( 'show' ); }, 100 );
		setTimeout( function() {
			toast.removeClass( 'show' );
			setTimeout( function() { toast.remove(); }, 300 );
		}, 2000 );
	}

} );
