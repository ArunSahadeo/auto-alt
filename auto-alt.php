<?php
/*
Plugin Name: Auto Alt
Plugin URI: https://github.com/ArunSahadeo/auto-alt
Description: Adds default alt values for images uploaded to the WordPress Media Library.
Author: Arun James Sahadeo <arunjamessahadeo@gmail.com>
Author URI: https://arunsahadeo.github.io
Text Domain: auto-alt
Domain Path: /languages/
Version: 0.0.1
*/

define( 'AUTO_ALT_VERSION', '0.0.1' );

define( 'AUTO_ALT_PLUGIN', __FILE__ );

define( 'AUTO_ALT_PLUGIN_BASENAME', __FILE__ );

define( 'AUTO_ALT_PLUGIN_NAME', trim(dirname(AUTO_ALT_PLUGIN_BASENAME)) );

define( 'AUTO_ALT_PLUGIN_DIR', trim(dirname(AUTO_ALT_PLUGIN)) );

require_once AUTO_ALT_PLUGIN_DIR . '/settings.php';
