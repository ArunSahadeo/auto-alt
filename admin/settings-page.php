<?php

function renderAPISettingsPage ()
{

    if ( isset($_POST['api_key']) )
    {
        $value = $_POST['api_key'];
        update_option('api_key', $value);
    }

    $apiKey = get_option('api_key', '');

    include plugin_dir_path(__FILE__) . '/templates/page-api-settings.php';
}
