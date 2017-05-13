<?php

class Push7_Post {
  public function __construct() {
    add_action('transition_post_status', array($this, 'hook_transition_post_status'), 10, 3);
    add_action('add_meta_boxes', array($this, 'adding_meta_boxes'));
  }

  public function hook_transition_post_status($new_status, $old_status, $post_data) {
    global $push7;
    $push7->init();

    if ($new_status !== 'publish') return;

    if (array_key_exists('metabox_exist', $_POST)) {
      if (isset($_POST['push7_not_notify']) && $_POST['push7_not_notify'] === 'false') {
        $_SESSION['notice_message'] = '右下の「通知を送信しない」のチェックボックスが入っていたため通知は送信されませんでした。';
        return;
      }
    } elseif(get_option('push7_update_from_thirdparty') == 'false') {
      return;
    }

    if ($old_status === 'future') {
      $future_opt_name = 'push7_future_'.$post_data->ID;
      if (get_option($future_opt_name) === false) return;
      delete_option($future_opt_name);
    }

    if(!self::check_ignored_posttype($post_data)){
      return;
    }

    if(!self::check_ignored_category($post_data)){
      return;
    }

    $this->push($post_data);
  }

  public function push($post_data) {
    $blogname = get_option(get_option('push7_blog_title', '') === '' ? 'blogname' : 'push7_blog_title');
    $appno = Push7::appno();
    $apikey = Push7::apikey();
    if( empty($appno) || empty($apikey) ) return;
    $app_head_response = self::get_app_head($appno);
    if (is_wp_error($app_head_response)) {
      self::check_ssl_error( $app_head_response->get_error_message() );
      return;
    }

    $app_head = json_decode($app_head_response['body']);
    $icon_url = $app_head->icon;

    $data = array(
      'title' => $blogname,
      'body' => $post_data->post_title,
      'icon' => $icon_url,
      'url' => get_permalink($post_data),
      'apikey' => $apikey
    );

    $response = wp_remote_post(
      Push7::API_URL . $appno.'/send',
      array(
        'method' => 'POST',
        'headers' => array(
          'Content-Type' => 'application/json',
          'X-Push7' => 'WordPress Plugin '.Push7::VERSION,
          'X-Push7-Appno' => Push7::appno()
        ),
        'body' => json_encode($data),
        'user-agent' => Push7::user_agent(),
        'sslverify' => Push7::sslverify()
      )
    );

    $message = json_decode($response['body']);

    if (is_wp_error($response)) {
      $_SESSION['p7_error'] = $response->get_error_message();
      return;
    }

    if (isset($message->error)) {
      $_SESSION['p7_error'] = $message->error;
      return;
    }

    $_SESSION['p7_success'] = '通知は正常に配信されました';
  }

  public function check_ssl_error($err){
    if (strpos($err, 'SSL certificate problem')) return;
    $_SESSION['error_message'] = sprintf(
      "SSLの検証がpush通知を阻害している可能性があります。<a href='%s'>%s</a>よりSSLの検証を無効化していただくことで対処できる可能性があります。",
      Push7::admin_url(),
      '管理画面'
    );
  }

  public function get_app_head($appno) {
    return wp_remote_get(
      Push7::API_URL.$appno.'/head',
      array(
        'headers' => array(
          'X-Push7' => 'WordPress Plugin '.Push7::VERSION,
          'X-Push7-Appno' => Push7::appno()
        ),
        'user-agent' => Push7::user_agent(),
        'sslverify' => Push7::sslverify()
      )
    );
  }

  public function adding_meta_boxes() {
    foreach (Push7::post_types() as $post_type) {
      add_meta_box(
        'push7metabox',
        'Push7 通知設定',
        array($this, 'metabox'),
        $post_type,
        'side'
      );
    }
  }

  public function metabox(){
    global $post;
    ?>
      <input type='hidden' name='metabox_exist' value='true'>
      <input type='checkbox' name='push7_not_notify' value='true'>
      通知を送信しない
    <?php
  }

  public function check_ignored_posttype($post){
    $post_type = get_post_type(get_post($post)->ID);
    return $post_type == 'post' ?: !(get_option("push7_push_pt_".$post_type, null) === "false");
  }

  public function check_ignored_category($post){
    $post = get_post($post);
    $categories = get_the_category($post->ID);
    foreach ($categories as $category) {
      if(get_option("push7_push_ctg_".$category->slug, null) === "false"){
        return false;
      }
    }
    return true;
  }
}
