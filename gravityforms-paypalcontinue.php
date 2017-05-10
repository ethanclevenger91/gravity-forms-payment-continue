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

	/**
	 * Add meta box to the entry detail page.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array $meta_boxes The properties for the meta boxes.
	 * @param array $entry      The entry currently being viewed/edited.
	 * @param array $form       The form object used to process the current entry.
	 *
	 * @uses GFFeedAddOn::get_active_feeds()
	 *
	 * @return array
	 */
	public function register_meta_box($meta_boxes, $entry, $form) {

		// Get instance of PayPal Add-On.
		$paypal = gf_paypal();
		
		// Get payment status.
		$payment_status = apply_filters( 'gform_payment_status', $entry['payment_status'], $form, $entry );

		// If active feeds were found and payment status is processing, display meta box.
		if ( $paypal->get_active_feeds( $form['id'] ) && 'Processing' === $payment_status ) {

			$meta_boxes[ 'paypal_continue_url' ] = array(
				'title'	   => 'PayPal URL to Finish Payment',
				'callback' => array( $this, 'add_details_meta_box' ),
				'context'  => 'side',
			);

		}

		return $meta_boxes;

	}

	/**
	 * Display entry meta box.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array $args An array containing the form and entry objects.
	 *
	 * @uses GFForms::include_payment_addon_framework()
	 * @uses GFFeedAddOn::get_single_submission_feed()
	 * @uses GFPaymentAddOn::get_single_submission_feed()
	 * @uses GFPayPal::redirect_url()
	 */
	public function add_details_meta_box( $args ) {
		
		// Get form and entry.
		$form  = $args['form'];
		$entry = $args['entry'];

		// Include Payment Add-On Framework.
		GFForms::include_payment_addon_framework();

		// Get instance of PayPal Add-On.
		$paypal = gf_paypal();

		// Get feed for entry.
		$feed = $paypal->get_single_submission_feed( $entry, $form );

		// Get submission data for feed.
		$submission_data = $paypal->get_submission_data( $feed, $form, $entry );

		// Get redirect URL.
		$url = $paypal->redirect_url( $feed, $submission_data, $form, $entry );

		// Display link.
		printf(
			'<a target="_blank" href="%s">%s</a>',
			esc_url( $url ),
			'Send this link to the customer!'
		);

	}

}
