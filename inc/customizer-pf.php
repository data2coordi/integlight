<?php
class Integlight_customize_performance
{

    /**
     * 初期化
     */
    public function __construct()
    {
        add_action('customize_register', [$this, 'register_settings']);
    }

    /**
     * カスタマイザ設定登録
     */
    public function register_settings($wp_customize)
    {

        ////////////////////////////////////////
        // 「高速化」セクションにキャッシュ機能追加 s
        ////////////////////////////////////////
        $wp_customize->add_section('integlight_section_performance', [
            'title'       => __('Cache Settings', 'integlight'),
            'priority'    => 200,
            'capability'  => 'edit_theme_options',
            'description' => __('Settings related to caching for site performance optimization.', 'integlight'),
            'panel'       => 'integlight_perf_panel',
        ]);

        // キャッシュ有効/無効の設定
        $wp_customize->add_setting('integlight_cache_enable', [
            'default'           => Integlight_Defaults::get_all()['integlight_cache_enable'] ?? true,
            'sanitize_callback' => 'wp_validate_boolean',
        ]);

        $wp_customize->add_control('integlight_cache_enable', [
            'type'        => 'checkbox',
            'section'     => 'integlight_section_performance',
            'label'       => __('Enable server-side cache', 'integlight'),
            'description' => __(
                "Uncheck to disable the theme's server-side cache. Normally, keeping it enabled is recommended. *This cache does not apply to logged-in users.",
                'integlight'
            ),
        ]);
        ////////////////////////////////////////
        // 「高速化」セクションにキャッシュ機能追加 e
        ////////////////////////////////////////



        ////////////////////////////////////////
        // 「高速化」セクションにキャッチ画像非表示機能追加 s
        ////////////////////////////////////////
        $wp_customize->add_section('integlight_section_HideFeaturedImage', [
            'title'       => __('Featured Image Settings', 'integlight'),
            'priority'    => 200,
            'capability'  => 'edit_theme_options',
            'description' => __('Control the display of the featured image on single post pages.', 'integlight'),
            'panel'       => 'integlight_perf_panel',
        ]);

        // Enable/Disable featured image display on single posts
        $wp_customize->add_setting('integlight_hideFeaturedImage_enable', [
            'default'           => Integlight_Defaults::get_all()['integlight_hideFeaturedImage_enable'] ?? false,
            'sanitize_callback' => 'wp_validate_boolean',
        ]);

        $wp_customize->add_control('integlight_hideFeaturedImage_enable', [
            'type'        => 'checkbox',
            'section'     => 'integlight_section_HideFeaturedImage',
            'label'       => __('Hide Featured Image on Single Posts', 'integlight'),
            'description' => __(
                'When enabled, the featured image is hidden on the single post page but still displayed as a thumbnail on archive pages. This option is used to improve the loading performance of featured images on single posts.',
                'integlight'
            ),
        ]);
        ////////////////////////////////////////
        // 「高速化」セクションにキャッチ画像非表示機能追加 e
        ////////////////////////////////////////






    }

    /**
     * キャッシュが有効か判定
     */
    public static function is_cache_enabled()
    {
        return Integlight_getThemeMod::getThemeMod('integlight_cache_enable', true);
    }


    /**
     * キャッチ画像非表示が有効か判定
     */
    public static function is_HideFeaturedImage_enabled()
    {
        return Integlight_getThemeMod::getThemeMod('integlight_hideFeaturedImage_enable', false);
    }
}

// クラス初期化
new Integlight_customize_performance();
