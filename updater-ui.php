<?php

class APP_Upgrader_UI {

	function init() {
		add_action( 'all_admin_notices', array( __CLASS__, 'show_notice' ) );
		add_action( 'admin_menu', array( __CLASS__, 'init_page' ) );
	}

	function show_notice() {
		if ( APP_Upgrader::get_key() )
			return;

		echo "<div id='app-updater-warning' class='updated fade'><p>"
			. "<strong>" . __( 'The AppThemes Updater is almost ready.', 'app-updater' ) . "</strong> "
			. sprintf( __('You must <a href="%1$s">enter your AppThemes API key</a> for it to work.'), "admin.php?page=appthemes-key-config", 'app-updater' )
			. "</p></div>";
	}

	function init_page() {
		// TODO: add to the network site
		add_submenu_page(
			'plugins.php',
			__( 'AppThemes Updater Configuration', 'at-updater' ),
			__( 'AppThemes Updater', 'at-updater' ),
			'manage_options',
			'appthemes-key-config',
			array( __CLASS__, 'render_page' )
		);
	}

	function render_page() {
		if ( isset( $_POST['appthemes_submit'] ) ) {
			APP_Upgrader::set_key( trim( $_POST['appthemes_key'] ) );

			echo "
				<div class='updated fade'><p>"
				. __( 'Saved Changes.', 'app-updater' )
				. "</p></div>";
		}

		include dirname(__FILE__) . '/templates/admin-page.php';
	}
}

