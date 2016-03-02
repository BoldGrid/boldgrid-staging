var IMHWPB = IMHWPB || {};

IMHWPB.Staging_Switcher = function() {
	var self = this;

	jQuery(function() {
		// switch to staging
		jQuery('li.wp_staging_switch_version a').on('click', function() {
			self.switch_to_staging();
			return false;
		});
	});

	this.switch_to_staging = function() {
		var data = {
			'action' : 'switch_to_staging'
		};

		jQuery.post(ajaxurl, data, function(response) {
			window.location = response;
		});
	}
};

new IMHWPB.Staging_Switcher();