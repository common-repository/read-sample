<?php
/**
 * Plugin Name: Read Sample
 * Description: Add a "Read Sample" button to WooCommerce single product page.
 * Plugin URI: https://github.com/kamrulhasanj/read-sample
 * Author: Kamrul Hasan
 * Author URI: https://facebook.com/uikamrul
 * Requires Plugins: woocommerce
 * Version: 1.0.3
 * Text Domain: read-sample
 * Domain Path: /languages
 * License: GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package ReadSample
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define constants.
define( 'READ_SAMPLE_VERSION', '1.0.3' );
define( 'READ_SAMPLE_PLUGIN_FILE', __FILE__ );
define( 'READ_SAMPLE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'READ_SAMPLE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Load required files.
require_once plugin_dir_path( __FILE__ ) . 'inc/settings.php';

/**
 * Enqueue necessary scripts and styles.
 */
function read_sample_enqueue_scripts() {
	if ( is_product() || is_shop() ) {
		wp_enqueue_style( 'read-sample-style', plugin_dir_url( __FILE__ ) . 'css/read-sample.css', array(), READ_SAMPLE_VERSION );
		wp_enqueue_script( 'read-sample-script', plugin_dir_url( __FILE__ ) . 'js/read-sample.js', array( 'jquery' ), READ_SAMPLE_VERSION, true );

		// Localize AJAX script.
		wp_localize_script(
			'read-sample-script',
			'read_sample_ajax',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'read-sample-nonce' ),
			)
		);
	}
}

add_action( 'wp_enqueue_scripts', 'read_sample_enqueue_scripts' );

/**
 * Enqueue admin scripts for media uploader.
 *
 * @param string $hook The current admin page.
 */
function read_sample_enqueue_admin_scripts( $hook ) {
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}
	// Only enqueue on product edit pages.
	global $post;
	if ( 'product' === $post->post_type ) {
		wp_enqueue_media();
		wp_enqueue_script( 'read-sample-admin', plugin_dir_url( __FILE__ ) . 'js/admin.js', array( 'jquery' ), READ_SAMPLE_VERSION, true );
	}
}

add_action( 'admin_enqueue_scripts', 'read_sample_enqueue_admin_scripts' );

/**
 * Add backend fields for sample images or PDF using media uploader.
 */
function read_sample_add_product_fields() {
	global $post;

	// Sample images gallery upload.
	echo '<div class="options_group">';
	woocommerce_wp_text_input(
		array(
			'id'          => '_sample_images',
			'label'       => __( 'Sample Image Gallery', 'read-sample' ),
			'placeholder' => __( 'Select or upload images', 'read-sample' ),
			'type'        => 'text',
			'desc_tip'    => true,
			'description' => __( 'Select multiple images from the media library or upload new ones.', 'read-sample' ),
		)
	);
	echo '<button class="button upload_image_gallery_button" data-media-title="' . esc_attr__( 'Select Images', 'read-sample' ) . '" data-media-button-text="' . esc_attr__( 'Add to gallery', 'read-sample' ) . '">' . esc_html__( 'Upload Images', 'read-sample' ) . '</button>';

	// Container to display selected images.
	echo '<div id="sample_image_gallery" style="margin-top: 10px;"></div>'; // Image preview area.
	echo '</div>';
}

add_action( 'woocommerce_product_options_general_product_data', 'read_sample_add_product_fields' );

/**
 * Save the uploaded media files.
 *
 * @param int $post_id The ID of the post being saved.
 */
function read_sample_save_product_fields( $post_id ) {
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'update-post_' . $post_id ) ) {
		return;
	}

	$sample_images = isset( $_POST['_sample_images'] ) ? sanitize_text_field( wp_unslash( $_POST['_sample_images'] ) ) : '';

	// Save the sample images as a comma-separated string.
	$sample_images_array = array_map( 'trim', explode( ',', $sample_images ) );
	update_post_meta( $post_id, '_sample_images', implode( ',', $sample_images_array ) );
}

add_action( 'woocommerce_process_product_meta', 'read_sample_save_product_fields' );

/**
 * Add "Read Sample" button to single product page with dynamic styles.
 */
function read_sample_add_button_single() {
	global $product;
	// Get the serialized options from the database.
	$options = get_option( 'read_sample_options' );

	// Check if button background color is set, but don't apply it if it's empty.
	$bg_color = isset( $options['read_sample_button_bg_color'] ) && ! empty( $options['read_sample_button_bg_color'] )
		? $options['read_sample_button_bg_color']
		: ''; // Leave blank to use theme's default.

	// Fallback text color if not set.
	$text_color = isset( $options['read_sample_button_text_color'] ) && ! empty( $options['read_sample_button_text_color'] )
		? $options['read_sample_button_text_color']
		: '#ffffff'; // Default fallback to white text color.

	// Fallback button text if not set.
	$button_text = isset( $options['read_sample_button_text'] ) && ! empty( $options['read_sample_button_text'] )
		? $options['read_sample_button_text']
		: __( 'Read Sample', 'read-sample' );

	$sample_images = get_post_meta( $product->get_id(), '_sample_images', true );

	if ( $sample_images ) {
		// Prepare inline styles.
		$style = 'color: ' . esc_attr( $text_color ) . ';';
		if ( ! empty( $bg_color ) ) {
			$style .= ' background-color: ' . esc_attr( $bg_color ) . ';';
		}

		// Echo the button with dynamic inline styles.
		printf(
			'<button class="read-sample-btn" style="%s" data-product-id="%d">%s</button>',
			esc_attr( $style ),
			esc_attr( $product->get_id() ),
			esc_html( $button_text )
		);
	}
}

add_action( 'woocommerce_after_add_to_cart_button', 'read_sample_add_button_single' );

/**
 * Add popup HTML structure in the footer.
 */
function read_sample_popup_content() {
	?>
	<div id="read-sample-popup" style="display:none;">
		<div class="read-sample-content">
			<button class="close-popup"><?php esc_html_e( 'Close', 'read-sample' ); ?></button>
			<div id="sample-content"></div>
		</div>
	</div>
	<?php
}

add_action( 'wp_footer', 'read_sample_popup_content' );

/**
 * Load images or PDF in the popup via AJAX.
 */

/**
 * Load images or PDF in the popup via AJAX.
 */
function read_sample_load_content() {
	check_ajax_referer( 'read-sample-nonce', 'nonce' );

	$product_id    = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
	$sample_images = get_post_meta( $product_id, '_sample_images', true );

	if ( $sample_images ) {
		$images = explode( ',', $sample_images );
		foreach ( $images as $index => $image ) {
			// translators: %d: The index of the sample image being displayed.
			$alt_text = sprintf( __( 'Sample image %d', 'read-sample' ), $index + 1 );
			echo '<img src="' . esc_url( trim( $image ) ) . '" alt="' . esc_attr( $alt_text ) . '" />';
		}
	} else {
		esc_html_e( 'No sample available.', 'read-sample' );
	}

	wp_die(); // Properly terminate the AJAX request.
}

add_action( 'wp_ajax_read_sample_load_content', 'read_sample_load_content' );
add_action( 'wp_ajax_nopriv_read_sample_load_content', 'read_sample_load_content' ); // Allow non-logged-in users to access.

/**
 * Add plugin action links.
 *
 * @param array $links An array of plugin action links.
 * @return array Modified array of plugin action links.
 */
function read_sample_plugin_action_links( $links ) {
	$settings_link = '<a href="admin.php?page=read-sample-settings">' . esc_html__( 'Settings', 'read-sample' ) . '</a>';
	array_push( $links, $settings_link );
	return $links;
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'read_sample_plugin_action_links' );