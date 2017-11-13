<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Staging
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * BoldGrid Staging class
 */
class Boldgrid_Staging {
	/**
	 * Private class property $boldgrid_staging_config
	 *
	 * @access private
	 *
	 * @var Boldgrid_Staging_Config
	 */
	private $boldgrid_staging_config;

	/**
	 * Status of whether or not we're in an ajax call.
	 *
	 * @since 1.5.1
	 * @var   bool
	 */
	public $doing_ajax;

	/**
	 * Keep track of which hooks have been added.
	 *
	 * Due to the current setup, some sets of hooks can be added from different
	 * locations. We need to track which hook sets have been added.
	 *
	 * @since 1.5.1
	 * @var   array
	 */
	private $has_added_hooks = array(
		'always' => false,
		'can_manage_options' => false,
		'cannot_manage_options' => false,
	);

	/**
	 * Status of whether or not there is a staging theme.
	 *
	 * @since 1.5.1
	 * @var   bool
	 */
	public $has_staging_theme = false;

	/**
	 * Global pagenow.
	 *
	 * @since 1.5.1
	 * @var   string
	 */
	public $pagenow;

	/**
	 * The URL address for this plugin.
	 *
	 * @var string
	 */
	public $plugins_url;

	/**
	 * Status of whether or not staging=1 is in the current url.
	 *
	 * @since 1.5.1
	 * @var   bool
	 */
	public $staging_in_url;

	/**
	 * Current staging stylesheet.
	 *
	 * @since 1.5.1
	 * @var   string
	 */
	public $staging_stylesheet;

	/**
	 * Current staging template.
	 *
	 * @since 1.5.1
	 * @var   string
	 */
	public $staging_template;

	/**
	 * Constructor.
	 *
	 * @since 1.5.1
	 *
	 * @global string $pagenow
	 */
	public function __construct() {
		global $pagenow;

		$this->doing = defined( 'DOING_AJAX' ) && DOING_AJAX;
		$this->pagenow = $pagenow;
		$this->staging_in_url = isset( $_REQUEST['staging'] ) && '1' === $_REQUEST['staging'];

		$this->set_staging_theme();
	}

	/**
	 * Init.
	 *
	 * @since 1.5.1
	 */
	public function init() {
		$this->load_dependencies();

		/*
		 * Add hooks.
		 *
		 * 1. Initialize needed classes and make available from $this. This
		 *    approach is similar to how the BoldGrid Backup plugin uses
		 *    $this->core as a Mediator.
		 * 2. For each of the classes, add the applicable hooks. This is taken
		 *    from version 1.0 of the Staging plugin, and should probably be
		 *    updated in the future.
		 *
		 * Initially, the init of the plugin was handled with the plugin's core
		 * file, boldgrid-staging.php. As time went on, we began adding logic to
		 * the file that included adding hooks for certain situations / users.
		 * That logic has instead been moved to this and several other method.
		 *
		 * We begin with add_hooks_always, which instantiates classes and adds
		 * hooks regardless of whether the user is logged in or not. Then we
		 * add additional hooks based upon whether the user can manage options
		 * or not. This logic will need to be updated in the future as well.
		 */
		$this->add_hooks_always();
		if ( $this->customize_changeset->in_staging() || current_user_can( 'manage_options' ) ) {
			$this->add_hooks_can_manage_options();
		} else {
			$this->add_hooks_cannot_manage_options();
		}

		$this->prepare_plugin_update();
	}

	/**
	 * Prepare for the update class.
	 *
	 * @since 1.3.8
	 */
	public function prepare_plugin_update() {
		$is_cron = ( defined( 'DOING_CRON' ) && DOING_CRON );
		$is_wpcli = ( defined( 'WP_CLI' ) && WP_CLI );

		if ( $is_cron || $is_wpcli || is_admin() ) {
			require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-update.php';

			if ( empty( $this->boldgrid_staging_config ) ) {
				require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-config.php';
				$this->boldgrid_staging_config = new Boldgrid_Staging_Config();
			}

			$plugin_update = new Boldgrid_Staging_Update( $this->boldgrid_staging_config->get_configs() );

			add_action( 'init', array(
				$plugin_update,
				'add_hooks',
			) );
		}
	}

	/**
	 * Set staging stylesheet and template.
	 *
	 * @since 1.5.1
	 */
	public function set_staging_theme() {
		$this->staging_stylesheet = get_option( 'boldgrid_staging_stylesheet' );
		$this->staging_template = get_option( 'boldgrid_staging_template' );

		$this->has_staging_theme = false !== $this->staging_stylesheet && false !== $this->staging_template;
	}

	/**
	 * Load dependencies.
	 *
	 * @since 1.3.8
	 */
	public function load_dependencies() {
		require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-config.php';
		require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-base.php';
		require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-page-and-post.php';
		require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-theme.php';
		require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-option.php';
		require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-menu.php';
		require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-switcher.php';
		require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-deployment.php';
		require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-plugin.php';
		require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-dashboard-menus.php';
		require_once BOLDGRID_STAGING_PATH . '/includes/boldgrid-inspirations/class-boldgrid-staging-inspirations-deploy.php';
		require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-search.php';
		require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-referer.php';
		require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-customize-changeset.php';
		require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-session.php';
		require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-post-meta.php';
	}

	/**
	 * Get staging config class property.
	 *
	 * @return Boldgrid_Staging_Config
	 */
	public function get_boldgrid_staging_config() {
		return $this->boldgrid_staging_config;
	}

	/**
	 * Add hooks regardless of user or situation or anything else.
	 *
	 * @since 1.5.1
	 */
	public function add_hooks_always() {
		if( $this->has_added_hooks['always'] ) {
			return;
		}

		/*
		 * These are classes that are required for both the front and back end
		 * of the site.
		 */
		$this->base                  = new Boldgrid_Staging_Base( $this );
		$this->search                = new Boldgrid_Staging_Search( $this );
		$this->customize_changeset   = new Boldgrid_Staging_Customize_Changeset( $this );
		$this->referer               = new Boldgrid_Staging_Referer();
		$this->page_and_post_staging = new Boldgrid_Staging_Page_And_Post_Staging( $this );
		$this->post_meta             = new Boldgrid_Staging_Post_Meta( $this );

		$this->search->add_hooks();
		$this->customize_changeset->add_hooks();

		$this->has_added_hooks['always'] = true;
	}

	/**
	 * Initialize BoldGrid Staging for site visitors/users who cannot
	 * 'manage_options'.
	 *
	 * @since 1.5.1
	 */
	public function add_hooks_cannot_manage_options() {
		if( $this->has_added_hooks['cannot_manage_options'] ) {
			return;
		}

		// Register custom post status for all others:
		Boldgrid_Staging_Page_And_Post_Staging::page_register_post_status_development_group();

		/**
		 * Handle redirects.
		 *
		 * Scenario 1: A once published page is now staged, and has a redirect to go to another
		 * page.
		 *
		 * Scenario 2: A once published page was staged then trashed, and has a redirect to go to
		 * another page.
		*/
		// Prevent staged pages from showing on the front-end of the site or redirect.
		add_filter( 'parse_query', array( $this->page_and_post_staging, 'prevent_public_from_seeing_staged_pages' ), 20 );
		add_filter( 'template_redirect', array( $this->page_and_post_staging, 'prevent_public_from_seeing_staged_pages' ) );

		// Visitors to the front end of the site, prevent them from accessing the attribution-staging page.
		add_filter( 'boldgrid_staging_is_contaminated', array( $this->page_and_post_staging, 'is_contaminated' ) );

		$this->has_added_hooks['cannot_manage_options'] = true;
	}

	/**
	 * Add hooks for users that can manage_options.
	 *
	 * The requirement for these hooks to only be ran for users that can
	 * manage_options was originally because we only allowed administrators to
	 * view a staging site.
	 *
	 * These hooks do the grunt work of the staging plugin. They intercept calls
	 * to update options, hook into other plugins like BoldGrid Inspirations,
	 * plus many other things. If there was a time that you needed this this
	 * functionality, you would need to add these hooks.
	 *
	 * There is another time that we need these hooks however. As of WordPress
	 * 4.9, you can schedule changes in the Customizer. Let's say you schedule
	 * changes to the Staging site for 5 minutes from now AND in 5 minutes a
	 * visitor triggers a wp-cron to publish the scheduled changes, the action
	 * needs to know to load all these hooks so we update the proper site.
	 */
	public function add_hooks_can_manage_options() {
		if( $this->has_added_hooks['can_manage_options'] ) {
			return;
		}

		// Staging relies on sessions, make sure we've got one started.
		if ( ! session_id() ) {
			session_start();
		}

		$this->boldgrid_staging_config = new Boldgrid_Staging_Config();
		$this->theme_staging           = new Boldgrid_Staging_Theme( $this );
		$this->option_staging          = new BoldGrid_Staging_Option( $this );
		$this->menu_staging            = new Boldgrid_Staging_Menu( $this );
		$this->staging_switcher        = new Boldgrid_Staging_Switcher( $this );
		$this->staging_deployment      = new Boldgrid_Staging_Deployment();
		$this->plugin_boldgrid_staging = new Boldgrid_Staging_Plugin( $this );
		$this->dashboard_menus         = new Boldgrid_Staging_Dashboard_Menus();
		$this->inspirations_deploy     = new Boldgrid_Staging_Inspirations_Deploy( $this );
		$this->session                 = new Boldgrid_Staging_Session( $this );

		if ( is_admin() ) {
			// Check PHP and WordPress versions for compatibility:
			add_action( 'admin_init', array (
				$this,
				'check_php_wp_versions'
			) );

			// Added staging redirect:
			add_filter( 'wp_redirect', array (
				$this,
				'add_staging_to_admin_url'
			), 10, 2 );

			// Add enqueue scripts for admin:
			add_action( 'admin_enqueue_scripts', array (
				$this,
				'enqueue_scripts'
			) );

			add_action( 'admin_footer', array (
				$this,
				'admin_footer'
			) );
		}

		$this->page_and_post_staging->add_hooks();
		$this->theme_staging->add_hooks();
		$this->option_staging->add_hooks();
		$this->menu_staging->add_hooks();
		$this->staging_switcher->add_hooks();
		$this->staging_deployment->add_hooks();
		$this->plugin_boldgrid_staging->add_hooks();
		$this->dashboard_menus->add_hooks();
		$this->inspirations_deploy->add_hooks();
		$this->session->add_hooks();

		$this->has_added_hooks['can_manage_options'] = true;
	}

	/**
	 *
	 *
	 * $params = Array
	 * (
	 * ....[parsed_url] => Array
	 * ........(
	 * ............[scheme] => https
	 * ............[host] => wpbex.boldgrid.com
	 * ............[path] => /2015-May-7th-125752/wp-admin/themes.php
	 * ............[query] => activated=true
	 * ........)
	 * ....[query_string] => activated=true
	 * ....[query_string_as_array] => Array
	 * ........(
	 * ............[activated] => true
	 * ........)
	 * )
	 *
	 * @param string $location
	 * @param unknown $status
	 *
	 * @return string
	 */
	public function add_staging_to_admin_url( $location, $status ) {
		/**
		 * Configure $params
		 */
		$params['parsed_url'] = parse_url( $location );

		if ( isset( $params['parsed_url']['query'] ) ) {
			$params['query_string'] = $params['parsed_url']['query'];
		} else {
			$params['query_string'] = '';
		}

		parse_str( $params['query_string'], $params['query_string_as_array'] );

		/**
		 * Modify the url if need by
		 */
		// if we're coming from a url with $_GET['staging'] = 1
		if ( isset( $_GET['staging'] ) and 1 == $_GET['staging'] ) {
			// if the location to redirect does not have staging
			if ( ! isset( $params['query_string_as_array']['staging'] ) ) {
				if ( ! empty( $params['query_string_as_array'] ) ) {
					$location .= '&staging=1';
				} else {
					$location .= '?staging=1';
				}
			}
		}

		return $location;
	}

	/**
	 * Actions to take within the admin footer.
	 *
	 * @since 1.0.7
	 */
	public function admin_footer() {
		// Include our templates in the footer.
		include BOLDGRID_STAGING_PATH . '/pages/templates/navigation.php';
	}

	/**
	 * Enqueue scripts
	 *
	 * @param string $hook
	 */
	public function enqueue_scripts( $hook ) {
		switch ( $hook ) {
			case 'themes.php' :
				wp_enqueue_script( 'themes.js',
					BOLDGRID_STAGING_URL . 'assets/js/themes.js',
					array (),
					BOLDGRID_STAGING_VERSION,
					true
				);

				wp_localize_script( 'themes.js', 'BoldGridStagingThemes', array(
					'Active'              => __( 'Active', 'boldgrid-staging' ),
					'errorStagingTheme'   => __( 'There was an error when trying to stage this theme. Please try again.', 'boldgrid-staging' ),
					'errorUnstagingTheme' => __( 'There was an error unstaging this theme. Please try again.', 'boldgrid-staging' ),
					'stagingStylesheet'   => get_option( 'boldgrid_staging_stylesheet' ),
					'Stage'               => __( 'Stage', 'boldgrid-staging' ),
					'Staged'              => __( 'Staged', 'boldgrid-staging' ),
					'Unstage'             => __( 'Unstage', 'boldgrid-staging' ),
					'themesUrl'           => get_admin_url( null, 'themes.php' ),
				));
				break;

			case 'nav-menus.php' :
				wp_enqueue_script( 'hook.nav-menus.php.js',
					BOLDGRID_STAGING_URL . 'assets/js/hook.nav-menus.php.js', array (),
					BOLDGRID_STAGING_VERSION, true );

				if ( isset( $_GET['action'] ) && 'locations' == $_GET['action'] ) {
					wp_enqueue_script( 'hook.nav-menus-locations.php.js',
						BOLDGRID_STAGING_URL . 'assets/js/hook.nav-menus-locations.php.js', array (),
						BOLDGRID_STAGING_VERSION, true );
				}
				break;

			case 'options-reading.php' :
				$this->enqueue_scripts_options_reading();
				break;

			case 'pages_page_boldgrid-add-gridblock-sets' :
				$this->enqueue_scripts_add_gridblock_sets();
				break;
		}
	}

	/**
	 * Enqueue scripts needed to Stage BoldGrid Inspiration's "Add GridBlock Sets".
	 *
	 * @since 1.0.7
	 */
	public function enqueue_scripts_add_gridblock_sets() {
		wp_register_script( 'hook.pages_page_boldgrid-add-gridblock-sets.js',
			BOLDGRID_STAGING_URL . 'assets/js/hook.pages_page_boldgrid-add-gridblock-sets.js',
			array (), BOLDGRID_STAGING_VERSION, true );

		$translate = array (
			'has_staging_theme' => ( $base->has_staging_theme ? 'true' : 'false' )
		);

		wp_localize_script( 'hook.pages_page_boldgrid-add-gridblock-sets.js',
			'boldgrid_staging_add_gridblock_sets', $translate );

		wp_enqueue_script( 'hook.pages_page_boldgrid-add-gridblock-sets.js' );
	}

	/**
	 * Scripts to enqueue when $hook == 'options-reading.php'.
	 *
	 * This code was removed from our enqueue_scripts() method for readability purposes.
	 *
	 * @since 1.0.7
	 */
	public function enqueue_scripts_options_reading() {
		// Include our Boldgrid_Staging_Option class.
		// This class contains a public "options_to_stage" property, which we need.
		include_once ( 'class-boldgrid-staging-option.php' );
		$boldgrid_staging_option = new Boldgrid_Staging_Option();

		wp_register_script( 'hook.options-reading.php.js',
			BOLDGRID_STAGING_URL . 'assets/js/hook.options-reading.php.js', array( 'jquery', 'wp-util' ),
			BOLDGRID_STAGING_VERSION, true );

		wp_localize_script( 'hook.options-reading.php.js', 'boldgrid_staging_options_to_stage',
			$boldgrid_staging_option->options_to_stage );

		wp_enqueue_script( 'hook.options-reading.php.js' );
	}

	/**
	 * Check PHP and WordPress versions for compatibility
	 */
	public function check_php_wp_versions() {
		// Check that PHP is installed at our required version or deactivate and die:
		$required_php_version = '5.3';
		if ( version_compare( phpversion(), $required_php_version, '<' ) ) {
			deactivate_plugins( BOLDGRID_STAGING_PATH . '/boldgrid-staging.php' );
			wp_die(
				'<p><center><strong>BoldGrid Staging</strong> requires PHP ' . $required_php_version .
					 ' or greater.</center></p>', 'Plugin Activation Error',
					array (
						'response' => 200,
						'back_link' => TRUE
					) );
		}

		// Check to see if WordPress version is installed at our required minimum or deactivate and
		// die:
		global $wp_version;
		$required_wp_version = '4.2';
		if ( version_compare( $wp_version, $required_wp_version, '<' ) ) {
			deactivate_plugins( BOLDGRID_STAGING_PATH . '/boldgrid-staging.php' );
			wp_die(
				'<p><center><strong>BoldGrid Staging</strong> requires WordPress ' .
					 $required_wp_version . ' or higher.</center></p>', 'Plugin Activation Error',
					array (
						'response' => 200,
						'back_link' => TRUE
					) );
		}
	}
}
