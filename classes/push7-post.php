<?php

class Push7_Post {
  public function __construct() {
    add_action('transition_post_status', array($this, 'hook_transition_post_status'), 10, 3);
    add_action('add_meta_boxes', array($this, 'adding_meta_boxes'));
  }

  public function hook_transition_post_status($new_status, $old_status, $post) {
    global $push7;
    $push7->init();

    if ($old_status == "future" && $new_status != "publish") $this->delete_reserved_push($post);
    if ($post->post_status == "auto-draft") return;
    if ($new_status == "publish" && $old_status != "future" && isset($_POST['metabox_exist'])) $this->push($post);

    if ($new_status == "future") {
      if (!isset($_POST['metabox_exist'])) return;
      $response = $this->push($post, true);
      if ($response) $this->set_ripd_dict($this->get_post_id($post), $response['pushid']);
    }
  }

  protected function delete_reserved_push($post) {
    $rp_id = $this->get_rpid_from_post_data($post);
    if (!$rp_id) return;

    $data = array(
      'apikey' => Push7::apikey()
    );

    $result = wp_remote_request(
      Push7::API_URL . Push7::appno() . '/reserved_push/delete/' . $rp_id,
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

    if (is_wp_error($response)) {
      $_SESSION['p7_error'] = $response->get_error_message();
      return;
    }

    if (isset($message['error'])) {
      $_SESSION['p7_error'] = $message['error'];
      return false;
    }

    $this->set_ripd_dict($post, null);
  }

  public function push($post, $is_rp=false) {
    if (isset($_REQUEST['push7_not_notify'])) return;

    $post_id = $this->get_post_id($post);

    if ($is_rp) {
      $rp_id = $this->get_rpid_from_post_data($post_id);
      if ($rp_id) return;
    }

    $post_type = get_post_type($post_id);

    if ($this->check_ignored_posttype($post_id)) return;
    // 通常投稿の場合のみカテゴリが存在するので、チェックを行う
    if ($post_type === 'post' && $this->check_ignored_category($post_id)) return;

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
      'body' => $post->post_title,
      'icon' => $icon_url,
      'url' => get_permalink($post),
      'apikey' => $apikey
    );

    // push7の予約投稿は分粒度での配信しかできないため、1分追加しないと記事公開前にpushが配送される可能性が高い.
    if ($is_rp) $data['transmission_time'] = date("Y-m-d H:i", strtotime(get_post($post)->post_date.'+1 minute'));

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

    if (is_wp_error($response)) {
      $_SESSION['p7_error'] = $response->get_error_message();
      return;
    }

    $message = json_decode($response['body'], true);

    if (isset($message['error'])) {
      $_SESSION['p7_error'] = $message['error'];
      return false;
    }

    return $message;
  }

  public function check_ssl_error($err){
    if (strpos($err, 'SSL certificate problem')) return;
    $_SESSION['error_message'] = sprintf( "SSLの検証がpush通知を阻害している可能性があります。<a href='%s'>管理画面</a>よりSSLの検証を無効化していただくことで対処できる可能性があります。", Push7::admin_url() );
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
      <input type='checkbox' name='push7_not_notify' value='true' <?= $post->post_status == 'publish' ? 'checked="checked"' : '' ?>>
      通知を送信しない
    <?php
  }

  public function check_ignored_posttype($post_id){
    $post_type = get_post_type($post_id);
    $setting = get_option("push7_push_pt_".$post_type, '');
    return $setting === '';
  }

  public function check_ignored_category($post_id){
    $categories = get_the_category($post->ID);
    foreach ($categories as $category) {
      if (get_option('push7_push_ctg_'.$category->slug, null) === 'false') return true;
    }
    return false;
  }

  /**
   * get_rpid_dict 投稿ID:Reserved PushのIDの辞書を取得する
   * @return array 投稿ID:Reserved PushのIDの辞書データとなる連想配列
   */
  protected function get_rpid_dict() {
    return json_decode(get_option("push7_rpid_dict", '{}'), true);
  }

  /**
   * get_rpid_from_post_data 投稿データから対象となるReserved PushのIDを引いてくる
   * @param int $post_id 投稿ID
   * @return mixed 対象ID(string) or 0
   */
  protected function get_rpid_from_post_data($post_id) {
    $rpid_dict = $this->get_rpid_dict();
    return isset($rpid_dict[$post_id]) ? $rpid_dict[$post_id] : 0;
  }

  /**
   * set_ripd_dict 投稿ID:Reserved PushのIDの辞書を更新する
   * @param int $post_id 投稿ID
   * @param mixed $id   Reserved PushのID(string) or null
   */
  protected function set_ripd_dict($post_id, $id) {
    $rpid_dict = $this->get_rpid_dict();
    $rpid_dict[$post_id] = $id;
    update_option("push7_rpid_dict", json_encode($rpid_dict));
  }

  /**
   * get_post_id WP_Postオブジェクトから対象のIDを引いてくる
   * @param WP_Post $post 投稿データ
   * @return int       投稿ID
   */
  protected function get_post_id ($post) {
    return get_post($post)->ID;
  }
}
