<?php

class Push7 {
  const API_URL = 'https://api.push7.jp/api/v1/';
  const VERSION = '3.0.3';
  const RESERVED_LINE = 1123200; // 13 days (86,400 * 13)

  public function __construct() {
    new Push7_Admin_Menu();
    new Push7_Admin_Notices();
    new Push7_Admin_Queuing();
    new Push7_Post();
    new Push7_Sdk();

    add_action('admin_init', array($this, 'init'));
    add_filter('plugin_action_links_'.PUSH7_BASE_NAME, array($this, 'add_setting_link'));
  }

  public static function admin_url() {
    return add_query_arg(array('page' => 'push7'), admin_url('options-general.php'));
  }

  public static function post_types() {
    return array_merge(get_post_types(array('_builtin' => false)), array('post' => 'post'));
  }

  public static function appno() {
    return get_option('push7_appno', '');
  }

  public static function apikey() {
    return get_option('push7_apikey', '');
  }

  public static function user_agent() {
    global $wp_version;
    return sprintf(
      "WordPress/%s; %s; Push7:%s/PHP%s",
      $wp_version,
      get_bloginfo('url'),
      Push7::VERSION,
      phpversion()
    );
  }

  public static function sslverify() {
    return get_option('push7_sslverify_disabled') === 'false';
  }

  public static function box_enabled() {
    return get_option('push7_sdk_enabled') === 'true';
  }

  public static function is_session_started() {
    if ( php_sapi_name() === 'cli' ) return false;
    if ( version_compare(phpversion(), '5.4.0', '>=') ) return session_status() === PHP_SESSION_ACTIVE;
    return !(session_id() === '');
  }

  public function init() {
    if (!$this->is_session_started()) session_start();

    $default_check_options = array(
      'push7_sslverify_disabled',
      'push7_sdk_enabled'
    );

    $settings_params = array(
      'push7_blog_title',
      'push7_appno',
      'push7_apikey',
      'push7_sslverify_disabled',
      'push7_sdk_enabled'
    );

    foreach ($settings_params as $setting) {
      register_setting('push7-settings-group', $setting);
    }

    foreach ($default_check_options as $option) {
      if (!get_option($option)) update_option($option, "false");
    }

    foreach (get_categories() as $category) {
      $opt = "push7_push_ctg_".$category->slug;
      register_setting('push7-settings-group', $opt);

      /* 後方互換性の維持コード */
      if (get_option($opt, null) === '1' || is_null(get_option($opt, null))) update_option($opt, 'true');
      if (get_option($opt, null) === '') update_option($opt, 'false');
    }

    foreach (Push7::post_types() as $post_type) {
      $opt = "push7_push_pt_".$post_type;
      register_setting('push7-settings-group', $opt);
      if (!is_null(get_option($opt, null))) continue;
      update_option($opt, ($post_type === 'post') ? 'true' : 'false');
    }
  }

  public function add_setting_link($links){
    return array_merge($links, array('<a href="'.menu_page_url('push7', false).'">設定</a>'));
  }
}
