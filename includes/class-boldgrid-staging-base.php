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
	 * Constructor
	 */
	public function __construct() {
		$this->plugins_url = plugins_url() . '/' . basename( BOLDGRID_STAGING_PATH ) . '/';

		$this->session_start();

		$this->staging_in_url = ( isset( $_REQUEST['staging'] ) && '1' == $_REQUEST['staging'] );

		$this->staging_disabled_in_url = ( isset( $_GET['staging'] ) && '0' == $_GET['staging'] );

		$this->in_customizer = ( isset( $_REQUEST['wp_customize'] ) &&
			 'on' == $_REQUEST['wp_customize'] );

		$this->set_view_version();

		$this->set_has_staging_theme();
	}

	/**
	 * Create select pages
	 *
	 * @param int $select_id
	 * @param array $pages
	 * @param id $option_id
	 * @param array $option_selected
	 */
	public function create_select_pages( $select_id, $pages, $option_selected, $params = array() ) {
		if ( count( $pages ) > 0 ) {
			$return = "<select name='" . $select_id . "' id='" . $select_id . "'>";

			if ( isset( $params['initial_select'] ) && true == $params['initial_select'] ) {
				$return .= '<option value="0">— Select —</option>';
			}

			foreach ( $pages as $page ) {
				$selected = $option_selected == $page->ID ? 'selected' : '';

				$return .= "<option value='" . $page->ID . "' $selected>" . $page->post_title .
					 "</option>";
			}
			$return .= "</select>";
		} else {
			return false;
		}

		return $return;
	}

	/**
	 * Are we on the deployment page?
	 *
	 * To check, we'll see if a handful of the common variables are there.
	 */
	public function is_inspiration_deployment() {
		if ( isset( $_REQUEST['boldgrid_theme_id'] ) && isset( $_REQUEST['boldgrid_page_set_id'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Does the refering url contain staging=1?
	 *
	 * @since 1.0.9
	 *
	 * @return boolean
	 */
	public function is_referer_staging() {
		$parts = parse_url( wp_get_referer() );

		if ( empty( $parts['query'] ) ) {
			return false;
		}

		parse_str( $parts['query'], $query );

		return ( ! empty( $query['staging'] ) && '1' === $query['staging'] );
	}

	/**
	 * Start the session
	 */
	public function session_start() {
		// if we don't yet have a session, start one.
		if ( ! session_id() ) {
			session_start();
		}
	}

	/**
	 * Determine if the user has a staging theme set.
	 */
	public function set_has_staging_theme() {
		$this->staging_stylesheet = get_option( 'boldgrid_staging_stylesheet' );
		$this->staging_template = get_option( 'boldgrid_staging_template' );

		// Set $this->has_staging_theme
		$this->has_staging_theme = true;
		if ( false == $this->staging_stylesheet || false == $this->staging_template ) {
			$this->has_staging_theme = false;
		}
	}

	/**
	 * Set Session to either production or staging.
	 *
	 * This method currently runs only while within the customizer's iframe. If
	 * that iframe is loaded from the Staging customizer, ensure the propper
	 * session variable is set so our iframe shows the correct content.
	 *
	 * @since 1.0.9
	 */
	public function set_view_version() {
		// If we are in the customizer's iframe.
		if ( $this->in_customizer ) {
			if ( $this->is_referer_staging() ) {
				$_SESSION['wp_staging_view_version'] = 'staging';
			} else {
				$_SESSION['wp_staging_view_version'] = 'production';
			}

			return;
		}
	}

	/**
	 * Determine if a passed in parameter for a post, is a staging post
	 *
	 * @param int $_REQUEST['post']
	 * @param int $_REQUEST['post_id']
	 * @since 1.0.6
	 * @return boolean
	 */
	public function is_staging_post() {
		$post_id = ! empty( $_REQUEST['post'] ) ? intval( $_REQUEST['post'] ) : null;

		$post_id_alt = ! empty( $_REQUEST['post_id'] ) ? intval( $_REQUEST['post_id'] ) : null;

		$post_id = $post_id ? $post_id : $post_id_alt;

		if ( $post_id ) {
			$post = get_post( $post_id );

			return ( $post && 'staging' == $post->post_status );
		}
	}

	/**
	 * Determine if the user wants to see the staging or dev version of their site
	 *
	 * @return boolean
	 */
	public function user_should_see_staging() {
		global $pagenow;

		// Return the staging theme instead of the active theme when viewing the edit page
		if ( in_array( $pagenow,
			array (
				'post-new.php',
				"post.php",
				'media-upload.php'
			) ) ) {

			return $this->is_staging_post();
		}

		if ( 'admin-ajax.php' == $pagenow ) {

			$action = ! empty( $_POST['action'] ) ? $_POST['action'] : '';
			if ( 'boldgrid_gridblock_html' == $action ) {
				return $this->is_staging_post();
			}
		}

		/**
		 * Configure some vars
		 */
		$wp_staging_in_session = ( isset( $_SESSION['wp_staging_view_version'] ) &&
			 'staging' == $_SESSION['wp_staging_view_version'] );

		// If we're in the Staging Customizer's iframe preview.
		if ( $this->in_customizer && $this->is_referer_staging() ) {
			return true;
		}

		/**
		 * If we're in the dashboard and passing in $_POST['staging']...
		 */
		if ( is_admin() && isset( $_POST['staging'] ) && 1 == $_POST['staging'] ) {
			return true;
		}

		/**
		 * If the user can 'manage_options'
		 */
		if ( current_user_can( 'manage_options' ) ) {
			/**
			 * For the dashboard
			 */
			if ( is_admin() ) {
				if ( $this->staging_in_url ) {
					return true;
				}
			} else {
				/**
				 * For the front end
				 */
				// standard approach
				if ( true == $wp_staging_in_session ) {
					return true;
				}
			}
		}

		/**
		 * If we are saving changes to a staged page...
		 */
		if ( isset( $_REQUEST['action'] ) && 'editpost' == $_REQUEST['action'] &&
			 isset( $_REQUEST['hidden_post_status'] ) && 'staging' == $_REQUEST['hidden_post_status'] ) {
			return true;
		}

		return false;
	}
}
