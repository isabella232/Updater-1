<?php
/*
Plugin Name: AppThemes Updater
Description: Allows customers to automatically update AppThemes Products.
Version: 1.0-beta
Author: AppThemes
Author URI: http://appthemes.com
*/

if ( is_admin() ) {
	require dirname( __FILE__ ) . '/updater-class.php';
	require dirname( __FILE__ ) . '/updater-ui.php';

	APP_Upgrader::init();
	APP_Upgrader_UI::init();
}
