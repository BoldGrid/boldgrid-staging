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
class Boldgrid_Staging_Menu {

	/**
	 * Core object.
	 *
	 * @since 1.5.1
	 * @var   Boldgrid_Staging
	 */
	public $core;

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
	 * Add hooks
	 */
	public function add_hooks() {
		if ( is_admin() ) {

			add_filter( 'get_terms', array( $this, 'get_menus' ), 10, 4 );

		} else {
			add_filter( 'wp_nav_menu_args', array (
				$this,
				'wp_nav_menu_args'
			) );

			add_filter( 'wp_page_menu_args' , array( $this, 'wp_page_menu_args' ) );
		}
	}

	/**
	 * Filter menus based on active / staging.
	 *
	 * For example: If we're in the Customizer looking at our Staged site, do not give the option
	 * to add a menu with active pages.
	 *
	 * This method won't give us perfection, but for now that'll do donkey.
	 *
	 * @since 1.3.2
	 *
	 * @global $pagenow.
	 *
	 * @param  array         $terms      Array of found terms.
	 * @param  array         $taxonomies An array of taxonomies.
	 * @param  array         $args       An array of get_terms() arguments.
	 * @param  WP_Term_Query $term_query The WP_Term_Query object.
	 * @return array         $terms
	 */
	public function get_menus( $terms, $taxonomies, $args, $term_query ) {
		global $pagenow;

		/*
		 * This filter may be used in other places besides the Customizer, but it hasn't been
		 * tested elsewhere. If not in the customizer, abort.
		 */
		if( 'customize.php' !== $pagenow ) {
			return $terms;
		}

		// If we don't have any $terms (menus), abort.
		if( ! is_array( $terms ) || empty( $terms ) ) {
			return $terms;
		}

		// Should the user see staging?
		$should_see_staging = $this->core->base->user_should_see_staging();

		foreach( $terms as $key => $term ) {

			// Analyize our menu.
			$data = $this->analyze( $term->term_id, $term->name );

			// If we were unable to analyize the menu (maybe it was empty), continue.
			if( empty( $data ) ) {
				continue;
			}

			if( isset( $data['post_type']['page'] ) ) {
				/*
				 * If the user should see staging and the menu does not have a staged page, remove
				 * the menu from the list.
				 *
				 * Else, if the user is on their active site and the menu includes a staged page,
				 * remove the menu from the list.
				 */
				if( $should_see_staging && false === $data['has_staging_page'] ) {
					unset($terms[ $key ]);
				}elseif( ! $should_see_staging && true === $data['has_staging_page'] ) {
					unset($terms[ $key ]);
				}
			} elseif( isset( $data['post_type']['nav_menu_item'] ) && true === $data['boldgrid_created'] ) {
				/*
				 * If the user should see staging and this menu was not created in the staging
				 * environment, remove it from the list.
				 *
				 * If the user is looking at their active site and the menu was not created in the
				 * active environment, remove it from the list.
				 */
				if( $should_see_staging && true !== $data['staging_created'] ) {
					unset($terms[ $key ]);
				}elseif( ! $should_see_staging && true !== $data['active_created'] ) {
					unset($terms[ $key ]);
				}
			}
		}

		return $terms;
	}

	/**
	 * Analyize a menu.
	 *
	 * Does a menu include at least one staging page?
	 * Was a menu created by the BoldGrid Theme Framework?
	 *
	 * @since 1.3.2
	 *
	 * @param  int    $menu_id.
	 * @param  string $menu_name.
	 * @return array
	 */
	public function analyze( $menu_id, $menu_name ) {
		$data = array();

		// Default the "This menu contains a staged page" value to false.
		$has_staging_page = false;

		// Remove / add filters to prevent an infinite loop.
		remove_filter( 'get_terms', array( $this, 'get_menus' ), 10, 4 );
		$items = wp_get_nav_menu_items( $menu_id );
		add_filter( 'get_terms', array( $this, 'get_menus' ), 10, 4 );

		// If this is an empty menu, continue.
		if( false === $items ) {
			return array();
		}

		/*
		 * In our first analysis, we'll review all of the items belonging to this menu.
		 *
		 * We'll keep track of:
		 * # The number of each post_type in the menu
		 * # If the menu includes a staged page.
		 */
		foreach( $items as $item ) {
			$post = get_post( $item->object_id );

			// If we don't have a valid post, abort.
			if( is_null( $post ) ) {
				continue;
			}

			$post_type = ( isset( $post->post_type ) ? $post->post_type : null );
			$post_status = ( isset( $post->post_status ) ? $post->post_status : null );

			// Keep track of the number of $post_type's we have.
			if( ! is_null( $post_type ) ) {
				$new_count =  ( isset( $data['post_type'][$post_type] ) ? $data['post_type'][$post_type] : 0 ) + 1;
				$data['post_type'][$post_type] = $new_count;
			}

			// Keep track of whether this menu includes a staged page.
			if( 'page' === $post_type && 'staging' === $post_status ) {
				$has_staging_page = true;
			}
		}

		$data['has_staging_page'] = $has_staging_page;

		// Get both the active and staging values of the 'boldgrid_menus_created' option.
		update_option( 'boldgrid_get_unfiltered_option', 'true' );
		$menus_created = array(
			'active' => get_option( 'boldgrid_menus_created', array() ),
			'staging' => get_option( 'boldgrid_staging_boldgrid_menus_created', array() )
		);
		update_option( 'boldgrid_get_unfiltered_option', 'false' );

		/*
		 * In our next analysis, we'll determine if the menu in question was created by the
		 * BoldGrid Theme Framework.
		 *
		 * The 'boldgrid_menus_created' option can be stored in 2 different formats, as described
		 * below:
		 *
		 * 		$option_version 1 = Auto incrementing key, Menu name as value.
		 * 		Example: [0] => Social Media-staging-2
		 *
		 * 		$option version 2 = Menu id as key, menu type as value.
		 * 		Example: [3111] => social
		 */
		foreach( $menus_created as $status => $menus ) {
			// Is this version 1 or 2 of the boldgrid_menus_created option?
			$option_version = ( isset( $menus['option_version'] ) ? $menus['option_version'] : 1 );

			// Either "active_created" or "staging_created".
			$data_key = $status . '_created';

			if( 1 === $option_version ) {
				$data[ $data_key ] = in_array( $menu_name, $menus );
			} elseif( 2 === $option_version ) {
				$data[ $data_key ] = ( isset( $menus[ $menu_id ] ) );
			} else {
				$data[ $data_key ] = false;
			}
		}

		// If the menu exists in either the active or staging option, then it was created by BoldGrid.
		$data['boldgrid_created'] = ( true === $data['active_created'] || true === $data['staging_created'] );

		return $data;
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
		if ( $this->core->base->user_should_see_staging() && isset( $args['menu'] ) ) {
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
		if ( $this->core->base->user_should_see_staging() ) {
			$args[ 'post_status' ] = array( 'staging' );
		}

		return $args;
	}
}
