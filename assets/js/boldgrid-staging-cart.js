/**
 * This file is intended to help BoldGrid Staging manipulate the BoldGrid Inspirations Cart page.
 *
 * @since 1.1.2
 */
var BoldGrid_Staging = BoldGrid_Staging || {};

BoldGrid_Staging.Cart = function( $ ) {
	var self = this;

	$( function() {
		self.add_navigation();
	});

	/**
	 * @summary Add active / staging navigation to the top of the cart.
	 *
	 * @since 1.1.2
	 */
	this.add_navigation = function() {
		var template = wp.template( 'boldgrid-staging-navigation' );

		// Configure the data we'll pass to our template.
		var template_data = {
			'tag'			: 'h3',
			'active_href'	: 'admin.php?page=boldgrid-cart',
			'staging_href'	: 'admin.php?page=boldgrid-cart&staging=1',
			'active_text'	: 'Active Site',
			'staging_text'	: 'Staging Site',
			'is_staging'	: self.getParameterByName('staging')
		}

		// Add our navigation under the nav tabs.
		$( '#boldgrid-transaction-tabs' ).after( template( template_data ) );
	}

	/**
	 * @summary Read parameters in the url.
	 *
	 * @since 1.1.2
	 */
	this.getParameterByName = function( name ) {
		name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
		var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"), results = regex.exec(location.search);

		return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
	}
};

new BoldGrid_Staging.Cart( jQuery );
