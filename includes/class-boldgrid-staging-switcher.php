<?php

/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Staging_Switcher
 * @copyright BoldGrid.com
 * @version $Id$
 * @author IMH Wpb
 */

// Prevent direct calls
if ( ! defined( 'WPINC' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * BoldGrid Staging Switcher class
 */
class Boldgrid_Staging_Switcher extends Boldgrid_Staging_Base {
	public $site_types = array (
		'active',
		'staging'
	);

	/**
	 * Constructor
	 */
	public function __construct() {
		// set the view version if it is not already set.
		if ( ! isset( $_SESSION['wp_staging_view_version'] ) ) {
			$_SESSION['wp_staging_view_version'] = 'production';
		}

		parent::__construct();
	}

	/**
	 * Adds the WordPress Ajax Library to the frontend.
	 */
	public function add_ajax_library() {
		?>
<script type="text/javascript">
		var ajaxurl = "<?php echo admin_url( 'admin-ajax.php' ); ?>"
		</script>
<?php
	} // end add_ajax_library

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		/**
		 * ********************************************************************
		 * wp hooks (frontend)
		 * ********************************************************************
		 */
		if ( ! is_admin() ) {
			add_action( 'wp_enqueue_scripts', array (
				$this,
				'enqueue_scripts'
			) );

			// Include the Ajax library on the front end
			add_action( 'wp_head', array (
				$this,
				'add_ajax_library'
			) );
		} else {
			/**
			 * ****************************************************************
			 * admin hooks
			 * ****************************************************************
			 */

			add_action( 'admin_enqueue_scripts',
				array (
					$this,
					'admin_enqueue_scripts'
				) );

			// Register method to handle ajax request to switch between staging / development
			add_action( 'wp_ajax_switch_to_staging',
				array (
					$this,
					'switch_to_staging_callback'
				) );

			// Go from dashboard to front end (production or staging)
			add_action( 'wp_enqueue_scripts', array (
				$this,
				'admin_enqueue_scripts'
			) );

			// Create an intermediary page to visit front end of site.
			add_action( 'init', array (
				$this,
				'visit_site_via_intermediary_page'
			) );
		}

		/**
		 * ********************************************************************
		 * global hooks (both admin and wp)
		 * ********************************************************************
		 */

		// Add to the admin menu bar the option to switch between staging / development
		add_action( 'admin_bar_menu', array (
			$this,
			'modify_admin_bar'
		), 999 );
	}

	/**
	 * Modify the admin bars for staging
	 *
	 * @param unknown $wp_admin_bar
	 */
	public function modify_admin_bar( $wp_admin_bar ) {
		// Only modify the admin bar IF the user has a staging theme set.
		if ( true == $this->has_staging_theme ) {
			/**
			 * ********************************************************************
			 * admin (dashboard) admin_bar
			 * ********************************************************************
			 */
			if ( is_admin() ) {
				// Configure the link in the admin menu bar that allows for switching between
				// staging / development
				$this->modify_admin_bar_visit_site( $wp_admin_bar );
			} else {
				/**
				 * ****************************************************************
				 * wp (frontend) admin_bar
				 * ****************************************************************
				 */
				// Add a 'Switch to staging' link.
				$this->modify_wp_admin_bar_add_switch_to_staging( $wp_admin_bar );

				// Modify links that include customize.php and add &staging=1 if applicable
				$wp_admin_bar = $this->modify_wp_admin_bar_update_customize_links( $wp_admin_bar );
			}
		}
	}

	/**
	 * Enqueue admin staging switcher
	 *
	 * @param string $hook
	 */
	public function admin_enqueue_scripts( $hook ) {
		wp_enqueue_script( 'admin-staging-switcher.js',
			$this->plugins_url . 'assets/js/admin-staging-switcher.js', array (),
			BOLDGRID_STAGING_VERSION, true );
	}

	/**
	 * Enqueue staging switcher
	 *
	 * @param string $hook
	 */
	public function enqueue_scripts( $hook ) {
		wp_enqueue_script( 'staging-switcher.js',
			$this->plugins_url . 'assets/js/staging-switcher.js', array (), BOLDGRID_STAGING_VERSION,
			true );
	}

	/**
	 * Add &staging=1 to all the links in the admin bar if applicable
	 */
	public function modify_wp_admin_bar_update_customize_links( $wp_admin_bar ) {
		if ( 'staging' == $_SESSION['wp_staging_view_version'] ) {
			/*
			 * First, update all the customize.php links
			 */
			$all_toolbar_nodes = $wp_admin_bar->get_nodes();

			foreach ( $all_toolbar_nodes as $node ) {
				if ( substr_count( $node->href, 'customize.php' ) > 0 ) {
					$node->href .= '&staging=1';
					$wp_admin_bar->add_node( $node );
				}
			}

			/*
			 * Then, update the "Menus" link
			 */
			$menus_node = $wp_admin_bar->get_node( 'menus' );
			$menus_node->href .= '?staging=1';
			$wp_admin_bar->add_node( $menus_node );
		}

		return $wp_admin_bar;
	}

	/**
	 * Modify admin bad visit site selection
	 *
	 * @param unknown $wp_admin_bar
	 */
	public function modify_admin_bar_visit_site( $wp_admin_bar ) {
		// Change "view site" to "view active site"
		$view_site_node = $wp_admin_bar->get_node( 'view-site' );
		$view_site_node->title = 'Visit Active Site';
		$view_site_node->href = '?page=boldgrid-staging&visit=active';
		$view_site_node->meta['class'] = 'admin_view_production_site';
		$wp_admin_bar->add_node( $view_site_node );

		// Add "view staging site"
		$args = array (
			'id' => 'boldgrid_staging',
			'title' => 'Visit Staging Site',
			'parent' => 'site-name',
			'href' => '?page=boldgrid-staging&visit=staging',
			'meta' => array (
				'class' => 'admin_view_staging_site'
			)
		);

		$wp_admin_bar->add_node( $args );
	}

	/**
	 * Modify the frontend admin bar and add a 'switch to staging' link.
	 */
	public function modify_wp_admin_bar_add_switch_to_staging( $wp_admin_bar ) {
		/**
		 * Configure some vars
		 *
		 * While much of the code refers to "production",
		 * we actually want the user to see "active".
		 */
		$switch_to = 'staging' == $_SESSION['wp_staging_view_version'] ? 'Active' : 'Staging';
		$current_version = 'staging' == $_SESSION['wp_staging_view_version'] ? 'Staging' : 'Active';

		/**
		 * Add parent item to admin bar.
		 *
		 * This parent item will say, "Version: Production"
		 */
		$args = array (
			'id' => 'boldgrid_staging',
			'title' => 'Version: ' . $current_version,
			'meta' => array (
				'class' => 'wp_staging_view_version'
			)
		);
		$wp_admin_bar->add_node( $args );

		/**
		 * Add to the parent item in the admin bar a link that
		 * allows the user to switch between staging / production.
		 */
		$args = array (
			'id' => 'boldgrid_staging_view_version_info',
			'title' => 'Visit ' . $switch_to . ' Site',
			'href' => '/',
			'parent' => 'boldgrid_staging',
			'meta' => array (
				'class' => 'wp_staging_switch_version'
			)
		);
		$wp_admin_bar->add_node( $args );
	}

	/**
	 * Process ajax requests to switch between staging / production
	 */
	public function switch_to_staging_callback() {
		global $wpdb;

		$this->session_start();

		/**
		 * ********************************************************************
		 * Requests coming from the admin (dashboard) admin_bar
		 * ********************************************************************
		 */
		if ( isset( $_POST['request_from'] ) && 'dashboard_admin_bar' == $_POST['request_from'] ) {
			// switch verison in session...
			if ( 'staging' == $_POST['version'] ) {
				$_SESSION['wp_staging_view_version'] = 'staging';
			} else {
				$_SESSION['wp_staging_view_version'] = 'production';
			}

			echo get_site_url();

			wp_die();
		}

		/**
		 * ********************************************************************
		 * Requests coming from admin (dashboard) edit.php
		 * ********************************************************************
		 */
		if ( isset( $_POST['request_from'] ) && 'all_pages_row_view' == $_POST['request_from'] &&
			 isset( $_POST['post_id'] ) ) {
			$post_status = get_post_status( $_POST['post_id'] );

			$_SESSION['wp_staging_view_version'] = ( 'staging' == $post_status ? 'staging' : 'production' );

			echo get_permalink( $_POST['post_id'] );

			wp_die();
		}

		/**
		 * ****************************************************************
		 * All other requests
		 * ****************************************************************
		 */

		$_SESSION['wp_staging_view_version'] = ( 'staging' == $_SESSION['wp_staging_view_version'] ? 'production' : 'staging' );

		// Initially, when a user switched between versions of their site, it would simply refresh
		// the page.
		// This triggers a 404 when switching however, as one URL cannot exist in both active and
		// staging.
		// Instead, we'll redirect the user to their homepage.
		echo get_site_url();

		wp_die(); // this is required to terminate immediately and return a proper response
	}

	/**
	 * Create an intermediary page to visit front end of site
	 *
	 * @param string $_GET['page']
	 * @param string $_GET['visit']
	 *
	 * @return void
	 */
	public function visit_site_via_intermediary_page() {
		if ( isset( $_GET['page'] ) && 'boldgrid-staging' == $_GET['page'] && isset(
			$_GET['visit'] ) ) {
			// Which site does the user want to visit?
			$visit = sanitize_text_field( $_GET['visit'] );

			// If the user did not pass over a valid 'visit', abort.
			if ( ! in_array( $visit, $this->site_types ) ) {
				return;
			}

			// Set the cookie...
			$_SESSION['wp_staging_view_version'] = ( 'staging' == $visit ? 'staging' : 'production' );

			wp_redirect( home_url() );

			exit();
		}
	}
}
