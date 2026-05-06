/**
 * Upgrade Page Admin JS
 *
 * @package Base47_HTML_Editor
 * @since 3.0.0
 */
jQuery( document ).ready( function( $ ) {

	// Pricing toggle
	$( '#pricing-toggle' ).on( 'change', function() {
		var isYearly = $( this ).is( ':checked' );
		$( '.price-amount' ).each( function() {
			var $this = $( this );
			$this.text( isYearly ? $this.data( 'yearly' ) : $this.data( 'monthly' ) );
		} );
		$( '.price-period' ).text( isYearly ? '/year' : '/month' );
	} );

	// FAQ accordion
	$( '.faq-question' ).on( 'click', function() {
		var $item   = $( this ).closest( '.faq-item' );
		var $answer = $item.find( '.faq-answer' );
		var $toggle = $( this ).find( '.faq-toggle .dashicons' );

		$( '.faq-item' ).not( $item ).removeClass( 'active' ).find( '.faq-answer' ).slideUp( 300 );
		$( '.faq-toggle .dashicons' ).not( $toggle ).removeClass( 'rotated' );

		$item.toggleClass( 'active' );
		$answer.slideToggle( 300 );
		$toggle.toggleClass( 'rotated' );
	} );

	// Smooth scroll to pricing
	$( 'a[href="#pricing"]' ).on( 'click', function( e ) {
		e.preventDefault();
		$( 'html, body' ).animate( { scrollTop: $( '#pricing' ).offset().top - 50 }, 800 );
	} );

} );
