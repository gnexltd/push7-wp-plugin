<?php

class Push7_Box {
  public function __construct() {
    add_action('wp_head', array($this, 'render'));
  }

  public function render() {
    if (get_option('push7_box_enabled') === 'true') {
      ?>
        <script src=""></script>
        <meta name="p7appno" content="<?php echo get_option('push7_appno'); ?>">
      <?php
    }
  }
}
