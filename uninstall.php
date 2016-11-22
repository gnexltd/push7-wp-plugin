<?php

delete_option('push7_blog_title');
delete_option('push7_appno');
delete_option('push7_apikey');
delete_option('push7_sslverify_disabled');

foreach (get_categories() as $category) {
  $opt = "push7_push_ctg_".$category->slug;
  delete_option($opt);
}

foreach (Push7::post_types() as $post_type) {
  $opt = "push7_push_pt_".$post_type;
  delete_option($opt);
}
