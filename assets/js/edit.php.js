var IMHWPB = IMHWPB || {};

IMHWPB.ScreenIdEditPage = function(configs) {
	var self = this;

	jQuery(function() {
		jQuery('.copy_to_post_status a').on('click', function() {
			self.copy_to_post_status(this);
		});

		// Swtich wp_staging_view_version to match page status
		jQuery('div.row-actions span.view').on('click', function() {
			self.update_staging_view_version_before_going_to_page(this);
			return false;
		});

		self.append_permalink_to_titles();
	});

	/**
	 *
	 */
	this.append_permalink_to_titles = function() {
		/*
		 * The permalink we want is safely stored as a data attribute in the
		 * 'Copy to staging' row action.
		 */
		jQuery('table.wp-list-table tbody tr').each(
				function(index) {
					// get the permalink
					var permalink = jQuery(this).children('td.page-title')
							.children('div.row-actions').children(
									'span.copy_to_post_status').children('a')
							.data('permalink');

					// now append it to the title
					jQuery(this).children('td.page-title').children('strong')
							.append(
									' <span class="permalink">/' + permalink
											+ '</span>');
				});
	}

	/**
	 *
	 */
	this.copy_to_post_status = function(e) {
		var data = {
			'action' : 'copy_to_post_status',
			'post_id' : jQuery(e).data('post-id'),
			'copy_to' : jQuery(e).data('copy-to')
		};

		jQuery.post(ajaxurl, data, function(response) {
			if (0 == response) {
				alert("Error: Please try again.");
			} else {
				// refersh the page so we can see the new post
				location.reload();
			}
		});
	}

	/**
	 * When a user clicks 'view' under a page, we need to make sure their
	 * staging version matches the page status.
	 *
	 * For example, if they want to 'view' a staging page, we need to make sure
	 * the front end is staging, and vice versa.
	 */
	this.update_staging_view_version_before_going_to_page = function(view_link) {
		// Get the post id we're clicking 'view' on.
		var element = jQuery(view_link).get(0);
		var tr_id = jQuery(element).closest('tr').attr('id');
		var tr_id_split = tr_id.split('-');
		var post_id = tr_id_split[tr_id_split.length - 1];

		var data = {
			'action' : 'switch_to_staging',
			'request_from' : 'all_pages_row_view',
			'post_id' : post_id
		};

		jQuery.post(ajaxurl, data, function(response) {
			window.location.href = response;
		});
	};
};

new IMHWPB.ScreenIdEditPage();