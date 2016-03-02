var IMHWPB = IMHWPB || {};

IMHWPB.ScreenIdPage = function() {
	var self = this;

	jQuery(function() {
		self.add_development_group_to_status();
	});

	this.add_development_group_to_status = function() {
		var dev_group = jQuery(
				'input[name=development_group_post_status]:checked').val();

		if ('staging' == dev_group) {
			// Check the staging option in the select
			/** jQuery('#post_status').val('staging'); */

			// Display "Staging", as in "Status: Staging"
			jQuery('#post-status-display').html("Staging");
		}
	}

};

new IMHWPB.ScreenIdPage();