<?php
/**
 * BoldGrid Source Code
 *
 * The BoldGrid Staging Search class.
 *
 * @package Boldgrid_Staging_Search
 * @since   1.3.5
 */

/**
 * BoldGrid Staging Search class.
 *
 * @since 1.3.5
 */
class Boldgrid_Staging_Search {

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
	 * @since 1.5.1
	 *
	 * @param Boldgrid_Staging $core
	 */
	public function __construct( $core ) {
		$this->core = $core;
	}

	/**
	 * Add hooks.
	 *
	 * @since 1.3.5
	 */
	public function add_hooks() {

		// Front end hooks.
		if( ! is_admin() ) {
			add_action( 'pre_get_posts', array ( $this, 'filter' ) );
		}
	}

	/**
	 * Filter search results.
	 *
	 * Only applicable pages should show in a search result on the front end. For example, if on
	 * the active site, don't show search results from the staging site, and vice versa.
	 *
	 * @since 1.3.5
	 *
	 * @param object $query
	 */
	public function filter( $query ) {
		if ( $query->is_search ) {
			$post_status = $this->core->base->user_should_see_staging() ? 'staging' : 'publish';
			$query->set( 'post_status', array ( $post_status ) );
		}
	}
}

?>