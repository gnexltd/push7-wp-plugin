<?php
// file_get_contents関数が使用不可能な時
if (!in_array("https", stream_get_wrappers()) || (ini_get("allow_url_fopen") != "1")) {
  ?>
  <div class="notice error is-dismissible"><p>
    php.iniの設定を見直し、allow_url_fopenが有効であるかどうかをご確認お願い致します。 この件に関しまして分からないことがある方はご利用中のサーバを明記の上
    <a href="https://dashboard.push7.jp/" target="_blank">Push7のダッシュボード左下のリンク</a>
    からお問い合わせ下さい。
  </p></div>
  <?php
}
?>

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
            <label for="blog_title">ブログのタイトル(任意)</label>
          </th>
          <td>
            <?php
              if (get_option('blog_title')) {?>
                <input type="text" id="blog_title" class="regular-text" name="blog_title" value="<?php echo get_option('blog_title'); ?>"><?php
              } else {?>
                <input type="text" id="blog_title" class="regular-text" name="blog_title" placeholder="<?php echo get_option('blogname'); ?>"><?php
              }
            ?>
          </td>
        </tr>

        <tr>
          <th>
            <label for="appno">APPNO</label>
          </th>
          <td>
            <input type="text" id="appno" class="regular-text" name="appno" value="<?php echo get_option('appno'); ?>">
          </td>
        </tr>

        <tr>
          <th>
            <label for="apikey">APIKEY</label>
          </th>
          <td>
            <input type="text" id="apikey" class="regular-text" name="apikey" value="<?php echo get_option('apikey'); ?>">
          </td>
        </tr>

        <tr>
          <th>新規記事をデフォルトでプッシュ通知する</th>
          <td>
            <fieldset>
              <label title="true">
                <input type="radio" name="push_default_on_new" value="true" <?php checked("true", get_option("push_default_on_new")); ?>>
                する
              </label>
              <label title="false">
                <input type="radio" name="push_default_on_new" value="false" <?php checked("false", get_option("push_default_on_new")); ?>>
                しない
              </label>
            </fieldset>
          </td>
        </tr>

        <tr>
          <th>更新記事をデフォルトでプッシュ通知する</th>
          <td>
            <fieldset>
              <label title="true">
                <input type="radio" name="push_default_on_update" value="true" <?php checked("true", get_option("push_default_on_update")); ?>>
                する
              </label>
              <label title="false">
                <input type="radio" name="push_default_on_update" value="false" <?php checked("false", get_option("push_default_on_update")); ?>>
                しない
              </label>
            </fieldset>
          </td>
        </tr>
      </tbody>
    </table>

    <?php submit_button(); ?>
  </form>
</div>
