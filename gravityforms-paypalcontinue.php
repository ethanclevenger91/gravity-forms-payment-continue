<?php

GFForms::include_addon_framework();

class GravityFormsPayPalContinue extends GFAddOn {

	/**
	 * Defines the version of Gravity Forms PayPal Standard Continue.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_version Contains the version, defined from paypal-continue.php
	 */
	protected $_version = GF_PAYPAL_CONTINUE_ADDON_VERSION;

	/**
	 * Defines the minimum Gravity Forms version required.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_min_gravityforms_version The minimum version required.
	 */
	protected $_min_gravityforms_version = '2.2';

	/**
	 * Defines the plugin slug.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_slug The slug used for this plugin.
	 */
	protected $_slug = 'paypalcontinue';

	/**
	 * Defines the main plugin file.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_path The path to the main plugin file, relative to the plugins folder.
	 */
	protected $_path = 'gravityforms-paypalcontinue/gravityforms-paypalcontinue.php';

	/**
	 * Defines the full path to this class file.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_full_path The full path.
	 */
	protected $_full_path = __FILE__;

	/**
	 * Defines the title of this Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_title The title of the Add-On.
	 */
	protected $_title = 'Gravity Forms PayPal Continue Add-On';

	/**
	 * Defines the short title of the Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_short_title The short title.
	 */
	protected $_short_title = 'PayPal Continue Add-On';

	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since  1.0
	 * @access private
	 * @var    object $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Get instance of this class.
	 *
	 * @since  1.0
	 * @access public
	 * @static
	 *
	 * @return $_instance
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new self;
		}

		return self::$_instance;

	}

	/**
	 * Define minimum requirements needed to run Gravity Forms PayPal Standard Continue.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return array
	 */
	public function minimum_requirements() {

		return array( 'add-ons' => array( 'gravityformspaypal' ) );

	}

	/**
	 * Register needed admin hooks.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function init_admin() {

		parent::init_admin();

		add_action('gform_entry_detail_meta_boxes', array( $this, 'register_meta_box' ), 10, 3 );

	}

	public function register_meta_box($meta_boxes, $entry, $form) {
		$paymentAddon = new GFPayPal();
	  $payment_status = apply_filters( 'gform_payment_status', $entry['payment_status'], $form, $entry );
	  if($paymentAddon->get_active_feeds($form['id']) && $payment_status == 'Processing') {
		$meta_boxes[ 'paypal_continue_url' ] = array(
			'title'	   => 'PayPal URL to Finish Payment',
			'callback' => array( $this, 'add_details_meta_box' ),
			'context'  => 'side',
		);
	  }
	  return $meta_boxes;
	}

	function add_details_meta_box($args) {
	  $form = $args['form'];
	  $entry = $args['entry'];
	  GFForms::include_payment_addon_framework();
	  $paymentAddon = new GFPayPal();
	  $feed = $paymentAddon->get_single_submission_feed($entry, $form);
	  $submissionData = $paymentAddon->get_submission_data($feed, $form, $entry);
	  $url = $paymentAddon->redirect_url( $feed, $submissionData, $form, $entry );
	  $html = '<a target="_blank" href="'.$url.'">Send this link to the customer!</a>';
	  echo $html;
	}
}
