<?php
GFForms::include_addon_framework();
class GravityFormsPayPalContinue extends GFAddOn
{
    protected $_version = GF_PAYPAL_CONTINUE_ADDON_VERSION;
    protected $_min_gravityforms_version = '1.9';
    protected $_slug = 'paypalcontinue';
    protected $_path = 'gravityforms-paypalcontinue/gravityforms-paypalcontinue.php';
    protected $_full_path = __FILE__;
    protected $_title = 'Gravity Forms PayPal Continue Add-On';
    protected $_short_title = 'PayPal Continue Add-On';

    private static $_instance = null;

    public static function get_instance() {
        if ( self::$_instance == null ) {
            self::$_instance = new GravityFormsPayPalContinue();
        }

        return self::$_instance;
    }

    public function init() {
      parent::init();
      if(class_exists('GFPayPal')) {
        add_action('gform_entry_detail_meta_boxes', array( $this, 'register_meta_box' ), 10, 3 );
      } else {
        add_action('admin_notices', [$this, 'admin_notice']);
      }
    }

    public function admin_notice() {
      $class = 'notice notice-warning';
    	$message = __( 'The Gravity Forms PayPal Standard Continue URL plugin requires the Gravity Forms PayPal Standard plugin to be activated.', 'sample-text-domain' );

    	printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
    }

    public function register_meta_box($meta_boxes, $entry, $form) {
    	$paymentAddon = new GFPayPal();
      $payment_status = apply_filters( 'gform_payment_status', $entry['payment_status'], $form, $entry );
      if($paymentAddon->get_active_feeds($form['id']) && $payment_status == 'Processing') {
        $meta_boxes[ 'paypal_continue_url' ] = array(
            'title'    => 'PayPal URL to Finish Payment',
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
