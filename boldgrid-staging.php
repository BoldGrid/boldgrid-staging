<?php
/*
 * Plugin Name: BoldGrid Staging
 * Plugin URI: http://www.boldgrid.com
 * Version: 1.3.2
 * Author: BoldGrid.com <wpb@boldgrid.com>
 * Author URI: http://www.boldgrid.com
 * Description: Edit your website in a staging environment
 * Text Domain: boldgrid-staging
 * Domain Path: /languages
 * License: GPLv2 or later
 */

// Prevent direct calls.
if ( false === defined( 'WPINC' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

// Define version.
if ( false === defined( 'BOLDGRID_STAGING_VERSION' ) ) {
	define( 'BOLDGRID_STAGING_VERSION', implode( get_file_data( __FILE__, array( 'Version' ), 'plugin' ) ) );
}

// Define Editor path.
if ( false === defined( 'BOLDGRID_STAGING_PATH' ) ) {
	define( 'BOLDGRID_STAGING_PATH', dirname( __FILE__ ) );
}

// Define Editor configuration directory.
if ( false === defined( 'BOLDGRID_STAGING_CONFIGDIR' ) ) {
	define( 'BOLDGRID_STAGING_CONFIGDIR', BOLDGRID_STAGING_PATH . '/includes/config' );
}

// Load only in the admin section for Administrators.
require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging.php';

// If DOING_CRON, then check if this plugin should be auto-updated.
if ( true === defined( 'DOING_CRON' ) && DOING_CRON ) {
	// Ensure required definitions for pluggable.
	if ( false === defined( 'AUTH_COOKIE' ) ) {
		define( 'AUTH_COOKIE', null );
	}

	if ( false === defined( 'LOGGED_IN_COOKIE' ) ) {
		define( 'LOGGED_IN_COOKIE', null );
	}

	// Load the pluggable class, if needed.
	require_once ABSPATH . 'wp-includes/pluggable.php';

	// Include the update class.
	require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging-update.php';

	// Instantiate the update class.
	$plugin_update = new Boldgrid_Staging_Update();

	// Check and update plugins.
	$plugin_update->wp_update_this_plugin();
}

/**
 * Plugin init.
 */
function boldgrid_staging_init() {
	/**
	 * ************************************************************************
	 * Initialize BoldGrid Staging for Admins / users who can 'manage_options'
	 * ************************************************************************
	 */
	if ( current_user_can( 'manage_options' ) ) {
		// Load and instantiate the staging class.
		$staging = new Boldgrid_Staging();
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

		// Visitors to the front end of the site, prevent them from accessing the attribution-staging page.
		add_filter( 'boldgrid_staging_is_contaminated', array( 'Boldgrid_Staging_Page_And_Post_Staging', 'is_contaminated' ) );
	}
}

/*
 * Add an action to load this plugin on plugins_loaded.
 *
 * Historically, this action was added with the default priority of 10. To resolve an issue
 * introduced by WordPress 4.7, this action is given a 9 priority.
 *
 * The Staging plugin hooks into get_option() to return the staging 'stylesheet' option while in a
 * staging scenario. In WordPress 4.7, BEFORE we can hook into get_option, the customizer is getting
 * the theme. The theme will always be the active theme because that call for the theme is coming
 * before the Staging plugin is initialized. This is the reason for the 9 priority @since 1.3.1.
 */
add_action( 'plugins_loaded', 'boldgrid_staging_init', 9 );
