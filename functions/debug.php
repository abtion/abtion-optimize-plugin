<?php

function abtion_debug_notice() {

  $class = 'notice notice-warning';
  $message = __('THIS PLUGIN IS CURRENTLY BEING DEVELOPED', 'abtion');

  printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);

}

add_action('admin_notices', 'abtion_debug_notice');