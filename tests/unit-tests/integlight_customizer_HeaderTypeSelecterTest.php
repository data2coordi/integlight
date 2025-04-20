<?php // tests/unit-tests/integlight_customizer_HeaderTypeSelecterTest.php

declare(strict_types=1);


/**
 * integlight_customizer_HeaderTypeSelecter クラスのユニットテスト
 *
 * @covers integlight_customizer_HeaderTypeSelecter
 * @group customizer
 */
require_once dirname(__DIR__, 2) . '/inc/integlight-customizer-slider.php';
class integlight_customizer_HeaderTypeSelecterTest extends WP_UnitTestCase
{
    /**
     * WP_Customize_Manager のインスタンス
     * @var WP_Customize_Manager
     */
    private $wp_customize;

    /**
     * integlight_customizer_slider_creSection のインスタンス (ヘルパーとして)
     * @var integlight_customizer_slider_creSection
     */
    private $slider_section_helper;

    /**
     * スライダー設定オブジェクトのモックまたはスタブ
     * @var stdClass
     */
    private $slider_settings_stub;

    /**
     * テスト対象クラスのインスタンス
     * @var integlight_customizer_HeaderTypeSelecter
     */
    private $instance;

    /**
     * 各テストの前に実行されるセットアップメソッド
     */
    public function setUp(): void
    {
        parent::setUp();

        // WP_Customize_Manager クラスを確実に読み込む
        if (!class_exists('WP_Customize_Manager')) {
            if (!defined('ABSPATH') || !defined('WPINC')) {
                $this->fail('WordPress core constants (ABSPATH, WPINC) are not defined.');
            }
            $customize_manager_path = ABSPATH . WPINC . '/class-wp-customize-manager.php';
            if (!file_exists($customize_manager_path)) {
                $this->fail('WP_Customize_Manager class file not found.');
            }
            require_once $customize_manager_path;
        }
        // WP_Customize_Manager の実際のインスタンスを作成
        $this->wp_customize = new WP_Customize_Manager();

        // 依存クラスのインスタンスを作成
        $this->slider_section_helper = new integlight_customizer_slider_creSection();

        // 依存する設定オブジェクトのスタブを作成
        $this->slider_settings_stub = new stdClass();
        $this->slider_settings_stub->headerTypeName_slider = 'slider'; // 実際の値に合わせる
        $this->slider_settings_stub->headerTypeName_image = 'image';   // 実際の値に合わせる

        // テスト対象クラスのインスタンスを作成
        $this->instance = new integlight_customizer_HeaderTypeSelecter(
            $this->slider_section_helper,
            $this->slider_settings_stub
        );
    }

    /**
     * 各テストの後に実行されるティアダウンメソッド
     */
    public function tearDown(): void
    {
        // コンストラクタで追加されたアクションフックを削除
        remove_action('customize_register', [$this->instance, 'integlight_customizer_HeaderTypeSelecter']);
        // プロパティをクリア
        unset($this->wp_customize);
        unset($this->slider_section_helper);
        unset($this->slider_settings_stub);
        unset($this->instance);

        parent::tearDown();
    }

    /**
     * @test
     * コンストラクタが customize_register アクションフックを正しく登録するかテスト
     */
    public function constructor_should_add_customize_register_action(): void
    {
        // setUp でインスタンスが作成され、コンストラクタが実行されている前提
        $hook_priority = has_action('customize_register', [$this->instance, 'integlight_customizer_HeaderTypeSelecter']);

        $this->assertNotFalse(
            $hook_priority,
            'Constructor should add the integlight_customizer_HeaderTypeSelecter method to the customize_register action.'
        );

        // デフォルトの優先度は 10
        $this->assertEquals(10, $hook_priority, 'The hook priority should be the default (10).');
    }

    /**
     * @test
     * integlight_customizer_HeaderTypeSelecter メソッドが設定とコントロールを正しく追加するかテスト
     */
    public function customize_register_method_should_add_setting_and_control(): void
    {
        // フックされたメソッドを手動で呼び出し、WP_Customize_Manager インスタンスを渡す
        $this->instance->integlight_customizer_HeaderTypeSelecter($this->wp_customize);

        // --- 設定 (Setting) の検証 ---
        $setting = $this->wp_customize->get_setting('integlight_display_choice');

        $this->assertInstanceOf(WP_Customize_Setting::class, $setting, 'Setting "integlight_display_choice" should be registered.');
        $this->assertEquals('slider', $setting->default, 'Setting default value should be "header".'); // デフォルト値を確認
        $this->assertEquals('sanitize_text_field', $setting->sanitize_callback, 'Setting sanitize_callback should be "sanitize_text_field".');

        // --- コントロール (Control) の検証 ---
        $control = $this->wp_customize->get_control('integlight_display_choice');

        $this->assertInstanceOf(WP_Customize_Control::class, $control, 'Control "integlight_display_choice" should be registered.');
        $this->assertEquals(__('Display Slider or Image', 'integlight'), $control->label, 'Control label should match.');
        // コンストラクタで渡されたヘルパーから取得したセクションIDと一致するか確認
        $this->assertEquals($this->slider_section_helper->getSliderOrImageSectionId(), $control->section, 'Control section should match the expected section ID.');
        $this->assertEquals('select', $control->type, 'Control type should be "select".');

        // 選択肢 (choices) の検証
        $expected_choices = [
            $this->slider_settings_stub->headerTypeName_slider => __('Slider', 'integlight'),
            $this->slider_settings_stub->headerTypeName_image => __('Image', 'integlight'),
            'none' => __('None', 'integlight'),
        ];
        $this->assertEquals($expected_choices, $control->choices, 'Control choices should match the expected array.');
    }
}
