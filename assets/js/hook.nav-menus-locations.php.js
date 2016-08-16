var IMHWPB = IMHWPB || {};

IMHWPB.StagingNavMenusLocations = function() {
	var self = this;

	jQuery(function() {
		self.add_switcher_under_nav_tabs();

		self.add_staging_to_save_changes_buttons();

		if ('locations' == self.getParameterByName('action')) {
			self.display_locations_warning();
		}
	});

	/**
	 *
	 */
	this.display_locations_warning = function() {
		var warning = "<p><strong style='color:red;'>Please note</strong>: "
				+ "Some theme developers program their theme to display a menu by a specific name, "
				+ "such as 'Primary'. After you deploy your Staging Site, "
				+ "if you are still seeing your old menu, try renaming the menu you want showing to 'Primary'";
		jQuery('div#menu-locations-wrap').after(warning);
	}

	/**
	 * For each "activate" button, add &staging=1 to the url
	 */
	this.add_staging_to_save_changes_buttons = function() {
		if ('1' == self.getParameterByName('staging')) {

			var action = jQuery('div#menu-locations-wrap form').attr('action');

			jQuery('div#menu-locations-wrap form').attr('action',
					action + '&staging=1');
		}
	}

	/**
	 * For each "activate" button, add &staging=1 to the url
	 */
	this.add_switcher_under_nav_tabs = function() {
		// Which tab should be "selected"?
		var production_selected = '1' == self.getParameterByName('staging') ? ''
				: 'nav-tab-active';
		var staging_selected = '1' == self.getParameterByName('staging') ? 'nav-tab-active'
				: '';

		// Create the html of our nav tabs
		var select_staging = "<h3 class='nav-tab-wrapper'>"
				+ "<a href='nav-menus.php?action=locations' class='nav-tab "
				+ production_selected
				+ "'>Active Menus</a>"
				+ "<a href='nav-menus.php?action=locations&staging=1' class='nav-tab "
				+ staging_selected + "'>Staging Menus</a>" + "</h3>";

		// Display the nav tags
		jQuery('h2.nav-tab-wrapper').after(select_staging);
	}

	this.getParameterByName = function(name) {
		name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
		var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"), results = regex
				.exec(location.search);
		return results === null ? "" : decodeURIComponent(results[1].replace(
				/\+/g, " "));
	}
};

new IMHWPB.StagingNavMenusLocations();
