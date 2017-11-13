<?php
/**
 * BoldGrid Source Code
 *
 * @package   Boldgrid_Staging_Session
 * @copyright BoldGrid.com
 * @version   $Id$
 * @author    BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * BoldGrid Staging Session.
 *
 * @since 1.5.1
 */
class Boldgrid_Staging_Session {

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
		$this->set_customize_preview();
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
	public function set_customize_preview() {

		if ( ! is_customize_preview() ) {
			return;
		}

		$version = $this->core->referer->is_staging() ? 'staging' : 'production';
		$this->set_version( $version );
	}

	/**
	 * Set session's view_version value.
	 *
	 * @since 1.5.1
	 */
	public function set_version( $version ) {
		$_SESSION['wp_staging_view_version'] = $version;
	}
}
