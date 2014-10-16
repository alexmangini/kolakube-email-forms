<?php
/**
 * Kolakube Email Forms API
 *
 * @package     KolakubeEmailForms
 * @copyright   Copyright (c) 2014, Alex Mangini
 * @license     GPL-2.0+
 * @link        http://kolakube.com/
 * @since       1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class kol_email_api {

	public $auth = array(
		'aweber'    => 'https://auth.aweber.com/1.0/oauth/authorize_app/e5957609',
		'mailchimp' => 'http://admin.mailchimp.com/account/api-key-popup/'
	);


	public function __construct() {
		global $kol_email;

		$this->_id         = 'kol_email';
		$this->_get_option = get_option( $this->_id );

		add_action( 'admin_print_footer_scripts', array( $this, 'admin_footer_scripts' ) );

		add_action( 'wp_ajax_connect', array( $this, 'connect' ) );
		add_action( 'wp_ajax_disconnect', array( $this, 'disconnect' ) );
	}


	public function admin_footer_scripts() {
		global $kol_email;

		if ( get_current_screen()->base != $kol_email->admin->_add_page )
			return;

		$data = get_option( 'kol_email_data' );

		if ( isset( $data['lists_options'] ) )
			$this->connect_scripts();
		else
			$this->disconnect_scripts();
	}


	public function connect_scripts() {
		global $kol_email;
	?>

		<script>

			jQuery( document ).ready( function( $ ) {

				$( '#kol-email-disconnect' ).on( 'click', function( e ) {

					e.preventDefault();

					if ( ! confirm( '<?php echo $kol_email->strings['disconnect_notice']; ?>' ) )
						return;

					$( '#kol-email-disconnect' ).prop( 'disabled', true );

					$.post( ajaxurl, { action: 'disconnect', form: $( '#kol-email-form' ).serialize() }, function( disconnected ) {
						$( '#kol-email-list' ).html( disconnected );
					});

				});

			});

		</script>

	<?php }


	public function disconnect_scripts() { ?>

		<script>

			jQuery( document ).ready( function( $ ) {

				$( '#kol-email-connect' ).on( 'click', function( e ) {

					e.preventDefault();

					$( '#kol-email-connect' ).prop( 'disabled', true );

					$.post( ajaxurl, { action: 'connect', form: $( '#kol-email-form' ).serialize() }, function( connected ) {
						$( '#kol-email-list' ).html( connected );
					});

				});

			});

			// Show API key input

			( function() {
				document.getElementById( 'kol_email_service' ).onchange = function( e ) {
					document.getElementById( 'kol-email-custom' ).style.display = this.value === 'custom_code' ? 'block' : 'none';
					document.getElementById( 'kol-email-key' ).style.display    = this.value == 'aweber' || this.value == 'mailchimp' ? 'block' : 'none';
					document.getElementById( 'kol-email-auth' ).href            = this.value == 'aweber' ? '<?php echo $this->auth['aweber']; ?>' : '<?php echo $this->auth['mailchimp']; ?>';
				}
			})();

		</script>

	<?php }


	public function connect() {
		global $kol_email;

		parse_str( stripslashes( $_POST['form'] ), $form );

		$api_key = $form[$this->_id]['api_key'];

		if ( ! wp_verify_nonce( $form['_wpnonce'], "{$this->_id}-options" ) )
			die ( $kol_email->strings['nonce_error'] );

		$service = $form[$this->_id]['service'];

		$data         = array();
		$data['save'] = array(
			'form_fields' => array(
				'type'    => 'checkbox',
				'options' => array( 'name' )
			),
			'form_style' => array(
				'type'    => 'checkbox',
				'options' => array( 'full' )
			),
			'name_label' => array(
				'type' => 'text'
			),
			'email_label' => array(
				'type' => 'text'
			),
			'button_text' => array(
				'type' => 'text'
			)
		);

		if ( ! empty( $api_key ) )
			if ( $service == 'aweber' )
				$this->aweber_connect( $api_key, $data );
			elseif ( $service == 'mailchimp' )
				$this->mailchimp_connect( $api_key, $data );

		$this->_get_option['service'] = $service;

		update_option( $this->_id, $this->_get_option );

		wp_cache_flush();

		$this->connect_scripts();
		$kol_email->admin->admin_fields();

		exit;
	}


	public function disconnect() {
		global $kol_email;

		$this->_get_option['service'] = '';
		delete_option( $this->_id );
		delete_option( 'kol_email_data' );

		if ( class_exists( 'page_leads_email' ) ) { # email page lead
			$lead = get_option( 'page_leads_email' );
			unset( $lead['list'] );

			update_option( 'page_leads_email', $lead );
			delete_post_meta_by_key( 'page_leads_email_list' );
		}

		$this->disconnect_scripts();
		$kol_email->admin->admin_fields();

		exit;
	}


	public function mailchimp_connect( $api_key, $data ) {
		include_once( KOL_EMAIL_DIR . 'api/services/mailchimp.php' );

		$api       = new kol_email_mailchimp( $api_key );
		$get_lists = $api->lists();

		if ( ! is_array( $get_lists['data'] ) )
			$this->error();

		foreach ( $get_lists['data'] as $list ) {
			$id   = esc_attr( $list['id'] );
			$name = esc_attr( $list['name'] );

			$data['lists_ids'][]        = $id;
			$data['lists_options'][$id] = $name;
			$data['lists_data'][$id]    = array(
				'name' => $name,
				'url'  => esc_url_raw( $list['subscribe_url_long'] )
			);
		}

		$data['save'] = array_merge( array(
			'list' => array(
				'type'    => 'select',
				'options' => $data['lists_ids']
			)
		), $data['save'] );

		update_option( 'kol_email_data', $data );
	}


	public function aweber_connect( $api_key, $data ) {
		require_once( KOL_EMAIL_DIR . 'api/services/vendor/aweber_api/aweber_api.php' );

		$keys = array();

		try {
			list( $keys['consumer_key'], $keys['consumer_secret'], $keys['access_key'], $keys['access_secret'] ) = AWeberAPI::getDataFromAweberID( $api_key );
		}
		catch( AWeberAPIException $e ) {
			$this->error();
		}

		$aweber  = new AWeberAPI( $keys['consumer_key'], $keys['consumer_secret'] );
		$account = $aweber->getAccount( $keys['access_key'], $keys['access_secret'] );

		foreach ( $account->lists->data['entries'] as $list ) {
			$id = $list['unique_list_id'];

			$data['lists_ids'][]        = esc_attr( $id );
			$data['lists_options'][$id] = esc_attr( $list['name'] );
		}

		foreach ( get_pages() as $p )
			$pages[] = $p->ID;

		$data['save'] = array_merge(
			array(
				'list' => array(
					'type'    => 'select',
					'options' => $data['lists_ids']
				),
				'thank_you' => array(
					'type'    => 'select',
					'options' => $pages
				),
				'already_subscribed' => array(
					'type'    => 'select',
					'options' => $pages
				),
				'form_id' => array(
					'type' => 'text'
				),
				'ad_tracking' => array(
					'type' => 'text'
				),
				'tracking_image' => array(
					'type' => 'text'
				)
			),
			$data['save']
		);

		update_option( 'kol_email_data', $data );
	}


	public function error() {
		global $kol_email;

		wp_die( $kol_email->strings['auth_code_error'] );
	}

}
