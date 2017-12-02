<?php

class Push7_Admin_Queuing {
  public function __construct() {
    add_action('admin_menu', array($this, 'update_queue'));
  }

  public function update_queue() {
    
  }
}
