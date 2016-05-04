<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Staging_Deployment
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrid.com>
 */

// Prevent direct calls
if ( ! defined( 'WPINC' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * BoldGrid Staging Deployment class
 */
class Boldgrid_Staging_Deployment {
	/**
	 * Menu properties to check
	 *
	 * @var array
	 */
	public $menu_properties_to_check = array (
		'name',
		'slug'
	);

	// Keep track of the menus that we've already updated / renamed.
	public $nav_menus_already_updated = array ();

	/**
	 * Keep track of the mensu we renamed that ended in '-tmp', those will need to be removed in the
	 * end.
	 *
	 * @var array
	 */
	public $nav_menus_needing_replacement_of_tmp = array ();

	/**
	 * Renamed template string
	 *
	 * @var string
	 */
	public $template_renamed = '<li>Renaming <strong>menu %s</strong> from <em>%s</em> to <em>%s</em>.</li>';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->dir_pages = BOLDGRID_STAGING_PATH;

		$this->all_staged_pages = get_posts(
			array (
				'post_type' => 'page',
				'post_status' => array (
					'staging'
				),
				'posts_per_page' => - 1,
				'orderby' => 'title',
				'order' => 'ASC'
			) );

		// Buggy. For some reason, we have 'publish' pages in the list. Take them out:
		foreach ( $this->all_staged_pages as $k => $page ) {
			if ( 'staging' != $page->post_status ) {
				unset( $this->all_staged_pages[$k] );
			}
		}

		$this->all_published_pages = get_posts(
			array (
				'post_type' => 'page',
				'post_status' => 'publish',
				'orderby' => 'title',
				'order' => 'ASC',
				'posts_per_page' => '-1'
			) );
	}

	/**
	 * Add hooks
	 */
	public function add_hooks() {
	}

	/**
	 * Delete the attribution page.
	 *
	 * The attribution page is found based on the ['page']['id'] stored in
	 * wp_options for boldgrid_attribution.
	 */
	public function delete_attribution_page() {
		// Get the option boldgrid_attribution:
		$active_attribution_details = get_option( 'boldgrid_attribution' );

		// Set the attribution_page_id to ['page']['id']:
		$active_atttribution_page_id = ( false != $active_attribution_details &&
			 isset( $active_attribution_details['page']['id'] ) ) ? $active_attribution_details['page']['id'] : false;

		// Delete the attribution_page:
		if ( is_numeric( $active_atttribution_page_id ) ) {
			$this->deploy_logger( 'Deleting <em>attribution</em> page.<br />' );
			wp_delete_post( $active_atttribution_page_id, true );
		}
	}

	/**
	 * Update feedback info.
	 *
	 * To be used after promoting a staged site to an active site.
	 *
	 * @since 1.0.6
	 */
	protected function update_feedback_info() {
		// Get BoldGrid settings:
		$options = get_option( 'boldgrid_settings' );

		// Get feedback option:
		$boldgrid_feedback_optout = isset( $options['boldgrid_feedback_optout'] ) ? $options['boldgrid_feedback_optout'] : '0';

		// If allowed, then update the feedback info:
		if ( ! $boldgrid_feedback_optout ) {
			// Get the current feedback data:
			$feedback_data = get_option( 'boldgrid_feedback' );

			// Get the build profile id for the promoted site:
			$boldgrid_install_options = get_option( 'boldgrid_install_options' );
			$build_profile_id = isset( $boldgrid_install_options['build_profile_id'] ) ? $boldgrid_install_options['build_profile_id'] : null;

			// Insert new data:
			$feedback_data[] = array (
				'type' => 'build_profile_feedback',
				'timestamp' => date( 'Y-m-d H:i:s' ),
				'build_profile_id' => $build_profile_id,
				'action' => 'promoted'
			);

			// Save data:
			update_option( 'boldgrid_feedback', $feedback_data );
		}
	}

	/**
	 * Publish the staging site.
	 *
	 * This method does all the magic for publishing the staging, and moving
	 * active to staging.
	 */
	public function deploy() {
		include BOLDGRID_STAGING_PATH . '/pages/deployment/deployment.php';

		$this->deploy_switch_theme();
		$this->switch_pages_from_publish_to_staging();
		$this->deploy_rename_pages_and_posts();
		$this->switch_options();

		include BOLDGRID_STAGING_PATH . '/pages/deployment/deployment-complete.php';

		// Update BoldGrid feedback info:
		$this->update_feedback_info();
	}

	/**
	 * Print to a log during deployment.
	 *
	 * While deploy() is running, it uses this method to print a log to the
	 * screen on a per message basis.
	 */
	public function deploy_logger( $message ) {
		?>
<script type="text/javascript">
	$installation_log.find(".plugin-card-top").append("<?php echo $message; ?>");
	update_deploy_log_line_count();
</script>
<?php
		ob_flush();
		flush();
	}

	/**
	 * Display the "Boldgrid Staging" page.
	 *
	 * IF we're converting staging to production, run necessary methods
	 * ELSE display info page giving user option to choose how to redirect pages
	 */
	public function display_staging_menu_page() {
		if ( isset( $_POST['action'] ) && 'launch_staging' == $_POST['action'] ) {
			if ( ! isset( $_POST['_wpnonce'] ) ||
				 ! wp_verify_nonce( $_POST['_wpnonce'], 'launch_staging' ) ) {
				// nonce not verified; print an error message and return false:
				?>
<div class="error">
	<p>Error processing request to launch staging website; WordPress
		security violation! Please try again.</p>
</div>
<?php
				include $this->dir_pages . '/pages/staging-deployment.php';
			} else {
				$this->deploy();
			}
		} else {
			// If we don't have any staged pages, display appropriate message.
			if ( empty( $this->all_staged_pages ) ) {
				include $this->dir_pages . '/pages/staging-deployment-no-staged-pages.php';
			} elseif ( empty( $this->all_published_pages ) ) {
				include $this->dir_pages . '/pages/staging-deployment-no-active-pages.php';
			} else {
				include $this->dir_pages . '/pages/staging-deployment.php';
			}
		}
	}

	/**
	 * Rename menus so that the new staging menu ends in '-staging'.
	 *
	 * This process is done in the following order:
	 * # Rename 'primary' to 'primary-staging-tmp'.
	 * # Rename 'primary-staging' to 'primary'.
	 * # Rename 'primary-staging-tmp' to 'primary-staging'.
	 *
	 * The reason we rename 'primary' to 'primary-staging-tmp' is because we
	 * can't rename it immediately to 'primary-staging' because that already
	 * exists (it would give us an error).
	 */
	public function deploy_helper_rename_menus( $staging_theme_mods, $production_theme_mods ) {
		// DO NOT CHANGE THE ORDER OF THIS ARRAY, it is important active is
		// first, then staging.
		$menus_to_rename = array (
			array (
				'theme_mod' => $production_theme_mods,
				'staging' => false
			),
			array (
				'theme_mod' => $staging_theme_mods,
				'staging' => true
			)
		);

		/**
		 * ********************************************************************
		 * Rename 'primary' to 'primary-staging-tmp'.
		 * Rename 'primary-staging' to 'primary'.
		 * ********************************************************************
		 */
		foreach ( $menus_to_rename as $menu_to_rename_key => $menu_to_rename_value ) {
			// For readability, SET $nav_menu_locations TO
			// $menu_to_rename_value['theme_mod']['nav_menu_locations']
			$nav_menu_locations = isset( $menu_to_rename_value['theme_mod']['nav_menu_locations'] ) ? $menu_to_rename_value['theme_mod']['nav_menu_locations'] : false;

			// Abort if necessary.
			if ( false === $nav_menu_locations ) {
				continue;
			}

			// Rename the menus.
			foreach ( $nav_menu_locations as $menu_location => $menu_id ) {
				switch ( $menu_to_rename_value['staging'] ) {
					case true :
						$this->deploy_helper_rename_menus_staging( $menu_id );
						break;

					case false :
						$this->deploy_helper_rename_menus_active( $menu_id );
						break;
				}
			}
		}

		/**
		 * ********************************************************************
		 * Rename 'primary-staging-tmp' to 'primary-staging'.
		 * ********************************************************************
		 */
		if ( ! empty( $this->nav_menus_needing_replacement_of_tmp ) ) {
			foreach ( $this->nav_menus_needing_replacement_of_tmp as $menu_id ) {
				$this->deploy_helper_rename_menus_tmp( $menu_id );
			}
		}
	}

	/**
	 * Rename 'primary' to 'primary-staging-tmp'.
	 * Later in the process, we'll remove the '-tmp'.
	 */
	public function deploy_helper_rename_menus_active( $menu_id ) {
		// Get the menu object based on the menu_id.
		// Make sure it is a valid menu, and that we have not already processed
		// it.
		$menu = $this->deploy_helper_rename_menus_exists_and_not_updated( $menu_id );

		// Abort if necessary.
		if ( false == $menu ) {
			return;
		}

		foreach ( $this->menu_properties_to_check as $property ) {
			// Check the $property name.
			if ( '-staging-tmp' != substr( $menu->$property, - 12 ) ) {
				// Calculate the new $property name.
				$new_property_name = $menu->$property . '-staging-tmp';

				// Print a log to the screen.
				$this->deploy_logger(
					sprintf( $this->template_renamed, $property, $menu->$property,
						$new_property_name ) );

				// Rename and save.
				$update_status = wp_update_term( $menu->term_id, 'nav_menu',
					array (
						$property => $new_property_name
					) );
				if ( is_wp_error( $update_status ) ) {
					$error_string = $update_status->get_error_message();
					$this->deploy_logger(
						'<div id="message" class="error"><p>' . $error_string . '</p></div>' );
				}

				// Keep track of this menu_id so we don't try to rename it
				// again.
				$this->nav_menus_already_updated[$menu_id] = true;
				$this->nav_menus_needing_replacement_of_tmp[$menu_id] = $menu_id;
			}
		}
	}

	/**
	 * Get and validate a menu.
	 *
	 * Make sure that it is a valid menu object and that we have not yet
	 * processed is.
	 */
	public function deploy_helper_rename_menus_exists_and_not_updated( $menu_id ) {
		// If we've already updated this menu, then abort and continue looping.
		if ( isset( $this->nav_menus_already_updated[$menu_id] ) ) {
			false;
		}

		// Get the menu.
		$menu = wp_get_nav_menu_object( $menu_id );

		// If the $menu is not an object, abort and keep looping.
		if ( ! is_object( $menu ) ) {
			return false;
		}

		return $menu;
	}

	/**
	 * Check to see if the menu name / slug ends in '-staging'.
	 * If it does'nt, remove '-staging' from the end and save.
	 */
	public function deploy_helper_rename_menus_staging( $menu_id ) {
		// Get the menu object based on the menu_id.
		// Make sure it is a valid menu, and that we have not already processed
		// it.
		$menu = $this->deploy_helper_rename_menus_exists_and_not_updated( $menu_id );

		// Abort if necessary.
		if ( false == $menu ) {
			return;
		}

		foreach ( $this->menu_properties_to_check as $property ) {
			// Check the $property name.
			if ( '-staging' == substr( $menu->$property, - 8 ) ) {
				// Calculate the new $property name.
				$new_property_name = substr( $menu->$property, 0, - 8 );

				// Print a log to the screen.
				$this->deploy_logger(
					sprintf( $this->template_renamed, $property, $menu->$property,
						$new_property_name ) );

				// Rename and save.
				$update_status = wp_update_term( $menu->term_id, 'nav_menu',
					array (
						$property => $new_property_name
					) );
				if ( is_wp_error( $update_status ) ) {
					$error_string = $update_status->get_error_message();
					$this->deploy_logger(
						'<div id="message" class="error"><p>' . $error_string . '</p></div>' );
				}

				// Keep track of this menu_id so we don't try to rename it
				// again.
				$this->nav_menus_already_updated[$menu_id] = true;
			}
		}
	}

	/**
	 * Rename 'primary-staging-tmp' to 'primary-staging'.
	 */
	public function deploy_helper_rename_menus_tmp( $menu_id ) {
		// Get the menu object.
		$menu = wp_get_nav_menu_object( $menu_id );

		// If it's not an object, abort.
		if ( ! is_object( $menu ) ) {
			return;
		}

		foreach ( $this->menu_properties_to_check as $property ) {
			// Check the $property name.
			if ( '-staging-tmp' == substr( $menu->$property, - 12 ) ) {
				// Calculate the new $property name.
				$new_property_name = substr( $menu->$property, 0, - 4 );

				// Print a log to the screen.
				$this->deploy_logger(
					sprintf( $this->template_renamed, $property, $menu->$property,
						$new_property_name ) );

				// Rename and save.
				$update_status = wp_update_term( $menu->term_id, 'nav_menu',
					array (
						$property => $new_property_name
					) );
				if ( is_wp_error( $update_status ) ) {
					$error_string = $update_status->get_error_message();
					$this->deploy_logger(
						'<div id="message" class="error"><p>' . $error_string . '</p></div>' );
				}
			}
		}
	}

	/**
	 * Handle the renaming / redirecting of the old production pages to staging.
	 *
	 * At this point, we have already moved 'staging' to 'active', and vice
	 * versa.
	 */
	public function deploy_rename_pages_and_posts() {
		if ( isset( $_POST['scenario'] ) ) {
			switch ( $_POST['scenario'] ) {
				/**
				 * ************************************************************
				 * We have staged pages, but no active pages.
				 *
				 * As the staged pages have already been set to active, grab all
				 * the active pages. Loop through and remove '-staging' from the
				 * urls.
				 * ************************************************************
				 */
				case 'yes-staged-pages-no-active-pages' :

					/**
					 * At this point, we have a staging and active attribution
					 * page.
					 * This is contrarty to the case,
					 * 'yes-staged-pages-no-active-pages', an active attribution
					 * page must have been created somewhere by mistake. To
					 * avoid creating an 'attribution-2' page because of a
					 * naming convention issue, let's just trash the
					 * 'attribution' page.
					 */
					$this->delete_attribution_page();

					$active_pages = get_pages(
						array (
							'post_type' => 'page',
							'post_status' => 'publish'
						) );

					// if we have pages...
					if ( false != $active_pages ) {
						// loop through each page
						foreach ( $active_pages as $key => $page ) {
							// If the url ends in '-staging', remove it.
							$page = get_post( $page->ID );

							if ( '-staging' == substr( $page->post_name, - 8 ) ) {
								// Calculate the new post name.
								$new_post_name = substr( $page->post_name, 0, - 8 );

								// Print a log to the screen.
								$template = 'Renaming URL for <strong>%s</strong> from <em>%s</em> to <em>%s</em>.<br />';

								$this->deploy_logger(
									sprintf( $template, $page->post_title, $page->post_name,
										$new_post_name ) );

								// Rename and save.
								$page->post_name = $new_post_name;

								wp_update_post( $page );
							}
						}
					}
					break;
			}

			return;
		}

		$wp_option_boldgrid_staging_redirects = array ();

		/**
		 * At this point, we may have $_POST similar to: Array
		 * (
		 * [replace_select] => Array
		 * (
		 * [22275] => 22226
		 * [22271] => 22226
		 * [22281] => 22226
		 * [22270] => 22226
		 * [22277] => 22226
		 * [22279] => 22226
		 * )
		 *
		 * [replace_option] => Array
		 * (
		 * [22275] => redirect
		 * [22271] => redirect
		 * [22281] => redirect
		 * [22270] => replace
		 * [22277] => redirect
		 * [22279] => redirect
		 * )
		 *
		 * [redirect_select] => Array
		 * (
		 * [22275] => 22222
		 * [22271] => 22222
		 * [22281] => 22222
		 * [22270] => 22222
		 * [22277] => 22222
		 * [22279] => 22222
		 * )
		 *
		 * [action] => launch_staging
		 * [_wpnonce] => ********
		 * [_wp_http_referer] => ********
		 * [submit] => Launch Staging!
		 * )
		 */

		if ( isset( $_POST['replace_option'] ) ) {
			foreach ( $_POST['replace_option'] as $page_id => $replace_option ) {
				switch ( $replace_option ) {
					/**
					 * ************************************************************
					 * Option 1: replace the page
					 * ie rename contact-us-staging to contact-us, and vice
					 * versa
					 * ************************************************************
					 */
					case 'replace' :
						$production_page = get_post( $page_id );
						$staged_page = get_post( $_POST['replace_select'][$page_id] );

						$new_production_post_name = $staged_page->post_name;
						$new_staged_post_name = $production_page->post_name;

						/**
						 * First, save the production post_name to something else so there are no
						 * conflicts when changing the staged post_name.
						 */
						$production_page->post_name = microtime( true );
						wp_update_post( $production_page );

						$staged_page->post_name = $new_staged_post_name;
						wp_update_post( $staged_page );

						$production_page->post_name = $new_production_post_name;
						wp_update_post( $production_page );

						break;

					/**
					 * ************************************************************
					 * Option 2: setup a redirect
					 * ************************************************************
					 */
					case 'redirect' :
						$production_page = get_post( $page_id );

						$wp_option_boldgrid_staging_redirects[$production_page->post_name] = intval(
							$_POST['redirect_select'][$page_id] );
						break;
				}
			}
		}

		/**
		 * Because of the logic above, not all staged pages may have been renamed to have -staging
		 * removed from their url.
		 *
		 * So, tie up any loose ends and rename all staged pages (if they end in -staging).
		 */
		$this->rename_all_staged_pages();

		/**
		 * Update wp_options boldgrid_staging_redirects
		 */
		update_option( 'boldgrid_staging_boldgrid_redirects',
			$wp_option_boldgrid_staging_redirects );
	}

	/**
	 * Remove '-staging' from active page URLs.
	 *
	 * When a staging site is deploy, staged pages have their URL's updated and -staging removed.
	 * This method applies this URL fix for those staged pages NOT already renamed within the
	 * deploy_rename_pages_and_posts method.
	 *
	 * @since 1.0
	 *
	 * @return null This method does not return anything.
	 */
	public function rename_all_staged_pages() {
		// Grab all of our staged pages.
		// At this point, all staged pages have already been set to active.
		// So, to 'rename staged pages' we actually need to grab all active pages.
		$staged_pages = get_pages(
			array (
				'post_type' => 'page',
				'post_status' => 'publish'
			) );

		// If we don't have any pages, abort.
		if ( false == $staged_pages ) {
			return;
		}

		// Loop through each staged page.
		// Modify the URL if it matches one of the following:
		// 1: The URL ends with -staging
		// 2: The URL ends with -staging-(a number)
		foreach ( $staged_pages as $key => $page ) {
			$page = get_post( $page->ID );

			$new_post_name = null;

			// Determine if the URL matches either of the two scenarios above.
			$url_ends_with_dash_staging = ( '-staging' == substr( $page->post_name, - 8 ) );
			$dash_staging_dash_number_count = preg_match( "/.*?-staging-(\d+)$/", $page->post_name );

			// If either of the above 2 scenarios match, create the new URL (post_name).
			if ( $url_ends_with_dash_staging ) {
				$new_post_name = substr( $page->post_name, 0, - 8 );
			} elseif ( 1 == $dash_staging_dash_number_count ) {
				$new_post_name = str_replace( '-staging-', '-', $page->post_name );
			} else {
				// This page does not need to be renamed, so continue.
				continue;
			}

			// Check to see if a page already exists with this slug.
			$args = array (
				'name' => $new_post_name,
				'post_type' => 'page'
			);
			$existing_pages = get_posts( $args );

			// If we don't have any existing pages with this slug, rename and save.
			if ( ! $existing_pages ) {
				$page->post_name = $new_post_name;
				wp_update_post( $page );
			}
		}
	}

	/**
	 * Create the html form / table that will allow users to determine how to
	 * redirect pages after staging deployment.
	 *
	 * @return string
	 */
	public function renaming_pages_and_posts() {
		// $return will be the <table> containing all the info created by this method.
		$return = '';

		// By default, we want pages that will redirect to redirect to the homepage. To
		// automatically select the "Home" page, we need to get the staging page_on_front option.
		$staging_page_on_front = get_option( 'boldgrid_staging_page_on_front' );

		// Abort if necessary.
		if ( ! is_array( $this->all_published_pages ) ) {
			return $return;
		}

		// Setup the heading / table:
		$return .= "<br />
				<table class='wp-list-table widefat striped boldgrid-plugin-card-two-thirds'>
					<thead>
						<tr>
							<th>Currently Published</th>
							<th>Staged</th>
						</tr>
					</thead>
					<tbody>
			";

		// Loop through all published pages and create a <tr> for it.
		foreach ( $this->all_published_pages as $page ) {
			// If is the attribution page, then skip it:
			if ( 'attribution' == $page->post_name ) {
				continue;
			}

			// Create <select></select>:
			$selected_found = 0;

			/**
			 * Begin by creating the <select> elements.
			 *
			 * Both the "Replace with" and "Redirect to" will have their own selects.
			 */
			$replace_select = "<select name='replace_select[" . $page->ID . "]'>";

			$redirect_select = "<select name='redirect_select[" . $page->ID . "]'>";

			/**
			 * Loop through all staged pages.
			 *
			 * During this loop, create the <select> elements that we need.
			 */
			foreach ( $this->all_staged_pages as $staged_page ) {
				/**
				 * For the "Replace with" <select>, should this staged page be selected?
				 *
				 * If the active page is 'about-us' and and staging page is 'about-us-staging', then
				 * preselect this option.
				 *
				 * Also keep track of the number of matches we've found. If we've found a match,
				 * then the "Replace with" option will be preselected.
				 */
				if ( $page->post_name == str_replace( '-staging', '', $staged_page->post_name ) ) {
					$selected = 'selected';
					$selected_found ++;
				} else {
					$selected = '';
				}

				/**
				 * Create the $staged_page_select <option>.
				 *
				 * This is the option the user selects if they want to "Replace with".
				 */
				$replace_select .= "<option value='" . $staged_page->ID . "' $selected>" .
					 $staged_page->post_title . " ( /" . $staged_page->post_name . ")</option>";

				/**
				 * Create the $redirect_select <option>.
				 *
				 * This is the option the user selects if they want to "Redirect to".
				 *
				 * If this page is the homepage, set it to selected by default.
				 */
				$selected_redirect = ( $staged_page->ID == $staging_page_on_front ) ? 'selected' : '';

				$redirect_select .= "<option value='" . $staged_page->ID . "' $selected_redirect>" .
					 $staged_page->post_title . " ( /" . $staged_page->post_name . ")</option>";
			}

			// End the </select>:
			$replace_select .= '</select>';
			$redirect_select .= '</select>';

			/**
			 * Which radio button should be selected by default for the user?
			 *
			 * "Replace with" = $replace_with_checked.
			 *
			 * "Redirect to" = $redirect_checked.
			 */
			if ( $selected_found > 0 ) {
				$replace_with_checked = 'checked="checked"';
				$redirect_checked = '';
			} else {
				$replace_with_checked = '';
				$redirect_checked = 'checked="checked"';
			}

			// Print the <tr></tr>:
			/* @formatter:off */
				$return .= "
					<tr>
						<td>
							<strong>" . $page->post_title . "</strong><br />
							<em>/" . $page->post_name . "</em>
						</td>
						<td>
								<input type='radio' name='replace_option[" . $page->ID . "]' id='replace_option[" . $page->ID . "]' value='replace' $replace_with_checked> <span style='width:100px; display:inline-block;'>Replace with</span> " . $replace_select . "<br />
								<input type='radio' name='replace_option[" . $page->ID . "]' id='replace_option[" . $page->ID . "]' value='redirect' $redirect_checked> <span style='width:100px; display:inline-block;'>Redirect to</span> " . $redirect_select . "<br />
								<input type='radio' name='replace_option[" . $page->ID . "]' id='replace_option[" . $page->ID . "]' value='none'> Return a 404 error
						</td>
					</tr>
				";
				/* @formatter:on */
		}

		// Finish the table:
		$return .= '
					</tbody>
				</table>
			';

		return $return;
	}

	/**
	 * Switch option
	 *
	 * @param array $option
	 *
	 * @return boolean
	 */
	public function switch_option( $option ) {
		/**
		 * ********************************************************************
		 * Configure our vars
		 * ********************************************************************
		 */

		/**
		 * If $option is an array
		 *
		 * $option['option_name'] = 'stylesheet';
		 * $option['production'] = get_option( 'stylesheet' );
		 * $option['staging'] = get_option( 'boldgrid_staging_stylesheet' );
		 */
		if ( is_array( $option ) ) {
			if ( ! isset( $option['option_name'] ) || ! isset( $option['production'] ) ||
				 ! isset( $option['staging'] ) ) {
				return false;
			}
		}/**
		 * Else if $option is a string
		 *
		 * $option = 'blogname';
		 */
		elseif ( is_string( $option ) ) {
			$tmp_option['option_name'] = $option;
			$tmp_option['production'] = get_option( $option );
			$tmp_option['staging'] = get_option( 'boldgrid_staging_' . $option );

			$option = $tmp_option;
		}

		/**
		 * ********************************************************************
		 * Switch the options
		 * ********************************************************************
		 */
		// only switch if there is a staging version
		if ( false !== $option['staging'] ) {

			// Set the staging as production
			update_option( $option['option_name'], $option['staging'] );

			// Set the production as staging
			update_option( 'boldgrid_staging_' . $option['option_name'], $option['production'] );
		}
	}

	/**
	 * Switch options
	 */
	public function switch_options() {
		$boldgrid_staging_option = new Boldgrid_Staging_Option();

		// An array of options that need to be switched between Active / Staging.
		$switch_options = array (
			// WordPress Options
			'blogname',
			'blogdescription',
			'sidebars_widgets',
			// Plugin BoldGrid Options
			'boldgrid_asset',
			'boldgrid_attribution',
			'boldgrid_has_built_site',
			'boldgrid_install_options',
			'boldgrid_installed_page_ids',
			'boldgrid_installed_pages_metadata',
			'boldgrid_widgets_created'
		);

		// In addition to the options above, there may be other options that need to be switched.
		// Those other options are stored in $boldgrid_staging_option->options_to_stage.
		$switch_options = array_merge( $switch_options, $boldgrid_staging_option->options_to_stage );

		$this->deploy_logger( '<p>Switching various options...</p>' );

		foreach ( $switch_options as $option ) {
			$this->deploy_logger( '<li>' . $option . '</li>' );
			$this->switch_option( $option );
		}
	}

	/**
	 * Switch pages from Publish to Staging:
	 */
	public function switch_pages_from_publish_to_staging() {
		/*
		 * When a menu has the following setting, 'Automatically add new top-level pages to this
		 * menu', switching a page from staging to active will cause the newly active page to be
		 * added to the menu. We don't want to mess with any menus, we just want to switch page
		 * stati. As this is an undesired effect, we will disable the call to
		 * _wp_auto_add_pages_to_menu now, and enable the call again when we're done.
		 */
		remove_action( 'transition_post_status', '_wp_auto_add_pages_to_menu', 10, 3 );

		$this->deploy_logger( '<p>Switching pages...</p>' );

		// Grab all 'publish' and 'staging' pages:
		$all_publish_and_staging_pages = get_pages(
			array (
				'post_type' => 'page',
				'post_status' => 'publish,staging'
			) );

		// If we have pages...
		if ( is_array( $all_publish_and_staging_pages ) &&
			 count( $all_publish_and_staging_pages ) > 0 ) {
			// Loop through each page:
			foreach ( $all_publish_and_staging_pages as $key => $page ) {
				// change the post_status
				$new_post_status = 'staging' != $page->post_status ? 'staging' : 'publish';
				$page->post_status = $new_post_status;

				// save the page
				wp_update_post( $page );
			}
		}

		// We removed this action above. Now we're adding it back.
		add_action( 'transition_post_status', '_wp_auto_add_pages_to_menu', 10, 3 );
	}

	/**
	 * Deploy switch theme
	 */
	public function deploy_switch_theme() {
		$this->deploy_logger( '<li>Switching theme...</li>' );
		/**
		 * ********************************
		 * Switch theme_mods_stylesheet *
		 * IMPORTANT THAT THIS GOES FIRST *
		 * ********************************
		 */

		// Setup some staging vars:
		$stylesheet_staging = get_option( 'boldgrid_staging_stylesheet' );
		$staging_theme_mods_option_name = 'boldgrid_staging_theme_mods_' . $stylesheet_staging;
		$staging_theme_mods_option_value = get_option( $staging_theme_mods_option_name );
		$staging_theme_mods_option_value['launched_staging'] = true;

		// Setup some production vars:
		$stylesheet_production = get_option( 'stylesheet' );
		$production_theme_mods_option_name = 'theme_mods_' . $stylesheet_production;
		$production_theme_mods_option_value = get_option( $production_theme_mods_option_name );
		$production_theme_mods_option_value['launched_staging'] = true;

		// Set the staging as production:
		update_option( 'theme_mods_' . $stylesheet_staging, $staging_theme_mods_option_value );

		// Set the production as staging:
		update_option( 'boldgrid_staging_theme_mods_' . $stylesheet_production,
			$production_theme_mods_option_value );

		// Switch stylesheet:
		$option['option_name'] = 'stylesheet';
		$option['production'] = get_option( 'stylesheet' );
		$option['staging'] = get_option( 'boldgrid_staging_stylesheet' );
		$this->switch_option( $option );

		// Switch template:
		$option['option_name'] = 'template';
		$option['production'] = get_option( 'template' );
		$option['staging'] = get_option( 'boldgrid_staging_template' );
		$this->switch_option( $option );

		// Rename menu from primary to primary-staging, and vice versa:
		$this->deploy_helper_rename_menus( $staging_theme_mods_option_value,
			$production_theme_mods_option_value );
	}
}
