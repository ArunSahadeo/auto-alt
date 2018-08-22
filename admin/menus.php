<?php

require_once plugin_dir_path(__FILE__) . '/settings-page.php';

add_action( 'admin_menu', 'addSettingsPage' );

function addSettingsPage ()
{
    $pageOptions = [
        'page_title'    => 'Clarifai API settings',
        'menu_title'    => 'Clarifai API settings',
        'capability'    => 'edit_posts',
        'menu_slug'     => 'clarifai-api-settings',
        'function'      => 'renderAPISettingsPage',
        'icon_url'      => '',
        'menu_position' => 81
    ];

    $settingsPage = add_menu_page (
        $pageOptions['page_title'],
        $pageOptions['menu_title'],
        $pageOptions['capability'],
        $pageOptions['menu_slug'],
        $pageOptions['function'],
        $pageOptions['icon_url'],
        $pageOptions['menu_position']
    );

    add_action( 'load-' . $settingsPage, 'loadValidationScript' );
}
