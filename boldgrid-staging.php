<?php

/*
 * Plugin Name: BoldGrid Staging
 * Plugin URI: http://www.boldgrid.com
 * Version: 1.0.8
 * Author: BoldGrid.com <wpb@boldgrid.com>
 * Author URI: http://www.boldgrid.com
 * Description: Edit your website in a staging environment
 * Text Domain: boldgrid-staging
 * Domain Path: /languages
 * License: GPLv2 or later
 */

// Prevent direct calls
if ( ! defined( 'WPINC' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

// Define version:
if ( ! defined( 'BOLDGRID_STAGING_VERSION' ) ) {
	define( 'BOLDGRID_STAGING_VERSION', '1.0.8' );
}

// Define Editor Path
if ( ! defined( 'BOLDGRID_STAGING_PATH' ) ) {
	define( 'BOLDGRID_STAGING_PATH', __DIR__ );
}

// Load only in the admin section for Administrators:
require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging.php';

/**
 * Plugin init
 */
function boldgrid_staging_init() {
	/**
	 * ************************************************************************
	 * Initialize BoldGrid Staging for Admins / users who can 'manage_options'
	 * ************************************************************************
	 */
	if ( current_user_can( 'manage_options' ) ) {
		// Initialize staging for Administrator:
		// Get the settings (configuration directory):
		$settings = array (
			'configDir' => BOLDGRID_STAGING_PATH . '/includes/config' 
		);
		// Load and instantiate the staging class:
		$staging = new Boldgrid_Staging( $settings );
	} else {
		/**
		 * ********************************************************************
		 * Initialize BoldGrid Staging for site visitors / users who
		 * cannot 'manage_options'
		 * ********************************************************************
		 */
		
		// Register custom post status for all others:
		require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-base.php';
		require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-page-and-post.php';
		Boldgrid_Staging_Page_And_Post_Staging::page_register_post_status_development_group();
		
		/**
		 * Handle redirects.
		 *
		 * Scenario 1: A once published page is now staged, and has a redirect to go to another
		 * page.
		 *
		 * Scenario 2: A once published page was staged then trashed, and has a redirect to go to
		 * another page.
		 */
		// Prevent staged pages from showing on the front-end of the site or redirect.
		add_filter( 'parse_query', 
			array (
				'Boldgrid_Staging_Page_And_Post_Staging',
				'prevent_public_from_seeing_staged_pages' 
			), 20 );
		
		add_filter( 'template_redirect', 
			array (
				'Boldgrid_Staging_Page_And_Post_Staging',
				'prevent_public_from_seeing_staged_pages' 
			) );
	}
}

// Add an action to load this plugin on plugins_loaded:
add_action( 'plugins_loaded', 'boldgrid_staging_init' );
