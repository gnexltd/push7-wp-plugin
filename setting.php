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
            $name = "push7_push_ctg_".$category->name;
        ?>
              <label for="<?php echo $name; ?>">
                <input type="checkbox" name="<?php echo $name; ?>" value="true" <?php checked("true", get_option($name)) ?>>
                <?php echo $category->name; ?>
              </label>
              <br>
        <?php
          }
        ?>
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
        foreach (self::post_types() as $post_type) {
          $name = "push7_push_pt_".$post_type;
      ?>
          <label for="<?php echo $name; ?>">
            <input type="checkbox" name="<?php echo $name;?>" value="true" <?php checked("true", get_option($name)) ?>>
            <?php echo self::disp_post_type($post_type); ?>
          </label>
          <br>
      <?php
        }
      ?>
        </td>
      </tr>
    </tbody></table>

    <?php submit_button(); ?>
  </form>
</div>
