var IMHWPB = IMHWPB || {};

IMHWPB.StagingThemes = function() {
	var self = this;

	this.active_theme_name = '';
	this.staged_theme_name = '';

	jQuery(function() {
		self.set_active_theme_name();
		self.set_staged_theme_name();
	});

	/**
	 *
	 */
	this.add_stage_button_to_each_theme = function() {
		// loop through each theme
		jQuery('div.themes div.theme')
				.each(
						function(index) {
							/**
							 * Get the stylesheet name by scanning the
							 * "Activate" url.
							 */
							// get the url to activate this theme
							var activate_url = String(jQuery(this).children(
									'div.theme-actions').children('a.activate')
									.attr('href'));

							// explode that url by '&'
							var params = activate_url.split('&');

							// create an empty variable to store the stylesheet
							// name once we parse it out
							var stylesheet = '';

							// loop through each params
							jQuery(params).each(function(key, value) {
								// if the first X characters are 'stylesheet='
								if ('stylesheet=' == value.substring(0, 11)) {
									// then grab the value of stylesheet
									stylesheet = value.substring(11);
								}
							});

							/**
							 * Create the "Stage" button and add it to the
							 * theme.
							 */
							var stage_button = "<a class='button button-secondary stage' data-stylesheet='"
									+ stylesheet + "'>Stage</a>";

							// Only add the "Stage" button IF the current theme
							// we're looping through is not already staged.
							if (self.staged_theme_name != stylesheet) {
								jQuery(this).children('div.theme-actions')
										.children('a.activate').after(
												stage_button);
							}
						});

		/**
		 * Add "Stage" button to active theme.
		 *
		 * ONLY add a "Stage" button to the active theme IF the active theme is
		 * not also the staged theme.
		 */
		if (self.staged_theme_name != self.active_theme_name) {
			var stage_button = "<a class='button button-secondary stage' data-stylesheet='"
					+ self.active_theme_name + "'>Stage</a>";

			jQuery('div.theme.active a.customize').before(stage_button);
		}
	}

	/**
	 *
	 */
	this.highlight_staged_stylesheet = function() {

		staging_theme_div = jQuery('div[aria-describedby*="'
				+ self.staged_theme_name + '"]');

		var button_unstage_theme = "<a class='button button-secondary unstage'>Unstage</a>";

		// if our active theme is also our staging theme
		if (self.active_theme_name == self.staged_theme_name) {
			jQuery('div.theme.active .theme-name span:first').html(
					'Active & Staged:');

			// Add "Unstage" button before "Customize" button.
			var button_customize = jQuery(staging_theme_div).children(
					'div.theme-actions').children('a.customize');
			jQuery(button_unstage_theme).insertBefore(jQuery(button_customize));
		} else {
			// move after active
			jQuery(staging_theme_div).insertAfter('div.themes div.active');

			// add 'Staged:' before the theme name
			jQuery(staging_theme_div).children('.theme-name').prepend(
					'<span>Staged:</span> ');

			// add the active class
			jQuery(staging_theme_div).addClass('active');

			// Add "Unstage" button after "Activate" button.
			var button_activate = jQuery(staging_theme_div).children(
					'div.theme-actions').children('a.activate');
			jQuery(button_unstage_theme).insertAfter(jQuery(button_activate));
		}

	}

	/**
	 *
	 */
	this.set_active_theme_name = function() {
		var h2_id = jQuery('div.theme.active .theme-name')[0].id;

		self.active_theme_name = h2_id.substring(0, h2_id.length - 5);
	}

	/**
	 *
	 */
	this.set_staged_theme_name = function() {
		var data = {
			'action' : 'get_staging_stylesheet',
		};

		jQuery.post(ajaxurl, data, function(stylesheet) {
			self.staged_theme_name = stylesheet;

			/**
			 * now that we have a staged_theme_name, let's do a few things...
			 */
			self.highlight_staged_stylesheet();

			self.add_stage_button_to_each_theme();

			jQuery('a.button.button-secondary.stage').on('click', function() {
				self.set_staged_theme(this);
			});

			jQuery('a.button.button-secondary.unstage').on('click', function() {
				self.unstage_theme();
			});
		});
	}

	/**
	 *
	 */
	this.set_staged_theme = function(theme_div) {
		var stylesheet = jQuery(theme_div).data('stylesheet');

		var data = {
			'action' : 'set_staged_theme',
			'stylesheet' : stylesheet
		};

		jQuery
				.post(
						ajaxurl + '?staging=1',
						data,
						function(response) {
							if ('success' == response) {
								window.location.href = window.location;
							} else {
								alert("There was an error when trying to stage this theme. Please try again.");
							}
						});
	};

	/**
	 *
	 */
	this.unstage_theme = function() {
		var data = {
			'action' : 'unstage_theme',
		};

		jQuery
				.post(
						ajaxurl,
						data,
						function(response) {
							if ('success' == response) {
								window.location.href = window.location;
							} else {
								alert("There was an error unstaging this theme. Please try again.");
							}
						});
	};
};

new IMHWPB.StagingThemes();