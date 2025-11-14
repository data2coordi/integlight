<?php


// 外観 → テーマエディター画面にマニュアルリンクを表示 s
function integlight_add_admin_page()
{
    add_theme_page(
        __('Welcome to Integlight', 'integlight'), // ページタイトル
        __('Integlight guide', 'integlight'),             // メニュー名
        'edit_theme_options',                         // 権限
        'integlight-welcome',                         // スラッグ
        'integlight_render_welcome_page'              // コールバック関数
    );
}
add_action('admin_menu', 'integlight_add_admin_page');

function integlight_add_icon_for_menuButton()
{
    echo '<style>' . "\n";
    echo '    /* スラッグに基づくセレクタ指定 */' . "\n";
    echo '    #adminmenu a[href="themes.php?page=integlight-welcome"]::before {' . "\n";
    echo '        content: "";' . "\n";
    echo '        display: inline-block;' . "\n";
    echo '        width: 16px;' . "\n";
    echo '        height: 16px;' . "\n";
    echo '        background-image: url("' . esc_url(get_template_directory_uri() . '/assets/easySetup.webp') . '");' . "\n";
    echo '        background-size: contain;' . "\n";
    echo '        background-repeat: no-repeat;' . "\n";
    echo '        margin-right: 6px;' . "\n";
    echo '        vertical-align: middle;' . "\n";
    echo '    }' . "\n";
    echo '</style>' . "\n";
}

add_action('admin_head', 'integlight_add_icon_for_menuButton');





/**
 * Integlight 専用ウェルカムページ
 */
function integlight_render_welcome_page()
{
    echo '<div class="wrap">' . "\n";
    echo '    <h1>' . esc_html__('Welcome to Integlight', 'integlight') . '</h1>' . "\n";
    echo '    <p>' . esc_html__('For information on how to use and configure themes, please refer to the following manual.', 'integlight') . '</p>' . "\n";
    echo '    <p>' . "\n";
    echo '        <a href="https://integlight.auroralab-design.com/category/how-to-use-integlight/" target="_blank" class="button button-primary">' . "\n";
    echo '            ' . esc_html__('See official manual', 'integlight') . "\n";
    echo '        </a>' . "\n";
    echo '    </p>' . "\n";
    echo '</div>' . "\n";
}

/**
 * 外観 → メニュー画面にマニュアルリンクを表示
 */
function integlight_add_menu_screen_manual_notice()
{
    $screen = get_current_screen();

    // Run only on the "Appearance > Menus" screen.
    if ($screen && 'nav-menus' === $screen->base) {
        echo '<div class="notice notice-info is-dismissible integlight-manual-notice">' . "\n";
        echo '    <p>' . "\n";
        echo '        <strong>' . esc_html__('Integlight Menu Setup Guide', 'integlight') . '</strong><br>' . "\n";
        echo '        ' . esc_html__('For detailed instructions on how to set up your menu and recommended configurations, please see the official manual below.', 'integlight') . "\n";
        echo '    </p>' . "\n";
        echo '    <p>' . "\n";
        echo '        <a href="https://integlight.auroralab-design.com/category/how-to-use-integlight/menu-settings/" target="_blank" class="button button-primary">' . "\n";
        echo '            ' . esc_html__('Open Manual', 'integlight') . "\n";
        echo '        </a>' . "\n";
        echo '    </p>' . "\n";
        echo '</div>' . "\n";
    }
}
add_action('admin_notices', 'integlight_add_menu_screen_manual_notice');

/**
 * Display a manual link on the Appearance → Widgets screen.   s
 */
function integlight_add_widgets_screen_manual_notice()
{
    $screen = get_current_screen();

    // 1. Check for classic/block widget screen.
    $is_widget_screen = $screen && in_array($screen->base, ['widgets', 'widgets-php'], true);

    // 2. Check for Customizer screen.
    $is_widget_customizer = false;
    if ($screen && 'customize' === $screen->base) {
        // Check if the focused panel is the sidebar panel.
        if (isset($_GET['autofocus']['panel']) && 'integlight_sidebar_panel' === $_GET['autofocus']['panel']) {
            $is_widget_customizer = true;
        }
        // Check if the focused section belongs to the sidebar panel.
        if (isset($_GET['autofocus']['section']) && strpos($_GET['autofocus']['section'], 'sidebar-widgets-') === 0) {
            $is_widget_customizer = true;
        }
    }

    if ($is_widget_screen || $is_widget_customizer) {
        echo '<div class="notice notice-info is-dismissible integlight-manual-notice">' . "\n";
        echo '    <p>' . "\n";
        echo '        <strong>' . esc_html__('Integlight Widget Setup Guide', 'integlight') . '</strong><br>' . "\n";
        echo '        ' . esc_html__('For detailed instructions on how to add and configure widgets for the Integlight theme, please see the official manual below.', 'integlight') . "\n";
        echo '    </p>' . "\n";
        echo '    <p>' . "\n";
        echo '        <a href="https://integlight.auroralab-design.com/widget-setup/" target="_blank" class="button button-primary">' . "\n";
        echo '            ' . esc_html__('Open Manual', 'integlight') . "\n";
        echo '        </a>' . "\n";
        echo '    </p>' . "\n";
        echo '</div>' . "\n";
    }
}
add_action('admin_notices', 'integlight_add_widgets_screen_manual_notice');

/**
 * Display a manual link on the Appearance → Widgets screen.   e
 */
