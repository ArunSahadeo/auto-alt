<?php

function renderClarifaiAPISettingsPage ()
{

    if ( isset($_POST['clarifai_api_key']) )
    {
        $value = $_POST['clarifai_api_key'];
        update_option('clarifai_api_key', $value);
    }

    $clarifaiAPIKey = get_option('clarifai_api_key', '');

    include plugin_dir_path(__FILE__) . '/templates/page-clarifai-api-settings.php';
}

function renderImageVisionAPISettingsPage ()
{

    if ( isset($_POST['imagevision_api_key']) )
    {
        $value = $_POST['imagevision_api_key'];
        update_option('imagevision_api_key', $value);
    }

    $clarifaiAPIKey = get_option('imagevision_api_key', '');

    include plugin_dir_path(__FILE__) . '/templates/page-imagevision-api-settings.php';
}
