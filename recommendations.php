<?php
/*
* Plugin Name: Recommendations
* Description: Creates block with recommended links
* Author: FurryCat
* Author URI: http://portfolio.furrycat.ru/
* Version: 1.0
* Text Domain: recommendationsl10n
* Domain Path: /lang
*/

add_action('plugins_loaded', function() {
	load_plugin_textdomain('recommendationsl10n', false, dirname( plugin_basename(__FILE__) ) . '/lang');
});

require_once 'templates/admin.php';
require_once 'templates/front.php';
require_once 'templates/settings.php';
