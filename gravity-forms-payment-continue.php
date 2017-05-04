<?php

GFForms::include_addon_framework();

class GravityFormsPaymentContinue extends GFAddOn {

	/**
	 * Defines the version of Gravity Forms Payment Continue.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_version Contains the version, defined from payment-continue.php
	 */
	protected $_version = GF_PAYMENT_CONTINUE_ADDON_VERSION;

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
	protected $_slug = 'payment-continue';

	/**
	 * Defines the main plugin file.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_path The path to the main plugin file, relative to the plugins folder.
	 */
	protected $_path = 'gravity-forms-payment-continue/gravity-forms-payment-continue.php';

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
	protected $_short_title = 'Payment Continue Add-On';

	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since  1.0
	 * @access private
	 * @var    object $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Defines the merge tag used for this Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_merge_tag The merge tag.
	 */
	protected static $_merge_tag = '{payment_url}';

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
	 * Define minimum requirements needed to run Gravity Forms Payment Continue.
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

		add_filter('gform_admin_pre_render', array( $this, 'add_merge_tags') );

	}

	/**
	 * Register all other hooks
	 *
	 * @since  1.0
	 * @access public
	 */

	public function init() {

		parent::init();

		add_filter('gform_replace_merge_tags', array( $this, 'replace_merge_tags' ), 10, 3);

	}

	/**
	 * Add Javascript for custom merge tags to the Merge Tag dropdown
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param object $form      The current form.
	 *
	 * @return object
	 */
	public function add_merge_tags( $form ) {
		?>
    <script type="text/javascript">
        gform.addFilter('gform_merge_tags', 'add_merge_tags');
        function add_merge_tags(mergeTags, elementId, hideAllFields, excludeFieldTypes, isPrepop, option){
            mergeTags["custom"].tags.push({ tag: '<?php echo self::$_merge_tag; ?>', label: 'Payment URL' });

            return mergeTags;
        }
    </script>
    <?php

    //return the form object from the php hook
    return $form;
	}

	/**
	 * Replace merge tags for URL.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param string $text 			The current text in which merge tags are being replaced.
	 * @param object $form      The current form.
	 * @param object $entry     The current entry.
	 *
	 *
	 * @return string
	 */

	public function replace_merge_tags( $text, $form, $entry ) {

		// Check that merge tag exists
		if ( strpos( $text, self::$_merge_tag ) === false ) {
      return $text;
    }

		// Get the PayPal payment URL
		$url = self::get_payment_url($form, $entry);

		// Replace the merge tag
		$text = str_replace( self::$_merge_tag, $url, $text );

		return $text;

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

		$url = self::get_payment_url($form, $entry);

		// Display link.
		printf(
			'<a target="_blank" href="%s">%s</a>',
			esc_url( $url ),
			'Send this link to the customer!'
		);

	}

	/**
	 * Get the PayPal URL for an entry
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array $form       The form object used to process the current entry.
	 * @param array $entry      The entry currently being viewed/edited.
	 *
	 * @uses GFForms::include_payment_addon_framework()
	 * @uses GFFeedAddOn::get_single_submission_feed()
	 * @uses GFPaymentAddOn::get_submission_data()
	 * @uses GFPayPal::redirect_url()
	 */
	public function get_payment_url( $form, $entry ) {

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

		// Return URL.
		return $url;
	}
}
