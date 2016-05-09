<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Staging_Menu
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
 * BoldGrid Menu Staging
 */
class Boldgrid_Staging_Menu extends Boldgrid_Staging_Base {
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Add hooks
	 */
	public function add_hooks() {
		if ( is_admin() ) {
		} else {
			add_filter( 'wp_nav_menu_args', array (
				$this,
				'wp_nav_menu_args'
			) );

			add_filter( 'wp_page_menu_args' , array( $this, 'wp_page_menu_args' ) );
		}
	}

	/**
	 * WP Nav Menu Args
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function wp_nav_menu_args( $args ) {

		/**
		 * Some theme authors may hard code 'primary' as the 'menu'.
		 * We need to unset 'menu' so that it will use the menu we defined.
		 *
		 * <?php wp_nav_menu( array(
		 * * 'menu' => 'primary',
		 * * 'theme_location' => 'primary',
		 * * 'depth' => 2,
		 * * 'container' => 'div',
		 * * 'container_class' => 'collapse navbar-collapse',
		 * * 'container_id' => 'primary-navbar',
		 * * 'menu_class' => 'nav navbar-nav',
		 * * 'fallback_cb' => 'wp_bootstrap_navwalker::fallback',
		 * * 'walker' => new wp_bootstrap_navwalker())
		 * * );
		 * ?>
		 */
		if ( $this->user_should_see_staging() && isset( $args['menu'] ) ) {
			$args['menu'] = '';
		}

		return $args;
	}

	/**
	 * Filter wp_page_menu_args.
	 *
	 * If the user does not have a menu assigned to a location, a theme my use wp_page_menu as
	 * their fallback_cb. If a theme does use wp_page_menu, ensure that only staged pages are
	 * fetched when applicable. Otherwise, a Staging site's menu will show Active pages.
	 *
	 * @since 1.1.2
	 *
	 * @param array $args An array of page menu arguments.
	 */
	public function wp_page_menu_args( $args ) {
		if ( $this->user_should_see_staging() ) {
			$args[ 'post_status' ] = array( 'staging' );
		}

		return $args;
	}
}
