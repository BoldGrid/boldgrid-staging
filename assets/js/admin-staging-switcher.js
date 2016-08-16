var IMHWPB = IMHWPB || {};

IMHWPB.Admin_Staging_Switcher = function() {
	var self = this;

	jQuery(function() {
		// Move "Visit staging site" under "Visit active site"
		self.move_visit_staging_site();

		// DEPRECATED
		// // View staging site
		// jQuery('li.admin_view_staging_site a').on('click', function() {
		// self.view_site_version('staging');
		// return false;
		// });

		// DEPRECATED
		// // View production site
		// jQuery('li.admin_view_production_site a').on('click', function() {
		// self.view_site_version('production');
		// return false;
		// });
	});

	/**
	 *
	 */
	this.move_visit_staging_site = function() {
		var visit_staging = jQuery('li.admin_view_staging_site');
		var visit_active = jQuery('li.admin_view_production_site');

		jQuery(visit_active).after(jQuery(visit_staging));
	}

	// DEPRECATED
	// /**
	// *
	// */
	// this.view_site_version = function(version) {
	// var data = {
	// 'action' : 'switch_to_staging',
	// 'version' : version,
	// 'request_from' : 'dashboard_admin_bar'
	// };
	//
	// jQuery.post(ajaxurl, data, function(response) {
	// window.location = response;
	// });
	// }
};

new IMHWPB.Admin_Staging_Switcher();