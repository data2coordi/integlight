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

        // 「高速化」セクション追加
        $wp_customize->add_section('integlight_section_performance', [
            'title'       => __('Cache Settings', 'integlight'),
            'priority'    => 200,
            'capability'  => 'edit_theme_options',
            'description' => __('Settings related to caching for site performance optimization.', 'integlight'),
            'panel'       => 'integlight_perf_panel',
        ]);

        // キャッシュ有効/無効の設定
        $wp_customize->add_setting('integlight_cache_enable', [
            'default'           => true,
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
    }

    /**
     * キャッシュが有効か判定
     */
    public static function is_cache_enabled()
    {
        return get_theme_mod('integlight_cache_enable', true);
    }
}

// クラス初期化
new Integlight_customize_performance();
