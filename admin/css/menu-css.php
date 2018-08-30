<?php

add_action( 'admin_head', 'addPluginMenuCSS', 10 );

function addPluginMenuCSS ()
{
?>
<style id="auto-alt-menu-css">
    li#toplevel_page_auto-alt-settings > ul.wp-submenu li.wp-first-item
    {
        display: none;
    }

    li#toplevel_page_auto-alt-settings > a:active
    {
        pointer-events: none;
    }

    li#toplevel_page_auto-alt-settings,
    li#toplevel_page_auto-alt-settings > a
    {
        cursor: default;
    }
</style>
<?php   
}
