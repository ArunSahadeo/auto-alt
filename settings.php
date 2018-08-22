<?php

require_once AUTO_ALT_PLUGIN_DIR . '/includes/functions.php';

if ( is_admin() )
{
    require_once AUTO_ALT_PLUGIN_DIR . '/admin/javascript-loaders.php';
    require_once AUTO_ALT_PLUGIN_DIR . '/admin/menus.php';
}
