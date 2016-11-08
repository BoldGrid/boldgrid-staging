<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Staging_Option
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
 * BoldGrid Option Staging
 */
class Boldgrid_Staging_Option extends Boldgrid_Staging_Base {
	/**
	 * WordPress options that are staged.
	 *
	 * @since 1.0.7
	 * @access public
	 * @var array $options_to_stage
	 */
	public $options_to_stage = array (
		'blog_public',
		'page_for_posts',
		'page_on_front',
		'posts_per_page',
		'posts_per_rss',
		'rss_use_excerpt',
		'show_on_front',
		// Plugin: BoldGrid Inspirations.
		// Class: Boldgrid_Inspirations_GridBlock_Sets_Kitchen_Sink.
		'boldgrid_inspirations_fetching_kitchen_sink_status',
		'_transient_boldgrid_inspirations_kitchen_sink',
		'_transient_timeout_boldgrid_inspirations_kitchen_sink',
		'boldgrid_attribution_rebuild'
	);

	/**
	 */
	public function __construct() {
		parent::__construct();

		// Are we in the customizer?
		$this->is_customizer = ( isset( $_REQUEST['wp_customize'] ) and
			 'on' == $_REQUEST['wp_customize'] ) ? true : false;

		// Is this the first call from the customizer?
		$this->is_first_customizer_preview = ( isset( $_REQUEST['customize_messenger_channel'] ) and
			 'preview-0' == $_REQUEST['customize_messenger_channel'] ) ? true : false;

		/*
		 * Some 'pre option' methods support getting an unfiltered option. For example, if you're in
		 * a staging scenario but need to get both the active and staging option, you can set
		 * 'boldgrid_get_unfiltered_option' to true. Then, after getting both options, set this
		 * option back to false.
		 *
		 * To prevent any issues, this option should always begin as 'false'.
		 */
		update_option( 'boldgrid_get_unfiltered_option', 'false' );
	}

	/**
	 * Add hooks/actions
	 */
	public function add_hooks() {
		// Add actions for blogname:
		add_action( 'pre_option_blogname', array (
			$this,
			'blogname_pre_option'
		) );

		add_action( 'pre_update_option_blogname',
			array (
				$this,
				'blogname_pre_option_update'
			), 10, 2 );

		// Add actions for blogdescription:
		add_action( 'pre_option_blogdescription',
			array (
				$this,
				'blogdescription_pre_option'
			) );

		add_action( 'pre_update_option_blogdescription',
			array (
				$this,
				'blogdescription_pre_option_update'
			), 10, 2 );

		// Add actions for sidebars_widgets
		add_action( 'pre_option_sidebars_widgets',
			array (
				$this,
				'sidebars_widgets_pre_option'
			) );

		add_action( 'pre_update_option_sidebars_widgets',
			array (
				$this,
				'sidebars_widgets_pre_option_update'
			), 10, 2 );

		// Add actions for theme_switched
		add_action( 'pre_option_theme_switched',
			array (
				$this,
				'theme_switched_pre_option'
			) );

		add_action( 'pre_update_option_theme_switched',
			array (
				$this,
				'theme_switched_pre_option_update'
			), 10, 2 );

		// Add actions for theme_switched_via_customizer
		add_action( 'pre_option_theme_switched_via_customizer',
			array (
				$this,
				'theme_switched_via_customizer_pre_option'
			) );

		add_action( 'pre_update_option_theme_switched_via_customizer',
			array (
				$this,
				'theme_switched_via_customizer_pre_option_update'
			), 10, 2 );

		// Fix issues caused by Staging / Customizer / sidebars_widgets.
		add_filter( 'boldgrid_staging_pre_option_sidebars_widgets',
			array (
				$this,
				'boldgrid_staging_pre_option_sidebars_widgets'
			) );

		// Add action for id user is missing show_on_front settings:
		add_action( 'admin_notices',
			array (
				$this,
				'admin_notice_set_front_page_settings'
			) );

		// Stage options defined in $this->options_to_stage.
		// Options are staged by hooking into the 'pre_option' and 'pre_update_option' filters.
		foreach ( $this->options_to_stage as $option ) {
			add_action( 'pre_option_' . $option, array (
				$this,
				'pre_option'
			) );

			add_action( 'pre_update_option_' . $option,
				array (
					$this,
					'pre_update_option'
				), 10, 2 );

			add_action( 'delete_option_' . $option, array (
				$this,
				'delete_option'
			) );
		}
	}

	/**
	 * Admin notice if staging has no front page
	 */
	public function admin_notice_set_front_page_settings() {
		global $pagenow;

		// Only run this on the options-reading.php page where staging=1 and notice=no-front-page
		if ( ! ( is_admin() && 'options-reading.php' == $pagenow && isset( $_GET['staging'] ) &&
			 '1' == $_GET['staging'] && isset( $_GET['notice'] ) &&
			 'no-front-page' == $_GET['notice'] ) ) {
			return;
		}

		?>
<div class="updated">
	<p>We noticed you were trying to visit the front page of your staging
		site, however you do not have any front page settings set.</p>
	<p>Please select which staging page you would like to use as your front
		page.</p>
</div>
<?php
	}

	/**
	 * Get WP Option for blogdescription
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function blogdescription_pre_option( $content ) {
		if ( $this->user_should_see_staging() ) {
			$blogdescription = get_option( 'boldgrid_staging_blogdescription' );

			return $blogdescription;
		}

		return $content;
	}

	/**
	 * Set WP Option for blogdescription
	 *
	 * @param string $new_value
	 * @param string $old_value
	 *
	 * @return string
	 */
	public function blogdescription_pre_option_update( $new_value, $old_value ) {
		if ( $this->user_should_see_staging() ) {
			update_option( 'boldgrid_staging_blogdescription', $new_value );

			return $old_value;
		}

		return $new_value;
	}

	/**
	 * Get WP Option for blogname
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function blogname_pre_option( $content ) {
		if ( $this->user_should_see_staging() ) {
			$blogname = get_option( 'boldgrid_staging_blogname' );
			return $blogname;
		}

		return $content;
	}

	/**
	 * Set WP Option for blogname
	 *
	 * @param string $new_value
	 * @param string $old_value
	 *
	 * @return string
	 */
	public function blogname_pre_option_update( $new_value, $old_value ) {
		if ( $this->user_should_see_staging() ) {
			update_option( 'boldgrid_staging_blogname', $new_value );

			return $old_value;
		}

		return $new_value;
	}

	/**
	 * Apply user's Customizer changes to allow for correct preview.
	 *
	 * As you add / remove widgets, $sidebars_widgets needs to be dynamically updated to create the
	 * propper preview. This method merges $sidebars_widgets with the changes supplied by the
	 * Customizer.
	 *
	 * Active sites do not need a function such as this, WordPress takes care of it. There may be a
	 * better solution than the below, however an initial review of
	 * wp-includes/class-wp-customize-widgets.php was not helpful.
	 *
	 * @since 1.0.2
	 *
	 * @param array $sidebars_widgets
	 *        	Example $sidebars_widgets: http://pastebin.com/bRBzvMbR
	 *
	 * @return array $sidebars_widgets
	 */
	public function boldgrid_staging_pre_option_sidebars_widgets( $sidebars_widgets ) {
		// Example $customized: http://pastebin.com/0euMC9kj
		$customized = ( isset( $_REQUEST['customized'] ) ) ? json_decode(
			stripslashes( $_REQUEST['customized'] ) ) : null;

		// If we don't have any customizations, abort.
		if ( empty( $customized ) ) {
			return $sidebars_widgets;
		}

		// Loop through all of the customizations:
		foreach ( $customized as $k => $widgets_in_this_sidebar ) {
			// Not all customizations are in regard to widgets.
			// Check to see if this is a sidebars_widgets customization.
			// Does the key begin with "sidebars_widgets[" ?
			if ( 'sidebars_widgets[' == substr( $k, 0, strlen( 'sidebars_widgets[' ) ) ) {
				// Get the name of the sidebar:
				// Example $sidebar_with_brackets: [boldgrid-widget-1]
				$sidebar_with_brackets = substr( $k, strlen( 'sidebars_widgets[' ) - 1 );
				// Example $sidebar: boldgrid-widget-1
				$sidebar = trim( $sidebar_with_brackets, '[]' );

				// Update $sidebars_widgets with the new settings supplied from the Customizer.
				$sidebars_widgets[$sidebar] = $widgets_in_this_sidebar;
			}
		}

		return $sidebars_widgets;
	}

	/**
	 * Delete a staged option rather than an active option.
	 *
	 * todo: delete_option does not have a way to change the option name before deleting it, like
	 * pre_option and pre_update_option. This isn't a problem, because it's rare we delete an
	 * option. In the future, we'll need to address this.
	 *
	 * @since 1.0.7
	 */
	public function delete_option() {
		// Get the name of the option, based upon the current filter.
		// For example, convert 'pre_option_blog_public' to 'blog_public'.
		$option = substr( current_filter(), strlen( 'delete_option_' ) );

		if ( $this->user_should_see_staging() ) {
			$staged_option = get_option( 'boldgrid_staging_' . $option );

			delete_option( $staged_option );
		}
	}

	/**
	 * Return a staged option rather than an active option.
	 *
	 * @since 1.0.7
	 *
	 * @return mixed If an option is found, the return type will that of the existing option, which
	 *         varies.
	 */
	public function pre_option() {
		// Get the name of the option, based upon the current filter.
		// For example, convert 'pre_option_blog_public' to 'blog_public'.
		$option = substr( current_filter(), strlen( 'pre_option_' ) );

		if ( $this->user_should_see_staging() ) {
			$staged_option = get_option( 'boldgrid_staging_' . $option );

			// Allow for custom values to be returned for specific options.
			if ( false === $staged_option ) {
				switch ( $option ) {
					// Return an empty array instead of false for
					// "boldgrid_inspirations_kitchen_sink".
					case '_transient_timeout_boldgrid_inspirations_kitchen_sink' :
					case '_transient_boldgrid_inspirations_kitchen_sink' :
						$staged_option = array ();
						break;
				}
			}

			return $staged_option;
		} else {
			return false;
		}
	}

	/**
	 * Update a staged option rather than an active option.
	 *
	 * @since 1.0.7
	 *
	 * @param mixed $new_value
	 *        	The new value of the option.
	 * @param mixed $old_value
	 *        	The existing value of the option.
	 * @return mixed We are returning either $new_value or $old_value.
	 */
	public function pre_update_option( $new_value, $old_value ) {
		// Get the name of the option based upon the current filter.
		// For example, convert 'pre_option_blog_public' to 'blog_public'.
		$option = substr( current_filter(), strlen( 'pre_option_update_' ) );

		if ( $this->user_should_see_staging() ) {
			update_option( 'boldgrid_staging_' . $option, $new_value );

			// Why are we returning the old_value?
			//
			// If update_option()'s call to this filter returns the $old_value,
			// Then update_option() will see that $new_value === $old_value.
			// Because they are the same value, it will return false.
			//
			// If we don't return the $old_value,
			// Then both the active and staging options will be updated:
			// 1. boldgrid_staging_OPTION is updated by this filter.
			// 2. OPTION is updated by update_option().
			return $old_value;
		}

		return $new_value;
	}

	/**
	 * Get WP Option for sidebars_widgets
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function sidebars_widgets_pre_option( $content ) {
		if ( $this->user_should_see_staging() ) {
			$sidebars_widgets = get_option( 'boldgrid_staging_sidebars_widgets' );

			return apply_filters( 'boldgrid_staging_pre_option_sidebars_widgets',
				$sidebars_widgets );
		}

		return $content;
	}

	/**
	 * Set WP Option for sidebars_widgets
	 *
	 * @param string $new_value
	 * @param string $old_value
	 *
	 * @return string
	 */
	public function sidebars_widgets_pre_option_update( $new_value, $old_value ) {
		if ( $this->user_should_see_staging() ) {
			update_option( 'boldgrid_staging_sidebars_widgets', $new_value );

			return $old_value;
		}

		return $new_value;
	}

	/**
	 * Get staging version of theme_switched option.
	 *
	 * @since 1.0.4
	 *
	 * @return false If the user should not see staging, return false.
	 * @return mixed The value of the boldgrid_staging_theme_switched option.
	 */
	public function theme_switched_pre_option() {
		if ( $this->user_should_see_staging() ) {
			return get_option( 'boldgrid_staging_theme_switched' );
		} else {
			return false;
		}
	}

	/**
	 * Set staging version of theme_switched option.
	 *
	 * @since 1.0.4
	 *
	 * @param mixed $new_value
	 *        	The new value for the theme_switched option.
	 * @param mixed $old_value
	 *        	The old value of the theme_switched option.
	 * @return mixed Depending on if staging, either $new_value or $old_value.
	 */
	public function theme_switched_pre_option_update( $new_value, $old_value ) {
		if ( $this->user_should_see_staging() ) {
			update_option( 'boldgrid_staging_theme_switched', $new_value );

			return $old_value;
		}

		return $new_value;
	}

	/**
	 * Get staging version of theme_switched_via_customizer option.
	 *
	 * @since 1.0.4
	 *
	 * @return false If the user should not see staging, return false.
	 * @return mixed The value of the theme_switched_via_customizer option.
	 */
	public function theme_switched_via_customizer_pre_option() {
		if ( $this->user_should_see_staging() ) {
			return get_option( 'boldgrid_staging_theme_switched_via_customizer' );
		} else {
			return false;
		}
	}

	/**
	 * Set staging version of theme_switched_via_customizer option.
	 *
	 * @since 1.0.4
	 *
	 * @param mixed $new_value
	 *        	The new value for the theme_switched_via_customizer option.
	 * @param mixed $old_value
	 *        	The old value of the theme_switched_via_customizer option.
	 * @return mixed Depending on if staging, either $new_value or $old_value.
	 */
	public function theme_switched_via_customizer_pre_option_update( $new_value, $old_value ) {
		if ( $this->user_should_see_staging() ) {
			update_option( 'boldgrid_staging_theme_switched_via_customizer', $new_value );

			return $old_value;
		}

		return $new_value;
	}
}
