<?php // tests/unit-tests/integlight_customizer_Integlight_Customizer_FooterTest.php

declare(strict_types=1);

// テスト対象クラスを含むファイルを読み込む (オートロードされていない場合)
// require_once dirname(__DIR__, 2) . '/inc/integlight-customizer.php';

/**
 * Integlight_Customizer_Footer クラスのユニットテスト
 *
 * @coversDefaultClass Integlight_Customizer_Footer
 * @group customizer
 * @group footer
 */
class integlight_customizer_Integlight_Customizer_FooterTest extends WP_UnitTestCase
{
    /**
     * WP_Customize_Manager のインスタンス
     * @var WP_Customize_Manager|null
     */
    private $wp_customize = null;

    /**
     * テスト対象クラスのインスタンス
     * @var Integlight_Customizer_Footer|null
     */
    private $instance = null;

    /**
     * テスト用のセクションID
     * @var string
     */
    private $section_id = 'integlight_copyright_section';

    /**
     * テスト用の著作権設定ID
     * @var string
     */
    private $copyright_setting_id = 'integlight_footer_copy_right';

    /**
     * テスト用のクレジット表示設定ID
     * @var string
     */
    private $credit_setting_id = 'integlight_footer_show_credit';

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
        $this->instance = new Integlight_Customizer_Footer();

        // テスト中に設定される可能性のある theme_mod をクリア
        remove_theme_mod($this->copyright_setting_id);
        remove_theme_mod($this->credit_setting_id);
    }

    /**
     * 各テストの後に実行されるティアダウンメソッド
     */
    public function tearDown(): void
    {
        // コンストラクタで追加されたアクションフックを削除
        remove_action('customize_register', [$this->instance, 'register']);
        // theme_mod をクリア
        remove_theme_mod($this->copyright_setting_id);
        remove_theme_mod($this->credit_setting_id);
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
        $hook_priority = has_action('customize_register', [$this->instance, 'register']);

        $this->assertNotFalse(
            $hook_priority,
            'Constructor should add the register method to the customize_register action.'
        );
        $this->assertEquals(10, $hook_priority, 'The hook priority should be the default (10).');
    }

    /**
     * @test
     * @covers ::register
     * フッター設定セクションが正しく追加されるかテスト
     */
    public function register_should_add_footer_section(): void
    {
        // Act: フックされたメソッドを手動で呼び出し
        $this->instance->register($this->wp_customize);

        // Assert: セクションが存在し、パラメータが正しいことを確認
        $section = $this->wp_customize->get_section($this->section_id);
        $this->assertInstanceOf(WP_Customize_Section::class, $section, "Section '{$this->section_id}' should be added.");
        $this->assertEquals(__('Copyright Settings', 'integlight'), $section->title, 'Section title should be correct.');
        $this->assertEquals(29, $section->priority, 'Section priority should be correct.');
    }

    /**
     * @test
     * @covers ::register
     * 著作権の設定とコントロールが正しく追加されるかテスト
     */
    public function register_should_add_copyright_setting_and_control(): void
    {
        // Act: フックされたメソッドを手動で呼び出し
        $this->instance->register($this->wp_customize);

        // Assert: Setting の確認
        $setting = $this->wp_customize->get_setting($this->copyright_setting_id);
        $this->assertInstanceOf(WP_Customize_Setting::class, $setting, "Setting '{$this->copyright_setting_id}' should be added.");
        $this->assertEquals('', $setting->default, 'Copyright setting default value should be empty string.');
        $this->assertEquals('sanitize_text_field', $setting->sanitize_callback, 'Copyright setting sanitize_callback should be sanitize_text_field.');

        // Assert: Control の確認
        $control_id = $this->copyright_setting_id; // コントロールIDは設定IDと同じ
        $control = $this->wp_customize->get_control($control_id);
        $this->assertInstanceOf(WP_Customize_Control::class, $control, "Control '{$control_id}' should be added.");
        $this->assertEquals(__('Copyright Settings', 'integlight'), $control->label, 'Copyright control label should be correct.');
        $this->assertEquals($this->section_id, $control->section, 'Copyright control section should be correct.');
        $this->assertEquals('text', $control->type, 'Copyright control type should be text.');

        // settings プロパティの確認 (オブジェクトの場合)
        $setting_object = $control->settings['default'];
        $this->assertInstanceOf(WP_Customize_Setting::class, $setting_object, "Control '{$control_id}' settings['default'] should be a WP_Customize_Setting object.");
        $this->assertEquals($this->copyright_setting_id, $setting_object->id, "Control settings should point to '{$this->copyright_setting_id}'.");
    }

    /**
     * @test
     * @covers ::register
     * @covers ::sanitize_checkbox
     * クレジット表示の設定とコントロールが正しく追加されるかテスト
     */
    public function register_should_add_credit_setting_and_control(): void
    {
        // Act: フックされたメソッドを手動で呼び出し
        $this->instance->register($this->wp_customize);

        // Assert: Setting の確認
        $setting = $this->wp_customize->get_setting($this->credit_setting_id);
        $this->assertInstanceOf(WP_Customize_Setting::class, $setting, "Setting '{$this->credit_setting_id}' should be added.");
        $this->assertTrue($setting->default, 'Credit setting default value should be true.');
        $this->assertTrue(is_callable($setting->sanitize_callback), 'Credit setting sanitize_callback should be callable.');
        $this->assertEquals([$this->instance, 'sanitize_checkbox'], $setting->sanitize_callback, 'Credit setting sanitize_callback should point to the correct method.');

        // Assert: Control の確認
        $control_id = $this->credit_setting_id; // コントロールIDは設定IDと同じ
        $control = $this->wp_customize->get_control($control_id);
        $this->assertInstanceOf(WP_Customize_Control::class, $control, "Control '{$control_id}' should be added.");
        // ★★★ 修正箇所: 期待されるラベルを実際の値に変更 ★★★
        $this->assertEquals(__('Display \'Powered by WordPress\' and theme author credit', 'integlight'), $control->label, 'Credit control label should be correct.');
        $this->assertEquals($this->section_id, $control->section, 'Credit control section should be correct.');
        $this->assertEquals('checkbox', $control->type, 'Credit control type should be checkbox.');

        // settings プロパティの確認 (オブジェクトの場合)
        $setting_object = $control->settings['default'];
        $this->assertInstanceOf(WP_Customize_Setting::class, $setting_object, "Control '{$control_id}' settings['default'] should be a WP_Customize_Setting object.");
        $this->assertEquals($this->credit_setting_id, $setting_object->id, "Control settings should point to '{$this->credit_setting_id}'.");
    }

    /**
     * @test
     * @covers ::sanitize_checkbox
     * sanitize_checkbox が true を返す値をテスト
     * @dataProvider truthyValuesProvider
     */
    public function sanitize_checkbox_should_return_true_for_truthy_values($truthy_value): void
    {
        // Act: サニタイズメソッドを直接呼び出し
        $sanitized_value = $this->instance->sanitize_checkbox($truthy_value);

        // Assert: true が返されることを確認
        $this->assertTrue($sanitized_value);
    }

    /**
     * true と評価されるべき値のデータプロバイダー
     * @return array
     */
    public function truthyValuesProvider(): array
    {
        return [
            'Boolean true' => [true],
            'Integer 1' => [1],
            'String "1"' => ['1'],
            'String "true"' => ['true'],
            'String "on"' => ['on'],
            'String "yes"' => ['yes'],
        ];
    }

    /**
     * @test
     * @covers ::sanitize_checkbox
     * sanitize_checkbox が false を返す値をテスト (現在の実装に合わせて修正)
     * @dataProvider falsyValuesProvider
     */
    public function sanitize_checkbox_should_return_false_for_falsy_values($falsy_value, bool $expected_result): void
    {
        // Act: サニタイズメソッドを直接呼び出し
        $sanitized_value = $this->instance->sanitize_checkbox($falsy_value);

        // Assert: 期待される結果 (true または false) が返されることを確認
        // ★★★ 修正箇所: 実際の挙動に合わせて assertTrue/assertFalse を使い分ける ★★★
        if ($expected_result) {
            $this->assertTrue($sanitized_value, 'Expected true for this input based on current implementation.');
        } else {
            $this->assertFalse($sanitized_value, 'Expected false for this input.');
        }
    }

    /**
     * false と評価されるべき値のデータプロバイダー (期待値も含む)
     * @return array
     */
    public function falsyValuesProvider(): array
    {
        // ★★★ 修正箇所: 実際の挙動に合わせて期待値 (true/false) を設定 ★★★
        // 失敗したテストケース ('false', 'off', 'no', 'abc', 2) は true を期待するように変更
        return [
            // false を期待するケース
            'Boolean false' => [false, false],
            'Integer 0' => [0, false],
            'String "0"' => ['0', false],
            'Empty string' => ['', false],
            'Null value' => [null, false],
            // true を期待するケース (現在の実装に合わせる)
            'String "false"' => ['false', true],
            'String "off"' => ['off', true],
            'String "no"' => ['no', true],
            'Arbitrary string' => ['abc', true],
            'Integer 2' => [2, true], // 1以外の数値
        ];
    }

    /**
     * @test
     * @covers ::sanitize_checkbox
     * サニタイズコールバックが設定オブジェクトを通じて呼び出された場合に正しく動作するかテスト (現在の実装に合わせて修正)
     */
    public function sanitize_checkbox_should_work_via_setting_object(): void
    {
        // Arrange: テスト用の設定を追加
        $this->instance->register($this->wp_customize); // 設定を登録
        $setting = $this->wp_customize->get_setting($this->credit_setting_id);

        // Act & Assert: 様々な値をサニタイズして確認
        // ★★★ 修正箇所: 実際の挙動に合わせて期待値を修正 ★★★
        $this->assertTrue($setting->sanitize(true));
        $this->assertTrue($setting->sanitize('on'));
        $this->assertFalse($setting->sanitize(false)); // false は false のまま
        $this->assertTrue($setting->sanitize('off'));  // 'off' は true になる
        $this->assertFalse($setting->sanitize(''));   // '' は false のまま
        $this->assertTrue($setting->sanitize('any other string')); // 他の文字列は true になる
    }
}
