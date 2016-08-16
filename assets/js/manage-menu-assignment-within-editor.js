var IMHWPB = IMHWPB || {};

IMHWPB.StagingManageMenuAssignmentWithinEditor = function( $ ) {
	var self = this;

	// Save the development group on page load.
	IMHWPB.loaded_dev_group = ( 'staging' == $( '#development_group_post_status:checked' ).val() ? 'staging'
	    : 'publish' );

	// Initialize IMHWPB.original_selections_active.
	IMHWPB.original_selections_active = [];

	// Initialize IMHWPB.original_selections_staging.
	IMHWPB.original_selections_staging = [];

	// Toggle menus and listen for change of development group.
	jQuery( function() {
		self.toggleMenusInAddPageToMenu( true );

		// When a user selects a "Development Group" in the "Publish" metabox,
		// adjust the list of menus the user can choose from.
		$( '[name="development_group_post_status"]' ).change( function() {
			self.toggleMenusInAddPageToMenu( false );

			// Set the selected menu names.
			self.setSelectedMenuNames();

			return false;
		} );
	} );

	/**
	 * Toggle menus.
	 */
	this.toggleMenusInAddPageToMenu = function( is_pageload ) {
		// Selected dev group.
		var selected_dev_group = ( 'staging' == $( '#development_group_post_status:checked' ).val() ? 'staging'
		    : 'publish' ),
		// Context for the menu listing.
		$menu_listing = $( '#boldgrid-auto-add-to-menu-menu-listing' ),
		// Is a new page.
		is_new_page = $( '#boldgrid-auto-add-to-menu-page-id' ).attr( 'data-is-new-page' ),
		// Switch for which selections to restore when needed.
		using_selections;

		/**
		 * Show only one menu type (active or staging menus).
		 *
		 * We don't want to add an active page to a staging menu, and vice
		 * versa. So, depending on what the selected_dev_group is, show
		 * applicable menus.
		 */
		$menu_listing.children( 'div' ).each( function() {
			// If we want to only show active menus:
			if ( 'publish' == selected_dev_group ) {
				if ( $( this ).hasClass( 'active' ) ) {
					$( this ).removeClass( 'hidden' );
				} else {
					$( this ).addClass( 'hidden' );
				}
			}

			// If we want to only show staging menus:
			if ( 'staging' == selected_dev_group ) {
				if ( $( this ).hasClass( 'staging' ) ) {
					$( this ).removeClass( 'hidden' );
				} else {
					$( this ).addClass( 'hidden' );
				}
			}
		} );

		/**
		 * On switch of post status, set default menu selection.
		 *
		 * This is only done when creating a new page, so we can properly
		 * default the menu to 'primary'. If we're editing an existing page, we
		 * won't default any menus.
		 *
		 * Scenario 1: Let's say you had it checked to add this new page to all
		 * staging menus. Then you clicked 'Active' under 'Development Group'.
		 * The staging menus with checkboxes would be hidden from view, but they
		 * would still be checked. This would then add an active page to staging
		 * menus, which is undesirable. In the event a user switches the page to
		 * active, uncheck all staging menus.
		 *
		 * Scenario 2: By default, we want the primary menu selected. If we auto
		 * checked both the active and staging primary menu, the new page would
		 * be added to both. When a page is toggled between active / staging,
		 * the appropriate primary menu needs to be selected.
		 */
		if ( '1' == is_new_page ) {
			// Is a new page.
			if ( 'publish' == selected_dev_group ) {
				// Uncheck all staging menus.
				$menu_listing.children( 'div.staging' ).children( 'input:checkbox' ).prop(
				    'checked', false );

				// Check the primary active menu.
				$menu_listing.children( 'div.active.primary' ).children( 'input:checkbox' ).prop(
				    'checked', true );
			} else {
				// Uncheck all active menus.
				$menu_listing.children( 'div.active' ).children( 'input:checkbox' ).prop(
				    'checked', false );

				// Check the primary staging menu.
				$menu_listing.children( 'div.staging.primary' ).children( 'input:checkbox' ).prop(
				    'checked', true );
			}
		} else {
			// Is an existing page.
			// Clear checkboxes in the non-selected develpoment group.
			if ( 'publish' == selected_dev_group ) {
				// Uncheck all staging menus.
				$menu_listing.children( 'div.staging' ).children( 'input:checkbox' ).prop(
				    'checked', false );
			} else {
				// Uncheck all active menus.
				$menu_listing.children( 'div.active' ).children( 'input:checkbox' ).prop(
				    'checked', false );
			}

			// Restore previous selections.
			if ( 'publish' == selected_dev_group ) {
				using_selections = IMHWPB.original_selections_active;
			} else {
				using_selections = IMHWPB.original_selections_staging;
			}

			// Reset selections to the original values.
			using_selections.forEach( function( menu_name ) {
				$( "[data-menu-name='" + menu_name + "']" ).prop( 'checked', true );
			} );
		}

		// Set the original selections for the development group.
		if ( 'publish' == selected_dev_group ) {
			// Active site.
			// Reset IMHWPB.original_selections_active.
			IMHWPB.original_selections_active = [];

			// Store the selector context.
			var $selector = $( '.boldgrid-auto-add-to-menu' );

			// Get the checked checkbox menu names from the document.
			$selector.find( 'input:checkbox:checked' ).each( function() {
				IMHWPB.original_selections_active.push( $( this ).attr( 'data-menu-name' ) );
			} );
		} else {
			// Staging site.
			// Reset IMHWPB.original_selections_staging.
			IMHWPB.original_selections_staging = [];

			// Store the selector context.
			var $selector = $( '.boldgrid-auto-add-to-menu' );

			// Get the checked checkbox menu names from the document.
			$selector.find( 'input:checkbox:checked' ).each( function() {
				IMHWPB.original_selections_staging.push( $( this ).attr( 'data-menu-name' ) );
			} );
		}

		/**
		 * Set selected menu names for summary display.
		 */
		self.setSelectedMenuNames = function() {
			// Initialize selected_menu_names and store the selector context.
			var selected_menu_names = [],
			// Store the selector context.
			$selector = $( '.boldgrid-auto-add-to-menu' );

			// Get the checked checkbox menu names from the document.
			$selector.find( 'input:checkbox:checked' ).each( function() {
				selected_menu_names.push( $( this ).attr( 'data-menu-name' ) );
			} );

			if ( 0 == selected_menu_names.length ) {
				selected_menu_names = 'None';
			} else {
				selected_menu_names = selected_menu_names.join( ', ' );
			}

			$selector.find( '#selected-menu-names' ).html( selected_menu_names );

			return false;
		};
	}
};

new IMHWPB.StagingManageMenuAssignmentWithinEditor( jQuery );
