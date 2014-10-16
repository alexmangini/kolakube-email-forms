<?php
/**
 * Kolakube Email Forms Admin Page
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

class kol_email_admin {

	public $_id;
	public $_add_page;
	public $_name;

	public function __construct() {
		$this->_id         = 'kol_email';
		$this->_get_option = get_option( $this->_id );

		add_action( 'admin_init', array( $this, 'register_setting' ) );
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}


	public function add_menu() {
		global $kol_email;

		$this->_add_page = add_submenu_page(
			'tools.php',
			$kol_email->strings['name'],
			$kol_email->strings['menu_title'],
			'manage_options',
			'kol-email-forms',
			array( $this, 'admin_page' )
		);
	}


	public function enqueue() {
		if ( get_current_screen()->base == $this->_add_page )
			wp_enqueue_style( 'kol-email-style', KOL_EMAIL_URL . 'css/admin-page.css' );
	}


	public function register_setting() {
		register_setting( $this->_id, $this->_id, array( $this, 'save' ) );
	}


	public function save( $input ) {
		$input['service'] = in_array( $input['service'], array( 'custom_code', 'mailchimp', 'aweber' ) ) ? $input['service'] : '';
		$input['api_key'] = '';

		return $input;
	}


	public function admin_page() {
		global $kol_email;
	?>

		<div class="wrap kol">

			<h2><?php echo $kol_email->strings['name']; ?></h2>

			<hr class="kol-hr" />

			<form id="kol-email-form" method="post" action="options.php">

				<?php settings_fields( $this->_id ); ?>

				<?php $this->admin_fields(); ?>

			</form>

		</div>

	<?php }


	public function admin_fields() {
		global $kol_email;

		$email = get_option( 'kol_email' ); #api
		$data  = get_option( 'kol_email_data' ); #api

		$lists   = isset( $data['lists_options'] ) ? $data['lists_options'] : '';
		$service = $email['service'];

		if ( $service == 'aweber' )
			$connected = $kol_email->strings['aweber'];
		elseif ( $service == 'mailchimp' )
			$connected = $kol_email->strings['mailchimp'];
	?>

		<div id="kol-email-list">

			<?php if ( ! empty( $lists ) ) : ?>

				<!-- Connection Success -->

				<p><i class="dashicons dashicons-yes kol-icon-yes"></i> <?php echo $kol_email->strings['connected_to'] . " $connected"; ?></p>

				<p><?php echo $kol_email->strings['connected_notice']; ?></p>

				<p><input type="submit" id="kol-email-disconnect" class="button" value="<?php esc_attr_e( $kol_email->strings['disconnect'] ); ?>" /></p>

			<?php else :
				$custom      = $service == 'custom_code' ? true : false;
				$custom_icon = $custom ? 'yes' : 'no';

				$services = array(
					''            => $kol_email->strings['select_service'],
					'mailchimp'   => $kol_email->strings['mailchimp'],
					'aweber'      => $kol_email->strings['aweber'],
					'custom_code' => $kol_email->strings['custom_code']
				);

				$api_key = isset( $this->_get_option['api_key'] ) ? $this->_get_option['api_key'] : '';
			?>

				<!-- Step 1 -->

				<div class="kol-email-setup" style="display: <?php echo empty( $lists ) ? 'block' : 'none'; ?>;">

					<!-- Select Service -->

					<div id="kol-email-service" class="kol-content-wrap">

						<h3><?php echo $kol_email->strings['step1']; ?></h3>

						<p><?php echo $kol_email->strings['connect_notice']; ?></p>

						<select name="kol_email[service]" id="kol_email_service">
							<?php foreach ( $services as $val => $label ) : ?>
								<option value="<?php esc_attr_e( $val ); ?>"><?php esc_html_e( $label ); ?></option>
							<?php endforeach; ?>
						</select>

					</div>

					<!-- Enter Key -->

					<div id="kol-email-key" style="display: none;">

						<hr class="kol-hr" />

						<div class="kol-content-wrap">

							<h3><?php echo $kol_email->strings['step2']; ?></h3>

							<p><a href="#" id="kol-email-auth" class="button" target="_blank"><?php echo $kol_email->strings['get_auth_code']; ?></a></p>

							<p class="description"><?php echo $kol_email->strings['get_auth_code_notice']; ?></p>

						</div>

						<p><textarea name="kol_email[api_key]" id="kol_email_api_key" class="large-text" rows="6"><?php echo esc_textarea( $api_key ); ?></textarea></p>

						<p><input type="submit" id="kol-email-connect" class="button-primary" value="<?php esc_attr_e( $kol_email->strings['connect'] ); ?>" /></p>

					</div>

					<!-- Custom HTML Form -->

					<div id="kol-email-custom" style="display: <?php echo $service == 'custom_code' ? 'block' : 'none'; ?>;">

						<hr class="kol-hr" />

						<p>
							<i class="dashicons dashicons-<?php echo $custom_icon; ?> kol-icon-<?php echo $custom_icon; ?>"></i>
							<?php if ( $custom ) : ?>
								<?php echo $kol_email->strings['custom_code_activated']; ?>
							<?php else : ?>
								<?php echo $kol_email->strings['custom_code_activate']; ?>
							<?php endif; ?>
						</p>

						<?php if ( $custom ) : ?>
							<p><?php echo $kol_email->strings['custom_code_activated_notice']; ?></p>
						<?php endif; ?>

						<?php if ( $service == 'custom_code' ) : ?>
							<?php update_option( 'kol_email_data', array(
								'save' => array(
									'custom_code' => array(
										'type' => 'code'
									)
								)
							) ); ?>
						<?php endif; ?>

						<?php submit_button(); ?>

					</div>

				</div>

			<?php endif; ?>

		</div>

	<?php }
}
