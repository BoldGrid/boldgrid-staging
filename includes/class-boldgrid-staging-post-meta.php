<?php
/**
 * BoldGrid Source Code
 *
 * @package   Boldgrid_Staging_Post_Meta
 * @copyright BoldGrid.com
 * @version   $Id$
 * @author    BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * Boldgrid_Staging_Post_Meta.
 *
 * @since 1.5.1
 */
class Boldgrid_Staging_Post_Meta {

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
	 * @param BoldGrid_Staging $core
	 */
	public function __construct( $core ) {
		$this->core = $core;
	}

	/**
	 * Determine if a post is staging based on is_staging post_meta.
	 *
	 * @since 1.5.1
	 *
	 * @param  int $post_id
	 * @return bool
	 */
	public function is_staging( $post_id ) {

		/*
		 * Current post_meta will be:
		 * # "" an empty string if no post_meta exists.
		 * # "1" A 1 if previously set to true.
		 */
		$is_staging = get_post_meta( $post_id, 'is_staging', true );

		return '1' === $is_staging;
	}

	/**
	 * Add is_staging post_meta.
	 *
	 * This method assumes you have already determined whether or not this post
	 * should be staging.
	 *
	 * @since 1.5.1
	 *
	 * @param int $post_id
	 */
	public function add_is_staging( $post_id ) {
		$key = 'is_staging';

		// Remove any prior is_staging data, then add.
		delete_post_meta( $post_id, $key );
		update_post_meta( $post_id, $key, true );
	}
}
