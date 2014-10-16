<?php
/**
 * Kolakube Email Forms Widget
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

class kol_email_form extends WP_Widget {

	public $_allowed_html = array(
		'a' => array(
			'href'   => array(),
			'class'  => array(),
			'id'     => array()
		),
		'span' => array(
			'class'  => array(),
			'id'     => array()
		),
		'img' => array(
			'src'    => array(),
			'alt'    => array(),
			'height' => array(),
			'width'  => array(),
			'class'  => array(),
			'id'     => array()
		),
		'br' => array(),
		'b'  => array(),
		'i'  => array()
	);


	public function __construct() {
		global $kol_email;

		$this->WP_Widget(
			'kol_email',
			$kol_email->strings['widget_name'],
			array( 'description' => $kol_email->strings['widget_description']
		) );

		$this->email      = get_option( 'kol_email' ); #api
		$this->email_data = get_option( 'kol_email_data' ); #api
	}


	public function widget( $args, $val ) {
		global $kol_email;

		$service = $this->email['service'];

		$title = $val['title'];
		$desc  = $val['desc'];
		$list  = $val['list'];
	?>

		<?php echo $args['before_widget']; ?>

			<!-- Intro -->

			<?php if ( $title || $desc ) : ?>

				<div class="kol-email-intro mb-single">

					<?php if ( $title ) : ?>
						<?php echo $args['before_title'] . $title . $args['after_title']; ?>
					<?php endif; ?>

					<?php if ( $desc ) : ?>
						<?php echo wpautop( $desc ); ?>
					<?php endif; ?>

				</div>

			<?php endif; ?>

			<!-- Email Form -->

			<?php if ( $service == 'custom_code' ) : ?>

				<div class="kol-email-form clear">

					<?php echo $val['custom_code']; ?>

				</div>

			<?php elseif ( $list && $this->email_data && in_array( $list, $this->email_data['lists_ids'] ) ) :
				$field_name = $val['form_fields_name'];

				$label_name  = ! empty( $val['name_label'] ) ? esc_attr( $val['name_label'] ) : $kol_email->strings['name_label'];
				$label_email = ! empty( $val['email_label'] ) ? esc_attr( $val['email_label'] ) : $kol_email->strings['email_label'];
				$button_text = ! empty( $val['button_text'] ) ? esc_attr( $val['button_text'] ) : $kol_email->strings['button_text'];

				$classes = ! empty( $val['classes'] ) ? esc_attr( $val['classes'] ) : 'form-full';

				if ( $service == 'aweber' ) {
					$action    = 'http://www.aweber.com/scripts/addlead.pl';
					$att_name  = 'name';
					$att_email = 'email';

					$image        = $val['tracking_image'];
					$form_id      = $val['form_id'];
					$thank_you    = $val['thank_you'];
					$already_subs = $val['already_subscribed'];
					$ad_tracking  = $val['ad_tracking'];
				}
				elseif ( $service == 'mailchimp' ) {
					$data = get_option( 'kol_email_data' );
					$lists      = $data['lists_data'];
					$lists_data = parse_url( $lists[$list]['url'] ); // convert URL params to array
					parse_str( $lists_data['query'] ); // convert query params to string vars (creates variables $u and $id)

					$action    = esc_url_raw( $lists_data['scheme'] . '://' . $lists_data['host'] . '/subscribe/post/' );
					$att_name  = 'MERGE1';
					$att_email = 'MERGE0';
				}
			?>

				<div class="kol-email-form clear">

					<form method="post" action="<?php esc_attr_e( $action ); ?>" class="<?php echo $classes; ?>">

						<?php if ( $service == 'aweber' ) : ?>

							<?php if ( ! empty( $form_id ) ) : ?>
								<input type="hidden" name="meta_web_form_id" value="<?php esc_attr_e( $form_id ); ?>" />
							<?php endif; ?>
							<input type="hidden" name="meta_split_id" value="" />
							<input type="hidden" name="listname" value="<?php esc_attr_e( $list ); ?>" />
							<?php if ( ! empty( $thank_you ) ) : ?>
								<input type="hidden" name="redirect" value="<?php echo get_permalink( $thank_you ); ?>" />
							<?php endif; ?>
							<?php if ( ! empty( $already_subs ) ) : ?>
								<input type="hidden" name="meta_redirect_onlist" value="<?php echo get_permalink( $already_subs ); ?>" />
							<?php endif; ?>
							<?php if ( ! empty( $ad_tracking ) ) : ?>
								<input type="hidden" name="meta_adtracking" value="<?php esc_attr_e( $ad_tracking ); ?>" />
							<?php endif; ?>
							<input type="hidden" name="meta_message" value="1" />
							<input type="hidden" name="meta_required" value="<?php echo $field_name ? "$att_name,$att_email" : $att_email; ?>" />
							<input type="hidden" name="meta_tooltip" value="" />

						<?php endif; ?>

						<?php if ( $field_name ) : ?>
							<input type="text" name="<?php esc_attr_e( $att_name ); ?>" id="kol-email-field-name" class="kol-email-field-name form-input icon-name" placeholder="<?php echo $label_name; ?>" />
						<?php endif; ?>

						<input type="email" name="<?php esc_attr_e( $att_email ); ?>" id="kol-email-field-email" placeholder="<?php echo $label_email; ?>" class="kol-email-field-name form-input icon-email" />

						<?php if ( $service == 'aweber' && ! empty( $image ) ) : ?>
							<img src="http://forms.aweber.com/form/displays.htm?id=<?php esc_attr_e( $image ); ?>" style="display: none;" alt="" />
						<?php endif; ?>

						<?php if ( $service == 'mailchimp' ) : ?>
							<input type="hidden" name="u" value="<?php esc_attr_e( $u ); ?>">
							<input type="hidden" name="id" value="<?php esc_attr_e( $id ); ?>">
						<?php endif; ?>

						<button class="kol-email-field-submit form-submit"><?php echo $button_text; ?></button>

					</form>

				</div>

			<?php endif; ?>

		<?php echo $args['after_widget']; ?>

	<?php }


	public function update( $new, $val ) {
		$val['title'] = wp_kses( $new['title'], $this->_allowed_html );

		$val['list'] = in_array( $new['list'], $this->email_data['lists_ids'] ) ? $new['list'] : '';

		$val['form_fields_name'] = $new['form_fields_name'] ? 1 : 0;

		foreach ( array( 'desc', 'name_label', 'email_label', 'button_text', 'form_id', 'ad_tracking', 'tracking_image' ) as $text_field )
			$val[$text_field] = sanitize_text_field( $new[$text_field] );

		foreach ( array( 'thank_you', 'already_subscribed' ) as $select_field )
			$val[$select_field] = in_array( $new[$select_field], $this->email_data['save'][$select_field]['options'] ) ? $new[$select_field] : '';

		$val['classes'] = strip_tags( $new['classes'] );

		$val['custom_code'] = $new['custom_code'];

		return $val;
	}


	public function form( $val ) {
		global $kol_email;

		$val = wp_parse_args( (array) $val,
			array(
				'title'              => '',
				'desc'               => '',
				'list'               => '',
				'form_fields_name'   => '',
				'name_label'         => '',
				'email_label'        => '',
				'button_text'        => '',
				'thank_you'          => '',
				'already_subscribed' => '',
				'form_id'            => '',
				'ad_tracking'        => '',
				'tracking_image'     => '',
				'custom_code'        => '',
				'classes'            => ''
			)
		);

		$service = $this->email['service'];
		$data    = isset( $this->email_data['lists_options'] ) ? $this->email_data['lists_options'] : '';
	?>

		<?php if ( $data || $service == 'custom_code' ) : ?>

			<!-- Title -->

			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php echo $kol_email->strings['widget_title']; ?>:</label>

				<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php esc_attr_e( $val['title'] ); ?>" class="widefat" />
			</p>

			<!-- Description -->

			<p>
				<label for="<?php echo $this->get_field_id( 'desc' ); ?>"><?php echo $kol_email->strings['widget_desc']; ?>:</label>

				<textarea id="<?php echo $this->get_field_id( 'desc' ); ?>" name="<?php echo $this->get_field_name( 'desc' ); ?>" class="widefat" rows="4"><?php printf( '%s', esc_textarea( $val['desc'] ) ); ?></textarea>
			</p>

		<?php endif; ?>

		<?php if ( $service == 'custom_code' ) : ?>

			<!-- Custom Code -->

			<p>
				<label for="<?php echo $this->get_field_id( 'custom_code' ); ?>"><?php echo $kol_email->strings['widget_custom_code']; ?>:</label>

				<textarea id="<?php echo $this->get_field_id( 'custom_code' ); ?>" name="<?php echo $this->get_field_name( 'custom_code' ); ?>" class="widefat" rows="7"><?php printf( '%s', esc_textarea( $val['custom_code'] ) ); ?></textarea>
			</p>

		<?php else : ?>

			<?php if ( empty( $data ) ) : ?>

				<p><?php echo $kol_email->strings['before_use']; ?></p>

			<?php else : ?>

				<!-- Select List -->

				<p>
					<label for="<?php echo $this->get_field_id( 'list' ); ?>"><?php echo $kol_email->strings['widget_list']; ?>:</label><br />

					<select id="<?php echo $this->get_field_id( 'list' ); ?>" name="<?php echo $this->get_field_name( 'list' ); ?>" style="max-width: 100%;">
						<option value=""><?php echo $kol_email->strings['select_list']; ?></option>

						<?php foreach( $this->email_data['lists_options'] as $list => $name ) : ?>
							<option value="<?php esc_attr_e( $list ); ?>" <?php selected( $val['list'], $list, true ); ?>><?php echo $name; ?></option>
						<?php endforeach; ?>
					</select>
				</p>

				<div id="<?php echo $this->get_field_id( 'kol-email' ); ?>-form" style="display: <?php echo $val['list'] ? 'block' : 'none'; ?>">

					<!-- Thank You / Already Subscribed -->

					<?php if ( $service == 'aweber' ) :
						$pages = array();

						foreach ( get_pages() as $p )
							$pages[$p->ID] = $p->post_title;

						$select = array(
							'thank_you' => array(
								'label' => $kol_email->strings['thank_you_page']
							),
							'already_subscribed' => array(
								'label' => $kol_email->strings['already_subs_page']
							)
						);
					?>

						<?php foreach ( $select as $select_id => $select_field ) : ?>

							<p>
								<label for="<?php echo $this->get_field_id( $select_id ); ?>"><?php echo $select_field['label']; ?>:</label><br />

								<select id="<?php echo $this->get_field_id( $select_id ); ?>" name="<?php echo $this->get_field_name( $select_id ); ?>" style="max-width: 100%;">
									<option value=""><?php echo $kol_email->strings['select_page']; ?></option>

									<?php foreach( $pages as $page_id => $page_name ) : ?>
										<option value="<?php esc_attr_e( $page_id ); ?>" <?php selected( $val[$select_id], $page_id, true ); ?>><?php echo $page_name; ?></option>
									<?php endforeach; ?>
								</select>
							</p>

						<?php endforeach; ?>

					<?php endif; ?>

					<!-- Input Fields -->

					<h4><?php echo $kol_email->strings['input_fields']; ?></h4>

					<!-- Show Name Field -->

					<p>
						<input id="<?php echo $this->get_field_id( 'form_fields_name' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'form_fields_name' ); ?>" value="1" <?php checked( $val['form_fields_name'] ); ?> />

						<label for="<?php echo $this->get_field_id( 'form_fields_name' ); ?>"><?php echo $kol_email->strings['widget_field_name']; ?></label>
					</p>

					<!-- Name Label -->

					<div id="<?php echo $this->get_field_id( 'name_label' ); ?>-field" style="display: <?php echo $val['form_fields_name'] ? 'block' : 'none'; ?>">

						<label for="<?php echo $this->get_field_id( 'name_label' ); ?>"><?php echo $kol_email->strings['widget_name_label']; ?>:</label>

						<input type="text" id="<?php echo $this->get_field_id( 'name_label' ); ?>" name="<?php echo $this->get_field_name( 'name_label' ); ?>" value="<?php esc_attr_e( $val['name_label'] ); ?>" placeholder="<?php echo $kol_email->strings['name_label']; ?>" class="widefat" />

					</div>

					<!-- Email Label -->

					<p>
						<label for="<?php echo $this->get_field_id( 'email_label' ); ?>"><?php echo $kol_email->strings['widget_email_label']; ?>:</label>

						<input type="text" id="<?php echo $this->get_field_id( 'email_label' ); ?>" name="<?php echo $this->get_field_name( 'email_label' ); ?>" value="<?php esc_attr_e( $val['email_label'] ); ?>" placeholder="<?php echo $kol_email->strings['email_label']; ?>" class="widefat" />
					</p>

					<!-- Button Text -->

					<p>
						<label for="<?php echo $this->get_field_id( 'button_text' ); ?>"><?php echo $kol_email->strings['widget_button_text']; ?>:</label>

						<input type="text" id="<?php echo $this->get_field_id( 'button_text' ); ?>" name="<?php echo $this->get_field_name( 'button_text' ); ?>" value="<?php esc_attr_e( $val['button_text'] ); ?>" placeholder="<?php echo $kol_email->strings['button_text']; ?>" class="widefat" />
					</p>

					<!-- Tracking -->

					<?php if ( $service == 'aweber' ) : ?>

						<h4><?php echo $kol_email->strings['tracking_management']; ?></h4>

						<!-- Form ID, Ad Tracking, Image ID -->

						<?php $tracking = array(
							'form_id' => array(
								'label' => $kol_email->strings['widget_form_id']
							),
							'ad_tracking' => array(
								'label' => $kol_email->strings['widget_ad_tracking']
							),
							'tracking_image' => array(
								'label' => $kol_email->strings['widget_image_id']
							)
						); ?>

						<?php foreach ( $tracking as $tracking_id => $tracking_field ) : ?>

							<p>
								<label for="<?php echo $this->get_field_id( $tracking_id ); ?>"><?php echo $tracking_field['label']; ?>:</label>

								<input type="text" id="<?php echo $this->get_field_id( $tracking_id ); ?>" name="<?php echo $this->get_field_name( $tracking_id ); ?>" value="<?php esc_attr_e( $val[$tracking_id] ); ?>" class="widefat" />
							</p>

						<?php endforeach; ?>

					<?php endif; ?>

					<h4><?php echo $kol_email->strings['advanced']; ?></h4>

					<!-- Classes -->

					<p>
						<label for="<?php echo $this->get_field_id( 'classes' ); ?>"><?php echo $kol_email->strings['widget_classes']; ?>:</label>

						<input type="text" id="<?php echo $this->get_field_id( 'classes' ); ?>" name="<?php echo $this->get_field_name( 'classes' ); ?>" value="<?php esc_attr_e( $val['classes'] ); ?>" placeholder="form-full" class="widefat" />
					</p>


				</div>

				<script>

					// Toggle conditional fields

					( function() {
						document.getElementById( '<?php echo $this->get_field_id( 'list' ); ?>' ).onchange = function() {
							document.getElementById( '<?php echo $this->get_field_id( 'kol-email' ); ?>-form' ).style.display = this.value != '' ? 'block' : 'none';
						}
						document.getElementById( '<?php echo $this->get_field_id( 'form_fields_name' ); ?>' ).onchange = function() {
							document.getElementById( '<?php echo $this->get_field_id( 'name_label' ); ?>-field' ).style.display = this.checked ? 'block' : 'none';
						}
					})();

				</script>

			<?php endif; ?>

		<?php endif; ?>

	<?php }
}
