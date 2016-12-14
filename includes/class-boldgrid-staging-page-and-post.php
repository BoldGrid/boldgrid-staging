<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Staging_Page_And_Post_Staging
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
 * BoldGrid Page and Post Staging
 */
class Boldgrid_Staging_Page_And_Post_Staging extends Boldgrid_Staging_Base {
	public function __construct() {
		parent::__construct();

		$this->possible_development_group_post_stati = array (
			// published == active
			'publish',
			'staging'
		);

		$this->possible_development_group_post_stati_value_to_key = array (
			'Staging' => 'staging',
			'Active' => 'publish'
		);

		// BradM: 2015.06.30: todo: this is a work in progress.
		// $this->is_404_has_been_triggered = false;
		// $this->is_home_has_been_triggered = false;

		/**
		 * Add hooks to __construct so they are available from ajax calls
		 */
		self::page_register_post_status_development_group();
	}

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		/**
		 * ********************************************************************
		 * admin hooks
		 * ********************************************************************
		 */
		if ( is_admin() ) {
			// Load our class-staging css/js
			add_action( 'admin_enqueue_scripts',
				array (
					$this,
					'wp_enqueue_scripts_class_staging'
				) );

			add_action( 'pre_get_posts', array (
				$this,
				'admin_page_pre_get_posts'
			) );

			// Change $wp_post_statuses so that "Published" becomes "Active"
			add_action( 'admin_init', array (
				$this,
				'modify_wp_post_statuses'
			) );

			add_action( 'admin_head', array (
				$this,
				'admin_head'
			) );

			// If editing a page, update $_SESSION['wp_staging_view_version'] to match page's
			// status.
			add_action( 'admin_init',
				array (
					$this,
					'switch_wp_staging_version_if_editing_a_page'
				) );

			add_filter( 'get_pages', array (
				$this,
				'get_pages'
			), 10, 2 );

			add_filter( 'post_updated', array( $this, 'new_via_customizer' ), 10, 3 );
		} else {
			/**
			 * ********************************************************************
			 * wp hooks
			 * ********************************************************************
			 */

			// If viewing staging site and trying to load an active page (and vice versa), trigger
			// 404.
			add_filter( 'parse_query', array (
				$this,
				'prevent_page_contamination'
			) );

			add_filter( 'parse_query', array (
				$this,
				'help_user_set_front_page'
			), 20 );
		}

		add_action( 'pre_get_posts', array (
			$this,
			'page_pre_get_posts'
		) );

		// handle ajax request for "Copy to Staging/Active"
		add_action( 'wp_ajax_copy_to_post_status',
			array (
				$this,
				'copy_to_post_status_callback'
			) );

		/**
		 * Add a "Development Group" column to "All pages"
		 *
		 * There are two hooks below for this, one for pages and one for posts
		 */

		// Add a "Development Group" column to "All pages"
		add_filter( 'manage_pages_columns',
			array (
				$this,
				'page_manage_pages_columns_development_group'
			) );

		// // Add a "Development Group" column to "All posts"
		// add_filter( 'manage_posts_columns',
		// array (
		// $this,
		// 'page_manage_pages_columns_development_group'
		// ) );

		// On 'All pages', order posts by development status and then title.
		add_filter( 'posts_orderby', array (
			$this,
			'posts_orderby'
		) );

		/**
		 * Add the post status to the "Development Group" column on "All Pages"
		 *
		 * There are two hooks below for this, one for pages and one for posts
		 */

		// Add the post status to the "Development Group" column on "All Pages"
		add_action( 'manage_pages_custom_column',
			array (
				$this,
				'page_manage_pages_custom_column_develment_group'
			), 10, 2 );

		// Add the post status to the "Development Group" column on "All Posts"
		// add_action( 'manage_posts_custom_column',
		// array (
		// $this,
		// 'page_manage_pages_custom_column_develment_group'
		// ), 10, 2 );

		/**
		 * Add "Copy to Staging" for each page in "All Pages"
		 *
		 * There are two hooks below for this, one for pages and one for posts
		 */

		// Add "Copy to Staging" for each page in "All Pages"
		add_filter( 'page_row_actions', array (
			$this,
			'page_row_copy_to'
		), 10, 2 );

		// Add "Copy to Staging" for each post in "All Posts"
		// add_filter( 'post_row_actions', array (
		// $this,
		// 'page_row_copy_to'
		// ), 10, 2 );

		add_action( 'post_submitbox_misc_actions',
			array (
				$this,
				'page_submitbox_misc_actions_development_group'
			) );

		add_action( 'save_post', array (
			$this,
			'save_post_development_group'
		), 9, 1 );

		// If a page is copied to staging and no staging theme is set,
		// prompt the user to set a staged theme.
		add_action( 'admin_notices',
			array (
				$this,
				'after_copy_to_staging_prompt_to_set_staged_theme'
			) );
	}

	/**
	 * Add css to <head> of wp-admin/edit.php
	 */
	public function admin_head() {
		global $pagenow;

		if ( 'edit.php' == $pagenow ) {
			?>
<style>
span.permalink {
	padding-left: 15px;
	font-weight: normal;
	font-style: italic;
}
</style>
<?php
		}
	}

	/**
	 * If we're looking for pages and posts, only return those belonging to our environment.
	 *
	 * For example, if we're staging and we're lookign for pages to add to a menu, don't show
	 * published pages as an options.
	 *
	 * @param unknown $query
	 */
	public function admin_page_pre_get_posts( $query ) {
		global $pagenow;

		/*
		 * If we're in the dashboard managing menus, filter the list of pages the user is able
		 * to see and add to their menu.
		 *
		 * nav_menu_items are not actually staged, they're published. Only modify the query if we're
		 * not looking at nav_menu_item's.
		 */
		if ( 'nav-menus.php' === $pagenow && 'nav_menu_item' != $query->get( 'post_type' ) ) {
			if ( $this->user_should_see_staging() ) {
				$query->set( 'post_status', array ( 'staging' ) );
			} else {
				$query->set( 'post_status', array ( 'publish' ) );
			}

			return;
		}

		/*
		 * While in the Customizer, an AJAX call is made to get a list of pages the user can add to
		 * a menu.
		 *
		 * The below conditional fixes a bug in which active pages were returned, when instead only
		 * staging pages should have been returned.
		 *
		 * The 2 $is_ variables below are set to help code readability. They are helping to determine
		 * if we are querying for a list of pages to add to a menu, as in:
		 * $_REQUEST['action'] = 'load-available-menu-items-customizer'
		 * $_REQUEST['object'] = 'page'
		 */

		$is_action_menu = ( isset( $_REQUEST['action'] ) && 'load-available-menu-items-customizer' === $_REQUEST['action'] );

		/*
		 * In WP Customizer <= 4.6, individual ajax calls were made to get pages, posts, etc.
		 * $is_object_page was a check to ensure the ajax call was for a page, and not any other type.
		 *
		 * In WP Customizer >= 4.7, all the ajax calls are now combined into one call. There is no
		 * longer a $_REQUEST['object'] being sent in. Instead, we'll look at
		 * $query->query_vars['post_type'] to determine if this is a query for a page.
		 */
		$is_object_page = ( ! empty( $query->query_vars['post_type'] ) && 'page' === $query->query_vars['post_type'] );

		if ( $this->in_ajax && $this->user_should_see_staging() && $is_action_menu && $is_object_page ) {
				$query->set( 'post_status', array( 'staging' ) );

				return;
		}
	}

	/**
	 * If a page is copied to staging and no staging theme is set,
	 * prompt the user to set a staged theme.
	 */
	public function after_copy_to_staging_prompt_to_set_staged_theme() {
		if ( false == $this->has_staging_theme &&
			 isset( $_SESSION['boldgrid_just_copied_a_page_to_staging'] ) &&
			 true == $_SESSION['boldgrid_just_copied_a_page_to_staging'] ) {
			// Get the link to themes.php
			$themes_url = admin_url( 'themes.php' );

			// Print the admin notice.
			echo '
				<div class="updated">
					<p>
						You have successfully copied a page to staging! Before you will be able to view your new staged page, please visit your <a href="' .
				 $themes_url . '">Themes page</a> and set a staged theme.
					</p>
				</div>
			';

			// Unset the session variable so we don't get this message again.
			$_SESSION['boldgrid_just_copied_a_page_to_staging'] = false;
		}
	}

	/**
	 * Copy to post_status callback
	 */
	public function copy_to_post_status_callback() {
		global $wpdb;

		// get the selected post
		$post_id = intval( $_POST['post_id'] );
		$post = get_post( $post_id );

		// remove the post id
		unset( $post->ID );

		// set the new post status
		// If the current status is 'staging', the new will be 'publish'. And, vice versa.
		$post->post_status = 'staging' == $post->post_status ? 'publish' : 'staging';

		// If we are copying to staging, create a session variable indicating so.
		// We will use this session variable to display an admin notice for the
		// user to set a staging menu if they have not alrady done so.
		$_SESSION['boldgrid_just_copied_a_page_to_staging'] = ( 'staging' == $post->post_status );

		$post = $this->set_post_name( $post );

		// get the id of the new post by saving it.
		$new_post_id = wp_insert_post( $post, false );

		echo get_permalink( $new_post_id );

		wp_die(); // this is required to terminate immediately and return a proper response
	}

	/**
	 * Change $wp_post_statuses so that "Published" becomes "Active"
	 */
	public function modify_wp_post_statuses() {
		global $wp_post_statuses;
		global $pagenow;

		if ( 'edit.php' == $pagenow ) {
			foreach ( $wp_post_statuses['publish']->label_count as $key => $value ) {
				$new_value = str_replace( 'Published', 'Active', $value );

				$wp_post_statuses['publish']->label_count[$key] = $new_value;
			}
		}
	}

	/**
	 * Filter new pages created via the Customizer.
	 *
	 * If you're in your Staging Customizer and you create a staged page, make sure it's post_status
	 * is set to staging after publishing via the Customizer.
	 *
	 * Creating new pages via the Customizer is new as of WordPress 4.7.
	 *
	 * @since 1.3.0.2
	 *
	 * @param int     $post_ID      Post ID.
	 * @param WP_Post $post_after   Post object following the update.
	 * @param WP_Post $post_before  Post object before the update.
	 */
	public function new_via_customizer( $post_ID, $post_after, $post_before ) {
		// If we're not saving a page via customizer, abort.
		if( 'customize_save' !== $this->ajax_action ) {
			return;
		}

		// If we're not saving a page, abort.
		if( 'page' !== $post_after->post_type ) {
			return;
		}

		// If we're not saving a page via the staging customizer, abort.
		if( ! $this->is_referer_staging() ) {
			return;
		}

		// Ah, all is good. Remove / add filter to avoid infinite loop and stage this post.
		remove_filter( 'post_updated', array( $this, 'new_via_customizer' ), 10, 3 );
		$this->stage( $post_ID );
		add_filter( 'post_updated', array( $this, 'new_via_customizer' ), 10, 3 );
	}

	/**
	 * Insert Development Group
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function page_manage_pages_columns_development_group( $columns ) {
		foreach ( $columns as $k => $v ) {
			$new_columns[$k] = $v;

			// Insert 'Development Group' after 'Title'
			if ( 'title' == $k ) {
				$new_columns['development_group'] = __( 'Development Group' );
			}
		}

		return $new_columns;
	}

	/**
	 * Add the post status to the "Development Group" column on "All Pages"
	 *
	 * @param array $column
	 * @param int $post_id
	 */
	public function page_manage_pages_custom_column_develment_group( $column, $post_id ) {
		switch ( $column ) {
			case 'development_group' :
				$this->set_post_stati();

				$post_status_key = get_post_status( $post_id );

				$post_status_value = $this->post_stati[$post_status_key]->label;

				// Show "Published" instead as "Active"
				$post_status_value = 'Published' == $post_status_value ? 'Active' : $post_status_value;

				echo $post_status_value;

				break;
		}
	}

	/**
	 * If we're looking for pages and posts, only return those belonging to our environment.
	 *
	 * For example, if we're staging and we're lookign for pages to add to a menu, don't show
	 * published pages as an options.
	 *
	 * @param object $query
	 */
	public function page_pre_get_posts( $query ) {
		$post_type = $query->get( 'post_type' );

		/**
		 * Below this line we call $query->is_front_page(), which in turn calls is_page(),
		 * which returns null when there is no query object.
		 *
		 * One example is wooCommerce, which get_queried_object_returns null on a product page.
		 *
		 * Certain queries don't return with a post_type param, such as wooCommerce in
		 * pre_get_posts, so if post_type is empty, we'll just return before attempting
		 * to do anything with it yet.
		 */
		if ( empty( $post_type ) ) {
			return;
		}

		/**
		 * ********************************************************************
		 * If in the Dashboard:
		 * ********************************************************************
		 */
		if ( is_admin() ) {
			$user_should_see_staging_and_this_is_inspiration_deployment = ( true ==
				 $this->user_should_see_staging() && true == $this->is_inspiration_deployment() ) ? true : false;

			$we_are_looking_for_a_page = ( ( is_array( $post_type ) && in_array( 'page',
				$post_type ) ) || 'page' == $post_type ) ? true : false;

			// if user should see staging and this is inspiration deployment
			if ( true == $user_should_see_staging_and_this_is_inspiration_deployment ) {
				// if we're looking for a 'page'
				if ( $we_are_looking_for_a_page ) {
					$query->set( 'post_status', array (
						'staging'
					) );
					return;
				}
			}
		} else {
			/**
			 * ****************************************************************
			 * If on the front end of the site:
			 * ****************************************************************
			 */

			// Only applicable pages should show in a search result on the front end. For example,
			// if on the active site, don't show search results from the staging site, and vice
			// versa.
			if ( $query->is_search ) {
				if ( $this->user_should_see_staging() ) {
					$query->set( 'post_status', array (
						'staging'
					) );
				} else {
					$query->set( 'post_status', array (
						'publish'
					) );
				}
			}

			// If we're looking for an attachment or a revision, return.
			if ( 'attachment' == $post_type || 'revision' == $post_type ) {
				return;
			}

			/**
			 * Posts are not currently staged.
			 *
			 * Your posts should appear on both your active and staging site. If we are looking for
			 * a post, return, so other calls below are not ran.
			 *
			 * is_home(): Checks if the blog posts index page is being displayed.
			 *
			 * get_post_type(): Retrieve the post type of the current post or of a given post.
			 */
			if ( true == is_home() || 'post' == get_post_type() ) {
				return;
			}

			/**
			 * Posts are not currently staged.
			 *
			 * When going directly to a post, the checks above are not accurately getting the
			 * post_type.
			 *
			 * Below, get post_type using $query->query['name'] (the slug) and get_page_by_path();
			 */
			if ( isset( $query->query['name'] ) && ! empty( $query->query['name'] ) ) {
				$slug = $query->query['name'];
				$post = get_page_by_path( $slug, OBJECT, 'post' );

				if ( isset( $post->post_type ) && 'post' == $post->post_type ) {
					return;
				}
			}

			$query_looking_for_either_page_or_post = ( $query->is_home() || $query->is_main_query() ||
				 $query->is_front_page() );

			/*
			 * If we should see staging and also looking for a nav_menu_item, return so that other
			 * statements are not ran.
			 */
			if ( true == $this->user_should_see_staging() && 'nav_menu_item' == $post_type ) {
				return;
			}

			/**
			 * Do we need to show only staged content?
			 */
			if ( true == $this->user_should_see_staging() &&
				 true == $query_looking_for_either_page_or_post ) {
				// By default, at this point we want to force only staged pages.
				// Below, we will allow plugins to change this.
				$show_only_staged_content = true;

				/**
				 * Allow plugins to determine if we should only show staged content.
				 *
				 * For example, a 'draft' page is created in order to preview 'GridBlock Sets'. If
				 * we're previewing a staged GridBlock Set, then we need to return AT THIS POINT and
				 * not require the page we're looking at be staged. Afterall, it's status is
				 * 'draft', not 'staged', but we still want to see it.
				 *
				 * @since 1.0.7
				 */
				$show_only_staged_content = apply_filters(
					'boldgrid_staging_pre_force_staged_pages_only', $show_only_staged_content );

				// If we should not force staged content, abort.
				if ( false === $show_only_staged_content ) {
					return;
				}

				$query->set( 'post_status', array (
					'staging'
				) );
				return;
			}

			/**
			 * Do we need to show only published content?
			 */
			if ( true == $query_looking_for_either_page_or_post &&
				 false == $this->user_should_see_staging() ) {
				// if we have post_status AND
				// if it is an array AND
				// 'staging' is in that array
				// THEN remove 'staging' from the post_status array.
				if ( isset( $query->query['post_status'] ) and is_array(
					$query->query['post_status'] ) && isset( $query->query['post_status']['staging'] ) ) {
					unset( $query->query['post_status']['staging'] );

					return;
				}
			}
		}
	}

	/**
	 * Register post status for Development Group
	 */
	public static function page_register_post_status_development_group() {
		register_post_status( 'staging',
			array (
				'label' => _x( 'Staging', 'post' ),
				// todo: not sure if public is working correctly below
				// if public == true, then people on the front can see it AND it does NOT show in
				// the all
				// pages list
				'public' => true,
				'exclude_from_search' => true,
				'show_in_admin_all_list' => true,
				'show_in_admin_status_list' => true,
				'label_count' => _n_noop( 'Staged <span class="count">(%s)</span>',
					'Staged <span class="count">(%s)</span>' )
			) );
	}

	/**
	 * Add "Copy to staging" under each of the pages in "All Pages"
	 *
	 * Sample Page
	 * Edit | Quick Edit | Trash | View | Copy to Staging
	 *
	 * @param array $actions
	 * @param object $page_object
	 *
	 * @return array
	 */
	public function page_row_copy_to( $actions, $page_object ) {
		// Set 'copy_to_post_status'
		// Should it read Copy to "Staging" or "Active"
		$copy_to_post_status = $page_object->post_status == 'staging' ? 'Active' : 'Staging';

		// Unrelated, but this is a good time to chime in.
		// Above the page row is a link to the actual page so you can edit it.
		// [ ] About Us
		// We need to modify that link (somewhere else) and show it's permalink.
		// [ ] About Us /about-us
		// Since we're here, let's add the permalink as a data attribute so we can grab it later.
		$data_page_id_permalink = "data-permalink='" . $page_object->post_name . "'";

		$actions['copy_to_post_status'] = "<a class='pointer' data-post-id='" . $page_object->ID .
			 "' data-copy-to='" . $copy_to_post_status .
			 "' data-success-action='reload_current_page' " . $data_page_id_permalink . ">Copy to " .
			 $copy_to_post_status . "</a>";

		return $actions;
	}

	/**
	 * Build post type selection box
	 */
	public function page_submitbox_misc_actions_development_group() {
		global $post;
		global $pagenow;

		// Get the value of 'post_type' from the url.
		$get_post_type = ( isset( $_GET['post_type'] ) ? $_GET['post_type'] : null );

		// Are we creating a new page?
		$is_new_page = ( 'post-new.php' == $pagenow && 'page' == $get_post_type );

		// Is this the first time we're loading a staging page created from a gridblock set?
		$is_new_staging_gridblock_set = ( true ==
			 get_post_meta( $post->ID, 'new_gridblock_set_staging' ) );
		delete_post_meta( $post->ID, 'new_gridblock_set_staging' );

		// for 'page' only, ie. not 'post'
		if ( 'page' != $post->post_type ) {
			return;
		}

		// Determine which radio should be automatically selected, active or staging?
		// The approach below is separated into two different scenarios:
		// 1. We are creating a new page
		// 2. We are editing an existing page (easy, just use the existing post_status)
		if ( true === $is_new_staging_gridblock_set ) {
			$active_checked = '';
			$staging_checked = 'checked';
		} elseif ( true == $is_new_page ) {
			// Get the number of pages per post type.
			// Example $post_counts: http://pastebin.com/YQTVhSjm
			$post_counts = wp_count_posts( 'page' );

			// If we have 0 active pages and > 0 staged pages:
			if ( 0 == $post_counts->publish && $post_counts->staging > 0 ) {
				$active_checked = '';
				$staging_checked = 'checked';
			}

			// If we have 0 staged pages, default to active regardles of the number of existing
			// active pages.
			if ( 0 == $post_counts->staging ) {
				$active_checked = 'checked';
				$staging_checked = '';
			}

			// As a fail-safe, if neither end up checked, check active by default.
			if ( empty( $active_checked ) && empty( $staging_checked ) ) {
				$active_checked = 'checked';
				$staging_checked = '';
			}
		} else {
			$active_checked = 'publish' == $post->post_status ? 'checked' : '';
			$staging_checked = 'staging' == $post->post_status ? 'checked' : '';
		}

		?>
<div class="misc-pub-section">
	<span>Development Group:</span><br /> <input type="radio"
		id="development_group_post_status"
		name="development_group_post_status" value="publish"
		<?php echo $active_checked; ?>> Active<br /> <input type="radio"
		id="development_group_post_status"
		name="development_group_post_status" value="staging"
		<?php echo $staging_checked; ?>> Staging
</div>
<?php
	}

	/**
	 * On 'All pages', order posts by development status and then title.
	 *
	 * Default orderby_statuement is:
	 * single_siteposts.menu_order ASC, single_siteposts.post_title ASC
	 *
	 * We're simply changing it to:
	 * 'post_status ASC, post_title ASC'
	 *
	 * @param unknown $orderby_statement
	 * @return string
	 */
	public function posts_orderby( $orderby_statement ) {
		global $pagenow;

		$is_query_attachment = ( isset( $_REQUEST['action'] ) &&
			 'query-attachments' == $_REQUEST['action'] );

		if ( is_admin() && false == $is_query_attachment && 'edit.php' == $pagenow &&
			 ! isset( $_GET['orderby'] ) ) {
			$orderby_statement = 'post_status ASC, post_title ASC';
		}

		return $orderby_statement;
	}

	/**
	 * If viewing staging site and trying to load an active page (and vice versa), trigger 404.
	 *
	 * @param unknown $wp_query
	 */
	public function prevent_page_contamination( $wp_query ) {
		// Abort if we don't have a post_status.
		if ( ! isset( $wp_query->queried_object->post_status ) ) {
			return;
		}

		/*
		 * Is there contamination?
		 * 1. We're in production but trying to see a staged page, or
		 * 2. We're in staging but trying to see a production page
		 */
		$contaminated = self::is_contaminated( $wp_query->queried_object->post_status );

		// Should we be redirecting this page_name based on a redirect setup for the option
		// boldgrid_staging_boldgrid_redirects?
		if ( true == $contaminated ) {
			$params = array (
				'post_name' => $wp_query->queried_object->post_name
			);

			$this->redirect_or_404( $params );
		}
	}

	/**
	 * Prevent the public from seeing staged pages.
	 */
	public static function prevent_public_from_seeing_staged_pages( $wp_query = null ) {
		// If we don't have wp_query, get it.
		if ( null == $wp_query ) {
			global $wp_query;
		}

		$post_name = null;

		// If a guest is trying to view a staged paged, either redirect or 404.
		if ( isset( $wp_query->queried_object ) and isset( $wp_query->queried_object->post_name ) and
			 'staging' == $wp_query->queried_object->post_status ) {

			// Redirect or 404.
			$params = array (
				'post_name' => $wp_query->queried_object->post_name
			);
			Boldgrid_Staging_Page_And_Post_Staging::redirect_or_404( $params );
		} elseif ( isset( $wp_query->query ) and isset( $wp_query->query['name'] ) and is_404() ) {
			// Redirect or 404.
			$params = array (
				'post_name' => $wp_query->query['name']
			);
			Boldgrid_Staging_Page_And_Post_Staging::redirect_or_404( $params );
		}
	}

	/**
	 * Should we be redirecting this page_name based on a redirect setup for the option
	 * boldgrid_staging_boldgrid_redirects?
	 */
	public static function redirect_exists( $page_name ) {
		$boldgrid_staging_boldgrid_redirects = get_option( 'boldgrid_staging_boldgrid_redirects' );

		// If there are no redirects, return false.
		if ( false == $boldgrid_staging_boldgrid_redirects ) {
			return false;
		}

		// If the page_name exists in the array:
		if ( array_key_exists( $page_name, $boldgrid_staging_boldgrid_redirects ) ) {
			$page_id_to_redirect_to = $boldgrid_staging_boldgrid_redirects[$page_name];

			// Make sure the page_id is numeric and then return it.
			if ( is_numeric( $page_id_to_redirect_to ) ) {
				return $page_id_to_redirect_to;
			}
		}

		return false;
	}

	/**
	 */
	public static function redirect_or_404( $params = null ) {
		if ( null == $params or ! is_array( $params ) or empty( $params ) ) {
			return;
		}

		if ( ! isset( $params['post_name'] ) or empty( $params['post_name'] ) ) {
			return;
		}

		// Should we be redirecting this page to another?
		$redirect_to_page_id = Boldgrid_Staging_Page_And_Post_Staging::redirect_exists(
			$params['post_name'] );

		// No, we should not be redirecting. Trigger a 404.
		if ( false == $redirect_to_page_id ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
		} else {
			// Yes, we should be redirecting. Redirect.
			$url_to_redirect_to = get_permalink( $redirect_to_page_id );
			wp_redirect( $url_to_redirect_to, 301 );
			exit();
		}
	}

	/**
	 * Should we run self::get_pages()?
	 *
	 * This is a helper function created to determine whether or not our self::get_pages() method
	 * should run and filter data.
	 *
	 * @since 1.0.7
	 *
	 * @see self::get_pages()
	 * @global $pagenow
	 *
	 * @param array $r
	 *        	See get_pages() within this class.
	 * @return bool
	 */
	public function run_get_pages( $r ) {
		global $pagenow;

		// todo: We should probably run this filter on every admin page. Until further testing can
		// be done, we'll only run it on the following pages:
		$pagenows = array (
			'options-reading.php',
			'customize.php',
			'post.php',
		);

		if ( ! in_array( $pagenow, $pagenows ) ) {
			return false;
		}

		// If the user should not see staging content, abort.
		if ( ! $this->user_should_see_staging() ) {
			return false;
		}

		$post_type = ( isset( $r['post_type'] ) ? $r['post_type'] : null );

		// We only stage pages. If this is not a page, abort.
		if ( 'page' != $post_type ) {
			return false;
		}

		// If you've gotten this far, then congratulations are in order!
		return true;
	}

	/**
	 * Save post
	 *
	 * @param int $post_id
	 */
	public function save_post_development_group( $post_id ) {
		/**
		 * ****************************************************************************
		 * Should we abort?
		 * ****************************************************************************
		 */

		// Abort if this is an autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Abort if the user is not allowed to edit:
		if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
		}

		// Abort if we just saved a revision.
		$post = get_post( $post_id );
		if ( 'revision' == $post->post_type ) {
			return;
		}

		// Make sure that $_POST['development_group_post_status'] is set:
		if ( ! isset( $_POST['development_group_post_status'] ) || ! in_array(
			$_POST['development_group_post_status'], $this->possible_development_group_post_stati ) ) {
			return;
		}

		// If the user is manually setting the "Status" to "Draft", let them do it.
		if ( 'draft' == $_POST['post_status'] ) {
			return;
		}

		/**
		 * ****************************************************************************
		 * We're not aborting.
		 * Go ahead and update the post_status.
		 * ****************************************************************************
		 */

		// unhook this function so it doesn't loop infinitely
		remove_action( 'save_post', array (
			$this,
			'save_post_development_group'
		), 9, 1 );

		/**
		 * Update the post's status
		 */
		wp_update_post(
			array (
				'ID' => $post_id,
				'post_status' => sanitize_text_field( $_POST['development_group_post_status'] )
			) );

		// re-hook this function
		add_action( 'save_post', array (
			$this,
			'save_post_development_group'
		), 9, 1 );
	}

	/**
	 * Set the appropriate post name.
	 *
	 * This is usually called when copying a page from active to staging, or vice versa.
	 *
	 * For example, when copying the active home page to staging, the page name needs to change
	 * from /home to /home-staging.
	 *
	 * This method is assuming that the post status has already been changed. If the post status
	 * is seen as staging here, then we assume it use to be active and was just changed to staging.
	 *
	 * @since 1.3.1
	 *
	 * @param  object $post A WordPress post object.
	 * @return object
	 */
	public function set_post_name( $post ) {
		$post_name = $post->post_name;

		// Remvoe -### from the post name. WordPress will ensure we don't have duplicate names later.
		$post_name = preg_replace( '/-\d+$/', '', $post_name );

		/*
		 * If staging, append -staging.
		 *
		 * Otherwise, remove -staging.
		 */
		if ( 'staging' == $post->post_status ) {
			$post_name .= '-staging';
		} else {
			$post_name = preg_replace( '/-staging$/', '', $post_name );
		}

		$post->post_name = $post_name;

		return $post;
	}

	/**
	 * WP's get_post_stati() returns an array of post status names or objects.
	 *
	 * $this->post_stati = Array
	 * (
	 * ....[publish] => stdClass Object
	 * ....(
	 * ........[label] => Published
	 * ........[label_count] => Array
	 * ............(
	 * ................[0] => Published <span class="count">(%s)</span>
	 * ................[1] => Published <span class="count">(%s)</span>
	 * ................[singular] => Published <span class="count">(%s)</span>
	 * ................[plural] => Published <span class="count">(%s)</span>
	 * ................[context] =>
	 * ................[domain] =>
	 * ............)
	 * ........[exclude_from_search] =>
	 * ........[_builtin] => 1
	 * ........[public] => 1
	 * ........[internal] =>
	 * ........[protected] =>
	 * ........[private] =>
	 * ........[publicly_queryable] => 1
	 * ........[show_in_admin_status_list] => 1
	 * ........[show_in_admin_all_list] => 1
	 * ........[name] => publish
	 * ....)
	 * ....[future] => stdClass Object
	 * ....[draft] => stdClass Object
	 * ....[pending] => stdClass Object
	 * ....[private] => stdClass Object
	 * ....[trash] => stdClass Object
	 * ....[auto-draft] => stdClass Object
	 * ....[inherit] => stdClass Object
	 */
	public function set_post_stati() {
		if ( ! isset( $this->post_stati ) ) {
			// get_post_stati() =
			$this->post_stati = get_post_stati( array (), 'objects' );
		}
	}

	/**
	 * Stage a page by setting its status to staging.
	 *
	 * @since 1.3.0.2
	 *
	 * @param  int  $id The id of the page to stage.
	 * @return bool
	 */
	public function stage( $id ) {
		$post = get_post( $id );

		// If we failed to get a post, abort.
		if( is_wp_error( $post ) ) {
			return false;
		}

		// Only pages may be staged. Abort if this is not a page.
		if( 'page' !== $post->post_type ) {
			return false;
		}

		// Set the post status to staging and save.
		$post->post_status = 'staging';
		$updated = wp_update_post( $post, true );

		// Return bool based on success of saving the post.
		if( is_wp_error( $updated ) ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * If editing a page, update $_SESSION['wp_staging_view_version'] to match page's status.
	 *
	 * If the front end is 'active' and in the dashboard you edit a 'staged' page and click 'view',
	 * it will 404 because the front is still set to active.
	 */
	public function switch_wp_staging_version_if_editing_a_page() {
		global $pagenow;

		// This should only be ran on post.php
		if ( 'post.php' != $pagenow ) {
			return;
		}

		// This should only be ran when we have a post=#### in the url.
		if ( ! isset( $_GET['post'] ) ) {
			return;
		}

		$post_id = intval( $_GET['post'] );
		$post_status = get_post_status( $post_id );

		$_SESSION['wp_staging_view_version'] = 'staging' == $post_status ? 'staging' : 'production';
	}

	/**
	 * Get all the staged pages
	 *
	 * @return array
	 */
	public static function get_all_staged_pages() {
		return get_pages( array (
			'post_status' => 'staging'
		) );
	}

	/**
	 * Filter the results of a call to WordPress' get_pages().
	 *
	 * This method is run when WordPress calls:
	 * return apply_filters( 'get_pages', $pages, $r );
	 *
	 * More details about the $pages and $r parameters can be found within the inline documentation
	 * immediately preceding the:
	 * apply_filters( 'get_pages' );
	 * ... call in wp-includes/post.php.
	 *
	 * @since 1.0.7
	 *
	 * @param array $pages
	 *        	An array of WP_Post Objects.
	 * @param array $r
	 *        	An array of arguments used with WordPress' get_pages function. Example:
	 *        	http://pastebin.com/Z2tkvhGQ.
	 * @return array|false List of pages matching $r.
	 */
	public function get_pages( $pages, $r ) {
		if ( ! $this->run_get_pages( $r ) ) {
			return $pages;
		}

		// Prevent an infinite loop.
		remove_filter( 'get_pages', array (
			$this,
			'get_pages'
		) );

		// Run get_pages() again.
		// Except this time, only look for Staging pages.
		$r['post_status'] = 'staging';
		$pages = get_pages( $r );

		// Infinite loop prevented, Great job Guy! Now add the filter back.
		add_filter( 'get_pages', array (
			$this,
			'get_pages'
		), 10, 2 );

		return $pages;
	}

	/**
	 */
	public function help_user_set_front_page( $wp_query ) {
		// BradM: 2015.06.30: todo: this is a work in progress.
		return;

		if ( is_home() ) {
			$this->is_home_has_been_triggered = true;
		}

		if ( is_404() ) {
			$this->is_404_has_been_triggered = true;
		}

		/**
		 * Determine if we need to run this method.
		 */

		$redirect_to_front_page_settings = false;

		// This only affects staging.
		if ( 'staging' != $_SESSION['wp_staging_view_version'] ) {
			return;
		}

		// This only affects the homepage
		if ( false === $this->is_home_has_been_triggered ) {
			return;
		}

		// This only affects 404's
		if ( false === $this->is_404_has_been_triggered ) {
			return;
		}

		// This only affects sites without a 'show_on_front' setting.
		if ( false !== get_option( 'boldgrid_staging_show_on_front' ) ) {
			return;
		}

		if ( false !== get_option( 'boldgrid_staging_page_on_front' ) ) {
			return;
		}

		/**
		 * If we do need to run it....
		 */

		wp_redirect( get_admin_url( null, 'options-reading.php?staging=1&notice=no-front-page' ),
			301 );
		exit();
	}

	/**
	 * Is the current page contaminated?
	 *
	 * For example, are we on a production site trying to pull up a staged page?
	 *
	 * @since 1.3.1
	 *
	 * @param string $post_status.
	 */
	public static function is_contaminated( $post_status ) {
		// Determine our session value.
		if( ! isset( $_SESSION ) ) {
			$session_version = 'production';
		} else {
			$session_version = ( 'staging' === $_SESSION['wp_staging_view_version'] ? 'staging' : 'production' );
		}

		// Determine if there's contamination.
		if( 'production' === $session_version && 'staging' === $post_status ) {
			return true;
		} elseif( 'staging' === $session_version && 'publish' === $post_status ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Load our class-staging css/js
	 */
	public function wp_enqueue_scripts_class_staging( $hook ) {
		if ( 'edit.php' === $hook && isset( $_GET['post_type'] ) && 'page' === $_GET['post_type'] ) {
			wp_enqueue_script( 'edit.php.js',
				$this->plugins_url . 'assets/js/edit.php.js',
				array (),
				BOLDGRID_STAGING_VERSION,
				true
			);
		}

		if ( 'post.php' === $hook ) {
			wp_enqueue_script( 'post.php.js',
				$this->plugins_url . 'assets/js/post.php.js',
				array (),
				BOLDGRID_STAGING_VERSION,
				true
			);
		}

		wp_register_style( 'class-staging',
			$this->plugins_url . 'assets/css/class-staging.css',
			array (),
			BOLDGRID_STAGING_VERSION
		);

		wp_enqueue_style( 'class-staging' );
	}
}
