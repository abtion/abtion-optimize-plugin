<?php

function abtion_health_check() {

  $site_health_transient = get_option('_transient_health-check-site-status-result');
  $site_health_obj = json_decode($site_health_transient);

  $email_body = [];
  
  /**
   * -------------------------------------------------------
   * Check if site has any "critical" issues (Site Health) *
   * -------------------------------------------------------
  */
  if($site_health_obj->critical >= 1) {
    $email_body['Critical'] = 'There are one or more critical errors on this site.';
  }

  /** --------------------------------------------
   * Check when SSL-certificate is set to expire *
   * ---------------------------------------------
  */
  $original_parse = parse_url(get_site_url(), PHP_URL_HOST);
  $get = stream_context_create(array('ssl' => array('capture_peer_cert' => true)));
  $read = stream_socket_client('ssl://' . $original_parse . ':443', $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $get);
  $cert = stream_context_get_params($read);
  $certinfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);
  $expires = $certinfo['validTo_time_t'];
  $now_time = strtotime('now');
  $dateDiff = $expires - $now_time;
  $calculatedDifference = round($dateDiff / (60 * 60 * 24));

  // Check if theres less than 40 days to expiration
  if($calculatedDifference < 40) {
    $email_body['SSL'] = date('d/m/Y H:i:s', $expires);
  }

  // Echo for now - send mail later...
  echo '<pre>' . print_r($email_body, true) . '</pre>';die;

}

add_action('admin_init', 'abtion_health_check');