<?php
class InteglightThemeColorLoader
{
    public function __construct()
    {
        add_action('wp', [$this, 'enqueue_custom_css']);
    }

    public function enqueue_custom_css()
    {
        $base_pattern = get_theme_mod('integlight_base_color_setting', 'pattern8');



        $styles = ['integlight-custom-color-pattern' => ['path' => '/css/build/' . $base_pattern . '.css', 'deps' => ['integlight-style-plus']]];
        InteglightFrontendStyles::add_styles($styles);
        InteglightEditorStyles::add_styles($styles);
        InteglightDeferCss::add_deferred_styles(['integlight-custom-color-pattern']); //PF対応!!!
    }
}
new InteglightThemeColorLoader();


class InteglightHomeTypeLoader
{
    public function __construct()
    {
        add_action('wp', [$this, 'enqueue_hometype_css']);
    }

    public function enqueue_hometype_css()
    {
        $home_type = get_theme_mod('integlight_hometype_setting', 'home1');

        $tmp_deps = ['integlight-integlight-menu', 'integlight-custom-color-pattern'];
        // slider かつフロントまたは home の場合に追記
        if (
            'slider' === get_theme_mod('integlight_display_choice', 'none') &&
            (is_front_page())
        ) {
            $tmp_deps[] = 'integlight-slide';
        }

        $styles = ['home-type' => ['path' => '/css/build/' . $home_type . '.css', 'deps' => $tmp_deps]];
        InteglightFrontendStyles::add_styles($styles);
        InteglightEditorStyles::add_styles($styles);
        //InteglightDeferCss::add_deferred_styles(['home-type']); //PF対応!!!
    }
}
new InteglightHomeTypeLoader();
