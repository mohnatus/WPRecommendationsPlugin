<?php

if( ! defined('WP_UNINSTALL_PLUGIN') ) exit;

delete_option('recommendations_custom_css_option');
delete_option('recommendations_options');
delete_option('recommendations_disable');
delete_option('recommendations_style');
