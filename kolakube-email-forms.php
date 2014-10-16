<?php
/**
 * Plugin Name: Kolakube Email Forms
 * Plugin URI: http://kolakube.com/#URLCOMINGSOON
 * Description: Easily connect to an email service like AWeber or MailChimp to display email signup forms throughout your website with a simple widget.
 * Version: 1.0.0
 * Author: Alex Mangini
 * Author URI: http://kolakube.com/about/
 * Author email: alex@kolakube.com
 * License: GPL2
 * Requires at least: 3.8
 * Tested up to: 4.0
 * Text Domain: kol-email-forms
 * Domain Path: /languages/
 *
 * This plugin is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, see http://www.gnu.org/licenses/.
 *
 * Just do what makes you happy.
 *
 * @package     KolakubeEmailForms
 * @copyright   Copyright (c) 2014, Alex Mangini
 * @license     GPL-2.0+
 * @link        http://kolakube.com/
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class kol_email_init {

	public $strings;
	public $api;
	public $admin;

	public function init() {
		$this->constants();
		$this->strings = $this->strings();

		load_plugin_textdomain( 'kol-email-forms', false, KOL_EMAIL_DIR . 'languages/' );

		require_once( KOL_EMAIL_DIR . 'api/api.php' );
		require_once( KOL_EMAIL_DIR . 'admin/admin-page.php' );
		require_once( KOL_EMAIL_DIR . 'widget.php' );

		$this->api   = new kol_email_api;
		$this->admin = new kol_email_admin;

		add_action( 'widgets_init', array( $this, 'widgets' ) );
	}


	private function constants() {
		define( 'KOL_EMAIL_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'KOL_EMAIL_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
	}


	public function widgets() {
		register_widget( 'kol_email_form' );
	}


	public function strings() {
		return array(
			'name'                         => __( 'Kolakube Email Forms', 'kol-email-forms' ),
			'menu_title'                   => __( 'Email Service Setup', 'kol-email-forms' ),
			'before_use'                   => sprintf( __( 'To use the Kolakube Email Form widget, first <a href="%s">connect to an email service</a>.', 'kol-email-forms' ), admin_url( 'tools.php?page=kol-email-forms' ) ),
			'select_list'                  => __( 'Select a List&hellip;', 'kol-email-forms' ),
			'select_page'                  => __( 'Select a Page&hellip;', 'kol-email-forms' ),
			'step1'                        => __( 'Step 1: Connect to Your Email Service', 'kol-email-forms' ),
			'step2'                        => __( 'Step 2: Get Your Authorization Code', 'kol-email-forms' ),
			'connect_notice'               => __( 'Before you can insert any email forms to your site, you need to connect to your email service provider below. If yours isn\'t listed in the dropdown, select "Custom Form Code" for further instructions.', 'kol-email-forms' ),
			'select_service'               => __( 'Select an Email Service&hellip;', 'kol-email-forms' ),
			'mailchimp'                    => __( 'MailChimp', 'kol-email-forms' ),
			'aweber'                       => __( 'AWeber', 'kol-email-forms' ),
			'custom_code'                  => __( 'Custom HTML Form Code', 'kol-email-forms' ),
			'custom_code_activate'         => __( 'To activate Custom HTML Forms, click the save button below.', 'kol-email-forms' ),
			'custom_code_activated'        => __( 'Custom HTML Forms are activated.', 'kol-email-forms' ),
			'custom_code_activated_notice' => sprintf( __( 'You can now use Kolakube products like the <a href="%s">Email Page Lead</a> and the <a href="%2s">email widget</a> to display email forms throughout your website.', 'kol-email-forms' ), admin_url( 'themes.php?page=page_leads&tab=email' ), admin_url( 'widgets.php' ) ),
			'auth_code_error'              => __( 'Could not connect to your email service. Your Authorization code may be incorrect. Reload the page to try again.', 'kol-email-forms' ),
			'get_auth_code'                => __( 'Get Authorization Code', 'kol-email-forms' ),
			'get_auth_code_notice'         => __( 'Click the "Get Authorization Code" button above and login to your account and get your authorization code. Once you have it, copy it and paste it into the text field below and click "Connect."', 'kol-email-forms' ),
			'connect'                      => __( 'Connect', 'kol-email-forms' ),
			'disconnect'                   => __( 'Disconnect', 'kol-email-forms' ),
			'disconnect_notice'            => __( 'Disconnecting your email service will set your form settings back to default. All email forms on your website will be deleted until you reconnect to an email service. Are you sure you want to continue?', 'kol-email-forms' ),
			'connected_to'                 => __( 'You are connected to', 'kol-email-forms' ),
			'connected_notice'             => sprintf( __( 'You can now use Kolakube products like the <a href="%s">Email Page Lead</a> and the <a href="%2s">email widget</a> to display email forms throughout your website.', 'kol-email-forms' ), admin_url( 'themes.php?page=page_leads&tab=email' ), admin_url( 'widgets.php' ) ),
			'nonce_error'                  => __( 'Sorry, your nonce did not verify.', 'kol-email-forms' ),
			'widget_name'                  => __( 'Kolakube Email Signup Form', 'kol-email-forms' ),
			'widget_description'           => __( 'Easily place email signup forms throughout your website.', 'kol-email-forms' ),
			'widget_title'                 => __( 'Title', 'kol-email-forms' ),
			'widget_desc'                  => __( 'Description', 'kol-email-forms' ),
			'widget_custom_code'           => __( 'Custom HTML Code', 'kol-email-forms' ),
			'widget_list'                  => __( 'Email List', 'kol-email-forms' ),
			'widget_field_name'            => __( 'Ask for subscribers name in signup form', 'kol-email-forms' ),
			'widget_name_label'            => __( 'Name Field Label', 'kol-email-forms' ),
			'widget_email_label'           => __( 'Email Field Label', 'kol-email-forms' ),
			'widget_button_text'           => __( 'Submit Button Text', 'kol-email-forms' ),
			'widget_form_id'               => __( 'Form ID', 'kol-email-forms' ),
			'widget_ad_tracking'           => __( 'Ad Tracking', 'kol-email-forms' ),
			'widget_image_id'              => __( 'Image ID', 'kol-email-forms' ),
			'widget_classes'               => __( 'HTML Classes', 'kol-email-forms' ),
			'thank_you_page'               => __( 'Thank You Page', 'kol-email-forms' ),
			'already_subs_page'            => __( 'Already Subscribed Page', 'kol-email-forms' ),
			'name_label'                   => __( 'Enter your name&hellip;', 'kol-email-forms' ),
			'email_label'                  => __( 'Enter your email&hellip;', 'kol-email-forms' ),
			'button_text'                  => __( 'Get Instant Access', 'kol-email-forms' ),
			'input_fields'                 => __( 'Input Fields', 'kol-email-forms' ),
			'tracking_management'          => __( 'Tracking &amp; Management', 'kol-email-forms' ),
			'advanced'                     => __( 'Advanced', 'kol-email-forms' )
		);
	}

}

$kol_email = new kol_email_init; // hat tip to CP
$kol_email->init();
