<?php // tests/unit-tests/integlight_customizer_sidebarTest.php

declare(strict_types=1);

// テスト対象クラスを含むファイルを読み込む (オートロードされていない場合)
// require_once dirname(__DIR__, 2) . '/inc/integlight-customizer.php';

/**
 * integlight_customizer_sidebar クラスのユニットテスト
 *
 * @coversDefaultClass integlight_customizer_sidebar
 * @group customizer
 * @group sidebar
 */
class integlight_customizer_integlight_customizer_sidebarTest extends WP_UnitTestCase
{
    /**
     * WP_Customize_Manager のインスタンス
     * @var WP_Customize_Manager|null
     */
    private $wp_customize = null;

    /**
     * テスト対象クラスのインスタンス
     * @var integlight_customizer_sidebar|null
     */
    private $instance = null;

    /**
     * テスト用のセクションID
     * @var string
     */
    private $section_id = 'integlight_sidebar_section';

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

        // テスト対象クラスのインスタンスを作成
        $this->instance = new integlight_customizer_sidebar();

        // テスト中に設定される可能性のある theme_mod をクリア
        remove_theme_mod('integlight_sidebar1_position');
        remove_theme_mod('integlight_sidebar2_position');
    }

    /**
     * 各テストの後に実行されるティアダウンメソッド
     */
    public function tearDown(): void
    {
        // コンストラクタで追加されたアクションフックを削除
        remove_action('customize_register', [$this->instance, 'customize_register_sidebar']);
        // theme_mod をクリア
        remove_theme_mod('integlight_sidebar1_position');
        remove_theme_mod('integlight_sidebar2_position');
        // プロパティをクリア
        unset($this->wp_customize);
        unset($this->instance);

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
        $hook_priority = has_action('customize_register', [$this->instance, 'customize_register_sidebar']);

        $this->assertNotFalse(
            $hook_priority,
            'Constructor should add the customize_register_sidebar method to the customize_register action.'
        );
        $this->assertEquals(10, $hook_priority, 'The hook priority should be the default (10).');
    }

    /**
     * @test
     * @covers ::customize_register_sidebar
     * サイドバー設定セクションが正しく追加されるかテスト
     */
    public function customize_register_sidebar_should_add_section(): void
    {
        // Act: フックされたメソッドを手動で呼び出し
        $this->instance->customize_register_sidebar($this->wp_customize);

        // Assert: セクションが存在し、パラメータが正しいことを確認
        $section = $this->wp_customize->get_section($this->section_id);
        $this->assertInstanceOf(WP_Customize_Section::class, $section, "Section '{$this->section_id}' should be added.");
        $this->assertEquals(__('Sidebar Settings', 'integlight'), $section->title, 'Section title should be correct.');
        $this->assertEquals(30, $section->priority, 'Section priority should be correct.');
    }

    /**
     * @test
     * @covers ::customize_register_sidebar
     * @covers ::helper_setting
     * サイドバー1と2の設定とコントロールが正しく追加されるかテスト
     */
    public function customize_register_sidebar_should_add_settings_and_controls(): void
    {
        // Act: フックされたメソッドを手動で呼び出し
        $this->instance->customize_register_sidebar($this->wp_customize);

        // Assert: 各設定とコントロールが存在し、パラメータが正しいことを確認
        $settings_controls = [
            'integlight_sidebar1_position' => [
                'setting_default' => 'right',
                'control_label' => __('Sidebar', 'integlight') . '1' . ' ' . __('Position', 'integlight'),
                'control_type' => 'radio',
                'control_choices' => [
                    'right' => __('Right', 'integlight'),
                    'left' => __('Left', 'integlight'),
                    'bottom' => __('Bottom', 'integlight'),
                    'none' => __('None', 'integlight'),
                ],
            ],
            'integlight_sidebar2_position' => [
                'setting_default' => 'none',
                'control_label' => __('Sidebar', 'integlight') . '2' . ' ' . __('Position', 'integlight'),
                'control_type' => 'radio',
                'control_choices' => [
                    'right' => __('Right', 'integlight'),
                    'left' => __('Left', 'integlight'),
                    'bottom' => __('Bottom', 'integlight'),
                    'none' => __('None', 'integlight'),
                ],
            ],
        ];

        foreach ($settings_controls as $setting_id => $params) {
            // Setting の確認
            $setting = $this->wp_customize->get_setting($setting_id);
            $this->assertInstanceOf(WP_Customize_Setting::class, $setting, "Setting '{$setting_id}' should be added.");
            $this->assertEquals($params['setting_default'], $setting->default, "Setting '{$setting_id}' default value should be correct.");
            $this->assertTrue(is_callable($setting->sanitize_callback), "Setting '{$setting_id}' sanitize_callback should be callable.");
            // sanitize_callback の内容自体は別のテストで確認

            // Control の確認
            // コントロールIDは設定ID + '_control'
            $control_id = $setting_id . '_control';
            $control = $this->wp_customize->get_control($control_id);
            $this->assertInstanceOf(WP_Customize_Control::class, $control, "Control '{$control_id}' should be added.");
            $this->assertEquals($params['control_label'], $control->label, "Control '{$control_id}' label should be correct.");
            $this->assertEquals($this->section_id, $control->section, "Control '{$control_id}' section should be correct.");



            // $control->settings['default'] が WP_Customize_Setting オブジェクトであることを確認
            $setting_object = $control->settings['default'];
            $this->assertInstanceOf(WP_Customize_Setting::class, $setting_object, "Control '{$control_id}' settings['default'] should be a WP_Customize_Setting object.");
            // オブジェクトの id プロパティと比較
            $this->assertEquals($setting_id, $setting_object->id, "Control '{$control_id}' settings should point to '{$setting_id}'.");





            $this->assertEquals($params['control_type'], $control->type, "Control '{$control_id}' type should be correct.");
            $this->assertEquals($params['control_choices'], $control->choices, "Control '{$control_id}' choices should be correct.");
        }
    }

    /**
     * @test
     * @covers ::sanitize_sidebar_position
     * サニタイズコールバックが入力値をそのまま返すことをテスト (現在の実装)
     * @dataProvider sidebarPositionProvider
     */
    public function sanitize_sidebar_position_should_return_input(string $input_position): void
    {
        // Act: サニタイズメソッドを直接呼び出し
        $sanitized_value = $this->instance->sanitize_sidebar_position($input_position);

        // Assert: 入力値がそのまま返されることを確認
        $this->assertEquals($input_position, $sanitized_value);
    }

    /**
     * サイドバー位置のデータプロバイダー
     * @return array<string, array{string}>
     */
    public function sidebarPositionProvider(): array
    {
        return [
            'Right position' => ['right'],
            'Left position' => ['left'],
            'Bottom position' => ['bottom'],
            'None position' => ['none'],
            'Invalid position' => ['invalid'], // 現在の実装ではこれもそのまま返される
            'Empty string' => [''],           // 空文字もそのまま返される
        ];
    }

    /**
     * @test
     * @covers ::sanitize_sidebar_position
     * サニタイズコールバックが設定オブジェクトを通じて呼び出された場合に正しく動作するかテスト
     * (より実践的なテスト)
     */
    public function sanitize_sidebar_position_should_work_via_setting_object(): void
    {
        // Arrange: テスト用の設定を追加
        $setting_id = 'test_sidebar_setting';
        $this->wp_customize->add_setting($setting_id, [
            'default' => 'right',
            'sanitize_callback' => [$this->instance, 'sanitize_sidebar_position'],
        ]);
        $setting = $this->wp_customize->get_setting($setting_id);

        // Act & Assert: 様々な値をサニタイズして確認
        $this->assertEquals('left', $setting->sanitize('left'));
        $this->assertEquals('none', $setting->sanitize('none'));
        $this->assertEquals('invalid_value', $setting->sanitize('invalid_value')); // 現在の実装ではそのまま返る
    }
}
