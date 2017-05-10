<?php
/*
Plugin Name: Gravity Forms PayPal Standard Continue URL
Description: Provides a meta box on entries with a URL to send to users to finish payment.
Version: 1.0
Author: Sterner Stuff Design
Author URI: https://sternerstuffdesign.com
*/

define( 'GF_PAYPAL_CONTINUE_ADDON_VERSION', '1.0' );

// If Gravity Forms is loaded, bootstrap Gravity Forms PayPal Standard Continue.
add_action( 'gform_loaded', array( 'GF_PayPal_Continue_AddOn_Bootstrap', 'load' ), 5 );

/**
 * Class GF_PayPal_Continue_AddOn_Bootstrap
 *
 * Handles the loading of Gravity Forms PayPal Standard Continue and registers with the Add-On framework.
 */
class GF_PayPal_Continue_AddOn_Bootstrap {

	/**
	 * If the Add-On Framework exists, Gravity Forms PayPal Standard Continue is loaded.
	 *
	 * @access public
	 * @static
	 */
	public static function load() {

		if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
			return;
		}

		require_once( 'gravityforms-paypalcontinue.php' );

		GFAddOn::register( 'GravityFormsPayPalContinue' );

	}

}

/**
 * Returns an instance of the GravityFormsPayPalContinue class
 *
 * @see	   GravityFormsPayPalContinue::get_instance()
 *
 * @return object GravityFormsPayPalContinue
 */
function gf_paypal_continue() {
	return GravityFormsPayPalContinue::get_instance();
}
