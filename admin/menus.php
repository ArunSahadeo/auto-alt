<?php

require_once plugin_dir_path(__FILE__) . '/settings-page.php';
require_once plugin_dir_path(__FILE__) . '/css/menu-css.php'; 

add_action( 'admin_menu', 'addPluginSettingsMenu' );

function addPluginSettingsMenu ()
{
    $pageOptions = [
        'page_title'    => 'Auto Alt Settings',
        'menu_title'    => 'Auto Alt Settings',
        'capability'    => 'edit_posts',
        'menu_slug'     => 'auto-alt-settings',
        'function'      => '',
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

    /**
        Hack to stop the top level menu item
        being duplicated as the first submenu item
    **/

    add_submenu_page(
        'auto-alt-settings',
        '',
        '',
        'edit_posts',
        'auto-alt-settings',
        ''
    );

    addSettingsPages();
}

function addSettingsPages ()
{
    addClarifaiAPISettingsPage();
    addImageVisionAPISettingsPage();
}

function addClarifaiAPiSettingsPage ()
{
    $pageOptions = [
        'parent_slug'   => 'auto-alt-settings', 
        'page_title'    => 'Clarifai API settings',
        'menu_title'    => 'Clarifai API settings',
        'capability'    => 'edit_posts',
        'menu_slug'     => 'clarifai-api-settings',
        'function'      => 'renderImageVisionAPISettingsPage',
    ];

    $settingsPage = add_submenu_page (
        $pageOptions['parent_slug'],
        $pageOptions['page_title'],
        $pageOptions['menu_title'],
        $pageOptions['capability'],
        $pageOptions['menu_slug'],
        $pageOptions['function']
    );

    add_action( 'load-' . $settingsPage, 'loadValidationScript' );
}

function addImageVIsionAPISettingsPage ()
{
    $pageOptions = [
        'parent_slug'   => 'auto-alt-settings', 
        'page_title'    => 'ImageVision API settings',
        'menu_title'    => 'ImageVision API settings',
        'capability'    => 'edit_posts',
        'menu_slug'     => 'imagevision-api-settings',
        'function'      => 'renderImageVisionAPISettingsPage',
    ];

    $settingsPage = add_submenu_page (
        $pageOptions['parent_slug'],
        $pageOptions['page_title'],
        $pageOptions['menu_title'],
        $pageOptions['capability'],
        $pageOptions['menu_slug'],
        $pageOptions['function']
    );
}
