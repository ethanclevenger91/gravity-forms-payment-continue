<?php

require_once(plugin_dir_path(__FILE__).'abstract.php');

class GFPaymentContinueUpgrader_1_1_0 extends GFPaymentContinueUpgrader {
  public function upgrade() {
    $paypal = gf_paypal();
    $forms = GFAPI::get_forms();
    foreach($forms as $form) {
      if($paypal->get_active_feeds( $form['id'] )) {
        $search_criteria['field_filters'][] = ['key' => 'payment_status', 'value' => 'Paid', 'operator' => 'isnot'];
        $perPage = 20;
        $page = 0;
        $total_count = 0;
        $paging = ['offset' => 0, 'page_size' => $perPage];
        do {
          $entries = GFAPI::get_entries($form['id'], $search_criteria, [], $paging, $total_count);
          foreach($entries as $entry) {
            $payment_status = apply_filters( 'gform_payment_status', $entry['payment_status'], $form, $entry );
        		if( 'Paid' !== $payment_status ) {
              // Get feed for entry.
          		$feed = $paypal->get_single_submission_feed( $entry, $form );

          		// Get submission data for feed.
          		$submission_data = $paypal->get_submission_data( $feed, $form, $entry );

          		// Get redirect URL.
          		$url = $paypal->redirect_url( $feed, $submission_data, $form, $entry );
              gform_update_meta( $entry['id'], 'payment_url', $url);
            }
          }
          $page++;
          $paging = ['offset' => ($perPage * $page), 'page_size' => $perPage];
        } while (($perPage * $page) < $total_count);
      }
    }
  }
}
