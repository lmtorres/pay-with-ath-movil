<?php
/*
 * Plugin Name: Pay With Ath Móvil
 * Plugin URI:
 * Description: Take ATH Móvil payments on your store.
 * Author: Luis Torres
 * Version: 1.0.0
 *
 *
 */
/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */

add_filter( 'woocommerce_payment_gateways', 'pwam_add_gateway_class' );
function pwam_add_gateway_class( $gateways ) {
	$gateways[] = 'PWAM_Gateway'; // your class name is here
	return $gateways;
}

/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded', 'pwam_init_gateway_class' );

function pwam_init_gateway_class() {

	class PWAM_Gateway extends WC_Payment_Gateway {
		/**
		 * @var string
		 */
		private $public_key;

		/**
		 * Class constructor, more about it in Step 3
		 */
		public function __construct() {

			$this->id = 'pwam'; // payment gateway plugin ID
			$this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
			$this->has_fields = true; // in case you need a custom credit card form
			$this->method_title = 'Pay With ATHM Móvil Gateway';
			$this->method_description = 'Description of PWAM payment gateway'; // will be displayed on the options page

			// gateways can support subscriptions, refunds, saved payment methods,
			// but in this tutorial we begin with simple payments
			$this->supports = array(
				'products'
			);

			// Method with all the options fields
			$this->init_form_fields();

			// Load the settings.
			$this->init_settings();
			$this->title = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );
			$this->enabled = $this->get_option( 'enabled' );
			$this->testmode = 'yes' === $this->get_option( 'testmode' );
			$this->private_key = $this->testmode ? $this->get_option( 'test_private_key' ) : $this->get_option( 'private_key' );
			$this->public_key = $this->testmode ? $this->get_option( 'test_public_key' ) : $this->get_option( 'public_key' );

			// This action hook saves the settings
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

			// We need custom JavaScript to obtain a token
			add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );

			// You can also register a webhook here
			add_action( 'woocommerce_api_athm_success', array( $this, 'webhook' ) );

		}

		/**
		 * Plugin options, we deal with it in Step 3 too
		 */
		public function init_form_fields(){

			$this->form_fields = array(
				'enabled' => array(
					'title'       => 'Enable/Disable',
					'label'       => 'Enable Pay With Ath Móvil Gateway',
					'type'        => 'checkbox',
					'description' => '',
					'default'     => 'no'
				),
				'title' => array(
					'title'       => 'Title',
					'type'        => 'text',
					'description' => 'This controls the title which the user sees during checkout.',
					'default'     => 'ATH Móvil',
					'desc_tip'    => false,
				),
				'description' => array(
					'title'       => 'Description',
					'type'        => 'textarea',
					'description' => 'This controls the description which the user sees during checkout.',
					'default'     => 'Pay with ATH Móvil.',
				),
				'testmode' => array(
					'title'       => 'Test mode',
					'label'       => 'Enable Test Mode',
					'type'        => 'checkbox',
					'description' => 'Place the payment gateway in test mode using test API keys.',
					'default'     => 'yes',
					'desc_tip'    => true,
				),
				'test_public_key' => array(
					'title'       => 'Test Public Key',
					'type'        => 'text'
				),
				'public_key' => array(
					'title'       => 'Live Public Key',
					'type'        => 'text'
				)
			);

		}


		/**
		 * You will need it if you want your custom credit card form, Step 4 is about it
		 */
		/*public function payment_fields() {



		}*/

		/*
		 * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
		 */
		public function payment_scripts() {



		}

		/*
 		 * Fields validation, more in Step 5
		 */
		/*public function validate_fields() {



		}*/

		/*
		 * We're processing the payments here, everything about it is in Step 5
		 */
		public function process_payment( $order_id ) {

			global $woocommerce;

			// we need it to get any order detailes
			$order = wc_get_order( $order_id );


			/*
			  * Array with parameters for API interaction
			 */
			$args = array(

				'total' => $order->get_total(),
				'publicKey' => $this->public_key,
				'orderId' => $order->get_id(),
				'redirectUrl' => urlencode($this->get_return_url( $order )),
				'env' => $this->testmode ? 'sandbox' : 'production'
			);

			/*
			 * Your API interaction could be built with wp_remote_post()
			  */
			$payment_page = add_query_arg( $args, '/wp-content/plugins/pay-with-ath-movil/src/payment.php' );

			return array(
				'result' => 'success',
				'redirect' => $payment_page
			);

		}

		/*
		 * In case you need a webhook, like PayPal IPN etc
		 */
		public function webhook() {
			$order = wc_get_order( $_POST['id'] );
			if ($_POST['result'] == 'success'){
				$order->payment_complete();
				wc_reduce_stock_levels($order->get_id());
			} else {
				wc_add_notice(  'Please try again.', 'error' );
				return;
			}

			update_option('webhook_debug', $_POST);

		}
	}
}
