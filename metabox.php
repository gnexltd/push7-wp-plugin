<?php
  function check_postType(){
    $post = get_post(get_the_ID());
    switch ($post->post_status) {
      // 新規投稿時
      case 'auto-draft':
        return get_option("push_default_on_new");
      // 記事更新時
      case 'publish':
        return get_option("push_default_on_update");
      case 'draft':
        return get_option("push_default_on_update");
    }
  }
?>
<span>通知を</span>
<br>
<input type="radio" name="is_notify" value="true" <?php checked("true", check_postType()); ?>>する
<br>
<input type="radio" name="is_notify" value="false" <?php checked("false", check_postType()); ?>>しない
