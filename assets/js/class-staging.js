var IMHWPB = IMHWPB || {};

IMHWPB.Staging = function(configs) {
	var self = this;

	jQuery(function() {
		// All Pages >> row actions >> copy to staging >> event handler
		jQuery('div.row-actions span.copy_to_post_status a').on('click',
				function() {
					self.copy_to_post_status(this);
				});

		// Set post-status-display to "Staging" if need be.
		self.update_post_status_display();

		// Add "Staging" to the drop down menu
		self.add_staging_to_status_select();
	});

	/**
	 * Copy a page to staging
	 * 
	 * Send the ajax request to WP so it will copy the page.
	 */
	this.copy_to_post_status = function(e) {
		var success_action = jQuery(e).data('success-action');

		var data = {
			'action' : 'copy_to_post_status',
			'post_id' : jQuery(e).data('post-id'),
		};

		jQuery.post(ajaxurl, data, function(response) {
			if (0 == response) {
				alert("Error: Please try again.");
			} else {
				if ('reload_current_page' == success_action) {
					location.reload();
				} else {
					// refersh the page so we can see the new post
					window.location = response;
				}
			}
		});
	}

	/**
	 * Set post-status-display to "Staging" if need be.
	 * 
	 * Status: STAGING Edit
	 */
	this.update_post_status_display = function() {
		var current_development_group_post_status = jQuery(
				'input[name=development_group_post_status]:checked').val();

		if ('staging' == current_development_group_post_status) {
			jQuery('span#post-status-display').html('Staging');
		}
	}

	/**
	 * Add "Staging" to the drop down menu
	 */
	this.add_staging_to_status_select = function() {
		jQuery('#post_status').append(jQuery('<option>', {
			value : 'staging',
			text : 'Staging'
		}));
	}
};

new IMHWPB.Staging();