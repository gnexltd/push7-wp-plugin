<?php
/*
Plugin Name: Push7
Plugin URI: https://push7.jp/
Description: Push7 plugin for WordPress
Version: 1.2
Author: GNEX Ltd.
Author URI: https://globalnet-ex.com
License:GPLv2 or later
Text Domain: push7
*/

new Push7();

class Push7 {

  const API_URL = 'https://dashboard.push7.jp/api/v1/';
  const VERSION = '1.1.1';

  public function __construct() {
    session_start();
    add_action('transition_post_status', array($this, 'push_post'), 10, 3);
    add_action('admin_menu', array($this, 'admin_menu'));
    add_action('admin_menu', array($this, 'metabox'));
    add_action('admin_init', array($this, 'page_init'));
    add_action('admin_notices', array($this, 'check_push_success'));
    add_action('admin_notices', array($this, 'is_enable'));
  }

  public function is_enable() {
    if ( !get_option("push7_appno") || !get_option("push7_apikey") ) {
      ?>
        <div class='update-nag is-dismissible'><p>
          <?php printf(
            __( 'Push7のダッシュボードにある自動プッシュ設定から、必要なAPPNOとAPIKEYを取得し%sから記入して下さい。','push7' ),
            sprintf( '<a href="%s">%s</a>', self::admin_url(), __( 'こちら', 'push7' ))
          ); ?>
        </p></div>
      <?php
    }
  }

  public function push_post($new_status, $old_status, $postData) {
    if (isset($_POST['push7_is_notify']) && $_POST['push7_is_notify'] == 'true') {
      if($new_status != 'publish') return;
      // emptyに式を渡すとWP提出時rejectされるので使用しないように
      $blogname = get_option ( get_option('push7_blog_title', '') == '' ? "blogname" : "push7_blog_title" );
      $appno = get_option( 'push7_appno', '' );
      $apikey = get_option( 'push7_apikey', '' );
      if(empty($appno) || empty($apikey)) return; //Validation
      $app_head = $this->get_app_head($appno);
      $icon_url = $app_head->icon;

      $data = array(
        'title' => $blogname,
        'body' => $postData->post_title,
        'icon' => $icon_url,
        'url' => get_permalink($postData),
        'apikey' => $apikey
      );

      $headers =  array(
        'Content-Type' => 'application/json',
      );

      $responce = wp_remote_post(
        self::API_URL . $appno.'/send',
        array(
          'method' => 'POST',
          'headers' => $headers + self::x_headers(),
          'body' => json_encode($data)
        )
      );
      $message = json_decode($responce['body']);

      if (is_wp_error($responce)) {
        $_SESSION['error_message'] = $responce->get_error_message();
      } else if (isset($message->success)) {
        $_SESSION['success_message'] = $message->success;
      } else if (isset($message->error)) {
        $_SESSION['error_message'] = $message->error;
      }
    }
  }

  public function get_app_head($appno) {
    $responce = wp_remote_get(
      self::API_URL.$appno.'/head',
      array(
        'headers' => self::x_headers()
      )
    );
    return json_decode( $responce[ 'body' ] );
  }

  public function check_push_success(){
    if (isset($_SESSION['success_message'])){
      ?><div class="notice-success is-dismissible"><p>Push7: <?php _e( '通知は正常に配信されました', 'push7' );?></p></div><?php
      unset($_SESSION['success_message']);
    } elseif (isset($_SESSION['error_message'])) {
      ?><div class="error is-dismissible"><p>Push7 Error: <?php echo $_SESSION['error_message'] ?></p></div><?php
      unset($_SESSION['error_message']);
    }
  }

  public function page_init() {
    register_setting('push7-settings-group', 'push7_blog_title');
    register_setting('push7-settings-group', 'push7_appno');
    register_setting('push7-settings-group', 'push7_apikey');
    register_setting('push7-settings-group', 'push7_push_default_on_new');
    register_setting('push7-settings-group', 'push7_push_default_on_update');
    // 初期値の設定
    if(!get_option("push7_push_default_on_new")) update_option("push7_push_default_on_new", "true");
    if(!get_option("push7_push_default_on_update")) update_option("push7_push_default_on_update", "true");

    load_plugin_textdomain( 'push7', null, dirname(__FILE__) . '/languages' );
  }

  public function admin_menu() {
    add_submenu_page(
      'options-general.php',
      'Push7',
      'Push7設定',
      'edit_dashboard',
      'push7',
      array($this, 'view_setting')
    );
  }

  public function metabox() {
    $post_types = get_post_types(array('_builtin' => false)) + array('post' => 'post');
    foreach ($post_types as $post_type) {
      add_meta_box(
        'push7metabox',
        __( 'Push7 通知設定', 'push7' ),
        array($this, 'view_metabox'),
        $post_type,
        'side'
      );
    }
  }

  public function view_setting() {
    include 'setting.php';
  }

  public function view_metabox() {
    include 'metabox.php';
  }

  public static function check_postType(){
    global $post;
    switch ($post->post_status) {
      // 新規投稿時
      case 'auto-draft':
        return get_option("push7_push_default_on_new");
      // 記事更新時
      case 'publish':
        return get_option("push7_push_default_on_update");
      case 'draft':
        return get_option("push7_push_default_on_update");
    }
  }


  public static function admin_url () {
    $args = array( 'page' => 'push7' );
    return add_query_arg( $args ,  admin_url( 'options-general.php' ));
  }

  public static function x_headers() {
    return array(
      'X-Push7' => 'WordPress Plugin '.self::VERSION,
      'X-Push7-Appno' => get_option( 'push7_appno', '' )
    );
  }
}
