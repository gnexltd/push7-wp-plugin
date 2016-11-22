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
}
