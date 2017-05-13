<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.5.10/clipboard.min.js"></script>
<script>
var clipboard = new Clipboard('.action')
function show_advances_info() {
  document.querySelector('#advanced').style.display = 'block';
  document.querySelector('.advanced_info_button_area').style.display = 'none';
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
              if (get_option('blog_title')) {
            ?>
                <input type="text" id="push7_blog_title" class="regular-text" name="push7_blog_title" value="<?= esc_attr( get_option( 'push7_blog_title' ) ); ?>">
            <?php
              } else {
            ?>
                <input type="text" id="push7_blog_title" class="regular-text" name="push7_blog_title" placeholder="<?= esc_attr( get_option( 'blogname' ) ); ?>">
            <?php
              }
            ?>
          </td>
        </tr>

        <tr>
          <th>
            <label for="appno">APPNO</label>
          </th>
          <td>
            <input type="text" id="push7_appno" class="regular-text" name="push7_appno" value="<?= esc_attr(get_option('push7_appno')); ?>">
          </td>
        </tr>

        <tr>
          <th>
            <label for="apikey">APIKEY</label>
          </th>
          <td>
            <input type="text" id="push7_apikey" class="regular-text" name="push7_apikey" value="<?= esc_attr(get_option('push7_apikey')); ?>">
          </td>
        </tr>

        <tr>
          <th>
            <label for="push7_sdk_enabled">
              Push7SDKを有効にする
            </label>
          </th>
          <td>
            <fieldset>
              <label title="true">
                <input type="radio" name="push7_sdk_enabled" value="true" <?php checked("true", get_option("push7_sdk_enabled")); ?>>
                する
              </label>
              <br>
              <label title="false">
                <input type="radio" name="push7_sdk_enabled" value="false" <?php checked("false", get_option("push7_sdk_enabled")); ?>>
                しない
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
        <table class="form-table">
          <tbody>
            <tr>
              <th>新規投稿時自動プッシュする</th>
              <td>
                <?php
                  foreach ($categories as $category) {
                    $name = "push7_push_ctg_".$category->slug;
                ?>
                    <label for="<?= $name; ?>">
                      <input type="checkbox" name="<?= $name; ?>" value="true" <?php checked("true", get_option($name)) ?>>
                      <?= $category->name; ?>
                    </label>
                    <br>
                <?php
                  }
                ?>
                <p class="description">ここでチェックを外したカテゴリを含んだ投稿は自動でのプッシュ通知が行われません。</p>
              </td>
            </tr>
          </tbody>
        </table>
      <?php
      }
    ?>


    <?php
      if(count(Push7::post_types()) >= 2){
    ?>
        <h2 class="title">投稿タイプ毎のプッシュ通知設定</h2>
        <table class="form-table">
          <tbody>
            <tr>
              <th>新規投稿時自動プッシュする</th>
              <td>
              <?php
                foreach (Push7::post_types() as $post_type) {
                  if($post_type == "post") continue;
                  $name = "push7_push_pt_".$post_type;
              ?>
                  <label for="<?= $name; ?>">
                    <input type="checkbox" name="<?= $name;?>" value="true" <?php checked("true", get_option($name)) ?>>
                    <?= $post_type == 'post' ? 'post(通常の投稿)' : $post_type; ?>
                  </label>
                  <br>
              <?php
                }
              ?>
              </td>
            </tr>
          </tbody>
        </table>
    <?php
      }
     ?>


    <div class="advanced_info_button_area">
      <button type="button" class="button action" onclick="show_advances_info()">高度な設定を表示する</button>
      <p>
        基本的に表示する必要はありません。
      </p>
    </div>
    <div id="advanced" style="display: none;">
      <h2 class="title">高度な設定</h2>

      <table class="form-table">
        <tbody>
          <tr>
            <th>
              <label for="push7_sslverify_disabled">
                SSLの検証を無効化する
              </label>
            </th>
            <td>
              <fieldset>
                <label title="true">
                  <input type="radio" name="push7_sslverify_disabled" value="true" <?php checked("true", get_option("push7_sslverify_disabled")); ?>>
                  する
                </label>
                <br>
                <label title="false">
                  <input type="radio" name="push7_sslverify_disabled" value="false" <?php checked("false", get_option("push7_sslverify_disabled")); ?>>
                  しない
                </label>
              </fieldset>
              <span>(必要のない場合には選択しないでください。)</span>
            </td>
          </tr>

          <tr>
            <th>
              <label for="push7_sslverify_disabled">
                外部エディタのプッシュ通知
              </label>
            </th>
            <td>
              <fieldset>
                <label title="true">
                  <input type="radio" name="push7_update_from_thirdparty" value="true" <?php checked("true", get_option("push7_update_from_thirdparty")); ?>>
                  する
                </label>
                <br>
                <label title="false">
                  <input type="radio" name="push7_update_from_thirdparty" value="false" <?php checked("false", get_option("push7_update_from_thirdparty")); ?>>
                  しない
                </label>
              </fieldset>
              <span>MarsEditなど、外部エディタを利用している場合の挙動についてはこちらをご利用ください。</span>
            </td>
          </tr>

          <tr>
            <th>
              <label for="push7_sslverify_disabled">
                デバッグ情報
              </label>
            </th>
            <td>
              <input id="debug_dump" rows="5" value="<?= $this->debug_dump(); ?>"></input>
              <button type="button" class="button action" data-clipboard-target="#debug_dump">コピーする</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <?php submit_button(); ?>
  </form>
</div>
