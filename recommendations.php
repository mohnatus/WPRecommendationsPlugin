<?php
/*
* Plugin Name: Recommendations
*/

add_action('add_meta_boxes', 'recommendations_meta_box', 1);
add_action('save_post', 'recommendations_meta_update', 0);
add_action('admin_enqueue_scripts', 'recommendations_assets');
add_filter('the_content', 'recommendations_view');

function recommendations_assets($hookSuffix){
  if (!in_array($hookSuffix, ['post.php', 'post-new.php', 'load-post.php'])) return;
  wp_enqueue_script('recommendations-plugin', plugins_url('/main.js', __FILE__), [], '', 'in_footer');
  wp_enqueue_style('recommendations-plugin', plugins_url('/main.css', __FILE__));
}

function recommendations_meta_box() {
  add_meta_box('recommendations', 'Рекомендации', 'recommendations_meta_box_view', array('post'), 'normal', 'high');
}

function recommendations_meta_box_view($post) {
  $postId = $post->ID;
  $recommendations = get_post_meta($postId, 'recommendation', 0);
  $isVisible = get_post_meta($postId, 'recommendations_visible', 1);
  $recommendationsTitle = get_post_meta($postId, 'recommendations_title', 1);
  ?>
    <script>
      window.__postRecommendations = <?= json_encode($recommendations) ?>;
    </script>
    <div id="recommendations-box" class="recommendations-box">
      <div class="recommendations-group">
        <div class="recommendations-field recommendations-checkbox">
          <input type="hidden" name="recommendations_visible" value="" />
          <input type="checkbox" name="recommendations_visible" value="1"
            id="recommendations-visible"
            <?php checked($isVisible, 1)?> />
          <label for="recommendations_visible">Показать блок Рекомендаций</label>
        </div>
      </div>
      <div class="recommendations-group">
        <div class="recommendations-field">
          <label for="recommendations-title">Заголовок блока</label>
          <input type="text" id="recommendations-title" name="recommendations_title" value="<?= $recommendationsTitle ?>">
        </div>
      </div>
      <div id="recommendations-list" class="recommendations-list"></div>
      <hr>
      <div id="recommendations-form" class="recommendations-form">
        <div class="recommendations-group">
          <div class="recommendations-field">
            <label for="recommendation-link-add">Ссылка</label>
            <input type="text" id="recommendation-link-add">
          </div>
          <div class="recommendations-field">
            <label for="recommendation-title-add">Текст</label>
            <input type="text" id="recommendation-title-add">
          </div>
          <div class="recommendations-field">
            <button type="button" id="recommendation-add">Добавить</button>
          </div>
        </div>
      </div>

      <input type="hidden" name="recommendations_nonce" value="<?php echo wp_create_nonce(__FILE__); ?>" />
    </div>


  <?php
}

function recommendations_meta_update($postId) {
  if (!wp_verify_nonce($_POST['recommendations_nonce'], __FILE__)
  || wp_is_post_autosave($postId)
  || wp_is_post_revision($postId)) {
    return false;
  }

  if (isset($_POST['recommendations'])) {
    $recommendations = $_POST['recommendations'];
    $recommendations = array_map(
      'sanitize_text_field',
      $recommendations
    );

    delete_post_meta($postId, 'recommendation');

    foreach($recommendations as $recommendation) {
      add_post_meta($postId, 'recommendation', $recommendation);
    }
  }

  if (isset($_POST['recommendations_title'])) {
    $title = $_POST['recommendations_title'];
    if (empty($title)) update_post_meta($postId, 'recommendations_title', 'Похожие статьи');
    else update_post_meta($postId, 'recommendations_title', sanitize_text_field($title));
  }

  if (isset($_POST['recommendations_visible'])) {
    $isVisible = $_POST['recommendations_visible'];
    if (empty($isVisible)) delete_post_meta($postId, 'recommendations_visible');
    else update_post_meta($postId, 'recommendations_visible', sanitize_text_field($isVisible));
  }
}

function recommendations_view($content) {
  global $post;
  $postId = $post->ID;
  $isRecommendationsVisible = get_post_meta( $postId, 'recommendations_visible', true );

  if (!$isRecommendationsVisible) return $content;



  $recommendations = get_post_meta($postId, 'recommendation', 0);
  if (!$recommendations || count($recommendations) == 0) return $content;

  $recommendationsTitle = get_post_meta( $postId, 'recommendations_title', true );

  $html = "<section class='post-recommendations'>";
  $html .= "<h2 class='post-recommendations__title'>$recommendationsTitle</h2>";
  $html .= "<ul class='post-recommendations__list'>";

  foreach($recommendations as $recommendation) {
    $recommendation = json_decode($recommendation);
    $link = $recommendation->link;
    $title = $recommendation->title;
    $html .= "<li class='post-recommendation'>
      <a href='$link' rel='noopener noreferrer'>$title</a>
    </li>";
  }

  $html .= "</ul>";
  $html .= "</section>";

  return $content.$html;
}
