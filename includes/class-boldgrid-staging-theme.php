<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Staging_Theme
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
 * BoldGrid Theme Staging class
 */
class Boldgrid_Staging_Theme {

	/**
	 * Core object.
	 *
	 * @since 1.5.1
	 * @var   Boldgrid_Staging
	 */
	public $core;

	/**
	 * The staged stylesheet.
	 *
	 * @since 1.2.3
	 * @access public
	 * @var string $staging_stylesheet
	 */
	public $staging_stylesheet;

	/**
	 * Constructor.
	 *
	 * @since unknown
	 *
	 * @param Boldgrid_Staging $core
	 */
	public function __construct( $core ) {
		$this->core = $core;
	}

	/**
	 * Are we currently updating theme mods
	 *
	 * @var boolean
	 */
	private $updating_staging_theme_mods = false;

	/**
	 * Add actions/hooks
	 */
	public function add_hooks() {
		// Add actions for stylesheet:
		add_action( 'pre_option_stylesheet', array (
			$this,
			'stylesheet_pre_option'
		) );

		add_action( 'pre_update_option_stylesheet',
			array (
				$this,
				'stylesheet_pre_option_update'
			), 10, 2 );

		// Add actions for template:
		add_action( 'pre_option_template', array (
			$this,
			'template_pre_option'
		) );

		add_action( 'pre_update_option_template',
			array (
				$this,
				'template_pre_option_update'
			), 10, 2 );


		/*
		 * Filter getting and setting theme mods.
		 *
		 * In instances we don't have a staging stylesheet, add the hooks for the active stylesheet.
		 *
		 *   For getting theme mods, this will ensure active theme mods are never returned in
		 *   instances we're expecting staged theme mods.
		 *
		 *   For setting theme mods, this will ensure we're not setting active theme mods when
		 *   we're expecting to set staged theme mods.
		 */
		if( $this->core->staging_stylesheet ) {
			add_action( 'pre_option_theme_mods_' . $this->core->staging_stylesheet, array( $this, 'theme_mods_pre_option' ) );
			add_action( 'pre_update_option_theme_mods_' . $this->core->staging_stylesheet, array( $this, 'theme_mods_pre_option_update' ), 10, 2 );
		} else {
			add_action( 'pre_option_theme_mods_' . get_option( 'stylesheet' ), array( $this, 'theme_mods_pre_option' ) );
			add_action( 'pre_update_option_theme_mods_' . get_option( 'stylesheet' ), array( $this, 'theme_mods_pre_option_update' ), 10, 2 );
		}

		/**
		 * ********************************************************************
		 * admin hooks
		 * ********************************************************************
		 */
		if ( is_admin() ) {

			add_action( 'wp_ajax_set_staged_theme',
				array (
					$this,
					'set_staged_theme_callback'
				) );

			add_action( 'wp_ajax_unstage_theme',
				array (
					$this,
					'unstage_theme_callback'
				) );

			add_action( 'tiny_mce_before_init',
				array (
					$this,
					'set_editor_styles'
				) );

			add_action( 'admin_head', array (
				$this,
				'admin_head'
			) );

			add_action( 'update_option_boldgrid_staging_theme_mods_' . $this->core->staging_stylesheet,
				array (
					$this,
					'updating_staging_mods'
				), 1 );

			add_action( 'update_option_boldgrid_staging_theme_mods_' . $this->core->staging_stylesheet,
				array (
					$this,
					'finished_updating_staging_mods'
				), 99 );

			add_action( 'add_option_boldgrid_staging_theme_mods_' . $this->core->staging_stylesheet,
				array (
					$this,
					'updating_staging_mods'
				), 1 );

			add_action( 'add_option_boldgrid_staging_theme_mods_' . $this->core->staging_stylesheet,
				array (
					$this,
					'finished_updating_staging_mods'
				), 99 );

			add_filter( 'wp_prepare_themes_for_js', array( $this, 'filter_wp_prepare_themes_for_js' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'customizer_switch_theme' ) );
		}

		add_filter( 'boldgrid_theme_framework_config', array (
			$this,
			'general_theme_framework'
		), 999 );
	}

	/**
	 * Add style to admin head for themes.php page
	 *
	 * @param string $hook
	 */
	public function admin_head( $hook ) {
		// Get the current page filename:
		global $pagenow;

		if ( 'themes.php' == $pagenow ) {
			?>
<!-- Add some margin to the 'Customize' button -->
<style type='text/css'>
a.button.button-primary.customize.load-customize.hide-if-no-customize {
	margin-left: 3px;
}
</style>
<?php
		}
	}

	/**
	 * Load js that allows modifying 'staging' from within the Customizer.
	 *
	 * @param string $hook
	 */
	public function customizer_switch_theme( $hook ) {
		if ( true == $this->core->has_staging_theme && 'customize.php' === $this->core->pagenow ) {
			wp_enqueue_script( 'customizer-switch-theme.js',
				BOLDGRID_STAGING_URL . 'assets/js/customizer-switch-theme.js', array (),
				BOLDGRID_STAGING_VERSION, true );
		}
	}

	/**
	 * Set the staged theme.
	 *
	 * COPY NAV_MENU LOCATIONS
	 * When you change themes, you lose your menu assignment settings.
	 * This is a WordPress standard.
	 * What we will do below is copy the old theme's menu assignment to the new theme.
	 */
	public function set_staged_theme_callback() {

		// If we did not pass in a stylesheet, abort.
		if( ! isset( $_POST['stylesheet'] ) ) {
			wp_die();
		}

		// COPY NAV_MENU LOCATIONS (1/2)
		$current_staged_stylesheet = get_option( 'boldgrid_staging_stylesheet' );
		$current_staged_nav_menu_locations = array ();
		if ( false != $current_staged_stylesheet ) {
			$current_staged_stylesheet_theme_mods = get_option(
				'boldgrid_staging_theme_mods_' . $current_staged_stylesheet );
			if ( false != $current_staged_stylesheet_theme_mods ) {
				$current_staged_nav_menu_locations = $current_staged_stylesheet_theme_mods['nav_menu_locations'];
			}
		}

		$stylesheet = sanitize_text_field( $_POST['stylesheet'] );

		$theme = wp_get_theme( $stylesheet );

		/*
		 * Check to see that our theme is valid.
		 *
		 * Due to a JS issue, $_POST['stylesheet'] could be 'undefined'. In that case, we wouldn't
		 * want to set 'undefined' as the staged theme.
		 */
		if ( $theme->exists() ) {
			// Update both the stylesheet and template STAGING options
			update_option( 'boldgrid_staging_stylesheet', $stylesheet );

			update_option( 'boldgrid_staging_template', $stylesheet );

			// COPY NAV_MENU LOCATIONS (2/2)
			$option_name = 'boldgrid_staging_theme_mods_' . $stylesheet;

			$new_staged_stylesheet_theme_mods = get_option( $option_name );

			$new_staged_stylesheet_theme_mods['nav_menu_locations'] = $current_staged_nav_menu_locations;
			$new_staged_stylesheet_theme_mods['force_scss_recompile']['staging'] = true;

			update_option( $option_name, $new_staged_stylesheet_theme_mods );

			/*
			 * Run 'activate' on the BoldGrid Theme Framework.
			 *
			 * This will handle tasks such as clearing and creating widgets.
			 *
			 * This causes issues with boldgrid widgets and active palette being reset.
			 */
			//do_action( 'boldgrid_activate_framework' );

			echo 'success';
		}

		wp_die();
	}

	/**
	 * Get WP Option for stylesheet
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function stylesheet_pre_option( $content ) {
		// abort if we're currently looking at wp-admin/themes.php
		global $pagenow;
		if ( 'themes.php' == $pagenow ) {
			return $content;
		}

		if ( $this->core->base->user_should_see_staging() ) {
			$stylesheet = get_option( 'boldgrid_staging_stylesheet' );

			return $stylesheet;
		}

		return $content;
	}

	/**
	 * Set WP Option for stylesheet
	 *
	 * @param string $new_value
	 * @param string $old_value
	 *
	 * @return string
	 */
	public function stylesheet_pre_option_update( $new_value, $old_value ) {
		if ( $this->core->base->user_should_see_staging() ) {
			update_option( 'boldgrid_staging_stylesheet', $new_value );

			return $old_value;
		}

		return $new_value;
	}

	/**
	 * Get WP Option for theme_mods_ stylesheet
	 *
	 * @param array $content
	 *
	 * @return array
	 */
	public function theme_mods_pre_option( $content ) {
		if ( $this->core->base->user_should_see_staging() || $this->updating_staging_theme_mods ) {

			if( $this->core->staging_stylesheet ) {
				return get_option( 'boldgrid_staging_theme_mods_' . $this->core->staging_stylesheet, array() );
			} else {
				return array();
			}
		}

		return $content;
	}

	/**
	 * Set WP Option for theme_mode_ stylesheet
	 *
	 * @param array $new_value
	 * @param array $old_value
	 *
	 * @return array
	 */
	public function theme_mods_pre_option_update( $new_value, $old_value ) {
		if ( $this->core->base->user_should_see_staging() ) {

			if( $this->core->staging_stylesheet ) {
				update_option( 'boldgrid_staging_theme_mods_' . $this->core->staging_stylesheet, $new_value );
			}

			return $old_value;
		}

		return $new_value;
	}

	/**
	 * Get WP Option for template
	 *
	 * @param unknown $content
	 *
	 * @return unknown
	 */
	public function template_pre_option( $content ) {
		// abort if we're currently looking at wp-admin/themes.php
		global $pagenow;
		if ( 'themes.php' == $pagenow ) {
			return $content;
		}

		if ( $this->core->base->user_should_see_staging() ) {
			$content = get_option( 'boldgrid_staging_template' );
		}

		return $content;
	}

	/**
	 * Set WP Option for template
	 *
	 * @param unknown $new_value
	 * @param unknown $old_value
	 *
	 * @return unknown
	 */
	public function template_pre_option_update( $new_value, $old_value ) {
		if ( $this->core->base->user_should_see_staging() ) {
			update_option( 'boldgrid_staging_template', $new_value );

			return $old_value;
		}

		return $new_value;
	}

	/**
	 * Get the staged theme
	 *
	 * @return WP_Theme
	 */
	public static function get_staging_theme() {
		return wp_get_theme( get_option( 'boldgrid_staging_stylesheet' ) );
	}

	/**
	 * If you are editing a staging page, use the staging themes, editor file if it exists.
	 * Also remove the active themes stylesheet
	 *
	 * @param array $init
	 *
	 * @return string
	 */
	public function set_editor_styles( $init ) {
		if ( ! isset( $init['content_css'] ) ) {
			return $init;
		}

		// Find expected editor standard stylesheet
		$post_status = get_post_status( get_the_ID() );

		$stylesheets = explode( ',', $init['content_css'] );

		// If this is a staged page, use the staged theme
		$staged_theme = wp_get_theme( get_option( 'boldgrid_staging_stylesheet' ) );

		if ( 'staging' == $post_status && is_object( $staged_theme ) ) {
			$directory = $staged_theme->theme_root . '/' . $staged_theme->stylesheet;

			$directory_url = content_url() . '/themes/' . $staged_theme->stylesheet;

			// Remove standard sheet
			$standard_stylesheet = get_stylesheet_directory_uri() . '/editor.css';

			$key = array_search( $standard_stylesheet, $stylesheets );

			if ( false !== $key ) {
				$editor_stylesheet = $directory . '/editor.css';

				if ( file_exists( $editor_stylesheet ) ) {
					$stylesheets[$key] = $directory_url . '/editor.css';
				} else {
					unset( $stylesheets[$key] );
				}
			}
		}

		$init['content_css'] = implode( ',', $stylesheets );

		return $init;
	}

	/**
	 * Override theme framework css output
	 *
	 * @param array $boldgrid_framework_configs
	 * @return mixed
	 */
	public function general_theme_framework( $boldgrid_framework_configs ) {

		// If staging == 1 or viewing staging pages.
		if ( true == $this->core->base->user_should_see_staging() ) {

			// Update the name of the css file.
			$output_name = $boldgrid_framework_configs['customizer-options']['colors']['settings']['output_css_name'];
			$basename = basename( $output_name, '.css' );
			$output_name = str_ireplace( $basename, 'boldgrid-staging-colors', $output_name );
			$boldgrid_framework_configs['customizer-options']['colors']['settings']['output_css_name'] = $output_name;

			// Update the buttons css file.
			if ( isset( $boldgrid_framework_configs['components']['buttons']['css_file'] )
				&& isset( $boldgrid_framework_configs['components']['buttons']['css_uri'] ) ) {
					$buttons_file = $boldgrid_framework_configs['components']['buttons']['css_file'];
					$base = basename( $buttons_file, '.css' );
					$buttons_file = str_ireplace( $base, 'staging-buttons', $buttons_file );
					$boldgrid_framework_configs['components']['buttons']['css_file'] = $buttons_file;

					$buttons_uri = $boldgrid_framework_configs['components']['buttons']['css_uri'];
					$buttons_uri = str_ireplace( $base, 'staging-buttons', $buttons_uri );
					$boldgrid_framework_configs['components']['buttons']['css_uri'] = $buttons_uri;
			}

			// Flag for staging.
			$boldgrid_framework_configs['customizer-options']['colors']['settings']['staging'] = true;
		}

		return $boldgrid_framework_configs;
	}

	/**
	 * Set this variable to let us that we are updtaing theme mods
	 */
	public function updating_staging_mods() {
		$this->updating_staging_theme_mods = true;
	}

	/**
	 * Filter for wp_prepare_themes_for_js.
	 *
	 * @since 1.2.3
	 *
	 * @global string $pagenow;
	 */
	public function filter_wp_prepare_themes_for_js( $prepared_themes ) {

		/*
		 * Move our staging theme to the top of the array.
		 *
		 * Let's say we have 100 themes. By default they're sorted alphabetically, and only the first
		 * few are loaded. Let's also say our staged theme is the 100th theme, the last one.
		 *
		 * If we go to the themes page in the dashboard, our staging theme will not show until it is
		 * lazy loaded. This is not what we want to have happen, we want the staged theme to show
		 * right away. To resolve this, we'll move the staged theme to the beginning of the array.
		 *
		 * The below code is essentially setting the staged theme as the second element in the
		 * $prepared_themes array.
		 */

		global $pagenow;

		// We only want this filter to run on themes.php. If we're not on that page, abort.
		if ( 'themes.php' !== $pagenow ) {
			return $prepared_themes;
		}

		/*
		 * Ensure we have a valid staging theme.
		 *
		 * # Make sure $this->core->staging_stylesheet is not empty.
		 * # Ensure wp_get_theme successfully fetches $this->core->staging_stylesheet.
		 *
		 * The empty check is required because passing null to wp_get_theme will return the current
		 * active theme, which will trigger a false positive.
		 */
		$staged_theme = wp_get_theme( $this->core->staging_stylesheet );
		if( empty( $this->core->staging_stylesheet ) || ! $staged_theme->exists() ) {
			return $prepared_themes;
		}

		// Create a copy of the first theme, which is the active theme.
		reset( $prepared_themes );
		$first_theme = current( $prepared_themes );
		$first_theme_key = $first_theme['id'];

		// Create a copy of our staged theme.
		$staged_theme = $prepared_themes[ $this->core->staging_stylesheet ];

		// Remove our active and staged theme from the array, we'll add it back later.
		unset( $prepared_themes[ $first_theme_key ] );
		unset( $prepared_themes[ $this->core->staging_stylesheet ] );

		// Add our active and staged theme to the begining of the array.
		$prepared_themes = array(
			$first_theme_key => $first_theme,
			$this->core->staging_stylesheet => $staged_theme,
			) + $prepared_themes;

		return $prepared_themes;
	}

	/**
	 * Reset this variable to let us know that updating theme mods has completed
	 */
	public function finished_updating_staging_mods() {
		$this->updating_staging_theme_mods = false;
	}

	/**
	 * Allow a user to unstage a theme.
	 *
	 * This will be called from themes.php in the dashboard.
	 *
	 * To unstage a theme, we will simply delete:
	 * staging_stylesheet and staging_template.
	 */
	public function unstage_theme_callback() {
		global $wpdb;

		delete_option( 'boldgrid_staging_stylesheet' );
		delete_option( 'boldgrid_staging_template' );

		echo 'success';

		wp_die();
	}
}
