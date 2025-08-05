<?php
class InteglightThemeColorLoader
{
    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_custom_css']);
    }

    public function enqueue_custom_css()
    {
        $base_pattern = get_theme_mod('integlight_base_color_setting', 'pattern8');

        $styles = ['custom-pattern' => '/css/' . $base_pattern . '.css'];
        InteglightFrontendStyles::add_styles($styles);
        InteglightEditorStyles::add_styles($styles);
        InteglightDeferCss::add_deferred_styles(['custom-pattern']);
    }
}
new InteglightThemeColorLoader();
