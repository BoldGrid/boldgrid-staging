var BoldGrid_Staging = BoldGrid_Staging || {};

/**
 * Stage blog / reading settings.
 *
 * @since 1.0.7
 */
BoldGrid_Staging.StagingSettingsReading = function($) {
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
		var template = wp.template('boldgrid-staging-navigation');

		// Configure the data we'll pass to our template.
		var template_data = {
			'tag' : 'h2',
			'active_href' : 'options-reading.php',
			'staging_href' : 'options-reading.php?staging=1',
			'active_text' : 'Active Site',
			'staging_text' : 'Staging Site',
			'is_staging' : self.getParameterByName('staging')
		}

		// Add the rendered template after the "Reading Settings" h1 at the top
		// of the page.
		self.$wrap.find('h1').first().after(template(template_data));
	}

	/**
	 * Is needle found within haystack?
	 *
	 * @since 1.0.7
	 *
	 * @link http://stackoverflow.com/questions/784012/javascript-equivalent-of-phps-in-array
	 *
	 * @param string
	 *            needle
	 * @param array
	 *            haystack
	 * @return bool
	 */
	this.in_array = function(needle, haystack) {
		var length = haystack.length;

		for (var i = 0; i < length; i++) {
			if (haystack[i] == needle) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Init.
	 *
	 * @since 1.0.7
	 */
	this.init = function() {
		// Context used for selectors.
		self.$wrap = $('.wrap', 'body');

		// The <form> on the page containing our settings.
		self.$settings_form = self.$wrap.find('form').first();

		self.is_staging = ('1' == self.getParameterByName('staging'));

		self.add_navigation();

		if (self.is_staging) {
			self.set_input_names();
			self.remove_options_not_staged();
			self.update_form_action();
		}
	}

	/**
	 * Remove options on the page not staged.
	 *
	 * As of WordPress 4.4.2, we've staged all of options on this page. However,
	 * future versions may have more settings that we have not yet staged.
	 *
	 * @since 1.0.7
	 */
	this.remove_options_not_staged = function() {
		// Loop through all of the settings on the page:
		$.each(self.input_names, function(key, value) {
			// If this setting is not in the array of settings we're staging:
			if (!self.in_array(value, boldgrid_staging_options_to_stage)) {
				// Remove the setting's <tr>, essentially removing the setting
				// from the page.
				$('input[name="' + value + '"]', self.$wrap).closest('tr')
						.remove();
			}
		});
	}

	/**
	 * Set self.input_names.
	 *
	 * What options are listed on this page? We loop through all of the
	 * <input>'s on this page and add their 'name' to an array.
	 *
	 * @since 1.0.7
	 */
	this.set_input_names = function() {
		self.input_names = [];

		self.$settings_form.find('input').each(function() {
			var input_name = $(this).attr('name');

			self.input_names.push(input_name);
		})
	}

	/**
	 * Update the action of the <form>.
	 *
	 * When we're working with Staging, we want to make sure we're submitting
	 * the data to '?staging=1'.
	 *
	 * @since 1.0.7
	 */
	this.update_form_action = function() {
		var new_action = self.$settings_form.attr('action') + '?staging=1';

		self.$settings_form.attr('action', new_action);
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

new BoldGrid_Staging.StagingSettingsReading(jQuery);