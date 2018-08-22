<?php

function loadValidationScript ()
{
    add_action( 'admin_enqueue_scripts', 'enqueueValidationScript' );
}

function enqueueValidationScript ()
{
    wp_enqueue_script( 'settings-page-validation-script', plugin_dir_url(__FILE__) . 'javascript/settings-page-validation.js' );
}
