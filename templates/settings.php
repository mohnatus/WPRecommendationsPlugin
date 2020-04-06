<?php

add_action('admin_menu', function() {


  add_options_page(__('Recommendations', 'recommendationsl10n'), __('Recommendations', 'recommendationsl10n'), 'manage_options', 'recommendations-plugin', 'recommendations_settings_page');
});

function recommendations_settings_page() {
  ?>

    <div class="wrap">
      <h2><?php echo get_admin_page_title() ?></h2>

      <form action="options.php" method="POST">
        <?php
          settings_fields('recommendations_settings_group');
          do_settings_sections('primer_page');
          submit_button();
        ?>
      </form>
    </div>

  <?php
}

add_action('admin_init', function() {
  register_setting('recommendations_settings_group', 'recommendations_custom_css_option', 'strip_tags');
  register_setting('recommendations_settings_group', 'recommendations_options', 'recommendations_sanitize');

  if (!get_option('recommendations_options')) {
    update_option('recommendations_options', [
      'css_prefix' => 'recommendations',
      'max_count' => 5,
      'hide_title' => false,
      'default_title' => __('Related posts', 'recommendationsl10n'),
    ]);
  }

  add_settings_section('main_settings', '', '', 'primer_page');

  add_settings_field('recommendations_hide_title', __('Hide block title', 'recommendationsl10n'), 'recommendations_hide_title_template', 'primer_page', 'main_settings');
  add_settings_field('recommendations_default_title', __('Default title', 'recommendationsl10n'), 'recommendations_default_title_template', 'primer_page', 'main_settings');
  add_settings_field('recommendations_max_count', __('Maximum number', 'recommendationsl10n'), 'recommendations_max_count_template', 'primer_page', 'main_settings');

  add_settings_field('recommendations_css_prefix', __('CSS prefix', 'recommendationsl10n'), 'recommendations_css_prefix_template', 'primer_page', 'main_settings');
  add_settings_field('recommendations_custom_css', __('Custom CSS', 'recommendationsl10n'), 'recommendations_custom_css_template', 'primer_page', 'main_settings');
});

function recommendations_custom_css_template() {
  $value = get_option('recommendations_custom_css_option');
  ?>
    <textarea name="recommendations_custom_css_option" style="width: 100%" rows="10"><?= $value ?></textarea>
  <?php
}

function recommendations_hide_title_template() {
  $option = get_option('recommendations_options');
  if (isset($option['hide_title'])) $value = $option['hide_title'];
  else $value = 0;
  ?>
    <input type="checkbox" name="recommendations_options[hide_title]" value="1" <?php checked(1, $value) ?> >
  <?php
}

function recommendations_default_title_template() {
  $option = get_option('recommendations_options');
  $value = $option['default_title'];
  ?>
    <input type="text" name="recommendations_options[default_title]" value="<?= $value ?>">
  <?php
}

function recommendations_css_prefix_template() {
  $option = get_option('recommendations_options');
  $value = $option['css_prefix'];
  ?>
    <input type="text" name="recommendations_options[css_prefix]" value="<?= $value ?>">
  <?php
}

function recommendations_max_count_template() {
  $option = get_option('recommendations_options');
  $value = $option['max_count'];
  ?>
    <input type="number" name="recommendations_options[max_count]" value="<?= $value ?>" min="1">
  <?php
}

function recommendations_sanitize($options) {
  //print_r($options);
  foreach($options as $name => &$val){
		if($name == 'default_title') {
      $val = strip_tags(trim($val));
      if (!$val) $val = __('Related posts', 'recommendationsl10n');
    }

    if($name == 'css_prefix') {
      $val = strip_tags(trim($val));
      if (!$val) $val = 'recommendations';
    }

		if($name == 'hide_title') {
      $val = intval($val);
      if (!$val) $val = 0;
    }

    if($name == 'max_count') {
      $val = intval($val);
      if ($val < 1) $val = 1;
    }
  }

  // die(print_r( $options )); // Array ( [input] => aaaa [checkbox] => 1 )

  return $options;
}
