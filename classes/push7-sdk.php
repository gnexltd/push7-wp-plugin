<?php

class Push7_Box {
  public function __construct() {
    add_action('wp_head', array($this, 'render'));
  }

  public function render() {
    if (get_option('push7_sdk_enabled') === 'true') {
      ?>
        <script src="https://sdk.push7.jp/v2/p7sdk.js"></script>
      <?php
    }
  }
}
