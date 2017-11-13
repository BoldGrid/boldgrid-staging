<?php
/*
 * Plugin Name: BoldGrid Staging
 * Plugin URI: https://www.boldgrid.com/boldgrid-staging/
 * Version: 1.5
 * Author: BoldGrid.com <wpb@boldgrid.com>
 * Author URI: https://www.boldgrid.com/
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

if ( false === defined( 'BOLDGRID_STAGING_URL' ) ) {
	define( 'BOLDGRID_STAGING_URL', plugins_url() . '/' . basename( BOLDGRID_STAGING_PATH ) . '/' );
}

// Load only in the admin section for Administrators.
require_once BOLDGRID_STAGING_PATH . '/includes/class-boldgrid-staging.php';

require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

/**
 * Plugin init.
 */
function boldgrid_staging_init() {
	$staging = new Boldgrid_Staging();
	$staging->init();
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
