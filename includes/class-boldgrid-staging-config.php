<?php

/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Staging_Config
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrod.com>
 */

// Prevent direct calls
if ( ! defined( 'WPINC' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * BoldGrid Staging configuration class
 */
class Boldgrid_Staging_Config {
	/**
	 * Protected class property $configs
	 *
	 * @var array
	 */
	protected $configs;
	
	/**
	 * Getter for configs
	 */
	public function get_configs() {
		return $this->configs;
	}
	
	/**
	 * Setter for configs
	 *
	 * @param unknown $s        	
	 */
	protected function set_configs( $s ) {
		$this->configs = $s;
	}
	
	/**
	 * Constructor
	 *
	 * @param array $settings        	
	 */
	public function __construct( $settings ) {
		$config_dir = $settings['configDir'];
		$global_configs = require $config_dir . '/config.plugin.php';
		$local_configs = array ();
		if ( file_exists( $local_config_filename = $config_dir . '/config.local.php' ) ) {
			$local_configs = include $local_config_filename;
		}
		
		$configs = array_merge( $global_configs, $local_configs );
		$this->set_configs( $configs );
	}
}
