<?php
/*
Plugin Name: AppThemes Updater
Description: Allows customers to automatically update AppThemes Products.
Version: 1.0-beta
Author: AppThemes
Author URI: http://appthemes.com
AppThemes ID: appthemes-updater
*/

if ( is_admin() ) {
	require dirname( __FILE__ ) . '/updater-class.php';
	require dirname( __FILE__ ) . '/updater-ui.php';

	new APP_Theme_Upgrader;
	new APP_Plugin_Upgrader;

	APP_Upgrader_UI::init();
}

