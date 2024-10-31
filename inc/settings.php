<?php
/**
 * Settings page for Read Sample plugin.
 *
 * @package ReadSample
 */

// Direct access not allowed.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

	/**
	 * Add custom row meta links for the plugin.
	 *
	 * @param array  $links Existing row meta links.
	 * @param string $file Current plugin file.
	 * @return array Updated row meta links.
	 */
function plugin_row_meta( $links, $file ) {
	if ( READ_SAMPLE_PLUGIN_BASENAME === $file ) {
		$row_meta = array(
			'support' => '<a href="https://wordpress.org/support/plugin/read-sample/reviews/#new-post" target="_blank">' . esc_html__( 'Rate this plugin', 'read-sample' ) . '</a>',
		);
			return array_merge( $links, $row_meta );
	}
		return (array) $links;
}

	add_filter( 'plugin_row_meta', 'plugin_row_meta', 10, 2 );

/**
 * Add settings page to the WordPress admin menu.
 */
function read_sample_add_settings_page() {
	add_submenu_page(
		'tools.php',
		'Read Sample Settings',
		'Read Sample Settings',
		'manage_options',
		'read-sample-settings',
		'read_sample_settings_page_html'
	);
}

add_action( 'admin_menu', 'read_sample_add_settings_page' );

/**
 * Render the settings page HTML.
 */
function read_sample_settings_page_html() {
	// Check user permissions.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Settings updated message.
	if ( isset( $_GET['settings-updated'] ) ) {
		add_settings_error(
			'read_sample_messages',
			'read_sample_message',
			esc_html__( 'Settings Saved', 'read-sample' ),
			'updated'
		);
	}

	// Check if the form has been submitted and nonce is verified.
	if ( isset( $_POST['submit'] ) ) {
		if ( ! isset( $_POST['read_sample_settings_nonce_name'] ) ||
			! wp_verify_nonce(
				sanitize_text_field( wp_unslash( $_POST['read_sample_settings_nonce_name'] ) ),
				'read_sample_settings_nonce_action'
			)
		) {
			wp_die( esc_html__( 'Nonce verification failed', 'read-sample' ) );
		}
		// Process form data safely here.
	}

	settings_errors( 'read_sample_messages' );
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			// Output nonce, action, and option_page fields for the settings form.
			settings_fields( 'read_sample_settings_group' );
			// Add a nonce field to the form.
			wp_nonce_field( 'read_sample_settings_nonce_action', 'read_sample_settings_nonce_name' );
			do_settings_sections( 'read-sample-settings' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Register settings for the Read Sample plugin.
 */
function read_sample_register_settings() {
	// Add a settings section.
	add_settings_section(
		'read_sample_customize_section',
		__( 'Customize Read Sample Button', 'read-sample' ),
		null,
		'read-sample-settings'
	);

	// Register a single option to store serialized data.
	register_setting(
		'read_sample_settings_group',
		'read_sample_options',
		array(
			'sanitize_callback' => 'read_sample_sanitize_options',
		)
	);

	// Add fields and save them in the serialized option.
	add_settings_field(
		'read_sample_button_bg_color',
		__( 'Button Background Color', 'read-sample' ),
		'read_sample_color_picker_html',
		'read-sample-settings',
		'read_sample_customize_section',
		array(
			'label_for'   => 'read_sample_button_bg_color',
			'description' => __( 'Choose the background color for the button.', 'read-sample' ),
		)
	);

	add_settings_field(
		'read_sample_button_text_color',
		__( 'Button Text Color', 'read-sample' ),
		'read_sample_color_picker_html',
		'read-sample-settings',
		'read_sample_customize_section',
		array(
			'label_for'   => 'read_sample_button_text_color',
			'description' => __( 'Choose the text color for the button.', 'read-sample' ),
		)
	);

	add_settings_field(
		'read_sample_button_text',
		__( 'Button Text', 'read-sample' ),
		'read_sample_text_input_html',
		'read-sample-settings',
		'read_sample_customize_section',
		array( 'label_for' => 'read_sample_button_text' )
	);
}

add_action( 'admin_init', 'read_sample_register_settings' );

/**
 * Sanitize and serialize options.
 *
 * @param array $input The array of input settings to be sanitized.
 * @return array $sanitized The sanitized settings array.
 */
function read_sample_sanitize_options( $input ) {
	$sanitized                                  = array();
	$sanitized['read_sample_button_bg_color']   = sanitize_hex_color( $input['read_sample_button_bg_color'] );
	$sanitized['read_sample_button_text_color'] = sanitize_hex_color( $input['read_sample_button_text_color'] );
	$sanitized['read_sample_button_text']       = sanitize_text_field( $input['read_sample_button_text'] );

	return $sanitized;
}

/**
 * Render color picker HTML for settings fields.
 *
 * @param array $args Arguments passed to the function.
 */
function read_sample_color_picker_html( $args ) {
	$options = get_option( 'read_sample_options' );
	$option  = isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : '';
	?>
	<input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="read_sample_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="<?php echo esc_attr( $option ); ?>" class="rs-color-picker">
	<?php
	// Output the description, if provided.
	if ( isset( $args['description'] ) ) {
		echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
	}
}

/**
 * Render text input HTML for settings fields.
 *
 * @param array $args Arguments passed to the function.
 */
function read_sample_text_input_html( $args ) {
	$options = get_option( 'read_sample_options' );
	$option  = isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : '';
	?>
	<input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="read_sample_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="<?php echo esc_attr( $option ); ?>">
	<?php
}

/**
 * Enqueue color picker scripts and styles.
 *
 * @param string $hook_suffix The current admin page.
 */
function read_sample_enqueue_color_picker( $hook_suffix ) {
	if ( 'tools_page_read-sample-settings' === $hook_suffix ) {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_add_inline_script(
			'wp-color-picker',
			'
            jQuery(document).ready(function($) {
                $(".rs-color-picker").wpColorPicker();
            });
        '
		);
	}
}

add_action( 'admin_enqueue_scripts', 'read_sample_enqueue_color_picker' );