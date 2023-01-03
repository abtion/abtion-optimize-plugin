<?php
/**
* Plugin Name: Abtion Optimize
* Plugin URI: http://abtion.com
* Description: This site is optimized by Abtion
* Author: Abtion.com
* Author URI: http://abtion.com/
* Version: 0.2
* License: GPLv2 or later
*/

/** No direct access */
if(!defined('ABSPATH')) {
  exit;
}

/** Current plugin path */
define('PLUGIN_DIR', plugin_dir_path(__FILE__));

/** Add the optimizing functions */
$autoload = ['functions/*.php'];
foreach ($autoload as $path) {
  $relative = PLUGIN_DIR .$path;
  $files = (is_file($relative)) ? [$relative] : glob($relative);
  foreach($files as $file) { 
    require_once $file; 
  }
}

/** 
 * ----------------------------------
 * Simple 'remove_action' functions *
 * ----------------------------------
 */

// Remove WordPress version number
remove_action('wp_head', 'wp_generator');

// Remove RSS feed links + Extra feed links
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'feed_links_extra', 3);

// Remove link for "Windows Live Writer
remove_action('wp_head', 'wlwmanifest_link');

// Remove Adjacent Post Links
remove_action('wp_head', 'adjacent_posts_rel_link');

// Remove shortlink tag (no need for 2 links (Bad SEO))
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);