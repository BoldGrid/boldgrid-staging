<?php
/**
 * BoldGrid Source Code
 *
 * @package   Boldgrid_Staging_Customize_Changeset
 * @copyright BoldGrid.com
 * @version   $Id$
 * @author    BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * Boldgrid_Staging_Customize_Changeset.
 *
 * @since 1.5.1
 */
class Boldgrid_Staging_Customize_Changeset {

	/**
	 * Core object.
	 *
	 * @since 1.5.1
	 * @var   Boldgrid_Staging
	 */
	public $core;

	/**
	 * Bool indicating we are publishing a staging site's customize_change.
	 *
	 * When the customize_changeset goes from future to publish, we check if
	 * it has been flagged with an is_staging post_meta. If it has, then we
	 * are publishing a staging customize_changeset.
	 *
	 * @since 1.5.1
	 * @var   bool
	 */
	public $publishing_staging = false;

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
	 * Add hooks.
	 *
	 * @since 1.5.1
	 */
	public function add_hooks() {
		add_action( 'save_post_customize_changeset', array( $this, 'on_save_customize_changeset' ), 10, 3 );
		add_action( 'save_post_page', array( $this, 'on_save_page' ), 10, 3 );
		add_action( 'transition_post_status', array( $this, 'on_transition_post_status' ), 9, 3 );
		add_action( 'wp_insert_post_data', array( $this, 'wp_insert_post_data'), 10, 2 );
	}

	/**
	 * Determine if we are in a staging customize changeset preview.
	 *
	 * @since 1.5.1
	 *
	 * @global object $wpdb
	 *
	 * @return bool
	 */
	public function in_staging() {
		if( is_admin() || empty( $_GET['customize_changeset_uuid'] ) ) {
			return false;
		}

		$uuid = $_GET['customize_changeset_uuid'];

		// Get post id.
		global $wpdb;
		$query = "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type = 'customize_changeset';";
		$post_id = $wpdb->get_var( $wpdb->prepare( $query, $uuid ));
		/*
		 * @todo If you immediately open the staging customizer and open any of
		 * the preview site's links in a new tab, the site in the new tab will
		 * be discombobulated. This is because at that point, the
		 * customize_changeset has yet to be saved to the database, and we have
		 * not had the chance to flag it as is_staging. But, if you first change
		 * your site title and then open a link in a new tab, it works. So, for
		 * the user, don't immediately load the staging customizer and open a
		 * link in a new tab.
		 */
		if( is_null( $post_id ) ) {
			return false;
		}

		// Verify post_meta data.
		return $this->core->post_meta->is_staging( $post_id );
	}

	/**
	 * Flag a new page as is_staging.
	 *
	 * If we are in the Customizer and adding new staged pages to the menu, we
	 * need to flag those as is_staging. When we publish those changes in the
	 * future, we'll read this attribute to determine if we need to make the
	 * page a staging page.
	 *
	 * @since 1.5.1
	 *
	 * @param int     $post_ID Post ID.
     * @param WP_Post $post    Post object.
     * @param bool    $update  Whether this is an existing post being updated or not.
	 */
	public function on_save_page( $post_id, $post, $update ) {
		if( ! $this->core->referer->is_staging_customizer() ) {
			return;
		}

		/*
		 * We may want to skip this check in the future. For now though, we'll
		 * only add is_staging post meta if we're creating a new page. From the
		 * customizer, new pages will have an auto-draft post_status.
		 */
		if( $update ) {
			return;
		}

		$this->core->post_meta->add_is_staging( $post_id );
	}

	/**
	 * Set is_staging post meta value for customize_changeset.
	 *
	 * This will tell us if the customize_changeset is for the staging site.
	 *
	 * We are assuming that calls to save a customize_changeset will only come
	 * from the customizer. Please let us know if you know othersize.
	 *
	 * @since 1.5.1
	 *
	 * @param int     $post_ID Post ID.
     * @param WP_Post $post    Post object.
     * @param bool    $update  Whether this is an existing post being updated or not.
	 */
	public function on_save_customize_changeset( $post_ID, $post, $update ) {

		// If we're not customizing our staging site, abort.
		if( ! $this->core->referer->is_staging_customizer() ) {
			return;
		}

		$this->core->post_meta->add_is_staging( $post_ID );
	}

	/**
	 * Listen to transition_post_status.
	 *
	 * Primarily, we want to know when a customize_changeset is going live.
	 *
	 * @since 1.5.1
	 *
	 * @param string  $new_status New post status.
     * @param string  $old_status Old post status.
     * @param WP_Post $post       Post object.
	 */
	public function on_transition_post_status( $new_status, $old_status, $post ) {

		/*
		 * We're only interested in this if we're publishing a
		 * customize_changeset (going from future to publish).
		 */
		if( 'publish' !== $new_status || 'future' !== $old_status || 'customize_changeset' !== $post->post_type ) {
			return;
		}

		// If this is not a staging customize_changeset, abort.
		if( ! $this->core->post_meta->is_staging( $post->ID ) ) {
			return;
		}

		$this->publishing_staging = true;

		/*
		 * After this point, WordPress will begin to make all the customizer
		 * changes live. We need to add the necessary hooks so that the upcoming
		 * changes are made to staging instead of active.
		 */
		$this->core->add_hooks_can_manage_options();
	}

	/**
	 * Filter post data before it is inserted into the database.
	 *
	 * This method will change a page to 'staging' when we are publishing a
	 * staging customize_changeset.
	 *
	 * @since 1.5.1
	 *
	 * @param array $data    An array of slashed post data.
	 * @param array $postarr An array of sanitized, but otherwise unmodified post data.
	 */
	public function wp_insert_post_data( $data, $postarr ) {
		$is_page = ! empty( $data['post_type'] ) && 'page' === $data['post_type'];
		$post_id = ! empty( $postarr['ID'] ) && is_numeric( $postarr['ID'] ) ? $postarr['ID'] : null;

		if( ! $this->publishing_staging || ! $is_page || empty( $post_id ) ) {
			return $data;
		}

		if( $this->core->post_meta->is_staging( $post_id ) ) {
			$data['post_status'] = 'staging';
		}

		return $data;
	}
}
