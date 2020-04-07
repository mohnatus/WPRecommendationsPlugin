<?php

add_action('wp_head', function() {
  if (!is_singular()) return;
  global $post;
  if (!get_post_meta($post->ID, 'recommendations_visible', true)) return;
  $css = get_option('recommendations_custom_css_option');
  echo "<style id='recommendations-custom'>$css</style>";
});


add_filter('the_content', 'recommendations_view');

function recommendations_view($content) {
  if (get_option('recommendations_disable')) {
    return $content;
  }

  global $post;
  $postId = $post->ID;

  /** Checks if the block is visible */
  $isRecommendationsVisible = get_post_meta($postId, 'recommendations_visible', true);
  if (!$isRecommendationsVisible) return $content;

  $recommendationsBlock = recommendations_create_block($content, $postId);

  return $content.$recommendationsBlock;
}

function recommendations_create_block($content, $postId) {
  $options = get_option('recommendations_options');

  /** Sets them limit count */
  $maxCount = $options['max_count'];

  /** Gets custom recommendations */
  $recommendations = get_post_meta($postId, 'recommendation', 0);
  $recommendations = array_slice($recommendations, 0, $maxCount);

  /** Gets tags recommendations */
  if (count($recommendations) < $maxCount) {
    $recommendationsTags = get_post_meta($postId, 'recommendations_tags', 0);
    if (!(empty($recommendationsTags))) {
      $query = new WP_Query([
        'tag__in' => $recommendationsTags,
        'posts_per_page' => $maxCount - count($recommendations)
      ]);

      while($query->have_posts()) {
        $query->the_post();
        $post = $query->post;
        $recommendations[] = json_encode([
          'link' => get_permalink(),
          'title' => get_the_title()
        ]);
      }
      wp_reset_postdata();
    }
  }

  /** If no recommendations */
  if (!$recommendations || count($recommendations) == 0) return $content;

  /** Renders block */
  $prefix = $options['css_prefix'];

  $html = "<section class='$prefix' id='$prefix'>";

  if (!get_option('recommendations_hide_title_option')) {
    $recommendationsTitle = get_post_meta( $postId, 'recommendations_title', true );
    $html .= "<h2 class='{$prefix}__title'>".
    __($recommendationsTitle, 'recommendationsl10n').
    "</h2>";
  }

  $html .= "<ul class='{$prefix}__list'>";

  foreach($recommendations as $recommendation) {
    $recommendation = json_decode($recommendation);
    $link = $recommendation->link;
    $title = $recommendation->title;
    $html .= "<li class='{$prefix}__item'>
      <a href='$link' rel='noopener noreferrer'>$title</a>
    </li>";
  }

  $html .= "</ul>";
  $html .= "</section>";
  return $html;
}

add_shortcode('recommendations', function($attrs) {
  $postId = $attrs['id'];
  if (!$postId) return '';

  $post = get_post($postId);
  return recommendations_create_block($post->post_content, $postId);
});
