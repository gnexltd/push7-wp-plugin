<?php

delete_option('push7_blog_title');
delete_option('push7_appno');
delete_option('push7_apikey');
delete_option('push7_sslverify_disabled');
delete_option('push7_sdk_enabled');

foreach (get_categories() as $category) {
  $opt = "push7_push_ctg_".$category->slug;
  delete_option($opt);
}

$post_types = array('post' => 'post') + get_post_types(array('_builtin' => false));
foreach ($post_types as $post_type) {
  $opt = "push7_push_pt_".$post_type;
  delete_option($opt);
}
