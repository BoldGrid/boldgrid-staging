<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Staging_Base
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
 * BoldGrid Staging Base class
 */
class Boldgrid_Staging_Base {

	/**
	 * Core object.
	 *
	 * @since 1.5.1
	 * @var   Boldgrid_Staging
	 */
	public $core;

	/**
	 * Constructor.
	 */
	public function __construct( $core ) {
		$this->core = $core;
	}

	/**
	 * Determine if the user wants to see the staging or dev version of their site
	 *
	 * @return boolean
	 */
	public function user_should_see_staging() {

		// For instances in which staging needs to be forced, use the boldgrid_force_staging option.
		if ( '1' === get_option( 'boldgrid_force_staging' ) ) {
			return true;
		}

		/*
		 * Determine if we are in the middle of "publishing" a staging site's
		 * customize_changeset.
		 */
		if( $this->core->customize_changeset->publishing_staging ) {
			return true;
		}

		if( $this->core->customize_changeset->in_staging() ) {
			return true;
		}

		/*
		 * Currently, only logged in admins can see staged content. This may
		 * change in the future, but for now if the user is not logged in,
		 * they will not be shown any staged content.
		 *
		 * As of @1.5.1, this is not entirely true. We do allow non logged in
		 * users to view staging sites if they're viewing a staging site's
		 * customize changeset.
		 */
		if( ! is_user_logged_in() ) {
			return false;
		}

		// Return the staging theme instead of the active theme when viewing the edit page
		if ( in_array( $this->core->pagenow, array( 'post-new.php', 'post.php', 'media-upload.php' ) ) ) {
			return $this->core->page_and_post_staging->is_staging_post();
		}

		if ( 'admin-ajax.php' == $this->core->pagenow ) {
			$action = ! empty( $_POST['action'] ) ? $_POST['action'] : '';
			if ( 'boldgrid_gridblock_html' == $action ) {
				return $this->core->page_and_post_staging->is_staging_post();
			}
		}

		$view_version = ( isset( $_SESSION['wp_staging_view_version'] ) && 'staging' == $_SESSION['wp_staging_view_version'] );

		// Customizer related checks.
		if ( 'customize.php' === $this->core->pagenow ) {
			return $this->core->staging_in_url;
		} elseif ( is_customize_preview() ) {
			return $this->core->referer->is_staging();
		} elseif ( $this->core->referer->is_customizer() && ! empty( $_GET['customize_changeset_uuid'] ) ) {
			return $this->core->referer->is_staging();
		}

		// If we're in the dashboard and passing in $_POST['staging'].
		if ( is_admin() && isset( $_POST['staging'] ) && 1 == $_POST['staging'] ) {
			return true;
		}

		// If the user can 'manage_options'.
		if ( current_user_can( 'manage_options' ) ) {
			if ( is_admin() ) {
				if ( $this->core->staging_in_url ) {
					return true;
				}
			} else {
				if ( true == $view_version ) {
					return true;
				}
			}
		}

		// If we are saving changes to a staged page.
		if ( isset( $_REQUEST['action'] ) && 'editpost' == $_REQUEST['action'] &&
			 isset( $_REQUEST['hidden_post_status'] ) && 'staging' == $_REQUEST['hidden_post_status'] ) {
			return true;
		}

		return false;
	}
}
