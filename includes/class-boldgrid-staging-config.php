<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Staging_Config
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrod.com>
 */

/**
 * BoldGrid Staging configuration class.
 */
class Boldgrid_Staging_Config {
	/**
	 * Configs.
	 *
	 * @var array
	 */
	private $configs;

	/**
	 * Getter for configs.
	 */
	public function get_configs() {
		return $this->configs;
	}

	/**
	 * Setter for configs.
	 *
	 * @param unknown $s
	 */
	private function set_configs( $s ) {
		$this->configs = $s;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$global_configs = require BOLDGRID_STAGING_CONFIGDIR . '/config.plugin.php';

		$local_configs = array ();

		if ( file_exists( $local_config_filename = BOLDGRID_STAGING_CONFIGDIR . '/config.local.php' ) ) {
			$local_configs = include $local_config_filename;
		}

		$configs = array_merge( $global_configs, $local_configs );

		$this->set_configs( $configs );
	}
}
