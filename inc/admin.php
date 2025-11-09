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

function integlight_render_welcome_page()
{
?>
    <div class="wrap">
        <h1><?php esc_html_e('Welcome to Integlight', 'integlight'); ?></h1>
        <p><?php esc_html_e('For information on how to use and configure themes, please refer to the following manual.', 'integlight'); ?></p>
        <p>
            <a href="https://integlight.auroralab-design.com/category/how-to-use-integlight/"
                target="_blank"
                class="button button-primary">
                <?php esc_html_e('See official manual', 'integlight'); ?>
            </a>
        </p>
    </div>
    <?php
}

// 外観 → テーマエディター画面にマニュアルリンクを表示 e






/**
 * 外観 → メニュー画面にマニュアルリンクを表示 s
 */
function integlight_add_menu_screen_manual_notice()
{
    $screen = get_current_screen();

    // Run only on the "Appearance > Menus" screen.
    if ($screen && 'nav-menus' === $screen->base) {
    ?>
        <div class="notice notice-info is-dismissible integlight-manual-notice">
            <p>
                <strong><?php esc_html_e('Integlight Menu Setup Guide', 'integlight'); ?></strong><br>
                <?php esc_html_e('For detailed instructions on how to set up your menu and recommended configurations, please see the official manual below.', 'integlight'); ?>
            </p>
            <p>
                <a href="https://integlight.auroralab-design.com/category/how-to-use-integlight/menu-settings/"
                    target="_blank"
                    class="button button-primary">
                    <?php esc_html_e('Open Manual', 'integlight'); ?>
                </a>
            </p>
        </div>
    <?php
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
    ?>
        <div class="notice notice-info is-dismissible integlight-manual-notice">
            <p>
                <strong><?php esc_html_e('Integlight Widget Setup Guide', 'integlight'); ?></strong><br>
                <?php esc_html_e('For detailed instructions on how to add and configure widgets for the Integlight theme, please see the official manual below.', 'integlight'); ?>
            </p>
            <p>
                <a href="https://integlight.auroralab-design.com/widget-setup/"
                    target="_blank"
                    class="button button-primary">
                    <?php esc_html_e('Open Manual', 'integlight'); ?>
                </a>
            </p>
        </div>
<?php
    }
}
add_action('admin_notices', 'integlight_add_widgets_screen_manual_notice');

/**
 * Display a manual link on the Appearance → Widgets screen.   e
 */
