<?php

add_action('admin_enqueue_scripts', function($hookSuffix) {
  if (!in_array($hookSuffix, ['post.php', 'post-new.php', 'load-post.php'])) return;
  wp_enqueue_script('recommendations-plugin', plugins_url('../dist/main.js', __FILE__), [], '', 'in_footer');
  wp_enqueue_style('recommendations-plugin', plugins_url('../dist/main.css', __FILE__));
});

add_action('add_meta_boxes', function() {
  add_meta_box('recommendations', __('Recommendations', 'recommendationsl10n'), 'recommendations_meta_box_view', array('post'), 'normal', 'default');
}, 1);
function recommendations_meta_box_view($post) {
  $postId = $post->ID;
  $recommendations = get_post_meta($postId, 'recommendation', 0);
  $isVisible = get_post_meta($postId, 'recommendations_visible', 1);
  $recommendationsTitle = get_post_meta($postId, 'recommendations_title', 1);
  $tagsList = get_tags();
  $selectedTags = get_post_meta($postId, 'recommendations_tags', 0);
  $tags = [];
  foreach($tagsList as $tag) {
    $tagName = $tag->name;
    $tagId = $tag->term_id;
    $checked = in_array($tagId, $selectedTags) ? 'checked' : '';
    $tags[] = "<label class='recommendations-tag'>
    <input type='checkbox' value='$tagId' name='recommendations_tags[]' $checked>
    <span>{$tagName}</span></label>";
  }
  $tags = implode(', ', $tags);

  $options = get_option('recommendations_options');
  ?>
    <script>
      window.__postRecommendations = <?= json_encode($recommendations) ?>;
    </script>

    <div id="recommendations-box" class="recommendations-box">
      <section>
        <h4><?= __('Main settings', 'recommendationsl10n') ?></h4>
        <div class="recommendations-block">

          <label for="recommendations-visible" class="selectit">
            <input type="hidden" name="recommendations_visible" value="" />
            <input type="checkbox" name="recommendations_visible" value="1" id="recommendations-visible" <?php checked($isVisible, 1)?> />
            <?= __('Show recommendations', 'recommendationsl10n') ?>
          </label>
        </div>

        <div class="recommendations-block">

          <label for="recommendations-title">
            <?= __('Block title', 'recommendationsl10n') ?>
          </label>
          <br>
          <input type="text" id="recommendations-title" name="recommendations_title" value="<?= $recommendationsTitle ? $recommendationsTitle : $options['default_title'] ?>">

          <?php if ($options['hide_title']): ?>
            <div class='recommendations-block__message'>
              <?= __('The block title is hidden. You can change this on the plugin settings page', 'recommendationsl10n') ?>.
              <a href="<?= admin_url('options-general.php?page=recommendations-plugin') ?>">
                <?= __('Go to settings', 'recommendationsl10n') ?>
              </a>
            </div>
          <?php endif; ?>
        </div>
      </section>

      <hr>

      <section>
        <h4>
          <?= __('Recommendations list', 'recommendationsl10n') ?>
          <span class="recommendations-block__message">(<?= __('Max', 'recommendationsl10n')?>: <?= $options['max_count'] ?>)</span>
        </h4>

        <div id="recommendations-list" class="recommendations-list"></div>

        <div class="recommendations-block">
          <button class="button" onclick="this.nextElementSibling.style.display='';this.remove();"><?= __('Add link', 'recommendationsl10n') ?></button>
          <div id="recommendations-form" style="display: none">
            <h4><?= __('Add link', 'recommendationsl10n') ?></h4>
            <div class="recommendations-form">
              <div class="recommendations-group">
                <div class="recommendations-field">
                  <label for="recommendation-link-add">
                    <?= __('URL', 'recommendationsl10n') ?>
                  </label>
                  <input type="text" id="recommendation-link-add">
                </div>
                <div class="recommendations-field">
                  <label for="recommendation-title-add">
                    <?= __('Link text', 'recommendationsl10n') ?>
                  </label>
                  <input type="text" id="recommendation-title-add">
                </div>
                <div class="recommendations-field">
                  <button type="button" class="button button-large" id="recommendation-add">
                    <?= __('Add', 'recommendationsl10n') ?>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

      </section>

      <hr>

      <section>
        <h4><?= __('Recommended tags', 'recommendationsl10n') ?></h4>
        <input type="hidden" name="recommendations_tags[]" value="">
        <?= $tags ?>
      </section>


      <input type="hidden" name="recommendations-plugin_nonce" value="<?= wp_create_nonce(__FILE__); ?>" />
    </div>
  <?php
}

add_action('save_post', 'recommendations_meta_update', 0);
function recommendations_meta_update($postId) {
  if (!wp_verify_nonce($_POST['recommendations-plugin_nonce'], __FILE__)
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
    if (empty($title)) update_post_meta($postId, 'recommendations_title', __('Related posts', 'recommendationsl10n'));
    else update_post_meta($postId, 'recommendations_title', sanitize_text_field($title));
  }

  if (isset($_POST['recommendations_visible'])) {
    $isVisible = $_POST['recommendations_visible'];
    if (empty($isVisible)) delete_post_meta($postId, 'recommendations_visible');
    else update_post_meta($postId, 'recommendations_visible', sanitize_text_field($isVisible));
  }


  $tags = $_POST['recommendations_tags'];
  delete_post_meta($postId, 'recommendations_tags');
  foreach($tags as $tag) {
    if (!empty($tag)) {
      add_post_meta($postId, 'recommendations_tags', $tag);
    }
  }
}
