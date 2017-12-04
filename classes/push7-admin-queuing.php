<?php

class Push7_Admin_Queuing {
  public function __construct() {
    add_action('admin_menu', array($this, 'on_init'));
  }

  /**
   * on_init メイン処理
   * @return [type] [description]
   */
  public function on_init() {
    $queue = self::get_queue();
    foreach ($queue as $post_id) {
      $post = get_post($post_id);
      // 予約投稿日時が13日以内の場合
      if ( (strtotime($post->post_date) - strtotime(current_time('Y-m-d H:i:s'))) < Push7::RESERVED_LINE ) {
        $response = Push7_Post::push($post, true);
        if($response) {
          Push7_Post::set_ripd_dict($post->ID, $response['pushid']);
          self::delete_queue($post->ID);
        }
      }
    }
  }

  /**
   * add_queue キューへの投稿の追加
   * @param int $post_id 投稿ID
   */
  public static function add_queue($post_id) {
    $queue = self::get_queue();
    $queue[] = $post_id;
    self::save_queue($queue);
  }

  /**
   *
   */
  public static function delete_queue($post_id) {
    $old_queue = self::get_queue();
    $new_queue = array();
    foreach ($old_queue as $item_id) {
      if($item_id != $post_id) $new_queue[] = $item_id;
    }
    self::save_queue($new_queue);
  }

  /**
   * get_queue Reserved Pushのキューを取得する
   * @return array Reserved Pushのキュー
   */
  public static function get_queue() {
    return json_decode(get_option("push7_rp_queue", '[]'), true);
  }

  /**
   * save_queue キューをJSON化して保存する
   * @param  [type] $queue [description]
   * @return [type]        [description]
   */
  public static function save_queue($queue) {
    update_option("push7_rp_queue", json_encode($queue));
  }
}
