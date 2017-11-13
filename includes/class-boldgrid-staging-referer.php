<?php
/**
 * BoldGrid Source Code
 *
 * @package   Boldgrid_Staging_Referer
 * @copyright BoldGrid.com
 * @version   $Id$
 * @author    BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * BoldGrid Staging Referer.
 *
 * @since 1.5.1
 */
class Boldgrid_Staging_Referer  {

	/**
	 * Referer.
	 *
	 * @since 1.5.1
	 * @var   string
	 */
	public $referer;

	/**
	 * Constructor.
	 *
	 * @since 1.5.1
	 */
	public function __construct() {
		$referer = wp_get_referer();
		$this->referer = is_string( $referer ) ? $referer : '';
	}

	/**
	 * Determine if our referer is the customizer.
	 *
	 * @since 1.5.1
	 *
	 * @return bool
	 */
	public function is_customizer() {
		$customizer_url = get_admin_url( null, 'customize.php' );

		return 0 === strpos( $this->referer, $customizer_url );
	}

	/**
	 * Determine if our referer has staging=1 in the url.
	 *
	 * @since 1.5.1
	 *
	 * @return bool
	 */
	public function is_staging() {
		$parts = parse_url( $this->referer );

		if ( empty( $parts['query'] ) ) {
			return false;
		}

		parse_str( $parts['query'], $query );

		return ( ! empty( $query['staging'] ) && '1' === $query['staging'] );
	}

	/**
	 * Determine if our referer is the staging customizer.
	 *
	 * @since 1.5.1
	 *
	 * @return bool
	 */
	public function is_staging_customizer() {
		return $this->is_customizer() && $this->is_staging();
	}
}
