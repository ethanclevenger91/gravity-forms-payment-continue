<?php
/*
Plugin Name: Gravity Forms PayPal Standard Continue URL
Description: Provides a meta box on entries with a URL to send to users to finish payment.
Version: 1.0
Author: Sterner Stuff Design
Author URI: https://sternerstuffdesign.com
*/
define( 'GF_PAYPAL_CONTINUE_ADDON_VERSION', '1.0' );

add_action( 'gform_loaded', array( 'GF_PayPal_Continue_AddOn_Bootstrap', 'load' ), 5 );

class GF_PayPal_Continue_AddOn_Bootstrap {

    public static function load() {

        if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
            return;
        }

        require_once( 'gravityforms-paypalcontinue.php' );

        GFAddOn::register( 'GravityFormsPayPalContinue' );
    }

}

function gf_simple_addon() {
    return GFSimpleAddOn::get_instance();
}
