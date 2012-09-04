<?php

abstract class APP_Upgrader_UI {

	abstract function init_page();
	abstract protected function can_set_key();
	abstract protected function get_admin_url();

	function show_notice() {
		if ( APP_Upgrader::get_key() )
			return;

		if ( !$this->can_set_key() )
			return;

		echo "<div id='app-updater-warning' class='updated fade'><p>"
			. "<strong>" . __( 'The AppThemes Updater is almost ready.', 'app-updater' ) . "</strong> "
			. sprintf(
				__( 'You must <a href="%1$s">enter your AppThemes API key</a> for it to work.', 'app-updater' ),
				$this->get_admin_url()
			)
			. "</p></div>";
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


class APP_Upgrader_Regular extends APP_Upgrader_UI {

	function __construct() {
		add_action( 'admin_notices', array( $this, 'show_notice' ) );

		add_action( 'admin_menu', array( $this, 'init_page' ) );
	}

	protected function can_set_key() {
		return current_user_can( 'manage_options' );
	}

	protected function get_admin_url() {
		return admin_url( 'admin.php?page=appthemes-key-config' );
	}

	function init_page() {
		add_submenu_page(
			'plugins.php',
			__( 'AppThemes Updater Configuration', 'appthemes-updater' ),
			__( 'AppThemes Updater', 'appthemes-updater' ),
			'manage_options',
			'appthemes-key-config',
			array( $this, 'render_page' )
		);
	}
}

class APP_Upgrader_Network extends APP_Upgrader_UI {

	function __construct() {
		add_action( 'all_admin_notices', array( $this, 'show_notice' ) );

		add_action( 'network_admin_menu', array( $this, 'init_page' ) );
	}

	protected function can_set_key() {
		return is_super_admin();
	}

	protected function get_admin_url() {
		return network_admin_url( 'settings.php?page=appthemes-key-config' );
	}

	function init_page() {
		add_submenu_page(
			'settings.php',
			__( 'AppThemes Updater Configuration', 'appthemes-updater' ),
			__( 'AppThemes Updater', 'appthemes-updater' ),
			'manage_options',
			'appthemes-key-config',
			array( $this, 'render_page' )
		);
	}
}

