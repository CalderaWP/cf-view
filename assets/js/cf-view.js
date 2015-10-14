/* globals jQuery, CF_VIEW_FOO_TABLE_OPTIONS */
jQuery( document ).ready( function ( $ ) {
    $
    var full_data = $( '#cf-view-full-parts' ).data( 'content' );

    $( '.cf-view-shortened-view' ).on( 'click', function(e){
        e.preventDefault();

        var key =  $(this ).data( 'cf-shortened' );
        if ( 'undefined' != full_data[ key ] ) {
            $( '#cf-view-table' ).slideUp();
            hide ( $( '#cf-view-table' ) );
            hide( $( '.footable-filter' ) );

            $( '#cf-view-full-viewer' ).html( full_data[ key ] );
            show( $( '#cf-view-full-viewer' ) );
            show( $( '#cf-full-close' ) );

            $( '#cf-full-close' ).on( 'click', function(e) {
                show ( $( '#cf-view-table' ) );
                $( '#cf-view-table' ).slideDown();
                show( $( '.footable-filter' ) );

                $( '#cf-view-full-viewer' ).html( '' );
                hide( $( '#cf-view-full-viewer' ) );
                hide( $( '#cf-full-close' ) );
            });



        }

    });

    function hide( el ) {
        el.css( 'visibility', 'hidden' ).attr( 'aria-hidden', 'true' );
    }

    function show( el ) {
        el.css( 'visibility', 'visible' ).attr( 'aria-hidden', 'false' );
    }

    $('.cf-view-table' ).footable( CF_VIEW_FOO_TABLE_OPTIONS );

} );
