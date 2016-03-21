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
            <label for="push7_push_post_on_new">
              <?php _e( '新規記事をデフォルトでプッシュ通知する', 'push7' ); ?>
            </label>
          </th>
          <td>
            <fieldset>
              <label title="true">
                <input type="radio" name="push7_push_post_on_new" value="true" <?php checked("true", get_option("push7_push_post_on_new")); ?>>
                <?php _e( 'する', 'push7' ); ?>
              </label>
              <br>
              <label title="false">
                <input type="radio" name="push7_push_post_on_new" value="false" <?php checked("false", get_option("ppush7_ush_post_on_new")); ?>>
                <?php _e( 'しない', 'push7' ); ?>
              </label>
            </fieldset>
          </td>
        </tr>

        <tr>
          <th>
            <label for="push7_push_post_on_update">
              <?php _e( '更新記事をデフォルトでプッシュ通知する', 'push7' ); ?>
            </label>
          </th>
          <td>
            <fieldset>
              <label title="true">
                <input type="radio" name="push7_push_post_on_update" value="true" <?php checked("true", get_option("push7_push_post_on_update")); ?>>
                <?php _e( 'する', 'push7' ); ?>
              </label>
              <br>
              <label title="false">
                <input type="radio" name="push7_push_post_on_update" value="false" <?php checked("false", get_option("push7_push_post_on_update")); ?>>
                <?php _e( 'しない', 'push7' ); ?>
              </label>
            </fieldset>
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

    <h2 class="title">カテゴリ毎のプッシュ通知のデフォルト値</h2>

    <?php
      $post_types = get_post_types(array('_builtin' => false));
      foreach ($post_types as $post_type) {
        $cpt_on_new = "push7_push_".$post_type."_on_new";
        $cpt_on_update = "push7_push_".$post_type."_on_update";
        ?>
          <h3 class="title"><?php echo $post_type; ?></h3>
          <table class="form-table">
            <tbody>
              <tr>
                <th>
                  <label for="<?php echo $cpt_on_new; ?>">新規記事をデフォルトでプッシュ通知する</label>
                </th>
                <td>
                  <fieldset>
                    <label title="true">
                      <input type="radio" name="<?php echo $cpt_on_new; ?>" value="true" <?php checked("true", get_option($cpt_on_new)); ?>>
                      <?php _e( 'する', 'push7' ); ?>
                    </label>
                    <br>
                    <label title="false">
                      <input type="radio" name="<?php echo $cpt_on_new; ?>" value="false" <?php checked("false", get_option($cpt_on_new)); ?>>
                      <?php _e( 'しない', 'push7' ); ?>
                    </label>
                  </fieldset>
                </td>
                <th>
                  <label for="<?php echo $cpt_on_update; ?>">更新記事をデフォルトでプッシュ通知する</label>
                </th>
                <td>
                  <fieldset>
                    <label title="true">
                      <input type="radio" name="<?php echo $cpt_on_update; ?>" value="true" <?php checked("true", get_option($cpt_on_update)); ?>>
                      <?php _e( 'する', 'push7' ); ?>
                    </label>
                    <br>
                    <label title="false">
                      <input type="radio" name="<?php echo $cpt_on_update; ?>" value="false" <?php checked("false", get_option($cpt_on_update)); ?>>
                      <?php _e( 'しない', 'push7' ); ?>
                    </label>
                  </fieldset>
                </td>
              </tr>
            </tbody>
          </table>
        <?php
      }
    ?>

    <?php submit_button(); ?>
  </form>
</div>
