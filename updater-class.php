<?php

abstract class APP_Upgrader {
	protected $wp_url;
	protected $app_url;

	protected $items = array();

	static function get_key() {
		if ( defined( 'APPTHEMES_API_KEY' ) )
			return APPTHEMES_API_KEY;

		return get_site_option( 'appthemes_api_key' );
	}

	static function set_key( $key ) {
		update_site_option( 'appthemes_api_key', $key );
	}

	static function disable_old_updater() {
		remove_filter( 'http_request_args', array( 'APP_Updater', 'exclude_themes' ), 10, 2 );
		remove_filter( 'http_response', array( 'APP_Updater', 'alter_update_requests' ), 10, 3 );
		remove_action( 'all_admin_notices', array( 'APP_Updater', 'display_warning' ) );
	}

	function __construct() {
		if ( !self::get_key() )
			return;

		add_action( 'init', array( __CLASS__, 'disable_old_updater' ) );

		add_filter( 'http_request_args', array( $this, 'exclude_items' ), 10, 2 );
		add_filter( 'http_response', array( $this, 'alter_update_requests' ), 10, 3 );
	}

	abstract function exclude_items( $r, $url );

	abstract protected function check_for_updates( $args );

	function alter_update_requests( $response, $args, $url ) {
		if ( 0 === strpos( $url, $this->wp_url ) ) {

			$our_updates = $this->check_for_updates( $args );

			if ( $our_updates ) {
				$response['body'] = serialize( array_merge(
					unserialize( $response['body'] ),
					$our_updates
				) );
			}
		}

		return $response;
	}

	protected function append_api_key( $items ) {
		foreach ( $items as &$item ) {
			$item['package'] = add_query_arg( 'api_key', self::get_key(), $item['package'] );
		}

		return $items;
	}
}


class APP_Theme_Upgrader extends APP_Upgrader {

	protected $wp_url = 'http://api.wordpress.org/themes/update-check/';
	protected $app_url = 'http://api.appthemes.com/themes/update-check/2.0/';

	protected $items;

	public static $instance;

	public function __construct() {
		parent::__construct();

		add_action( 'all_admin_notices', array( __CLASS__, 'display_warning' ) );

		self::$instance = $this;
	}

	function exclude_items( $r, $url ) {
		if ( 0 === strpos( $url, $this->wp_url ) ) {
			$themes = unserialize( $r['body']['themes'] );

			$this->items['current_theme'] = $themes['current_theme'];

			foreach ( array( 'vantage', 'qualitycontrol', 'classipress', 'clipper', 'jobroller' ) as $name ) {
				if ( !isset( $themes[ $name ] ) )
					continue;

				$this->items[ $name ] = $themes[ $name ];
				unset( $themes[ $name ] );
			}

			$r['body']['themes'] = serialize( $themes );
		}

		return $r;
	}

	protected function check_for_updates( $args ) {
		if ( empty( $this->items ) )
			return false;

		$args['body'] = array(
			'themes' => $this->items,
			'api_key' => APP_Upgrader::get_key()
		);

		$raw_response = wp_remote_post( $this->app_url, $args );

		// DEBUG
		/* debug_k($raw_response); */

		if ( is_wp_error( $raw_response ) || 200 != wp_remote_retrieve_response_code( $raw_response ) )
			return false;

		$body = unserialize( wp_remote_retrieve_body( $raw_response ) );
		if ( !$body )
			return false;

		return parent::append_api_key( $body );
	}

	function display_warning() {
		global $pagenow;

		if ( !in_array( $pagenow, array( 'themes.php', 'update-core.php' ) ) )
			return;

		if ( !current_user_can( 'update_themes' ) )
			return;

		$themes_update = get_site_transient( 'update_themes' );

		$stylesheet = get_stylesheet();

		if ( isset( $themes_update->response[ $stylesheet ] ) ) {
?>
				<div id="message" class="error">
					<p><?php echo sprintf( __( '<strong>IMPORTANT</strong>: If you have made any modifications to the AppThemes files, they will be overwritten if you proceed with the automatic update. Those with modified theme files should do a manual update instead. Visit your <a href="%1$s" target="_blank">customer dashboard</a> to download the latest version.', 'appthemes' ), 'http://www.appthemes.com/cp/member.php' ); ?></p>
				</div>
<?php
		}
	}
}

