<?php
/*
  Plugin Name: My Shortcodes
  Plugin URI: http://digilab.co.za
  Description: Build advanced custom plugins, widgets shortcodes and web elements to use within your site or distribute to others as standalone plugins or included in your themes.
  Author: David Cramer
  Version: 2.01
  Author URI: http://digilab.co.za
 */

//initilize plugin
define('MYSHORTCODES_PATH', plugin_dir_path(__FILE__));
define('MYSHORTCODES_URL', plugin_dir_url(__FILE__));
define('MYSHORTCODES_VER', '2.01');

require_once MYSHORTCODES_PATH . 'libs/functions.php';
require_once MYSHORTCODES_PATH . 'libs/actions.php';
require_once MYSHORTCODES_PATH . 'libs/alwaysload.php';
require_once MYSHORTCODES_PATH . 'libs/widget.php';

?>