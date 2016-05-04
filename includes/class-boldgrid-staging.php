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
	 */
	private $boldgrid_staging_config;

	/**
	 * The URL address for this plugin
	 *
	 * @var string
	 */
	public $plugins_url;

	// Constructor:
	public function __construct( $settings ) {
		// Load and check for plugin updates:
		require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-update.php';
		$plugin_update = new Boldgrid_Staging_Update( $this );

		// Load and instantiate Boldgrid_Staging_Config:
		require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-config.php';
		$this->boldgrid_staging_config = new Boldgrid_Staging_Config( $settings );

		$this->plugins_url = plugins_url() . '/' . basename( BOLDGRID_STAGING_PATH ) . '/';

		// Include our base functions
		require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-base.php';

		// Include and instantiate additional staging classes:
		require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-page-and-post.php';
		$this->page_and_post_staging = new Boldgrid_Staging_Page_And_Post_Staging();

		require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-theme.php';
		$this->theme_staging = new Boldgrid_Staging_Theme();

		require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-option.php';
		$this->option_staging = new BoldGrid_Staging_Option();

		require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-menu.php';
		$this->menu_staging = new Boldgrid_Staging_Menu();

		require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-switcher.php';
		$this->staging_switcher = new Boldgrid_Staging_Switcher();

		require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-deployment.php';
		$this->staging_deployment = new Boldgrid_Staging_Deployment();

		require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-plugin.php';
		$this->plugin_boldgrid_staging = new Boldgrid_Staging_Plugin();

		require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-dashboard-menus.php';
		$this->dashboard_menus = new Boldgrid_Staging_Dashboard_Menus();

		// Add hooks:
		$this->add_hooks();
	}

	/**
	 * Get staging config class property
	 *
	 * @return Boldgrid_Staging_Config
	 */
	public function get_boldgrid_staging_config() {
		return $this->boldgrid_staging_config;
	}

	/**
	 * Add WordPress hooks:
	 */
	public function add_hooks() {
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
				wp_enqueue_script( 'hook.themes.php.js',
					$this->plugins_url . 'assets/js/hook.themes.php.js', array (),
					BOLDGRID_STAGING_VERSION, true );

			case 'nav-menus.php' :
				wp_enqueue_script( 'hook.nav-menus.php.js',
					$this->plugins_url . 'assets/js/hook.nav-menus.php.js', array (),
					BOLDGRID_STAGING_VERSION, true );

				if ( isset( $_GET['action'] ) && 'locations' == $_GET['action'] ) {
					wp_enqueue_script( 'hook.nav-menus-locations.php.js',
						$this->plugins_url . 'assets/js/hook.nav-menus-locations.php.js', array (),
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
		$base = new Boldgrid_Staging_Base();
		$base->set_has_staging_theme();

		wp_register_script( 'hook.pages_page_boldgrid-add-gridblock-sets.js',
			$this->plugins_url . 'assets/js/hook.pages_page_boldgrid-add-gridblock-sets.js',
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
			$this->plugins_url . 'assets/js/hook.options-reading.php.js', array (),
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
