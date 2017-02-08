<?php
if ( ! defined( 'WPINC' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

return array (
	'ajax_calls' => array (
		'get_plugin_version' =>							'/api/open/get-plugin-version',
		'get_asset' =>									'/api/open/get-asset',
	),
	'asset_server' =>									'https://wp-assets.boldgrid.com',
	'plugin_name' => 'boldgrid-staging',
	'plugin_key_code' => 'staging',
	'main_file_path' => BOLDGRID_STAGING_PATH . '/boldgrid-staging.php',
	'plugin_transient_name' => 'boldgrid_staging_version_data',
);
