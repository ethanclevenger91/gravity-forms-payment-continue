<?php

GFForms::include_addon_framework();
GFForms::include_payment_addon_framework();

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
	 * @var    string $_slug The slug used for this plugin, defined from payment-continue.php
	 */
	protected $_slug = GF_PAYMENT_CONTINUE_ADDON_SLUG;

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
	 * @access private
	 * @var    string $merge_tag The merge tag.
	 */
	private $merge_tag = '{payment_url}';

	/**
	 * Holds the GFPaymentAddon currently active. Populated by load_gateway
	 *
	 * @since  1.1
	 * @access private
	 * @var    object $gateway The GFPaymentAddon.
	 */
	private $gateway = null;

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

		$meets_requirements = $this->meets_minimum_requirements();
		if ( ! $meets_requirements['meets_requirements'] ) {
			return;
		}

		$this->load_gateway();

		add_action('gform_loaded', [$this, 'upgrade']);

		add_action('gform_entry_detail_meta_boxes', [$this, 'register_meta_box' ], 10, 3 );

		add_filter('gform_admin_pre_render', [$this, 'add_merge_tags'] );

		add_filter( 'gform_entries_column_filter', [$this, 'payment_url_entries_column_filter'], 10, 4);

	}

	/**
	 * Register all other hooks
	 *
	 * @since  1.0
	 * @access public
	 */

	public function init() {

		parent::init();

		$meets_requirements = $this->meets_minimum_requirements();
		if ( ! $meets_requirements['meets_requirements'] ) {
			return;
		}

		$this->load_gateway();

		add_filter('gform_replace_merge_tags', [$this, 'replace_merge_tags'], 10, 3);

	}

	/**
	 * Assign active GFPaymentAddon
	 *
	 * @since  1.1
	 * @access public
	 *
	 * @uses gf_paypal()
	 */
	public function load_gateway() {
		// Get instance of PayPal Add-On.
		// Eventually this will be a conditional
		$this->gateway = gf_paypal();
	}

	/**
	 * See if entry has processing payment
	 *
	 * @since  1.1
	 * @access public
	 *
	 * @param object $form The current form.
	 * @param object $entry The current entry.
	 *
	 * @uses GFFeedAddOn::get_active_feeds()
	 */
	public function has_processing_payment($form, $entry) {
		$payment_status = apply_filters( 'gform_payment_status', $entry['payment_status'], $form, $entry );

		// If active feeds were found and payment status is processing, display meta box.
		return ( $this->gateway->get_active_feeds( $form['id'] ) && 'Paid' !== $payment_status );
	}

	/**
	 * Return custom entry meta
	 *
	 * @since  1.1
	 * @access public
	 *
	 * @param array $entry_meta	The existing entry meta.
	 * @param int $form      		The current form.
	 *
	 *
	 * @return array
	 */
	 public function get_entry_meta($entry_meta, $form_id) {
		 $gateway = gf_paypal();
		 if($gateway->get_active_feeds( $form_id )) {
			 $entry_meta['payment_url'] = [
				 'label'                      => 'Payment URL',
				 'is_numeric'                 => false,
				 'is_default_column'          => true,
				 'update_entry_meta_callback' => [ $this, 'update_payment_url_meta' ],
			 ];
		 }
		 return $entry_meta;
 	 }

	/**
	 * Update payment url meta
	 *
	 * @since  1.1
	 * @access public
	 *
	 * @param array $key	The existing entry meta.
	 * @param int $form      		The current form.
	 *
	 *
	 * @return array
	 */
	public function update_payment_url_meta( $key, $entry, $form ) {
		if($this->has_processing_payment($form, $entry)) {
	  	return $this->get_payment_url($form, $entry); // return the value of the entry meta
		} else {
			gform_delete_meta( $entry['id'], 'payment_url' );
		}
	}

	/**
	 * Print UX-friendly link rather than full URL in entries table.
	 * Will not work with pre-1.1 meta
	 *
	 * @since  1.1
	 * @access public
	 *
	 * @param string $value					The entry meta.
	 * @param int $form_id      		The current form ID.
	 * @param string $field_id  		The meta key.
	 * @param object $entry 				The current entry.
	 *
	 *
	 * @return array
	 */
	function payment_url_entries_column_filter($value, $form_id, $field_id, $entry) {
		if($field_id == 'payment_url') {
			if($value) {
				$value = sprintf(
					'<a target="_blank" href="%s">%s</a>',
					esc_url( $value ),
					'Payment URL'
				);
			} else {
				$value = 'Payment complete.';
			}
		}
		return $value;
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
    	if(typeof gform !== 'undefined') {
        	gform.addFilter('gform_merge_tags', 'add_merge_tags');
        }
        function add_merge_tags(mergeTags, elementId, hideAllFields, excludeFieldTypes, isPrepop, option){
            mergeTags["custom"].tags.push({ tag: '<?php echo $this->merge_tag; ?>', label: 'Payment URL' });

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
		if ( strpos( $text, $this->merge_tag ) === false ) {
      return $text;
    }

		// Get the payment URL
		$url = $this->get_payment_url($form, $entry);

		// Replace the merge tag
		$text = str_replace( $this->merge_tag, $url, $text );

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
	 *
	 * @return array
	 */
	public function register_meta_box($meta_boxes, $entry, $form) {

		if ( $this->has_processing_payment($form, $entry) ) {

			$meta_boxes[ 'paypal_continue_url' ] = array(
				'title'	   => 'URL to Finish Payment',
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
	 * @uses GFFeedAddOn::get_single_submission_feed()
	 * @uses GFPaymentAddOn::get_single_submission_feed()
	 * @uses GFPayPal::redirect_url()
	 */
	public function add_details_meta_box( $args ) {

		$entry = $args['entry'];

		$url = rgar( $entry, 'payment_url' );

		// Display link.
		printf(
			'<a target="_blank" href="%s">%s</a>',
			esc_url( $url ),
			'Send this link to the customer!'
		);

	}

	/**
	 * Get the payment URL for an entry
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array $form       The form object used to process the current entry.
	 * @param array $entry      The entry currently being viewed/edited.
	 *
	 * @uses GFFeedAddOn::get_single_submission_feed()
	 * @uses GFPaymentAddOn::get_submission_data()
	 * @uses GFPayPal::redirect_url()
	 */
	public function get_payment_url( $form, $entry ) {

		// Get feed for entry.
		$feed = $this->gateway->get_single_submission_feed( $entry, $form );

		// Get submission data for feed.
		$submission_data = $this->gateway->get_submission_data( $feed, $form, $entry );

		// Get redirect URL.
		// Heavily dependant on PayPal.
		// If other gateways that need coverage are introduced, they'll hopefully implement this function.
		$url = $this->gateway->redirect_url( $feed, $submission_data, $form, $entry );

		// Return URL.
		return $url;
	}

	/**
	 * Run upgrades if necessary
	 * 
	 * @since  1.1.1
	 * @access public
	 */
	public function gf_payment_continue_upgrade() {
		// Newer updates should go at the top
		$available_upgrades = [
			'1_1_0',
		];
		$db_version = get_option('gravityformsaddon_'.GF_PAYMENT_CONTINUE_ADDON_SLUG.'_version');
		if(!$db_version || version_compare($db_version, GF_PAYMENT_CONTINUE_ADDON_VERSION, '<')) {
			foreach($available_upgrades as $upgrade) {
				if(version_compare($db_version, $upgrade, '<')) {
					require_once(plugin_dir_path(__FILE__).'includes/upgraders/upgrade_'.$upgrade.'.php');
					$class_name = 'GFPaymentContinueUpgrader_'.$upgrade;
					$upgrader = new $class_name();
					$upgrader->upgrade();
				} else {
					break;
				}
			}
		}
	}
}
