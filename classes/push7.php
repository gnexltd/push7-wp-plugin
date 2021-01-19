<?php

class Push7 {
  const API_URL = 'https://api.push7.jp/api/v1/';
  const VERSION = '3.0.6';

  public function __construct() {
    new Push7_Admin_Menu();
    new Push7_Admin_Notices();
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

    /**
     * カスタム投稿タイプごとの送信設定
     * Key: push7_push_pt_$post_type
     * Value:
     *  - 'true': 通知を送信する場合
     *  - ''(空文字): 通知を送信しない場合（チェックボックスにチェックが入っていない）
     *  - 'false': 過去に空文字と同じ意味合いで使われていた値
     */
    if(count(Push7::post_types()) >= 2) {
      // カスタム投稿タイプの設定が表示されている場合
      foreach (Push7::post_types() as $post_type) {
        $opt = "push7_push_pt_".$post_type;
        register_setting('push7-settings-group', $opt);

        // 記事のデフォルトは 'true', それ以外の投稿タイプのデフォルトは空文字(通知を送信しない)
        $default_value = $post_type === 'post' ? 'true' : '';
        $opt_value = get_option($opt, $default_value);

        // true もしくは ''(空文字) が想定される値
        if($opt_value === 'true' || $opt_value === '') continue;

        /* 後方互換性の維持コード */
        if($opt_value === 'false') {
          // 過去に空文字ではなく false が設定されていたことがあるので, 空文字に置き換える
          update_option($opt, '');
          continue;
        }

        // その他の予期しない値が入っている, もしくは値が設定されていない場合, デフォルト値を設定する
        update_option($opt, $default_value);
      }
    } else {
      // カスタム投稿タイプ設定が表示されていない場合 -> postはPush通知を送信する
      $opt = 'push7_push_pt_post';
      $default_value = 'true';
      $opt_value = get_option($opt);
      if($opt_value !== $default_value) {
        // optionに既にデフォルト値が入っていない場合, 自動的にデフォルト値を書き込む
        update_option($opt, $default_value);
      }
    }

    session_write_close();
  }

  public function add_setting_link($links){
    return array_merge($links, array('<a href="'.menu_page_url('push7', false).'">設定</a>'));
  }
}
