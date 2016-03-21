<?php
if (get_option("push7_push_default_on_new")) {
  register_setting('push7-settings-group', 'push7_push_post_on_new');
  update_option("push7_push_post_on_new", get_option("push7_push_default_on_new"));
  delete_option("push7_push_default_on_new");
}
if (get_option("push7_push_default_on_update")) {
  register_setting('push7-settings-group', 'push7_push_post_on_update');
  update_option("push7_push_post_on_update", get_option("push7_push_default_on_update"));
  delete_option("push7_push_default_on_update");
}
?>
