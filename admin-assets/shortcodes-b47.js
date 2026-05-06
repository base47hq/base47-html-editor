/**
 * Shortcodes page — search filter & copy-to-clipboard
 *
 * @package Base47_HTML_Editor
 * @since 3.1.0
 */
( function() {
    'use strict';

    /* Search filter */
    var searchInput = document.getElementById( 'b47-sc-search' );
    if ( searchInput ) {
        searchInput.addEventListener( 'input', function() {
            var q = this.value.toLowerCase();
            document.querySelectorAll( '.b47-sc-card-v2' ).forEach( function( card ) {
                var name = ( card.getAttribute( 'data-name' ) || '' ) + ' ' + card.textContent.toLowerCase();
                card.style.display = name.includes( q ) ? '' : 'none';
            } );
        } );
    }

    /* Toast helper */
    function b47ShowToast( msg ) {
        var t = document.getElementById( 'b47-toast' );
        if ( ! t ) {
            t = document.createElement( 'div' );
            t.id = 'b47-toast';
            t.className = 'b47-toast';
            document.body.appendChild( t );
        }
        t.textContent = msg;
        t.classList.add( 'show' );
        clearTimeout( t._to );
        t._to = setTimeout( function() { t.classList.remove( 'show' ); }, 2500 );
    }

    /* Copy shortcode — delegated click handler */
    function b47CopyShortcode( text ) {
        if ( navigator.clipboard ) {
            navigator.clipboard.writeText( text ).then( function() {
                b47ShowToast( 'Copied to clipboard!' );
            } );
        } else {
            var ta = document.createElement( 'textarea' );
            ta.value = text;
            document.body.appendChild( ta );
            ta.select();
            document.execCommand( 'copy' );
            document.body.removeChild( ta );
            b47ShowToast( 'Copied to clipboard!' );
        }
    }

    /* Expose globally so onclick attributes in PHP can call it */
    window.b47CopyShortcode = function( el, text ) {
        b47CopyShortcode( text );
    };
} )();
