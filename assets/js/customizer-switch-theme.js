var IMHWPB = IMHWPB || {};

IMHWPB.Staging_Customizer_Switch_Theme = function() {
	var self = this;

	self.text_save_and_stage = "Save & Stage";

	jQuery(function() {
		// In the left sidebar of the customizer, allow the user to change to
		// their staged theme.
		// CHANGE: Active theme
		// TO: Active theme / Staged theme
		self.add_staging_theme_next_to_active_theme();

		if ('1' == self.getParameterByName('staging')) {
			self.hide_unknown_settings_in_customizer();
		}

		self.update_customizer_verbiage();

		// Change "Save & Activate" button to "Save & Stage".
		jQuery('div#customize-theme-controls', top.document).on('input', function() {
			self.update_save_and_activate_button()
		});
	});

	/**
	 * In the left sidebar of the customizer, allow the user to change to their
	 * staged theme.
	 *
	 * CHANGE: Active theme
	 *
	 * TO: Active theme / Staged theme
	 */
	this.add_staging_theme_next_to_active_theme = function() {
		/**
		 * Configure some vars...
		 */
		var active_theme_span = jQuery(
				'li#accordion-section-themes h3.accordion-section-title span:first', top.document);
		var active_theme_html = jQuery(active_theme_span).html();
		var staging_in_url = self.getParameterByName('staging');
		var return_in_url = encodeURIComponent(self.getParameterByName('return'));

		/**
		 * Abort if...
		 */

		// if there's no active theme span when user is using wordpress preview functionality
		if ( ! active_theme_span.length ) {
			return;
		}

		// if we already have 'staged theme' in the span, then abort as this has
		// already been done.
		if (active_theme_html.indexOf('Staged theme') >= 0) {
			return;
		}

		// if we are previewing a theme, above. Otherwise it would say:
		// "Previewing theme / Staged theme
		if (active_theme_html.indexOf('Previewing theme') >= 0) {
			return;
		}

		// Modify the text.
		if ('1' == staging_in_url) {
			var staging_theme_html = "<strong>Staged theme</strong>";
			active_theme_html = "<a href='customize.php?return=" + return_in_url
					+ "'><em>Active theme</em></a>";
		} else {
			active_theme_html = '<strong>' + active_theme_html + "</strong>";
			var staging_theme_html = "<a href='customize.php?return=" + return_in_url
					+ "&staging=1'><em>Staged theme</em></a>";
		}

		jQuery(active_theme_span).html(active_theme_html + " / " + staging_theme_html);
	}

	/**
	 *
	 */
	this.getParameterByName = function(name) {
		name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
		var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"), results = regex
				.exec(location.search);
		return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
	}

	/**
	 *
	 */
	this.hide_unknown_settings_in_customizer = function() {
		//Temporarily allowing all panels until we lock down which panels we will use
		return;
		var allowed_sections = [ 'accordion-section-themes', 'accordion-section-title_tagline',
				'accordion-panel-logo_stylzr', 'accordion-section-colors',
				'accordion-section-social_media', 'accordion-section-header_image',
				'accordion-section-background_image', 'accordion-section-footer',
				'accordion-section-call_to_action', 'accordion-panel-site_title' ];

		var customize_ul_li = jQuery('div#customize-theme-controls ul:first', top.document)
				.children();

		jQuery(customize_ul_li).each(function(index) {
			var li_id = jQuery(this).attr('id');

			if (-1 == jQuery.inArray(li_id, allowed_sections)) {
				jQuery(this).empty();
			}
		});
	}

	/**
	 *
	 */
	this.update_customizer_verbiage = function() {
		if ('1' != self.getParameterByName('staging')) {
			return false;
		}

		// Change "Active theme" label to "Staged theme".
		// This is located at: Customize -> theme "Change"
		var span_active_theme = jQuery('h3.customize-section-title span:first', top.document);
		jQuery(span_active_theme).html('Staged theme');

		// Change "Save & Activate" button to "Save & Stage".
		// This is located at: Customize -> theme "Change" -> Preview.
		// self.update_save_and_activate_button();
	}

	/**
	 * Change "Save & Activate" to "Save & Stage".
	 *
	 * This is the save button of the customizer.
	 */
	this.update_save_and_activate_button = function() {
		if ('1' != self.getParameterByName('staging')) {
			return false;
		}

		var button_save_and_activate = jQuery('div.primary-actions input#save', top.document);

		// If the button's value is not already "Save & Stage"...
		if (jQuery(button_save_and_activate).val() != self.text_save_and_stage) {
			// WordPress is changing the value of this submit button with js.
			// Be polite, let WordPress make the change first,
			// then we'll make it a split second later.
			setTimeout(function() {
				// Change "Save & Activate" button to "Save & Stage"

				jQuery(button_save_and_activate).attr('value', self.text_save_and_stage);
			}, 200);
		}
	}
};

new IMHWPB.Staging_Customizer_Switch_Theme();
