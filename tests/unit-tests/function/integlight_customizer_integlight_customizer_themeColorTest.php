<?php // tests/unit-tests/integlight_customizer_themeColorTest.php

declare(strict_types=1);

// テスト対象クラスと依存クラスを含むファイルを読み込む (オートロードされていない場合)
// require_once dirname(__DIR__, 2) . '/inc/integlight-customizer.php';
// require_once dirname(__DIR__, 2) . '/inc/integlight-functions-outerAssets.php';

/**
 * integlight_customizer_themeColor クラスのユニットテスト
 *
 * @coversDefaultClass integlight_customizer_themeColor
 * @group customizer
 * @group colors
 * @group assets
 */
class integlight_customizer_integlight_customizer_themeColorTest extends WP_UnitTestCase
{
    /**
     * WP_Customize_Manager のインスタンス
     * @var WP_Customize_Manager|null
     */
    private $wp_customize = null;

    /**
     * テスト対象クラスのインスタンス
     * @var integlight_customizer_themeColor|null
     */
    private $instance = null;

    /**
     * テスト用の設定ID
     * @var string
     */
    private $setting_id = 'integlight_base_color_setting';

    /**
     * テスト用のコントロールID
     * @var string
     */
    private $control_id = 'integlight_base_color_setting'; // 設定IDと同じ

    /**
     * テストで使用する theme_mod のキー
     * @var string
     */
    private $theme_mod_key = 'integlight_base_color_setting';

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
        // グローバル変数にもセット (sanitize_choices で参照されるため)
        global $wp_customize;
        $this->wp_customize = new WP_Customize_Manager();
        $wp_customize = $this->wp_customize; // グローバル変数に代入

        // テスト対象クラスのインスタンスを作成
        $this->instance = new integlight_customizer_themeColor();

        // WordPress のスクリプト/スタイルシステムをリセット
        $this->reset_wp_scripts_styles();

        // 依存クラスの静的プロパティをリセット
        $this->reset_static_property(InteglightFrontendStyles::class, 'styles');
        $this->reset_static_property(InteglightEditorStyles::class, 'styles');
        $this->reset_static_property(InteglightDeferCss::class, 'deferred_styles');

        // テスト前に theme_mod をクリア
        remove_theme_mod($this->theme_mod_key);
    }

    /**
     * 各テストの後に実行されるティアダウンメソッド
     */
    public function tearDown(): void
    {
        // コンストラクタで追加されたアクションフックを削除
        remove_action('customize_register', [$this->instance, 'customize_register']);
        remove_action('wp_enqueue_scripts', [$this->instance, 'enqueue_custom_css']);

        // テスト後に theme_mod をクリア
        remove_theme_mod($this->theme_mod_key);

        // プロパティとグローバル変数をクリア
        unset($this->instance);
        unset($this->wp_customize);
        unset($GLOBALS['wp_customize']); // グローバル変数もクリア

        // WordPress のスクリプト/スタイルシステムを再度リセット
        $this->reset_wp_scripts_styles();

        // 依存クラスの静的プロパティをリセット
        $this->reset_static_property(InteglightFrontendStyles::class, 'styles');
        $this->reset_static_property(InteglightEditorStyles::class, 'styles');
        $this->reset_static_property(InteglightDeferCss::class, 'deferred_styles');

        parent::tearDown();
    }

    /**
     * WordPress のスクリプト/スタイルシステムをリセットするヘルパーメソッド
     */
    private function reset_wp_scripts_styles(): void
    {
        global $wp_scripts, $wp_styles;
        $wp_scripts = new WP_Scripts();
        $wp_styles = new WP_Styles();
    }

    /**
     * Reflection を使用して静的プロパティをリセットするヘルパーメソッド
     */
    private function reset_static_property(string $className, string $propertyName, $defaultValue = []): void
    {
        try {
            // クラスが存在するか確認
            if (!class_exists($className)) {
                $this->markTestSkipped("Dependency class {$className} not found.");
                return;
            }
            $reflection = new ReflectionProperty($className, $propertyName);
            if (method_exists($reflection, 'setAccessible')) {
                $reflection->setAccessible(true);
            }
            $reflection->setValue(null, $defaultValue);
        } catch (ReflectionException $e) {
            $this->fail("Failed to reset static property {$className}::{$propertyName}: " . $e->getMessage());
        }
    }

    /**
     * Reflection を使用して静的プロパティの値を取得するヘルパーメソッド
     */
    private function get_static_property_value(string $className, string $propertyName)
    {
        try {
            // クラスが存在するか確認
            if (!class_exists($className)) {
                $this->markTestSkipped("Dependency class {$className} not found.");
                return null;
            }
            $reflectionClass = new ReflectionClass($className);
            $property = $reflectionClass->getProperty($propertyName);
            if (method_exists($property, 'setAccessible')) {
                $property->setAccessible(true);
            }
            return $property->getValue();
        } catch (ReflectionException $e) {
            $this->fail("Failed to get static property {$className}::{$propertyName}: " . $e->getMessage());
        }
    }

    /**
     * @test
     * @covers ::__construct
     * コンストラクタがフックを正しく登録するかテスト
     */
    public function constructor_should_add_hooks(): void
    {
        // setUp でインスタンスが作成され、コンストラクタが実行されている前提
        $hook_priority_customize = has_action('customize_register', [$this->instance, 'customize_register']);
        $hook_priority_enqueue = has_action('wp_enqueue_scripts', [$this->instance, 'enqueue_custom_css']);

        $this->assertNotFalse($hook_priority_customize, 'Constructor should add customize_register hook.');
        $this->assertEquals(10, $hook_priority_customize, 'customize_register hook priority should be 10.');

        $this->assertNotFalse($hook_priority_enqueue, 'Constructor should add wp_enqueue_scripts hook.');
        $this->assertEquals(10, $hook_priority_enqueue, 'wp_enqueue_scripts hook priority should be 10.');
    }

    /**
     * @test
     * @covers ::customize_register
     * 設定とコントロールが正しく追加されるかテスト
     */
    public function customize_register_should_add_setting_and_control(): void
    {
        // Act: フックされたメソッドを手動で呼び出し
        $this->instance->customize_register($this->wp_customize);

        // Assert: Setting の確認
        $setting = $this->wp_customize->get_setting($this->setting_id);
        $this->assertInstanceOf(WP_Customize_Setting::class, $setting, "Setting '{$this->setting_id}' should be added.");
        $this->assertEquals('theme_mod', $setting->type, 'Setting type should be theme_mod.');
        $this->assertEquals('pattern1', $setting->default, 'Setting default value should be "pattern1".');
        $this->assertTrue(is_callable($setting->sanitize_callback), 'Setting sanitize_callback should be callable.');
        // コールバックが正しいメソッドを指しているか確認
        $this->assertEquals([$this->instance, 'sanitize_choices'], $setting->sanitize_callback, 'Setting sanitize_callback should point to the correct method.');

        // Assert: Control の確認
        // Assert: Control の確認
        $control = $this->wp_customize->get_control($this->control_id);
        $this->assertInstanceOf(WP_Customize_Control::class, $control, "Control '{$this->control_id}' should be added.");
        $this->assertEquals('colors', $control->section, 'Control section should be "colors".');

        // $control->settings['default'] が WP_Customize_Setting オブジェクトであることを確認
        $setting_object = $control->settings['default'];
        $this->assertInstanceOf(WP_Customize_Setting::class, $setting_object, "Control '{$this->control_id}' settings['default'] should be a WP_Customize_Setting object.");
        // オブジェクトの id プロパティと比較
        $this->assertEquals($this->setting_id, $setting_object->id, "Control settings should point to '{$this->setting_id}'.");

        $this->assertEquals(__('Accent color setting', 'integlight'), $control->label, 'Control label should be correct.');
        // ... (以下略)





        $this->assertEquals(__('Select favorite accent color', 'integlight'), $control->description, 'Control description should be correct.');
        $this->assertEquals('radio', $control->type, 'Control type should be "radio".');

        // 選択肢の確認
        $expected_choices = [
            'pattern1' => __('None', 'integlight'),
            'pattern2' => __('Blue', 'integlight'),
            'pattern3' => __('Green', 'integlight'),
            'pattern4' => __('Orange', 'integlight'),
            'pattern5' => __('Red', 'integlight'),
            'pattern6' => __('Pink', 'integlight'),
            'pattern7' => __('Khaki', 'integlight'),
            'pattern8' => __('Navy', 'integlight'),

        ];
        $this->assertEquals($expected_choices, $control->choices, 'Control choices should be correct.');
    }

    /**
     * @test
     * @covers ::sanitize_choices
     * サニタイズコールバックが有効な入力値をそのまま返すことをテスト
     * @dataProvider validChoicesProvider
     */
    public function sanitize_choices_should_return_valid_input(string $valid_input): void
    {
        // Arrange: 設定とコントロールを登録
        $this->instance->customize_register($this->wp_customize);
        $setting = $this->wp_customize->get_setting($this->setting_id);

        // Act: サニタイズメソッドを直接呼び出し
        $sanitized_value = $this->instance->sanitize_choices($valid_input, $setting);

        // Assert: 入力値がそのまま返されることを確認
        $this->assertEquals($valid_input, $sanitized_value);
    }

    /**
     * 有効な選択肢のデータプロバイダー
     * @return array<string, array{string}>
     */
    public function validChoicesProvider(): array
    {
        return [
            'Pattern 1' => ['pattern1'],
            'Pattern 2' => ['pattern2'],
            'Pattern 3' => ['pattern3'],
            'Pattern 4' => ['pattern4'],
            'Pattern 5' => ['pattern5'],
            'Pattern 6' => ['pattern6'],
        ];
    }

    /**
     * @test
     * @covers ::sanitize_choices
     * サニタイズコールバックが無効な入力値に対してデフォルト値を返すことをテスト
     * @dataProvider invalidChoicesProvider
     */
    public function sanitize_choices_should_return_default_for_invalid_input(string $invalid_input): void
    {
        // Arrange: 設定とコントロールを登録
        $this->instance->customize_register($this->wp_customize);
        $setting = $this->wp_customize->get_setting($this->setting_id);
        $default_value = $setting->default; // 'pattern1'

        // Act: サニタイズメソッドを直接呼び出し
        $sanitized_value = $this->instance->sanitize_choices($invalid_input, $setting);

        // Assert: デフォルト値が返されることを確認
        $this->assertEquals($default_value, $sanitized_value);
    }

    /**
     * 無効な選択肢のデータプロバイダー
     * @return array<string, array{string}>
     */
    public function invalidChoicesProvider(): array
    {
        return [
            'Invalid string' => ['invalid_pattern'],
            'Empty string' => [''],
            'Number string' => ['1'],
        ];
    }

    /**
     * @test
     * @covers ::sanitize_choices
     * サニタイズコールバックが設定オブジェクトを通じて呼び出された場合に正しく動作するかテスト
     */
    public function sanitize_choices_should_work_via_setting_object(): void
    {
        // Arrange: 設定とコントロールを登録
        $this->instance->customize_register($this->wp_customize);
        $setting = $this->wp_customize->get_setting($this->setting_id);

        // Act & Assert: 様々な値をサニタイズして確認
        $this->assertEquals('pattern3', $setting->sanitize('pattern3')); // Valid
        $this->assertEquals('pattern1', $setting->sanitize('invalid_value')); // Invalid, should return default 'pattern1'
        $this->assertEquals('pattern1', $setting->sanitize('')); // Empty, should return default 'pattern1'
    }

    /**
     * @test
     * @covers ::enqueue_custom_css
     * テーマ設定に基づいて正しいCSSが追加され、遅延対象になるかテスト
     * @dataProvider themeModProvider
     */
    public function enqueue_custom_css_should_add_styles_and_defer(string $theme_mod_value, string $expected_css_file): void
    {
        // Arrange: テーマ設定を設定
        set_theme_mod($this->theme_mod_key, $theme_mod_value);

        // Act: wp_enqueue_scripts アクションを実行
        $this->instance->enqueue_custom_css(); // 直接呼び出してアセット追加を確認

        // Assert: InteglightFrontendStyles にスタイルが追加されたか
        $frontend_styles = $this->get_static_property_value(InteglightFrontendStyles::class, 'styles');
        $this->assertArrayHasKey('custom-pattern', $frontend_styles, 'FrontendStyles should have "custom-pattern" key.');
        $this->assertEquals($expected_css_file, $frontend_styles['custom-pattern'], 'FrontendStyles path should be correct.');

        // Assert: InteglightEditorStyles にスタイルが追加されたか
        $editor_styles = $this->get_static_property_value(InteglightEditorStyles::class, 'styles');
        $this->assertArrayHasKey('custom-pattern', $editor_styles, 'EditorStyles should have "custom-pattern" key.');
        $this->assertEquals($expected_css_file, $editor_styles['custom-pattern'], 'EditorStyles path should be correct.');

        // Assert: InteglightDeferCss に遅延スクリプトが追加されたか
        $deferred_styles = $this->get_static_property_value(InteglightDeferCss::class, 'deferred_styles');
        $this->assertContains('custom-pattern', $deferred_styles, 'Style "custom-pattern" should be added for deferring.');
    }

    /**
     * テーマ設定値と期待されるCSSファイルのデータプロバイダー
     * @return array<string, array{string, string}>
     */
    public function themeModProvider(): array
    {
        return [
            'Default (pattern1)' => ['pattern1', '/css/pattern1.css'],
            'Pattern 2' => ['pattern2', '/css/pattern2.css'],
            'Pattern 6' => ['pattern6', '/css/pattern6.css'],
            // 必要に応じて他のパターンも追加
        ];
    }

    /**
     * @test
     * @covers ::enqueue_custom_css
     * テーマ設定が未設定の場合にデフォルトのCSSが追加されるかテスト
     */
    public function enqueue_custom_css_should_use_default_when_mod_not_set(): void
    {
        // Arrange: テーマ設定は setUp でクリア済み
        $expected_css_file = '/css/pattern1.css'; // デフォルト値 'pattern1' に対応

        // Act: wp_enqueue_scripts アクションを実行
        $this->instance->enqueue_custom_css(); // 直接呼び出し

        // Assert: デフォルトのCSSが追加されているか確認
        $frontend_styles = $this->get_static_property_value(InteglightFrontendStyles::class, 'styles');
        $this->assertEquals($expected_css_file, $frontend_styles['custom-pattern'], 'Default FrontendStyles path should be correct.');

        $editor_styles = $this->get_static_property_value(InteglightEditorStyles::class, 'styles');
        $this->assertEquals($expected_css_file, $editor_styles['custom-pattern'], 'Default EditorStyles path should be correct.');

        $deferred_styles = $this->get_static_property_value(InteglightDeferCss::class, 'deferred_styles');
        $this->assertContains('custom-pattern', $deferred_styles, 'Default style "custom-pattern" should be added for deferring.');
    }
}
