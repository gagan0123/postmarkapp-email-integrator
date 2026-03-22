( function ( $ ) {
	'use strict';

	$( document ).ready(
		function () {
			$( '#test-form' ).on(
				'submit',
				function ( e ) {
					e.preventDefault();
					var sendTo = $( '#pma_test_address' ).val();

					$( '#test-form .button-primary' ).val( 'Sending...' );
					$.post(
						pmaAdmin.ajaxUrl,
						{
							email: sendTo,
							action: $( this ).attr( 'action' ),
							nonce: pmaAdmin.testNonce
						},
						function ( data ) {
							$( '#test-form .button-primary' ).val( data );
						}
					);
				}
			);

			$( '#pma_import_button' ).on(
				'click',
				function () {
					$.post(
						pmaAdmin.ajaxUrl,
						{
							action: 'pma_import_settings',
							nonce: pmaAdmin.importNonce
						},
						function ( data ) {
							$( '#test-form .button-secondary' ).val( data );
							if ( data === 'Settings Imported' ) {
								location.reload();
							}
						}
					);
				}
			);
		}
	);
} )( jQuery );
