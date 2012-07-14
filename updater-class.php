<?php

class APP_Upgrader {
	const WP_URL = 'http://api.wordpress.org/themes/update-check/';
	const APP_URL = 'http://api.appthemes.com/themes/update-check/2.0/';

	private static $themes;

	function get_key() {
		if ( defined( 'APPTHEMES_API_KEY' ) )
			return APPTHEMES_API_KEY;

		return get_site_option( 'appthemes_api_key' );
	}

	function set_key( $key ) {
		update_site_option( 'appthemes_api_key', $key );
	}

	function init() {
		if ( !self::get_key() )
			return;

		add_action( 'init', array( __CLASS__, 'disable_old_updater' ) );

		add_filter( 'http_request_args', array( __CLASS__, 'exclude_themes' ), 10, 2 );
		add_filter( 'http_response', array( __CLASS__, 'alter_update_requests' ), 10, 3 );
		add_action( 'all_admin_notices', array( __CLASS__, 'display_warning' ) );
	}

	function disable_old_updater() {
		remove_filter( 'http_request_args', array( 'APP_Updater', 'exclude_themes' ), 10, 2 );
		remove_filter( 'http_response', array( 'APP_Updater', 'alter_update_requests' ), 10, 3 );
		remove_action( 'all_admin_notices', array( 'APP_Updater', 'display_warning' ) );
	}

	function exclude_themes( $r, $url ) {
		if ( 0 === strpos( $url, self::WP_URL ) ) {
			$themes = unserialize( $r['body']['themes'] );

			self::$themes['current_theme'] = $themes['current_theme'];

			foreach ( array( 'vantage', 'qualitycontrol', 'classipress', 'clipper', 'jobroller' ) as $name ) {
				if ( !isset( $themes[ $name ] ) )
					continue;

				self::$themes[ $name ] = $themes[ $name ];
				unset( $themes[ $name ] );
			}

			$r['body']['themes'] = serialize( $themes );
		}

		return $r;
	}

	function alter_update_requests( $response, $args, $url ) {
		if ( 0 === strpos( $url, self::WP_URL ) ) {

			$our_updates = self::check_for_updates( $args );

			if ( $our_updates ) {
				$response['body'] = serialize( array_merge(
					unserialize( $response['body'] ),
					unserialize( $our_updates )
				) );
			}
		}

		return $response;
	}

	protected function check_for_updates( $args ) {
		$args['body'] = array(
			'themes' => self::$themes,
			'api_key' => self::get_key()
		);

		$raw_response = wp_remote_post( self::APP_URL, $args );

		/* debug_k($raw_response); */

		if ( is_wp_error( $raw_response ) || 200 != wp_remote_retrieve_response_code( $raw_response ) )
			return false;

		return wp_remote_retrieve_body( $raw_response );
	}

	function display_warning() {
		static $themes_update;

		if ( !current_user_can( 'update_themes' ) )
			return;

		if ( !isset( $themes_update ) )
			$themes_update = get_site_transient( 'update_themes' );

		$stylesheet = get_stylesheet();
		if ( isset( $themes_update->response[ $stylesheet ] ) ) {
			global $pagenow;

			if ( in_array( $pagenow, array( 'themes.php', 'update-core.php' ) ) ) : ?>
				<div id="message" class="error">
					<p><?php echo sprintf( __( '<strong>IMPORTANT</strong>: If you have made any modifications to the AppThemes files, they will be overwritten if you proceed with the automatic update. Those with modified theme files should do a manual update instead. Visit your <a href="%1$s" target="_blank">customer dashboard</a> to download the latest version.', 'appthemes' ), 'http://www.appthemes.com/cp/member.php' ); ?></p>
				</div>
<?php endif;
		}
	}
}
