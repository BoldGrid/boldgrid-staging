<?php
/**
 * BoldGrid Source Code
 *
 * @package   Boldgrid_Staging_Base
 * @copyright BoldGrid.com
 * @version   $Id$
 * @author    BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * BoldGrid Staging Customizer.
 *
 * @since 1.4.0.1
 */
class Boldgrid_Staging_Customizer {

	/**
	 * Determine if the customizer is the referer.
	 *
	 * @since 1.4.0.1
	 */
	public static function is_referer() {
		$file = '/customize.php';
		$referer = parse_url( wp_get_referer() );

		return( $file === substr( $referer['path'], -1 * strlen( $file ) ) );
	}

	/**
	 * Determine if we are in a customizer preview changeset.
	 *
	 * Normally WordPress' native is_customize_preview() function can be used, but not in every
	 * case.
	 *
	 * @since 1.4.0.1
	 */
	public static function in_changeset_preview() {
		return ! empty( $_GET['customize_changeset_uuid'] ) && ! empty( $_GET['customize_messenger_channel'] );
	}
}
