<?php

/*
Plugin Name: Push7
Plugin URI: https://push7.jp/
Description: Push7 plugin for WordPress
Version: 1.5.0
Author: GNEX Ltd.
Author URI: https://globalnet-ex.com
License:GPLv2 or later
Text Domain: push7
*/

require_once (plugin_dir_path( __FILE__ ).'classes/push7.php');
require_once (plugin_dir_path( __FILE__ ).'classes/push7-admin-notices.php');
require_once (plugin_dir_path( __FILE__ ).'classes/push7-admin-menu.php');
require_once (plugin_dir_path( __FILE__ ).'classes/push7-post.php');

define('PUSH7_DIR', dirname(__FILE__));
define('PUSH7_BASE_NAME', plugin_basename(__FILE__));

$push7 = new Push7();
