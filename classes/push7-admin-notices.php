<?php

class Push7_Admin_Notices {
  public function __construct() {
    add_action('admin_notices', array($this, 'check_setting'));
    add_action('admin_notices', array($this, 'message'));
  }

  public function check_setting() {
    if (!get_option("push7_appno") || !get_option("push7_apikey")) {
      ?>
        <div class='update-nag is-dismissible'><p>
          <?php
            printf(
              __('Push7のダッシュボードにある自動プッシュ設定から、必要なAPPNOとAPIKEYを取得し%sから記入して下さい。','push7'),
              sprintf('<a href="%s">%s</a>', Push7::admin_url(), __('こちら', 'push7'))
            );
          ?>
        </p></div>
      <?php
    }
  }

  public function message() {
    if (isset($_SESSION['p7_success'])){
      ?><div class="notice notice-success is-dismissible"><p>Push7: <?php _e($_SESSION['p7_success'], 'push7' );?></p></div><?php
      unset($_SESSION['p7_success']);
    } elseif (isset($_SESSION['p7_error'])) {
      ?><div class="notice error is-dismissible"><p>Push7 Error: <?php _e($_SESSION['p7_error'], 'push7') ?></p></div><?php
      unset($_SESSION['p7_error']);
    } elseif (isset($_SESSION['p7_notice'])) {
      ?><div class="notice update-nag is-dismissible"><p>Push7 Error: <?php _e($_SESSION['p7_notice'], 'push7') ?></p></div><?php
      unset($_SESSION['p7_notice']);
    }
  }
}
