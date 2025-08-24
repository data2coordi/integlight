<?php
class Integlight_Customize_Performance
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
            'title'       => __('Performance', 'integlight'),
            'priority'    => 200,
            'capability'  => 'edit_theme_options',
            'description' => __('Cache and performance related settings.', 'integlight'),
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
            'description' => __('Uncheck to disable theme output cache.', 'integlight'),
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
new Integlight_Customize_Performance();
