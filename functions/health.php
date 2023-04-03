<?php

function abtion_health_check() {

  $site_health_transient = get_option('_transient_health-check-site-status-result');
  $site_health_obj = json_decode($site_health_transient);
  $site_url = get_site_url();
  $now_time = strtotime('now');
  $email_body = [];
  $domain_pathInfo = pathinfo($_SERVER['SERVER_NAME'], PATHINFO_EXTENSION);

  /**
   * -------------------------------------------------------
   * Check if site has any "critical" issues (Site Health) *
   * -------------------------------------------------------
  */
  if($site_health_obj) {
    if($site_health_obj->critical >= 1) {
      $email_body['Critical'] = 'There are one or more critical errors on this site.';
    }
  }

  /** --------------------------------------------
   * Check when SSL-certificate is set to expire *
   * ---------------------------------------------
  */
  $original_parse = parse_url($site_url, PHP_URL_HOST);
  $get = stream_context_create(array('ssl' => array('capture_peer_cert' => true)));
  $read = stream_socket_client('ssl://' . $original_parse . ':443', $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $get);
  $cert = stream_context_get_params($read);
  $certinfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);
  $expires = $certinfo['validTo_time_t'];
  $dateDiff = $expires - $now_time;
  $calculatedDifference = round($dateDiff / (60 * 60 * 24));

  // Check if theres less than 40 days to expiration
  if($calculatedDifference < 40 && $domain_pathInfo != 'test') {
    $email_body['SSL'] = date('d/m/Y H:i:s', $expires);
  } else {
    $email_body['SSL'] = 'Not applicable for DEV-sites';
  }

  /** --------------------------------------------------------------------------
   * Check GTMetrix score - but ONLY if its more than one week since last time *
   * ---------------------------------------------------------------------------
  */
  function abtion_gtmetrix($url) {

    // Make sure the newest "run date" is in the options
    update_option('last_gtmetrix_check', strtotime('now'));

    // GTMetrix API key - also the password
    $gtm_api = '4f9b3cf4ba78f8a4acd4cb32b1ccf208';
    $gtm_pass = ''; // Should be empty!

    // GTMetrix Test URL
    $gtm_test_url = 'https://gtmetrix.com/api/2.0/tests';

    // GTMetrix Data JSON
    $data = [
      'data' => [
        'type' => 'test',
        'attributes' => [
          'url' => $url
        ]
      ]
    ];

    // Create test on GTMetrix
    $test_response = wp_remote_post($gtm_test_url, [
      'body' => json_encode($data),
      'headers' => [
        'Authorization' => 'Basic ' . base64_encode($gtm_api . ':' . $gtm_pass),
        'Content-Type' => 'application/vnd.api+json'
      ]
    ]);
    $test_response_object = json_decode($test_response['body']);

    // Now let's wait 60 seconds - and make sure GTMetrix will process the test
    // This part should be rewritten in the near futue - checking the "report status" in stead of waiting 60 seconds.
    sleep(60);

    // Get the test results from GTMetrix based on the test-ID
    $test_url = $gtm_test_url . '/' . $test_response_object->data->id;
    $test_results = wp_remote_get($test_url, [
      'headers' => [
        'Authorization' => 'Basic ' . base64_encode($gtm_api . ':' . $gtm_pass),
      ]
    ]);
    $test_results_object = json_decode($test_results['body'])->data->attributes;

    $report = [
      'GTMetrix Grade' => $test_results_object->gtmetrix_grade,
      'Structure Score' => $test_results_object->structure_score,
      'Speed Index' => $test_results_object->speed_index,
      'Total Blocking Time' => $test_results_object->total_blocking_time,
      'Link to report (expires in 30 days)' => json_decode($test_results['body'])->data->links->report_url
    ];

    return $report;
  }

  $last_gtmetrix = get_option('last_gtmetrix_check');
  if(!$last_gtmetrix && $domain_pathInfo != 'test') {
    $email_body['GTMetrix'] = abtion_gtmetrix($site_url);
  } elseif($domain_pathInfo == 'test') {
    $email_body['GTMetrix'] = 'Not running for DEV-sites';
  } else {
    $gtmetrix_date = $now_time - $last_gtmetrix;
    $gtmetrix_diff = round($gtmetrix_date / (60 * 60 * 24));
    if($gtmetrix_diff >= 7) {
      $email_body['GTMetrix'] = abtion_gtmetrix($site_url);
    }
  }

  // Echo for now - send mail later...
  echo '<pre>' . print_r($email_body, true) . '</pre>';die;

}

add_action('admin_init', 'abtion_health_check');