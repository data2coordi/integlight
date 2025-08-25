<?php // tests/unit-tests/integlight_customizer_slider_settingTest.php

declare(strict_types=1);
// 依存クラスとテスト対象クラスを読み込む (オートロードされていない場合)
// require_once dirname(__DIR__, 2) . '/inc/integlight-customizer-slider-creSection.php';
// require_once dirname(__DIR__, 2) . '/inc/integlight-customizer-slider-setting.php';

/**
 * integlight_customizer_slider_setting クラスのユニットテスト
 *
 * @coversDefaultClass integlight_customizer_slider_setting
 * @group customizer
 * @group slider
 */
class integlight_customizer_slider_settingTest extends WP_UnitTestCase
{
    /**
     * WP_Customize_Manager のインスタンス
     * @var WP_Customize_Manager|null
     */
    private $wp_customize = null;

    /**
     * integlight_customizer_slider_creSection のインスタンス (依存関係)
     * @var integlight_customizer_slider_creSection|null
     */
    private $slider_section_helper = null;

    /**
     * テスト用のグローバル設定
     * @var stdClass|null
     */
    private $mock_slider_settings = null;

    /**
     * テスト対象クラスのインスタンス
     * @var integlight_customizer_slider_setting|null
     */
    private $instance = null;

    /**
     * テスト用のセクションID
     * @var string
     */
    private $test_section_id = 'test_slider_settings_section';





    /*ブローバル変数復元用*/
    private $backup_slider_settings;


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

        // Explicitly load WP_Customize_Control as it's a dependency for integlight-customizer-cmn.php
        if (!class_exists('WP_Customize_Control')) {
            $customize_control_path = ABSPATH . WPINC . '/class-wp-customize-control.php';
            if (!file_exists($customize_control_path)) {
                $this->fail('WP_Customize_Control class file not found at ' . $customize_control_path);
            }
            require_once $customize_control_path;
        }

        // WP_Customize_Manager の実際のインスタンスを作成
        $this->wp_customize = new WP_Customize_Manager();

        // Add core settings that are modified by other hooks called during 'customize_register'
        // to prevent "Attempt to assign property on null" errors.
        $this->wp_customize->add_setting('blogname');
        $this->wp_customize->add_setting('blogdescription');
        $this->wp_customize->add_setting('header_textcolor');

        // Now that WP_Customize_Control is loaded, we can load our custom control file.
        require_once dirname(__DIR__, 3) . '/inc/integlight-customizer-cmn.php';

        // Manually call the function that defines the custom control class,
        // as the 'customize_register' action is not being fired in these tests.
        integlight_define_custom_control_for_customizer();

        // 依存クラスのインスタンスを作成
        $this->slider_section_helper = new integlight_customizer_slider_creSection();
        // テスト対象のコントロールが追加されるセクションIDを取得
        $this->test_section_id = $this->slider_section_helper->getSliderSectionId();


        // テスト用のグローバル設定オブジェクトを作成
        $this->mock_slider_settings = new stdClass();
        $this->mock_slider_settings->effectName_fade = 'fade';
        $this->mock_slider_settings->effectName_slide = 'slide';


        // グローバル変数にセット
        $this->backup_slider_settings = $GLOBALS['Integlight_slider_settings'];
        $GLOBALS['Integlight_slider_settings'] = $this->mock_slider_settings;

        // テスト対象クラスのインスタンスを作成 (引数の順番を修正済み)
        $this->instance = new integlight_customizer_slider_setting(
            $GLOBALS['Integlight_slider_settings'], // 第一引数: 設定オブジェクト (stdClass)
            $this->slider_section_helper          // 第二引数: ヘルパー (integlight_customizer_slider_creSection)
        );

        // テスト中に設定される可能性のある theme_mod をクリア
        remove_theme_mod('integlight_display_choice');
        remove_theme_mod('integlight_slider_effect');
        // remove_theme_mod('integlight_slider_speed'); // setting()内で定義されていないためコメントアウト
        remove_theme_mod('integlight_slider_change_duration'); // setting()内で定義されているもの
        // remove_theme_mod('integlight_slider_autoplay'); // setting()内で定義されていないためコメントアウト
        // remove_theme_mod('integlight_slider_autoplaySpeed'); // setting()内で定義されていないためコメントアウト
        // remove_theme_mod('integlight_slider_dots'); // setting()内で定義されていないためコメントアウト
        // remove_theme_mod('integlight_slider_arrows'); // setting()内で定義されていないためコメントアウト
        remove_theme_mod('integlight_slider_text_1');
        remove_theme_mod('integlight_slider_text_2');
        remove_theme_mod('integlight_slider_text_color');
        remove_theme_mod('integlight_slider_text_font');
        remove_theme_mod('integlight_slider_text_top');
        remove_theme_mod('integlight_slider_text_left');
        remove_theme_mod('integlight_slider_image_1');
        remove_theme_mod('integlight_slider_image_2');
        remove_theme_mod('integlight_slider_image_3');
        remove_theme_mod('integlight_slider_text_top_mobile');
        remove_theme_mod('integlight_slider_text_left_mobile');
        remove_theme_mod('integlight_slider_image_mobile_1');
        remove_theme_mod('integlight_slider_image_mobile_2');
        remove_theme_mod('integlight_slider_image_mobile_3');
    }

    /**
     * 各テストの後に実行されるティアダウンメソッド
     */
    public function tearDown(): void
    {
        // コンストラクタで追加されたアクションフックを削除
        remove_action('customize_register', [$this->instance, 'setting']); // メソッド名を修正
        // グローバル変数をクリア
        unset($GLOBALS['Integlight_slider_settings']);
        // theme_mod をクリア (setUp と合わせる)
        remove_theme_mod('integlight_display_choice');
        remove_theme_mod('integlight_slider_effect');
        remove_theme_mod('integlight_slider_change_duration');
        remove_theme_mod('integlight_slider_text_1');
        remove_theme_mod('integlight_slider_text_2');
        remove_theme_mod('integlight_slider_text_color');
        remove_theme_mod('integlight_slider_text_font');
        remove_theme_mod('integlight_slider_text_top');
        remove_theme_mod('integlight_slider_text_left');
        remove_theme_mod('integlight_slider_image_1');
        remove_theme_mod('integlight_slider_image_2');
        remove_theme_mod('integlight_slider_image_3');
        remove_theme_mod('integlight_slider_text_top_mobile');
        remove_theme_mod('integlight_slider_text_left_mobile');
        remove_theme_mod('integlight_slider_image_mobile_1');
        remove_theme_mod('integlight_slider_image_mobile_2');
        remove_theme_mod('integlight_slider_image_mobile_3');
        // プロパティをクリア
        unset($this->wp_customize);
        unset($this->slider_section_helper);
        unset($this->mock_slider_settings);
        unset($this->instance);
        $GLOBALS['Integlight_slider_settings'] = $this->backup_slider_settings;

        parent::tearDown();
    }

    /**
     * @test
     * @covers ::__construct
     * コンストラクタが customize_register アクションフックを正しく登録するかテスト
     */
    public function constructor_should_add_customize_register_action(): void
    {
        // setUp でインスタンスが作成され、コンストラクタが実行されている前提
        // 修正: チェックするメソッド名を 'setting' に変更
        $hook_priority = has_action('customize_register', [$this->instance, 'setting']);

        $this->assertNotFalse(
            $hook_priority,
            // 修正: アサーションメッセージを実態に合わせる
            'Constructor should add the setting method to the customize_register action.'
        );
        $this->assertEquals(10, $hook_priority, 'The hook priority should be the default (10).');
    }

    /**
     * @test
     * @covers ::setting
     * スライダー関連のカスタマイザー設定が正しく追加されるかテスト
     * 注意: このテストは setting() メソッドで実際に定義されている設定のみを対象とします。
     */
    public function settings_should_be_added_correctly(): void
    {
        $manager = new Integlight_Customizer_Manager();
        $manager->register_panels($this->wp_customize); // パネルの登録

        // Act: テスト対象のメソッドと、その依存先のメソッドを直接呼び出す
        $this->slider_section_helper->creSection($this->wp_customize); // 依存: セクションを作成
        $this->instance->setting($this->wp_customize); // テスト対象: 設定とコントロールを作成

        // Assert: 各設定が存在し、パラメータが正しいことを確認
        // setting() メソッドで定義されている設定のみをチェック
        $settings_to_check = [
            // Animation
            'integlight_slider_Animation_heading' => [ // labelSetting
                'sanitize_callback' => 'sanitize_text_field',
                // 'default' は labelSetting では設定されない
            ],
            'integlight_slider_effect' => [ // effectSetting
                'default' => 'slide', // effectSetting 内のデフォルト値
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'integlight_slider_change_duration' => [ // numberSetting
                'default' => '1', // numberSetting 内のデフォルト値
                'sanitize_callback' => 'absint',
            ],
            // Text
            'integlight_slider_text_heading' => [ // labelSetting
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'integlight_slider_text_1' => [ // textSetting
                'default' => __('Slider Text Main', 'integlight'), // textSetting 内のデフォルト値 (label)
                'sanitize_callback' => 'sanitize_textarea_field',
            ],
            'integlight_slider_text_2' => [ // textSetting
                'default' => __('Slider Text Sub', 'integlight'),
                'sanitize_callback' => 'sanitize_textarea_field',
            ],
            'integlight_slider_text_color' => [ // colorSetting
                'default' => '#000000', // colorSetting 内のデフォルト値
                'sanitize_callback' => 'sanitize_hex_color',
            ],
            'integlight_slider_text_font' => [ // fonttypeSetting
                'default' => 'yu_gothic', // fonttypeSetting 内のデフォルト値
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'integlight_slider_text_position_heading' => [ // labelSetting
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'integlight_slider_text_top' => [ // numberSetting
                'default' => '1',
                'sanitize_callback' => 'absint',
            ],
            'integlight_slider_text_left' => [ // numberSetting
                'default' => '1',
                'sanitize_callback' => 'absint',
            ],
            // Image
            'integlight_slider_image_heading' => [ // labelSetting
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'integlight_slider_image_1' => [ // imageSetting
                'default' => '', // imageSetting 内のデフォルト値
                'sanitize_callback' => 'absint',
            ],
            'integlight_slider_image_2' => [ // imageSetting
                'default' => '',
                'sanitize_callback' => 'absint',
            ],
            'integlight_slider_image_3' => [ // imageSetting
                'default' => '',
                'sanitize_callback' => 'absint',
            ],
            // Mobile Text
            'integlight_slider_text_position_heading_mobile' => [ // labelSetting
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'integlight_slider_text_top_mobile' => [ // numberSetting
                'default' => '1',
                'sanitize_callback' => 'absint',
            ],
            'integlight_slider_text_left_mobile' => [ // numberSetting
                'default' => '1',
                'sanitize_callback' => 'absint',
            ],
            // Mobile Image
            'integlight_slider_image_mobile_heading' => [ // labelSetting
                'sanitize_callback' => 'sanitize_text_field',
            ],
            'integlight_slider_image_mobile_1' => [ // imageSetting
                'default' => '',
                'sanitize_callback' => 'absint',
            ],
            'integlight_slider_image_mobile_2' => [ // imageSetting
                'default' => '',
                'sanitize_callback' => 'absint',
            ],
            'integlight_slider_image_mobile_3' => [ // imageSetting
                'default' => '',
                'sanitize_callback' => 'absint',
            ],
        ];

        foreach ($settings_to_check as $setting_id => $expected_params) {
            $setting = $this->wp_customize->get_setting($setting_id);
            $this->assertInstanceOf(WP_Customize_Setting::class, $setting, "Setting '{$setting_id}' should be added.");

            // default 値が存在する場合のみチェック
            if (isset($expected_params['default'])) {
                $this->assertEquals($expected_params['default'], $setting->default, "Setting '{$setting_id}' default value should be correct.");
            }

            // sanitize_callback は callable として比較
            $this->assertTrue(is_callable($setting->sanitize_callback), "Setting '{$setting_id}' sanitize_callback should be callable.");
            // 可能であればコールバック関数名を比較
            if (is_string($expected_params['sanitize_callback']) && is_string($setting->sanitize_callback)) {
                $this->assertEquals($expected_params['sanitize_callback'], $setting->sanitize_callback, "Setting '{$setting_id}' sanitize_callback function name should be correct.");
            }
            // 必要に応じて他のコールバックタイプ（配列など）のチェックを追加
        }
    }


    /**
     * @test
     * @covers ::setting
     * スライダー関連のカスタマイザーコントロールが正しく追加されるかテスト
     * 注意: このテストは setting() メソッドで実際に定義されているコントロールのみを対象とします。
     */
    public function controls_should_be_added_correctly(): void
    {
        // Act: テスト対象のメソッドと、その依存先のメソッドを直接呼び出す
        $this->slider_section_helper->creSection($this->wp_customize); // 依存: セクションを作成
        $this->instance->setting($this->wp_customize); // テスト対象: コントロールを作成

        // Assert: 各コントロールが存在し、パラメータが正しいことを確認
        // setting() メソッドで定義されているコントロールのみをチェック
        $controls_to_check = [
            // Animation
            'integlight_slider_Animation_heading' => [ // labelSetting
                'label' => __('Slider Animation', 'integlight'),
                'section' => $this->test_section_id,
                'type' => 'heading', // integlight_customizer_creBigTitle の type
                'control_class' => 'integlight_customizer_creBigTitle',
            ],
            'integlight_slider_effect' => [ // effectSetting
                'label' => __('Effect', 'integlight'),
                'section' => $this->test_section_id,
                'type' => 'select',
                'choices' => [
                    $this->mock_slider_settings->effectName_fade => __('Fade', 'integlight'),
                    $this->mock_slider_settings->effectName_slide => __('Slide', 'integlight'),
                ],
                'control_class' => 'WP_Customize_Control',
            ],
            'integlight_slider_change_duration' => [ // numberSetting
                'label' => __('Slider Change Duration (seconds)', 'integlight'),
                'section' => $this->test_section_id,
                'type' => 'number',
                'input_attrs' => ['min' => 1, 'step' => 1],
                'control_class' => 'WP_Customize_Control',
            ],
            // Text
            'integlight_slider_text_heading' => [ // labelSetting
                'label' => __('Slider Text', 'integlight'),
                'section' => $this->test_section_id,
                'type' => 'heading',
                'control_class' => 'integlight_customizer_creBigTitle',
            ],
            'integlight_slider_text_1' => [ // textSetting
                'label' => __('Slider Text Main', 'integlight'),
                'section' => $this->test_section_id,
                'type' => 'textarea', // textSetting はデフォルトのテキストコントロールを追加
                'control_class' => 'WP_Customize_Control',
            ],
            'integlight_slider_text_2' => [ // textSetting
                'label' => __('Slider Text Sub', 'integlight'),
                'section' => $this->test_section_id,
                'type' => 'textarea',
                'control_class' => 'WP_Customize_Control',
            ],
            'integlight_slider_text_color' => [ // colorSetting
                'label' => __('Slider Text color', 'integlight'),
                'section' => $this->test_section_id,
                'type' => 'color', // WP_Customize_Color_Control の type
                'control_class' => 'WP_Customize_Color_Control',
            ],
            'integlight_slider_text_font' => [ // fonttypeSetting
                'label' => __('Slider Text Font', 'integlight'),
                'section' => $this->test_section_id,
                'type' => 'select',
                'choices' => [
                    'yu_gothic' => __('yu gothic', 'integlight'),
                    'yu_mincho' => __('yu mincho', 'integlight'),
                ],
                'control_class' => 'WP_Customize_Control',
            ],
            'integlight_slider_text_position_heading' => [ // labelSetting
                'label' => __('Slider Text Position', 'integlight'),
                'section' => $this->test_section_id,
                'type' => 'heading',
                'control_class' => 'integlight_customizer_creBigTitle',
            ],
            'integlight_slider_text_top' => [ // numberSetting
                'label' => __('Slider Text Position Top (px)', 'integlight'),
                'section' => $this->test_section_id,
                'type' => 'number',
                'input_attrs' => ['min' => 0, 'step' => 1],
                'control_class' => 'WP_Customize_Control',
            ],
            'integlight_slider_text_left' => [ // numberSetting
                'label' => __('Slider Text Position Left (px)', 'integlight'),
                'section' => $this->test_section_id,
                'type' => 'number',
                'input_attrs' => ['min' => 0, 'step' => 1],
                'control_class' => 'WP_Customize_Control',
            ],
            // Image
            'integlight_slider_image_heading' => [ // labelSetting
                'label' => __('Slider Image', 'integlight'),
                'section' => $this->test_section_id,
                'type' => 'heading',
                'description' => __('Recommended: 1920px (width) × 1080px (height).', 'integlight'),
                'control_class' => 'integlight_customizer_creBigTitle',
            ],
            'integlight_slider_image_1' => [ // imageSetting
                'label' => __('Slider Image 1', 'integlight'),
                'section' => $this->test_section_id,
                'type' => 'media', // WP_Customize_Media_Control の type
                'mime_type' => 'image',
                'control_class' => 'WP_Customize_Media_Control',
            ],
            'integlight_slider_image_2' => [ // imageSetting
                'label' => __('Slider Image 2', 'integlight'),
                'section' => $this->test_section_id,
                'type' => 'media',
                'mime_type' => 'image',
                'control_class' => 'WP_Customize_Media_Control',
            ],
            'integlight_slider_image_3' => [ // imageSetting
                'label' => __('Slider Image 3', 'integlight'),
                'section' => $this->test_section_id,
                'type' => 'media',
                'mime_type' => 'image',
                'control_class' => 'WP_Customize_Media_Control',
            ],
            // Mobile Text
            'integlight_slider_text_position_heading_mobile' => [ // labelSetting
                'label' => __('Slider Text Position Mobile', 'integlight'),
                'section' => $this->test_section_id,
                'type' => 'heading',
                'control_class' => 'integlight_customizer_creBigTitle',
            ],
            'integlight_slider_text_top_mobile' => [ // numberSetting
                'label' => __('Slider Text Position Top Mobile (px)', 'integlight'),
                'section' => $this->test_section_id,
                'type' => 'number',
                'input_attrs' => ['min' => 0, 'step' => 1],
                'control_class' => 'WP_Customize_Control',
            ],
            'integlight_slider_text_left_mobile' => [ // numberSetting
                'label' => __('Slider Text Position Left Mobile (px)', 'integlight'),
                'section' => $this->test_section_id,
                'type' => 'number',
                'input_attrs' => ['min' => 0, 'step' => 1],
                'control_class' => 'WP_Customize_Control',
            ],
            // Mobile Image
            'integlight_slider_image_mobile_heading' => [ // labelSetting
                'label' => __('Slider Image mobile *option', 'integlight'),
                'section' => $this->test_section_id,
                'type' => 'heading',
                'description' => __('Recommended: 750px (width) × 1334px (height).*If not set, the PC version will be applied.', 'integlight'),
                'control_class' => 'integlight_customizer_creBigTitle',
            ],
            'integlight_slider_image_mobile_1' => [ // imageSetting
                'label' => __('Slider Image mobile 1', 'integlight'),
                'section' => $this->test_section_id,
                'type' => 'media',
                'mime_type' => 'image',
                'control_class' => 'WP_Customize_Media_Control',
            ],
            'integlight_slider_image_mobile_2' => [ // imageSetting
                'label' => __('Slider Image mobile 2', 'integlight'),
                'section' => $this->test_section_id,
                'type' => 'media',
                'mime_type' => 'image',
                'control_class' => 'WP_Customize_Media_Control',
            ],
            'integlight_slider_image_mobile_3' => [ // imageSetting
                'label' => __('Slider Image mobile 3', 'integlight'),
                'section' => $this->test_section_id,
                'type' => 'media',
                'mime_type' => 'image',
                'control_class' => 'WP_Customize_Media_Control',
            ],
        ];


        foreach ($controls_to_check as $control_id => $expected_params) {
            $control = $this->wp_customize->get_control($control_id);

            // 期待されるコントロールクラスのインスタンスであることを確認
            $this->assertInstanceOf($expected_params['control_class'], $control, "Control '{$control_id}' should be an instance of {$expected_params['control_class']}.");

            $this->assertEquals($expected_params['label'], $control->label, "Control '{$control_id}' label should be correct.");
            $this->assertEquals($expected_params['section'], $control->section, "Control '{$control_id}' section should be correct.");
            $this->assertEquals($expected_params['type'], $control->type, "Control '{$control_id}' type should be correct.");

            if (isset($expected_params['choices'])) {
                $this->assertEquals($expected_params['choices'], $control->choices, "Control '{$control_id}' choices should be correct.");
            }
            if (isset($expected_params['input_attrs'])) {
                $this->assertEquals($expected_params['input_attrs'], $control->input_attrs, "Control '{$control_id}' input_attrs should be correct.");
            }
            if (isset($expected_params['description'])) {
                $this->assertEquals($expected_params['description'], $control->description, "Control '{$control_id}' description should be correct.");
            }
            if (isset($expected_params['mime_type'])) {
                $this->assertEquals($expected_params['mime_type'], $control->mime_type, "Control '{$control_id}' mime_type should be correct.");
            }

            // active_callback はこのクラスでは設定されていないため、チェックしない
            // $this->assertTrue(isset($control->active_callback), "Control '{$control_id}' should have an active_callback.");
            // $this->assertTrue(is_callable($control->active_callback), "Control '{$control_id}' active_callback should be callable.");
        }
    }


    /**
     * @test
     * @covers ::setting
     * コントロールの表示状態が、属するセクションの active_callback に基づいて正しく決定されるかテスト
     * 注意: コントロール自体に active_callback は設定されていませんが、セクションの表示状態に依存します。
     * @dataProvider activeCallbackDataProvider
     */
    public function controls_active_callback_should_work_correctly(string $display_choice, bool $expected_result): void
    {
        // Arrange: テーマ設定を設定
        set_theme_mod('integlight_display_choice', $display_choice);

        // Act: 必要なメソッドを直接呼び出して、セクションとコントロールをセットアップ
        $this->slider_section_helper->creSection($this->wp_customize);
        $this->instance->setting($this->wp_customize);

        // Assert: コントロールが属するセクションを取得し、その active() メソッドの結果を検証
        // PHPのテストでは、コントロールの active() はセクションの状態を自動的に反映しないため、
        // 表示状態のロジックを持つセクションを直接テストします。
        $section = $this->wp_customize->get_section($this->test_section_id);
        $this->assertInstanceOf(WP_Customize_Section::class, $section, 'Section for active_callback test should exist.');

        $actual_result = $section->active();
        $this->assertSame(
            $expected_result,
            $actual_result,
            "The section's active state should be " . ($expected_result ? 'true' : 'false') . " when display_choice is '{$display_choice}'."
        );
    }


    /**
     * active_callback テスト用のデータプロバイダー
     */
    public function activeCallbackDataProvider(): array
    {
        return [
            'Display choice is slider' => ['slider', true],
            'Display choice is image' => ['image', false],
            'Display choice is none' => ['none', false],
        ];
    }
}
