var BoldGrid_Staging = BoldGrid_Staging || {};

/**
 * Stage blog / reading settings.
 *
 * @since 1.0.7
 */
BoldGrid_Staging.AdminPageAddGridBlockSet = function($) {
	var self = this;

	jQuery(function() {
		self.init();
	});

	/**
	 * Add "Active / Staging" navigation to the top of the page.
	 *
	 * @since 1.0.7
	 */
	this.add_navigation = function() {
		// If the user does not have a staging theme, then they cannot preview
		// any staging GridBlock Sets.
		// Therefore, do not show this navigation if the user has no staging
		// theme.
		if ('false' == boldgrid_staging_add_gridblock_sets.has_staging_theme) {
			// If the user somehow has ?staging=1, reload the page.
			// Otherwise, simply return so that the navigation is not added.
			if (self.is_staging) {
				window.location.href = 'edit.php?post_type=page&page=boldgrid-add-gridblock-sets';
			} else {
				return;
			}
		}

		var template = wp.template('boldgrid-staging-navigation');

		// Configure the data we'll pass to our template.
		var template_data = {
			'tag' : 'h2',
			'active_href' : 'edit.php?post_type=page&page=boldgrid-add-gridblock-sets',
			'staging_href' : 'edit.php?post_type=page&page=boldgrid-add-gridblock-sets&staging=1',
			'active_text' : 'Active Site',
			'staging_text' : 'Staging Site',
			'is_staging' : self.getParameterByName('staging')
		}

		// Add the rendered template after the "Reading Settings" h1 at the top
		// of the page.
		self.$wrap.find('h1').first().after(template(template_data));
	}

	/**
	 * Init.
	 *
	 * @since 1.0.7
	 */
	this.init = function() {
		// Context used for selectors.
		self.$wrap = $('.wrap', 'body');

		self.is_staging = ('1' == self.getParameterByName('staging'));

		self.add_navigation();
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
};

new BoldGrid_Staging.AdminPageAddGridBlockSet(jQuery);