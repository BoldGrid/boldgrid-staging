<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Staging_Inspirations_Deploy
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid <support@boldgrid.com>
 */

/**
 * BoldGrid Staging Inspirations Deploy class.
 *
 * This class contains methods to assist in deploying a staging site via BoldGrid Inspirations.
 *
 * @since 1.3.10
 */
class Boldgrid_Staging_Inspirations_Deploy {

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
	 * @since 1.3.10
	 */
	public function add_hooks() {
		if( is_admin() && current_user_can( 'manage_options' ) ) {
			add_filter( 'boldgrid_deploy_media_pages', array( $this, 'media_pages' ), 10, 2 );
		}
	}

	/**
	 * Are we on the deployment page?
	 *
	 * To check, we'll see if a handful of the common variables are there.
	 *
	 * This method was formerly in the base class, moved here as of 1.5.1.
	 *
	 * @since 1.5.1
	 *
	 * @return bool
	 */
	public function is_inspiration_deployment() {
		if ( isset( $_REQUEST['boldgrid_theme_id'] ) && isset( $_REQUEST['boldgrid_page_set_id'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Filter media pages.
	 *
	 * When BoldGrid gets a list of pages it needs to loop through to replace images within, it
	 * doesn't grab private posts just installed.
	 *
	 * This method grabs all those private posts and adds them to the list of pages.
	 *
	 * @since 1.3.10
	 *
	 * @param  array $posts     An array of post objects.
	 * @param  array $installed An array of pages we've installed.
	 * @return array
	 */
	public function media_pages( $posts, $installed ) {
		if ( ! $this->core->base->user_should_see_staging() ) {
			return $posts;
		}

		/*
		 * Remove from $installed pages we already installed.
		 *
		 * We don't want our call to get_posts() below to find them, we already have them.
		 */
		foreach( $posts as $post ) {
			if ( ( $key = array_search( $post->ID, $installed )) !== false ) {
				unset( $installed[ $key ] );
			}
		}

		$params = array (
			'posts_per_page' => -1,
			'post__in' => $installed,
			'post_type' => 'post',
			'post_status' => 'private',
		);

		$private_posts = get_posts( $params );

		return array_merge( $posts, $private_posts );
	}
}
