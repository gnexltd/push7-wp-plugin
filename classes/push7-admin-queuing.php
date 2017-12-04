<?php

class Push7_Admin_Queuing {
  public function __construct() {
    add_action('admin_init', array($this, 'register_scheduler'));

    // 環境によってはcronが正しく動作しない場合もあるようなので管理画面では常に叩いておく
    add_action('admin_init', array($this, 'update_queue'));
  }

  /**
   * on_init メイン処理
   * @return [type] [description]
   */
  public static function update_queue() {
    $semaphore = Push7_Semaphore::factory();
    $semaphore->init();
    if(!$semaphore->lock()) return;
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
    $semaphore->unlock();
  }

  public function register_scheduler() {
    if (wp_next_scheduled('push7_queue_cron')) return; // すでにスケジューリングが登録されている場合は登録しない
    wp_schedule_event(time(), 'hourly', 'push7_queue_cron');
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

function push7_queue_cron() {
  try {
    Push7_Admin_Queuing::update_queue();
  } catch (Exception $e) {
    // ユーザーが閲覧する層に対しては失敗時も動作するように進める。
  }
}
