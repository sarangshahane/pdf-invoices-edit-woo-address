<?php
/**
 * Edit Billing Address Functionality for My Account Section
 *
 * Handles the functionality of the edit the billing order for specific WooCommerce order.
 * placed by the user.
 *
 * @package pdf-invoices-edit-woo-address
 * @version 1.0.1
 */

namespace PDF_IEWA\Modules\My_Account;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use PDF_IEWA\Traits\Get_Instance;

/**
 * Pdf_Iewa_My_Account object.
 *
 * @since 1.0.0
 */
class Pdf_Iewa_My_Account {

	use Get_Instance;

	public static $billing_fields = array();

	/**
	 * Class constructor to initialize the main actions.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_script_for_my_account' ));
		add_action( 'woocommerce_my_account_my_address_description', array( $this, 'modify_edit_address_page_note' ), 11 );
		add_action( 'woocommerce_order_details_after_customer_details', array( $this, 'add_edit_address_popup_html' ), 10, 1 );
		add_action( 'wp', array( $this, 'update_billing_address' ) );
		add_action( 'wp_ajax_pdf_iewa_get_customer_details', array( $this, 'get_customer_address' ) );
	}

	/**
	 * Enqueue the scripts required to edit the address.
	 *
	 * @since x.x.x
	 * @return void
	 */
	public static function enqueue_script_for_my_account() {

		if ( is_wc_endpoint_url( 'view-order' )  ) {
			// Enqueue your custom script
			wp_enqueue_style( 'pdf-iewa-my-account-style', PDF_IEWA_URL. 'modules/my-account/assets/css/my-account.css', '', PDF_IEWA_VER );
			wp_enqueue_script('pdf-iewa-my-account-script', PDF_IEWA_URL . 'modules/my-account/assets/js/my-account.js', array('jquery'), PDF_IEWA_VER, true);
		}
	}

	/**
	 * Modifying the string to display on the Edit Address tab in my-account
	 * section for adding more clarity for the user.
	 *
	 * @param string $string The note to display
	 * @return HTML
	 * @since 1.0.0
	 */
	public function modify_edit_address_page_note( $string ) {
		ob_start();
		?>
		<div>
			<p style="border: 1px solid rgb(241 99 52 / 10%); padding: 10px; border-radius: 3px; background-color: rgb(241 99 52 / 10%);">
				<strong><?php echo esc_html( __( 'Note: ', 'pdf-invoices-edit-woo-address' ) ); ?></strong>
				<?php echo esc_html( __( 'The following addresses will be used on the checkout page by default. modifying these default addresses will not affect orders that have already been placed or their corresponding invoices.', 'pdf-invoices-edit-woo-address' ) )?>
			</p>
		</div>
		<?php
		$string = ob_get_clean();

		return $string;
	}

	/**
	 * HTML to display the edit address form
	 *
	 * @return HTML
	 * @since x.x.x
	 */
	public function add_edit_address_popup_html( $order ) {
		self::$billing_fields = array(
			'first_name' => array(
				'label' => __( 'First name', 'pdf-invoices-edit-woo-address' ),
				'show'  => false,
				'class' => 'form-row-first validate-required',
			),
			'last_name'  => array(
				'label' => __( 'Last name', 'pdf-invoices-edit-woo-address' ),
				'show'  => false,
				'class' => 'form-row-last validate-required',
			),
			'company'    => array(
				'label' => __( 'Company', 'pdf-invoices-edit-woo-address' ),
				'show'  => false,
				'class' => 'form-row-wide',
			),
			'address_1'  => array(
				'label' => __( 'Address line 1', 'pdf-invoices-edit-woo-address' ),
				'show'  => false,
				'class' => 'form-row-first validate-required',
			),
			'address_2'  => array(
				'label' => __( 'Address line 2', 'pdf-invoices-edit-woo-address' ),
				'show'  => false,
				'class' => 'form-row-last',
			),
			'city'       => array(
				'label' => __( 'City', 'pdf-invoices-edit-woo-address' ),
				'show'  => false,
				'class' => 'form-row-first validate-required',
			),
			'postcode'   => array(
				'label' => __( 'Postcode / ZIP', 'pdf-invoices-edit-woo-address' ),
				'show'  => false,
				'class' => 'form-row-last validate-required',
			),
			'country'    => array(
				'label'   => __( 'Country / Region', 'pdf-invoices-edit-woo-address' ),
				'show'    => false,
				'class'   => 'js_field-country select short',
				'type'    => 'select',
				'options' => array( '' => __( 'Select a country / region&hellip;', 'pdf-invoices-edit-woo-address' ) ) + WC()->countries->get_allowed_countries(),
			),
			'state'      => array(
				'label' => __( 'State / County', 'pdf-invoices-edit-woo-address' ),
				'class' => 'js_field-state select short',
				'show'  => false,
			),
			'email'      => array(
				'label' => __( 'Email address', 'pdf-invoices-edit-woo-address' ),
				'class' => 'form-row-wide validate-required',
			),
			'phone'      => array(
				'label' => __( 'Phone', 'pdf-invoices-edit-woo-address' ),
				'class' => 'form-row-wide validate-required',
			),
		);

		$shipping_fields = array(
			'first_name' => array(
				'label' => __( 'First name', 'pdf-invoices-edit-woo-address' ),
				'show'  => false,
			),
			'last_name'  => array(
				'label' => __( 'Last name', 'pdf-invoices-edit-woo-address' ),
				'show'  => false,
			),
			'company'    => array(
				'label' => __( 'Company', 'pdf-invoices-edit-woo-address' ),
				'show'  => false,
			),
			'address_1'  => array(
				'label' => __( 'Address line 1', 'pdf-invoices-edit-woo-address' ),
				'show'  => false,
			),
			'address_2'  => array(
				'label' => __( 'Address line 2', 'pdf-invoices-edit-woo-address' ),
				'show'  => false,
			),
			'city'       => array(
				'label' => __( 'City', 'pdf-invoices-edit-woo-address' ),
				'show'  => false,
			),
			'postcode'   => array(
				'label' => __( 'Postcode / ZIP', 'pdf-invoices-edit-woo-address' ),
				'show'  => false,
			),
			'country'    => array(
				'label'   => __( 'Country / Region', 'pdf-invoices-edit-woo-address' ),
				'show'    => false,
				'type'    => 'select',
				'class'   => 'js_field-country select short',
				'options' => array( '' => __( 'Select a country / region&hellip;', 'pdf-invoices-edit-woo-address' ) ) + WC()->countries->get_shipping_countries(),
			),
			'state'      => array(
				'label' => __( 'State / County', 'pdf-invoices-edit-woo-address' ),
				'class' => 'js_field-state select short',
				'show'  => false,
			),
			'phone'      => array(
				'label' => __( 'Phone', 'pdf-invoices-edit-woo-address' ),
			),
		);
		ob_start();
		?>
			<div class="edit-address-popup--wrapper">

				<div class="edit-address-popup--content">
					<h4>
						<?php echo esc_html__( 'Edit Address', 'pdf-invoices-edit-woo-address' ); ?>
						<span class="edit-address-popup--close-btn">
							<span class="dashicons dashicons-no-alt"></span>
						</span>
					</h4>
					<div class="edit-address-popup--load-address">
						<?php echo esc_html__( 'Load billing address', 'pdf-invoices-edit-woo-address' ); ?>
					</div>
					<div class="edit-address-popup--form-group woocommerce">
						<form method="post" enctype="multipart/form-data">
							<?php
								foreach ( self::$billing_fields as $key => $field ) {
									if ( ! isset( $field['type'] ) ) {
										$field['type'] = 'text';
									}
									if ( ! isset( $field['id'] ) ) {
										$field['id'] = '_billing_' . $key;
									}

									$field_name = 'billing_' . $key;

									if ( ! isset( $field['value'] ) ) {
										if ( is_callable( array( $order, 'get_' . $field_name ) ) ) {
											$field['value'] = $order->{"get_$field_name"}( 'edit' );
										} else {
											$field['value'] = $order->get_meta( '_' . $field_name );
										}
									}

									echo woocommerce_form_field( $field_name, $field, wc_get_post_data_by_key( $key, $field['value'] ) );
								}
							?>
							<div class="edit-address--action-buttons">
								<button type="submit" class="woocommerce-button button update-order-address">Update</button>
								<button type="reset" class="woocommerce-button button cancel-update-order-address">Cancel</button>
							</div>

							<?php
								wp_nonce_field( 'pdf-iewa-update-billing-address', '_pdf-iewa-update-billing-address_wpnonce' );
								wp_nonce_field( 'pdf-iewa-get-customer-details', 'pdf_iewa_get_customer_details_nonce' );

								echo woocommerce_form_field( 'billing_order_id', array(
									'show'  => false,
									'type'  => 'hidden',
								), $order->get_id() );
								echo woocommerce_form_field( '_payment_method', array(
									'show'    => false,
									'type'    => 'hidden',
								), $order->get_payment_method() );

								echo woocommerce_form_field( 'customer_user_id', array(
									'show'    => false,
									'type'    => 'hidden',
								), get_current_user_id() )
							?>
						</form>
					</div>
				</div>


			</div>
		<?php
		echo ob_get_clean();
	}

	public function update_billing_address(){

		if( $_POST && isset( $_POST['_pdf-iewa-update-billing-address_wpnonce'] ) ){

			// Verify that the nonce is valid.
			if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_pdf-iewa-update-billing-address_wpnonce'] ) ), 'pdf-iewa-update-billing-address' ) ){
				$order = isset( $_POST['billing_order_id'] ) ? wc_get_order( intval( $_POST['billing_order_id'] ) ) : 0;

				if( empty( $order ) || empty( $_POST ) ){
					return;
				}

				$posted_data = self::sanitize_recursively( 'sanitize_text_field', $_POST );

				// Save the updated address in the respective order.
				// \WC_Meta_Box_Order_Data::save( intval( $_POST['billing_order_id'] ) );

				foreach ( $posted_data as $key => $value ) {
					if ( is_callable( array( $order, "set_{$key}" ) ) ) {
						$order->{"set_{$key}"}( $value );
					}
				}
				// Save the order with updated .
				$order_id = $order->save();
			}
		}
	}

	public function get_customer_address(){

		if ( ! current_user_can( 'edit_shop_orders' ) || ! isset( $_POST['user_id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Sorry! You don\'t have permission to perform this action', 'pdf-invoices-edit-woo-address' ) ) );
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'pdf-iewa-get-customer-details' ) ){
			wp_send_json_error( array( 'message' => __( 'Nonce verification failed', 'pdf-invoices-edit-woo-address' ) ) );
		}

		$user_id  = absint( $_POST['user_id'] );
		$customer = new \WC_Customer( $user_id );

		if ( has_filter( 'woocommerce_found_customer_details' ) ) {
			wc_deprecated_function( 'The woocommerce_found_customer_details filter', '3.0', 'woocommerce_ajax_get_customer_details' );
		}

		wp_send_json_success( $customer->get_data() );
	}

	/**
	 * This function performs array_map for multi dimensional array
	 *
	 * @param string $function function name to be applied on each element on array.
	 * @param array  $data_array array on which function needs to be performed.
	 * @return array
	 * @since 1.0.0
	 */
	public static function sanitize_recursively( $function, $data_array ) {
		$response = [];
		foreach ( $data_array as $key => $data ) {
			$val              = is_array( $data ) ? self::sanitize_recursively( $function, $data ) : $function( $data );
			$response[ $key ] = $val;
		}

		return $response;
	}

}
