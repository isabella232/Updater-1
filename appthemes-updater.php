<?php
/*
 * Plugin Name: AppThemes Updater
 * Description: Automatically notifies you when AppThemes and Marketplace product updates are available.
 * Version: 1.4.0
 * Author: AppThemes
 * Author URI: https://www.appthemes.com
 * AppThemes ID: appthemes-updater
 * Network: true
 * Text Domain: appthemes-updater
 * Domain Path: /languages
*/

function app_updater_activate() {
	app_refresh_themes();
	app_refresh_plugins();
}

function app_refresh_themes() {
	delete_site_transient( 'update_themes' );
	wp_update_themes();
}

function app_refresh_plugins() {
	delete_site_transient( 'update_plugins' );
	wp_update_plugins();
}

function app_extra_headers( $headers ) {
	$headers['AppThemes ID'] = 'AppThemes ID';

	return $headers;
}

function app_updater_init() {

	require_once( dirname( __FILE__ ) . '/updater-class.php' );
	require_once( dirname( __FILE__ ) . '/updater-ui.php' );

	new APP_Theme_Upgrader;
	new APP_Plugin_Upgrader;
	new APP_Upgrader_Regular;
}

if ( is_admin() ) {
	app_updater_init();

	add_filter( 'extra_plugin_headers', 'app_extra_headers' );
	add_filter( 'extra_theme_headers', 'app_extra_headers' );

	add_action( 'load-update-core.php', 'app_updater_activate' );
	remove_action( 'load-update-core.php', 'wp_update_plugins' );
	remove_action( 'load-update-core.php', 'wp_update_themes' );

	register_activation_hook( __FILE__, 'app_updater_activate' );
}


/**
 * Adds plugin action links.
 *
 * @since 1.4.0
 */
function app_updater_action_links( $links ) {

	$plugin_links = array(
		'<a href="' . esc_url( get_admin_url( null, 'options-general.php?page=appthemes-key-config' ) ) . '">' . __( 'Settings', 'appthemes-updater' ) . '</a>',
		'<a href="https://docs.appthemes.com/tutorials/installing-the-appthemes-updater-plugin/" target="_blank">' . __( 'Docs', 'appthemes-updater' ) . '</a>',
		'<a href="http://forums.appthemes.com/appthemes-updater-plugin/" target="_blank">' . __( 'Support', 'appthemes-updater' ) . '</a>',
	);

	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'app_updater_action_links' );
