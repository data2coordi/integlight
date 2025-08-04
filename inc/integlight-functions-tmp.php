<?php
// ユーザーが以前の menu-1 にメニューを割り当てていた場合、それを header に移行
function integlight_migrate_nav_menu()
{
    if (get_theme_mod('nav_menu_locations')) {
        $locations = get_theme_mod('nav_menu_locations');
        if (isset($locations['menu-1']) && ! isset($locations['header'])) {
            $locations['header'] = $locations['menu-1'];
            unset($locations['menu-1']);
            set_theme_mod('nav_menu_locations', $locations);
        }
    }
}
add_action('after_setup_theme', 'integlight_migrate_nav_menu');
