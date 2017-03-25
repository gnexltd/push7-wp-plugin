<?php

class Push7 {
  const API_URL = 'https://api.push7.jp/api/v1/';
  const VERSION = '2.0.0';

  public function __construct() {
    new Push7_Admin_Menu();
    new Push7_Admin_Notices();
    new Push7_Post();
    new Push7_Sdk();

    add_action('admin_init', array($this, 'init'));
    add_filter('plugin_action_links_'.PUSH7_BASE_NAME, array($this, 'add_setting_link'));
  }

  public function init() {
    if (!$this->is_session_started()) {
      session_start();
    }

    register_setting('push7-settings-group', 'push7_blog_title');
    register_setting('push7-settings-group', 'push7_appno');
    register_setting('push7-settings-group', 'push7_apikey');
    register_setting('push7-settings-group', 'push7_sslverify_disabled');
    register_setting('push7-settings-group', 'push7_sdk_enabled');

    if (!get_option("push7_sslverify_disabled")) {
      update_option("push7_sslverify_disabled", "false");
    }
    if (!get_option("push7_sslverify_disabled")) {
      update_option("push7_sdk_enabled", "false");
    }

    foreach (get_categories() as $category) {
      $opt = "push7_push_ctg_".$category->slug;
      register_setting('push7-settings-group', $opt);
      if (is_null(get_option($opt, null))) {
        update_option($opt, true);
      }
    }

    foreach (Push7::post_types() as $post_type) {
      $opt = "push7_push_pt_".$post_type;
      register_setting('push7-settings-group', $opt);
      if ($post_type === 'post') {
        if (is_null(get_option($opt, null))) {
          update_option($opt, true);
        }
      } else {
        if (is_null(get_option($opt, null))) {
          update_option($opt, false);
        }
      }
    }

    load_plugin_textdomain('push7', null, PUSH7_DIR.'/languages');
  }

  public static function admin_url() {
    return add_query_arg(array('page' => 'push7'), admin_url('options-general.php'));
  }

  public static function post_types() {
    return array('post' => 'post') + get_post_types(array('_builtin' => false));
  }

  public static function user_agent() {
    return 'WordPress/'.$wp_version.'; '.get_bloginfo('url').'; Push7:'.Push7::VERSION;
  }

  public static function sslverify() {
    return get_option('push7_sslverify_disabled') === 'false' ? true : false;
  }

  public static function box_enabled() {
    return get_option('push7_sdk_enabled') === 'true' ? true : false;
  }

  public static function is_session_started() {
    if ( php_sapi_name() !== 'cli' ) {
      if ( version_compare(phpversion(), '5.4.0', '>=') ) {
        return session_status() === PHP_SESSION_ACTIVE ? true : false;
      } else {
        return session_id() === '' ? false : true;
      }
    }
    return false;
  }

  public function add_setting_link($links){
    return array_merge($links, array('<a href="'.menu_page_url('push7', false).'">設定</a>'));
  }
}
