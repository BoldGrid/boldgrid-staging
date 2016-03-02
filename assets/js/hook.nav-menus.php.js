var IMHWPB = IMHWPB || {};

IMHWPB.StagingNavMenus = function() {
	var self = this;

	jQuery(function() {
		jQuery(function() {
			self.add_switcher_above_pages();

			if ('1' == self.getParameterByName('staging')) {
				self.remove_menu_settings();
			}
		});
	});

	this.add_switcher_above_pages = function() {
		// Which tab should be "selected"?
		var production_selected = '1' == self.getParameterByName('staging') ? ''
				: 'nav-tab-active';
		var staging_selected = '1' == self.getParameterByName('staging') ? 'nav-tab-active'
				: '';

		var page_filename = 'nav-menus.php';

		// Create the html of our nav tabs
		var select_staging = "<h3 class='nav-tab-wrapper' style='padding-bottom:0px;'>"
				+ "<a href='"
				+ page_filename
				+ "' class='nav-tab "
				+ production_selected
				+ "'>Active</a>"
				+ "<a href='"
				+ page_filename
				+ "?staging=1' class='nav-tab "
				+ staging_selected + "'>Staging</a>" + "</h3>";

		// Display the nav tags
		jQuery('div#side-sortables').before(select_staging);
	}

	/**
	 * 
	 */
	this.getParameterByName = function(name) {
		name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
		var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"), results = regex
				.exec(location.search);
		return results === null ? "" : decodeURIComponent(results[1].replace(
				/\+/g, " "));
	}

	/**
	 * Menu settings staging not yet implemented, so remove
	 */
	this.remove_menu_settings = function() {
		jQuery('div.menu-settings').remove();
	}
};

new IMHWPB.StagingNavMenus();