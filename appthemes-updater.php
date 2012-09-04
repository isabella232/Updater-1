<?php
/*
Plugin Name: AppThemes Updater
Description: Allows customers to automatically update AppThemes Products.
Version: 1.1.1-alpha
Author: AppThemes
Author URI: http://appthemes.com
AppThemes ID: appthemes-updater
Network: true
Text Domain: appthemes-updater
Domain Path: /languages
*/

function is_app_updater_network_activated() {
	if ( !is_multisite() )
		return false;

	$plugins = get_site_option( 'active_sitewide_plugins' );

	return isset( $plugins[ plugin_basename( __FILE__ ) ] );
}

function app_updater_activate() {
	// Delete caches, so that a new check is performed
	delete_site_transient( 'update_themes' );
	delete_site_transient( 'update_plugins' );
}

if ( is_admin() ) {
	load_plugin_textdomain( 'appthemes-updater', '', basename( dirname( __FILE__ ) ) . '/languages' );

	require dirname( __FILE__ ) . '/updater-class.php';
	require dirname( __FILE__ ) . '/updater-ui.php';

	new APP_Theme_Upgrader;
	new APP_Plugin_Upgrader;

	if ( is_app_updater_network_activated() )
		$app_updater = new APP_Upgrader_Network;
	else
		$app_updater = new APP_Upgrader_Regular;

	register_activation_hook( __FILE__, 'app_updater_activate' );
}

