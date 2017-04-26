<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.5.10/clipboard.min.js"></script>
<script type="text/javascript">
  var clipboard = new Clipboard('.action')
  function show_debug_info() {
    document.getElementById('debug').style.display = 'inline'
  }
</script>

<div class="wrap">
  <h2>Push7 Setting</h2>
  <form action="options.php" method="post">
    <?php
      settings_fields('push7-settings-group');
      do_settings_sections('push7-settings-group');
    ?>

    <table class="form-table">
      <tbody>
        <tr>
          <th>
            <label for="blog_title"><?php _e('ブログのタイトル(任意)', 'push7');?></label>
          </th>
          <td>
            <?php
              if (get_option('blog_title')) {?>
                <input type="text" id="push7_blog_title" class="regular-text" name="push7_blog_title" value="<?php echo esc_attr( get_option( 'push7_blog_title' ) ); ?>"><?php
              } else {?>
                <input type="text" id="push7_blog_title" class="regular-text" name="push7_blog_title" placeholder="<?php echo esc_attr( get_option( 'blogname' ) ); ?>"><?php
              }
            ?>
          </td>
        </tr>

        <tr>
          <th>
            <label for="appno">APPNO</label>
          </th>
          <td>
            <input type="text" id="push7_appno" class="regular-text" name="push7_appno" value="<?php echo esc_attr(get_option('push7_appno')); ?>">
          </td>
        </tr>

        <tr>
          <th>
            <label for="apikey">APIKEY</label>
          </th>
          <td>
            <input type="text" id="push7_apikey" class="regular-text" name="push7_apikey" value="<?php echo esc_attr(get_option('push7_apikey')); ?>">
          </td>
        </tr>

        <tr>
          <th>
            <label for="push7_sslverify_disabled">
              <?php _e( 'SSLの検証を無効化する', 'push7' ); ?>
            </label>
          </th>
          <td>
            <fieldset>
              <label title="true">
                <input type="radio" name="push7_sslverify_disabled" value="true" <?php checked("true", get_option("push7_sslverify_disabled")); ?>>
                <?php _e( 'する(必要のない場合には選択しないでください。)', 'push7' ); ?>
              </label>
              <br>
              <label title="false">
                <input type="radio" name="push7_sslverify_disabled" value="false" <?php checked("false", get_option("push7_sslverify_disabled")); ?>>
                <?php _e( 'しない', 'push7' ); ?>
              </label>
            </fieldset>
          </td>
        </tr>

        <tr>
          <th>
            <label for="push7_sdk_enabled">
              <?php _e( 'Push7SDKを有効にする', 'push7' ); ?>
            </label>
          </th>
          <td>
            <fieldset>
              <label title="true">
                <input type="radio" name="push7_sdk_enabled" value="true" <?php checked("true", get_option("push7_sdk_enabled")); ?>>
                <?php _e( 'する', 'push7' ); ?>
              </label>
              <br>
              <label title="false">
                <input type="radio" name="push7_sdk_enabled" value="false" <?php checked("false", get_option("push7_sdk_enabled")); ?>>
                <?php _e( 'しない', 'push7' ); ?>
              </label>
            </fieldset>
          </td>
        </tr>
      </tbody>
    </table>

    <?php
      $categories = get_categories();
      if (count($categories) !== 0) {
      ?>
        <h2 class="title">カテゴリ毎のプッシュ通知設定</h2>
        <table class="form-table"><tbody>
          <tr>
            <th>新規投稿時自動プッシュする</th>
            <td>
              <?php
                foreach ($categories as $category) {
                  $name = "push7_push_ctg_".$category->slug;
              ?>
                  <label for="<?php echo $name; ?>">
                    <input type="checkbox" name="<?php echo $name; ?>" value="true" <?php checked("true", get_option($name)) ?>>
                    <?php echo $category->name; ?>
                  </label>
                  <br>
              <?php
                }
              ?>
              <p class="description">ここでチェックを外したカテゴリを含んだ投稿は自動でのプッシュ通知が行われません。</p>
            </td>
          </tr>
        </tbody></table>
      <?php
      }
    ?>

    <h2 class="title">投稿タイプ毎のプッシュ通知設定</h2>
    <table class="form-table"><tbody>
      <tr>
        <th>新規投稿時自動プッシュする</th>
        <td>
        <?php
          foreach (Push7::post_types() as $post_type) {
            $name = "push7_push_pt_".$post_type;
        ?>
            <label for="<?php echo $name; ?>">
              <input type="checkbox" name="<?php echo $name;?>" value="true" <?php checked("true", get_option($name)) ?>>
              <?php
                if ($post_type == 'post') {
                  echo 'post(通常の投稿)';
                } else {
                  echo $post_type;
                }
              ?>
          </label>
          <br>
        <?php
          }
        ?>
        </td>
      </tr>
    </tbody></table>

    <h2 class="title">サードパーティエディタのプッシュ通知設定</h2>

    <p>
      MarsEditなど、外部エディタを利用している場合の挙動についてはこちらをご利用ください。
    </p>

    <table class="form-table">
      <tbody>
        <tr>
          <th>
            記事更新時のプッシュ通知送信
          </th>
          <td>
            <fieldset>
              <label title="true">
                <input type="radio" name="push7_update_from_thirdparty" value="true" <?php checked("true", get_option("push7_update_from_thirdparty")); ?>>
                <?php _e( 'する', 'push7' ); ?>
              </label>
              <br>
              <label title="false">
                <input type="radio" name="push7_update_from_thirdparty" value="false" <?php checked("false", get_option("push7_update_from_thirdparty")); ?>>
                <?php _e( 'しない', 'push7' ); ?>
              </label>
            </fieldset>
          </td>
        </tr>
      </tbody>
    </table>

    <?php submit_button(); ?>

    <button type="button" class="button action" onclick="show_debug_info()">デバッグ情報を出力する</button>
    <p>
      基本的に出力する必要はありません。
    </p>
    <div id="debug" style="display: none;">
      <input id="debug_dump" rows="5" value="<?php echo $this->debug_dump(); ?>"></input>
      <button type="button" class="button action" data-clipboard-target="#debug_dump">コピーする</button>
    </div>
  </form>
</div>
