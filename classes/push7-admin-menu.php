<?php

class Push7_Admin_Menu {
  public function __construct() {
    add_action('admin_menu', array($this, 'add_page'));
  }

  public function add_page() {
    add_submenu_page(
      'options-general.php',
      'Push7',
      'Push7設定',
      'edit_dashboard',
      'push7',
      array($this, 'render_setting')
    );
  }

  public function render_setting() {
    include PUSH7_DIR.'/setting.php';
  }

  public function debug_dump() {
    $data = array(
      'host' => $_SERVER['SERVER_NAME'],
      'plugin_ver' => Push7::VERSION,
      'system' => php_uname(),
      'php_ver' => phpversion(),
      'appno' => get_option('push7_appno'),
      'sdk_enabled' => get_option('push7_sdk_enabled')
    );
    return base64_encode(json_encode($data));
  }
}
