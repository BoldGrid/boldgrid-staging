<?php

/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Staging_Update
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
 * BoldGrid Staging update class
 */
class Boldgrid_Staging_Update {
	/**
	 * BoldGrid Staging class object
	 *
	 * @var object
	 */
	private $boldgrid_staging = null;
	
	/**
	 * Setter for the BoldGrid Staging class object
	 *
	 * @param object $boldgrid_staging        	
	 *
	 * @return bool
	 */
	private function set_boldgrid_staging( $boldgrid_staging ) {
		$this->boldgrid_staging = $boldgrid_staging;
		return true;
	}
	
	/**
	 * Getter for the BoldGrid Staging class object
	 *
	 * @return object $this->boldgrid_staging
	 */
	protected function get_boldgrid_staging() {
		return $this->boldgrid_staging;
	}
	
	/**
	 * Constructor
	 *
	 * Add filters
	 */
	public function __construct( $boldgrid_staging ) {
		// Set the BoldGrid Editor class object (used to get configs):
		$this->set_boldgrid_staging( $boldgrid_staging );
		
		// Only for wp-admin:
		if ( is_admin() ) {
			// Get global $pagenow
			global $pagenow;
			
			// Add filters to modify boldgrid-staging update transient information
			if ( 'plugins.php' == $pagenow || 'update-core.php' == $pagenow ||
				 'plugin-install.php' == $pagenow ||
				 ( 'admin-ajax.php' == $pagenow && 'update-plugin' == $_REQUEST['action'] ) ) {
				// Add filters:
				add_filter( 'pre_set_site_transient_update_plugins', 
					array (
						$this,
						'custom_plugins_transient_update' 
					), 10, 3 );
				
				add_filter( 'plugins_api', 
					array (
						$this,
						'custom_plugins_transient_update' 
					), 10, 3 );
				
				// Force WP to check for updates, don't rely on cache / transients.
				add_filter( 'site_transient_update_plugins', 
					array (
						$this,
						'site_transient_update_plugins' 
					), 10 );
			}
		}
	}
	
	/**
	 * Update the plugin transient
	 *
	 * @param object $transient        	
	 * @param string $plugin_info        	
	 * @param string $plugin_info_obj        	
	 *
	 * @return object $transient
	 */
	public function custom_plugins_transient_update( $transient, $plugin_info = null, $plugin_info_obj = null ) {
		// Get version data transient:
		if ( is_multisite() ) {
			$version_data = get_site_transient( 'boldgrid_staging_version_data' );
		} else {
			$version_data = get_transient( 'boldgrid_staging_version_data' );
		}
		
		// Get the BoldGrid Editor class object for getting configs:
		$boldgrid_staging = $this->get_boldgrid_staging();
		
		// Get configs:
		$configs = $boldgrid_staging->get_boldgrid_staging_config()
			->get_configs();
		
		// Get the installed plugin data:
		$plugin_data = get_plugin_data( BOLDGRID_STAGING_PATH . '/boldgrid-staging.php', false );
		
		// Get the WordPress version:
		global $wp_version;
		
		// If we have no transient or force-check is called, and we do have configs, then get data and set transient:
		if ( ( false === $version_data || ( isset( $_GET['force-check'] ) &&
			 1 == $_GET['force-check'] && ( empty( $version_data->updated ) ||
			 $version_data->updated < time() - 60 ) ) ) && ! empty( $configs ) ) {
			// Determine the plugin update release channel:
			if ( is_multisite() ) {
				( $options = get_site_option( 'boldgrid_settings' ) ) ||
				 ( $options = get_option( 'boldgrid_settings' ) );
		} else {
			$options = get_option( 'boldgrid_settings' );
		}
		
		// Set the release channel:
		$channel = ( isset( $options['release_channel'] ) ) ? $options['release_channel'] : 'stable';
		
		// Get the latest version information:
		// Build the http query:
		$params_array['key'] = 'staging';
		$params_array['channel'] = $channel;
		$params_array['installed_staging_version'] = $plugin_data['Version'];
		$params_array['installed_wp_version'] = $wp_version;
		$params_array['site_hash'] = get_option( 'boldgrid_site_hash' );
		
		$params = http_build_query( $params_array );
		
		$query = $configs['asset_server'] . $configs['ajax_calls']['get_plugin_version'] . '?' .
			 $params;
		
		// Make the call:
		$version_data = json_decode( wp_remote_retrieve_body( wp_remote_get( $query ) ) );
		
		// Set version data transient, expired in 8 hours:
		if ( ! empty( $version_data ) && 200 == $version_data->status &&
			 ! empty( $version_data->result->data ) ) {
			// Add the current timestamp (in seconds):
			$version_data->updated = time();
			
			if ( is_multisite() ) {
				delete_site_transient( 'boldgrid_staging_version_data' );
				set_site_transient( 'boldgrid_staging_version_data', $version_data, 
					8 * HOUR_IN_SECONDS );
			} else {
				delete_transient( 'boldgrid_staging_version_data' );
				set_transient( 'boldgrid_staging_version_data', $version_data, 8 * HOUR_IN_SECONDS );
			}
		} else {
			// Something went wrong, so just skip adding update data; return unchanged transient data:
			return $transient;
		}
	}
	
	// Get global $pagenow
	global $pagenow;
	
	// Create a new object to be injected into transient:
	if ( 'plugin-install.php' == $pagenow && isset( $_GET['plugin'] ) &&
		 'boldgrid-staging' == $_GET['plugin'] ) {
		// For version information iframe (/plugin-install.php):
		$transient = new stdClass();
		
		// If we have section data, then prepare it for use:
		if ( ! empty( $version_data->result->data->sections ) ) {
			// Remove new lines and double-spaces, to help prevent a broken JSON set:
			$version_data->result->data->sections = preg_replace( '/\s+/', ' ', 
				trim( $version_data->result->data->sections ) );
			
			// Convert the JSON set into an array:
			$transient->sections = json_decode( $version_data->result->data->sections, true );
			
			// If we have data, format it for use, else set a default message:
			if ( ! empty( $transient->sections ) && count( $transient->sections ) > 0 ) {
				foreach ( $transient->sections as $section => $section_data ) {
					$transient->sections[$section] = html_entity_decode( $section_data, ENT_QUOTES );
				}
			} else {
				$transient->sections['description'] = 'Data not available';
			}
		} else {
			$transient->sections['description'] = 'Data not available';
		}
		
		// Set the other elements:
		$transient->name = $version_data->result->data->title;
		$transient->requires = $version_data->result->data->requires_wp_version;
		$transient->tested = $version_data->result->data->tested_wp_version;
		// $transient->downloaded = $version_data->result->data->downloads;
		$transient->last_updated = $version_data->result->data->release_date;
		$transient->download_link = $configs['asset_server'] . $configs['ajax_calls']['get_asset'] .
			 '?id=' . $version_data->result->data->asset_id . '&installed_staging_version=' .
			 $plugin_data['Version'] . '&installed_wp_version=' . $wp_version;
		
		if ( ! empty( $version_data->result->data->compatibility ) && null !== ( $compatibility = json_decode( 
			$version_data->result->data->compatibility, true ) ) ) {
			$transient->compatibility = $version_data->result->data->compatibility;
		}
		
		/*
		 * Not currently showing ratings.
		 * if ( ! ( empty( $version_data->result->data->rating ) ||
		 * empty( $version_data->result->data->num_ratings ) ) ) {
		 * $transient->rating = ( float ) $version_data->result->data->rating;
		 * $transient->num_ratings = ( int ) $version_data->result->data->num_ratings;
		 * }
		 */
		
		$transient->added = '2015-03-19';
		if ( ! empty( $version_data->result->data->siteurl ) ) {
			$transient->homepage = $version_data->result->data->siteurl;
		}
		
		if ( ! empty( $version_data->result->data->tags ) && null !== ( $tags = json_decode( 
			$version_data->result->data->tags, true ) ) ) {
			$transient->tags = $version_data->result->data->tags;
		}
		
		if ( ! empty( $version_data->result->data->banners ) && null !== ( $banners = json_decode( 
			$version_data->result->data->banners, true ) ) ) {
			$transient->banners = $banners;
		}
		
		$transient->plugin_name = 'boldgrid-staging.php';
		$transient->slug = 'boldgrid-staging';
		$transient->version = $version_data->result->data->version;
		$transient->new_version = $version_data->result->data->version;
		// $transient->active_installs = true;
	} elseif ( 'plugins.php' == $pagenow || 'update-core.php' == $pagenow ||
		 'admin-ajax.php' == $pagenow ) {
		// For plugins.php and update-core.php pages:
		$obj = new stdClass();
		$obj->slug = 'boldgrid-staging';
		$obj->plugin = 'boldgrid-staging/boldgrid-staging.php';
		$obj->new_version = $version_data->result->data->version;
		
		if ( ! empty( $version_data->result->data->siteurl ) ) {
			$obj->url = $version_data->result->data->siteurl;
		}
		
		$obj->package = $configs['asset_server'] . $configs['ajax_calls']['get_asset'] . '?id=' .
			 $version_data->result->data->asset_id . '&installed_staging_version=' .
			 $plugin_data['Version'] . '&installed_wp_version=' . $wp_version;
		
		if ( $plugin_data['Version'] != $version_data->result->data->version ) {
			$transient->response[$obj->plugin] = $obj;
			$transient->tested = $version_data->result->data->tested_wp_version;
		} else {
			$transient->no_update[$obj->plugin] = $obj;
		}
	}
	
	return $transient;
}

/**
 * Force WP to check for updates, don't rely on cache / transients.
 *
 * @param object $value        	
 * @return object
 */
public function site_transient_update_plugins( $value ) {
	global $pagenow;
	
	// Only require fresh data IF user is clicking "Check Again".
	if ( 'update-core.php' != $pagenow || ! isset( $_GET['force-check'] ) ) {
		return $value;
	}
	
	// Set the last_checked to 1, so it will trigger the timeout and check again.
	if ( isset( $value->last_checked ) ) {
		$value->last_checked = 1;
	}
	
	return $value;
}
}
