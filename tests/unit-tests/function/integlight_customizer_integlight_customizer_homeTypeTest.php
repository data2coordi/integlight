<?php
class integlight_customizer_integlight_customizer_homeTypeTest extends WP_UnitTestCase
{
    private $wp_customize;
    private $instance;
    private $setting_id = 'integlight_hometype_setting';

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

        $this->instance = new integlight_customizer_HomeType();
        $this->homeTypeLoader = new InteglightHomeTypeLoader();
    }

    public function tearDown(): void
    {
        remove_action('customize_register', [$this->instance, 'customize_register']);
        unset($this->instance);
        unset($this->wp_customize);
        unset($GLOBALS['wp_customize']);
        remove_action('wp_enqueue_scripts', [$this->homeTypeLoader, 'enqueue_hometype_css']);
        unset($this->homeTypeLoader);


        parent::tearDown();
    }

    /**
     * @test
     * カスタマイザーにセクション 'integlight_hometype_section' が追加されているか検証
     */
    public function customize_register_should_add_section(): void
    {
        // customize_register を手動呼び出し
        $this->instance->customize_register($this->wp_customize);

        // セクションの存在確認
        $section = $this->wp_customize->get_section('integlight_hometype_section');

        $this->assertNotNull($section, 'integlight_hometype_section セクションが存在すること');
        $this->assertInstanceOf(WP_Customize_Section::class, $section);
        $this->assertEquals('Site Type Settings', $section->title);
    }

    /**
     * @test
     * カスタマイザーに integlight_hometype_setting のコントロールが追加されているか検証
     */
    public function customize_register_should_add_hometype_setting_control(): void
    {
        // カスタマイザーの登録処理を実行
        $this->instance->customize_register($this->wp_customize);

        // 設定の存在確認
        $setting = $this->wp_customize->get_setting('integlight_hometype_setting');
        $this->assertNotNull($setting, 'integlight_hometype_setting の設定が存在すること');
        $this->assertInstanceOf(WP_Customize_Setting::class, $setting);

        // コントロールの存在確認
        $control = $this->wp_customize->get_control('integlight_hometype_setting');
        $this->assertNotNull($control, 'integlight_hometype_setting のコントロールが存在すること');
        $this->assertInstanceOf(WP_Customize_Control::class, $control);

        // コントロールの基本情報を検証
        $this->assertEquals('Site Type Settings', $control->label);
        $this->assertEquals('radio', $control->type);
        $this->assertEquals(
            ['home1' => 'Elegant', 'home2' => 'Pop'],
            $control->choices
        );
    }
    /**
     * @test
     * integlight_hometype_setting の初期値が home1（Elegant）であることを検証
     */
    public function hometype_setting_should_have_default_value_home1(): void
    {
        // カスタマイザーの登録処理を実行
        $this->instance->customize_register($this->wp_customize);

        // 設定を取得
        $setting = $this->wp_customize->get_setting('integlight_hometype_setting');
        $this->assertNotNull($setting, 'integlight_hometype_setting の設定が存在すること');

        // 初期値が home1 であることを確認
        $this->assertEquals('home1', $setting->default, '初期値が home1 に設定されていること');

        // 念のため choices との整合性も確認（ラベルが Elegant であること）
        $control = $this->wp_customize->get_control('integlight_hometype_setting');
        $this->assertArrayHasKey('home1', $control->choices);
        $this->assertEquals('Elegant', $control->choices['home1']);
    }
    /**
     * @test
     * integlight_hometype_setting の保存動作を正常系・異常系含めて検証
     */
    public function hometype_setting_should_save_and_validate_values(): void
    {
        // カスタマイザー登録処理
        $this->instance->customize_register($this->wp_customize);

        $setting = $this->wp_customize->get_setting('integlight_hometype_setting');
        $this->assertNotNull($setting, 'integlight_hometype_setting の設定が存在すること');

        // ---- 正常系: home1 ----
        $sanitizedHome1 = call_user_func($setting->sanitize_callback, 'home1', $setting);
        $this->assertEquals('home1', $sanitizedHome1, 'home1 がそのまま保存されること');

        // ---- 正常系: home2 ----
        $sanitizedHome2 = call_user_func($setting->sanitize_callback, 'home2', $setting);
        $this->assertEquals('home2', $sanitizedHome2, 'home2 がそのまま保存されること');

        // ---- 異常系: 不正値 ----
        $invalidValue   = 'invalid_value';
        $sanitizedInvalid = call_user_func($setting->sanitize_callback, $invalidValue, $setting);
        $this->assertEquals(
            $setting->default,
            $sanitizedInvalid,
            '不正値はデフォルト値 ' . $setting->default . ' に戻ること'
        );
    }

    /**
     * @test
     * カスタマイザーで設定変更・保存してもエラーや警告が発生しないことを検証
     */
    public function customize_and_save_should_not_trigger_errors_or_warnings(): void
    {
        // カスタマイザー登録処理
        $this->instance->customize_register($this->wp_customize);

        $setting = $this->wp_customize->get_setting('integlight_hometype_setting');
        $this->assertNotNull($setting, '設定 integlight_hometype_setting が存在すること');

        // エラーハンドラをカスタムして PHP 警告・Notice をキャッチ
        $errors = [];
        set_error_handler(function ($errno, $errstr) use (&$errors) {
            $errors[] = ['type' => $errno, 'message' => $errstr];
            return true; // デフォルト処理をスキップ
        });

        try {
            // 有効な値 home1 を保存
            call_user_func($setting->sanitize_callback, 'home1', $setting);

            // 有効な値 home2 を保存
            call_user_func($setting->sanitize_callback, 'home2', $setting);

            // 無効な値を保存（デフォルトに戻る想定）
            call_user_func($setting->sanitize_callback, 'invalid_value', $setting);
        } finally {
            restore_error_handler();
        }

        // 警告やエラーが1つも発生していないことを検証
        $this->assertEmpty($errors, '設定変更・保存時にエラーや警告が発生しないこと');
    }
}
