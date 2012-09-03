<?php
/*
Plugin Name: AppThemes Updater
Description: Allows customers to automatically update AppThemes Products.
Version: 1.0
Author: AppThemes
Author URI: http://appthemes.com
AppThemes ID: appthemes-updater
*/

function is_app_updater_network_activated() {
	if ( !is_multisite() )
		return false;

	$plugins = get_site_option( 'active_sitewide_plugins' );

	return isset( $plugins[ plugin_basename( __FILE__ ) ] );
}

if ( is_admin() ) {
	require dirname( __FILE__ ) . '/updater-class.php';
	require dirname( __FILE__ ) . '/updater-ui.php';

	new APP_Theme_Upgrader;
	new APP_Plugin_Upgrader;

	if ( is_app_updater_network_activated() )
		$app_updater = new APP_Upgrader_Network;
	else
		$app_updater = new APP_Upgrader_Regular;
}

