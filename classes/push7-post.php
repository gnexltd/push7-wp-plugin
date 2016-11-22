<?php

class Push7_Post {
  public function __construct() {
    add_action('transition_post_status', array($this, 'hook_transition_post_status'), 10, 3);
    add_action('add_meta_boxes', array($this, 'adding_meta_boxes'));
  }

  public function hook_transition_post_status($new_status, $old_status, $postData) {
    global $push7;
    $push7->init();

    if ($new_status !== 'publish') {
      return;
    }

    if (array_key_exists('metabox_exist', $_POST)) {
      if (!empty($_POST['push7_not_notify'])) {
        $_SESSION['notice_message'] = '右下の「通知を送信しない」のチェックボックスが入っていたため通知は送信されませんでした。';
        return;
      }
    } else {
      if ($old_status === 'future') {
        $future_opt_name = 'push7_future_'.$postData->ID;
        if (get_option($future_opt_name) === false) {
          return;
        } else {
          delete_option($future_opt_name);
        }
      } elseif ($this::push_default_config() === 'false') {
        return;
      }
    }

    $this->push($postData);
  }

  public function push($postData) {
    $blogname = get_option(get_option('push7_blog_title', '') === '' ? "blogname" : "push7_blog_title");
    $appno = get_option('push7_appno', '');
    $apikey = get_option('push7_apikey', '');
    if( empty($appno) || empty($apikey) ) {
      return;
    }

    $app_head_responce = self::get_app_head($appno);
    if (is_wp_error($app_head_responce)) {
      $err = $app_head_responce->get_error_message();
      if (!strpos($err, 'SSL certificate problem')) {
        $message =
          'SSLの検証がpush通知を阻害している可能性があります。'
          .sprintf('<a href="%s">%s</a>', Push7::admin_url(), __( '管理画面', 'push7' ))
          .'よりSSLの検証を無効化していただくことで対処できる可能性があります。';
      }
      $_SESSION['error_message'] = $message;
      return;
    }

    $app_head = json_decode($app_head_responce['body']);
    $icon_url = $app_head->icon;

    $data = array(
      'title' => $blogname,
      'body' => $postData->post_title,
      'icon' => $icon_url,
      'url' => get_permalink($postData),
      'apikey' => $apikey
    );

    $responce = wp_remote_post(
      Push7::API_URL . $appno.'/send',
      array(
        'method' => 'POST',
        'headers' => array(
          'Content-Type' => 'application/json',
          'X-Push7' => 'WordPress Plugin '.Push7::VERSION,
          'X-Push7-Appno' => get_option('push7_appno', '')
        ),
        'body' => json_encode($data),
        'user-agent' => Push7::user_agent(),
        'sslverify' => Push7::sslverify()
      )
    );
    $message = json_decode($responce['body']);

    if (is_wp_error($responce)) {
      $_SESSION['p7_error'] = $responce->get_error_message();
    } else if (isset($message->error)) {
      $_SESSION['p7_error'] = $message->error;
    } else {
      $_SESSION['p7_success'] = '通知は正常に配信されました';
    }
  }

  public function get_app_head($appno) {
    $responce = wp_remote_get(
      Push7::API_URL.$appno.'/head',
      array(
        'headers' => array(
          'X-Push7' => 'WordPress Plugin '.Push7::VERSION,
          'X-Push7-Appno' => get_option('push7_appno', '')
        ),
        'user-agent' => Push7::user_agent(),
        'sslverify' => Push7::sslverify()
      )
    );
    return $responce;
  }

  public function adding_meta_boxes() {
    foreach (Push7::post_types() as $post_type) {
      add_meta_box(
        'push7metabox',
        __( 'Push7 通知設定', 'push7' ),
        array($this, 'metabox'),
        $post_type,
        'side'
      );
    }
  }

  public function metabox(){
    global $post;
    ?>
      <input type="hidden" name="metabox_exist" value="true">
      <input type="checkbox" name="push7_not_notify" value="false" <?php checked("false", self::push_default_config($post)); ?>>通知を送信しない
    <?php
  }

  public function push_default_config($post) {
    $opt = "push7_push_pt_".get_post_type($post);
    if ($post->post_status === 'publish') {
      return 'false';
    } else {
      return var_export(get_option($opt), true);
    }
  }
}
