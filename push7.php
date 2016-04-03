<?php
/*
Plugin Name: Push7
Plugin URI: https://push7.jp/
Description: Push7 plugin for WordPress
Version: 1.4.2
Author: GNEX Ltd.
Author URI: https://globalnet-ex.com
License:GPLv2 or later
Text Domain: push7
*/

new Push7();

class Push7 {

  const API_URL = 'https://api.push7.jp/api/v1/';
  const VERSION = '1.4.2';

  public function __construct() {
    add_action('transition_post_status', array($this, 'push_post'), 10, 3);
    add_action('transition_post_status', array($this, 'check_future'), 10, 3);
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
    // new_statusが公開済みでなければpush通知しない
    if($new_status !== 'publish') return;

    // metaboxが存在する場合
    if (!empty($_POST['metabox_exist'])) {
      // 「通知しない」にチェックが入っている場合、push通知しない
      if (!empty($_POST['push7_not_notify'])) return;

      foreach (get_the_category($post) as $category) {
        if (get_option("push7_push_ctg_".$category->slug) !== "true") {
          $_SESSION['notice_message'] =
            'カテゴリー「'
            .$category->name
            .'」の「投稿時自動プッシュする」の設定が無効になっていたので、プッシュ通知は送信されませんでした。もしプッシュ通知を送信したい場合'
            .sprintf('<a href="%s" target="_blank">こちら</a>', 'https://dashboard.push7.jp/u/d/')
            .'より手動で送信をお願いします。';
          return;
        }
      }

    // metaboxが存在しない(内部での処理,API経由,サードパーティのクライアント経由での投稿の場合)
    } else {
      if ($old_status === 'future') {
        $future_opt_name = 'push7_future_'.$postData->ID;
        if (get_option($future_opt_name) === false) {
          // option-table内にデータが保持されていなければpush通知しない
          return;
        } else {
          delete_option($future_opt_name);
        }
      } elseif ($this->push_default_config() === 'false') {
        // 設定を読み、falseならばpush通知しない
        return;
      }
    }

    // emptyに式を渡すとWP提出時rejectされるので使用しないように
    $blogname = get_option ( get_option('push7_blog_title', '') === '' ? "blogname" : "push7_blog_title" );
    $appno = get_option( 'push7_appno', '' );
    $apikey = get_option( 'push7_apikey', '' );
    if(empty($appno) || empty($apikey)) return; //Validation

    $app_head_responce = $this->get_app_head($appno);
    if (is_wp_error($app_head_responce)) {
      $message = $app_head_responce->get_error_message();
      if (strpos($message, 'SSL certificate problem') !== false) {
        $message =
          'SSLの検証がpush通知を阻害している可能性があります。'
          .sprintf('<a href="%s">%s</a>', self::admin_url(), __( '管理画面', 'push7' ))
          .'よりSSLの検証を無効化していただくことで対処できる可能性があります。';
      }
      $_SESSION['error_message'] = $message;
      return;
    } else {
      $app_head = json_decode($app_head_responce['body']);
    }
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
        'body' => json_encode($data),
        'sslverify' => self::sslverify()
      )
    );
    $message = json_decode($responce['body']);

    if (is_wp_error($responce)) {
      $_SESSION['error_message'] = $responce->get_error_message();
    } else if (isset($message->error)) {
      $_SESSION['error_message'] = $message->error;
    } else {
      $_SESSION['success'] = '1';
    }
  }

  public function get_app_head($appno) {
    $responce = wp_remote_get(
      self::API_URL.$appno.'/head',
      array(
        'headers' => self::x_headers(),
        'sslverify' => self::sslverify()
      )
    );
    return $responce;
  }

  public function check_push_success(){
    if (isset($_SESSION['success'])){
      ?><div class="notice notice-success is-dismissible"><p>Push7: <?php _e( '通知は正常に配信されました', 'push7' );?></p></div><?php
      unset($_SESSION['success']);
    } elseif (isset($_SESSION['error_message'])) {
      ?><div class="error is-dismissible"><p>Push7 Error: <?php echo $_SESSION['error_message'] ?></p></div><?php
      unset($_SESSION['error_message']);
    } elseif (isset($_SESSION['notice_message'])) {
      ?><div class="notice update-nag is-dismissible"><p>Push7 Error: <?php echo $_SESSION['notice_message'] ?></p></div><?php
      unset($_SESSION['notice_message']);
    }
  }

  public function check_future($new_status, $old_status, $postData) {
    if ($new_status === 'future') {
      if ( (!empty($_POST['metabox_exist'])) && (empty($_POST['push7_not_notify'])) ) {
        // 投稿された時にそれが予約投稿でありかつmetaboxから渡された値が'通知する'だった場合option-tableに保持しておく
        update_option('push7_future_'.$postData->ID, 1);
      }
    }
  }

  public function page_init() {
    session_start();
    register_setting('push7-settings-group', 'push7_blog_title');
    register_setting('push7-settings-group', 'push7_appno');
    register_setting('push7-settings-group', 'push7_apikey');
    register_setting('push7-settings-group', 'push7_sslverify_disabled');

    if(get_option("push7_sslverify_disabled") === false) update_option("push7_sslverify_disabled", "false");
    if(get_option("push7_store") === false) update_option("push7_store", array());

    // カテゴリ設定
    foreach (get_categories() as $category) {
      $name = "push7_push_ctg_".$category->slug;
      register_setting('push7-settings-group', $name);
      if(get_option($name) === false) update_option($name, "true");
    }

    // デフォルトの投稿タイプ(post)及びカスタム投稿タイプ設定
    foreach (self::post_types() as $post_type) {
      $name = "push7_push_pt_".$post_type;
      register_setting('push7-settings-group', $name);
      if(get_option($name) === false) update_option($name, "false");
    }

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
    foreach (self::post_types() as $post_type) {
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

  public function push_default_config() {
    global $post;
    $name = "push7_push_pt_".get_post_type($post);
    // 新規投稿なら設定されているデフォルト値を返し,新規投稿でないならfalse(デフォルトでブッシュ通知をしない)と返す
    return $post->post_date === current_time('mysql') ? get_option($name) : "false";
  }

  public static function admin_url() {
    $args = array( 'page' => 'push7' );
    return add_query_arg( $args ,  admin_url( 'options-general.php' ));
  }

  public static function post_types() {
    return array('post' => 'post') + get_post_types(array('_builtin' => false));
  }

  public static function disp_post_type($post_type) {
    switch ($post_type) {
      case 'post':
        return 'post(通常の投稿)';
      default:
        return $post_type;
    }
  }

  public static function x_headers() {
    return array(
      'X-Push7' => 'WordPress Plugin '.self::VERSION,
      'X-Push7-Appno' => get_option( 'push7_appno', '' )
    );
  }

  public static function sslverify() {
    return get_option('push7_sslverify_disabled') === 'false' ? true : false;
  }
}
