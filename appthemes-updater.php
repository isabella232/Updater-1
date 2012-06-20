<?php
/*
Plugin Name: AppThemes Updater
Description: Allows customers automatically update AppThemes Products.

Version: dev
Author: AppThemes
Author URI: http://appthemes.com
*/

if( is_admin() ){
	require dirname( __FILE__ ) . '/updater-class.php';
	APP_Updater::init();
}