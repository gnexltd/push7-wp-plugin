<?php

class Push7_Sdk {
  public function __construct() {
    add_action('wp_head', array($this, 'render'));
  }

  public function render() {
    $appno = get_option('push7_appno', null);
    if (get_option('push7_sdk_enabled') !== 'true' || empty($appno)){
      return;
    }
    ?>
    <script src="https://sdk.push7.jp/v2/p7sdk.js"></script>
    <script>p7.init("<?php echo $appno;?>");</script>
    <?php
  }
}
