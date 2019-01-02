<?php
/**
 * Plugin Name:     Gravity Forms Payment Continue
 * Plugin URI:      https://sternerstuffdesign.com/2017/04/adding-gravity-forms-paypal-continue-url/
 * Description:     Exposes the PayPal URL needed to complete payments.
 * Author:          Sterner Stuff Design
 * Author URI:      https://sternerstuffdesign.com
 * Text Domain:     gravity-forms-payment-continue
 * Domain Path:     /languages
 * Version:         1.1.1
 *
 * @package         Gravity_Forms_Payment_Continue
 */

define( 'GF_PAYMENT_CONTINUE_ADDON_VERSION', '1.1.0' );
define( 'GF_PAYMENT_CONTINUE_ADDON_SLUG', 'payment-continue');

// If Gravity Forms is loaded, bootstrap Gravity Forms PayPal Standard Continue.
add_action( 'gform_loaded', array( 'GF_Payment_Continue_AddOn_Bootstrap', 'load' ), 5 );

/**
 * Class GF_Payment_Continue_AddOn_Bootstrap
 *
 * Handles the loading of Gravity Forms Payment Continue and registers with the Add-On framework.
 */
class GF_Payment_Continue_AddOn_Bootstrap {

	/**
	 * If the Add-On Framework exists, Gravity Forms Payment Continue is loaded.
	 *
	 * @access public
	 * @static
	 */
	public static function load() {

		if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
			return;
		}

		require_once( 'gravity-forms-payment-continue.php' );

		GFAddOn::register( 'GravityFormsPaymentContinue' );

	}

}

/**
 * Returns an instance of the GravityFormsPaymentContinue class
 *
 * @see	   GravityFormsPaymentContinue::get_instance()
 *
 * @return object GravityFormsPaymentContinue
 */
function gf_payment_continue() {
	return GravityFormsPaymentContinue::get_instance();
}
