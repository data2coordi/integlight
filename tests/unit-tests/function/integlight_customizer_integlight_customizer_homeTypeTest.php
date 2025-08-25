<?php
class integlight_customizer_integlight_customizer_homeTypeTest extends WP_UnitTestCase
{
    private $wp_customize;
    private $instance;
    private $setting_id = 'integlight_hometype_setting';
    private $homeTypeLoader;

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



    /*フロント部分*/
    /*フロント部分*/
    /*フロント部分*/
    /*フロント部分*/
    /*
1	コンストラクタがフックを登録しているか	wp_enqueue_scripts アクションに enqueue_hometype_css メソッドが登録されていることを確認
2	enqueue_hometype_css が正しく動作するか	get_theme_mod('integlight_hometype_setting') の値をもとにCSSパスが組み立てられ、3つのスタイル管理クラスに正しく渡されているか検証
3	テーマ設定が未設定の場合のデフォルトCSS適用	get_theme_mod が値を返さない場合に 'home1' を使ってCSSが組み立てられていること
4	複数の設定値に対して正しくパスが生成されるか	例えば 'home1'、'home2' の場合それぞれ /css/build/home1.css、/css/build/home2.css になること

*/

    /**
     * @test
     * @covers ::__construct
     */
    public function homeTypeLoader_constructor_should_add_wp_enqueue_scripts_hook(): void
    {
        $hooked = has_action('wp_enqueue_scripts', [$this->homeTypeLoader, 'enqueue_hometype_css']);
        $this->assertNotFalse($hooked, 'enqueue_hometype_css should be hooked to wp_enqueue_scripts');
        $this->assertEquals(10, $hooked, 'Default hook priority should be 10');
    }

    /**
     * @test
     * @covers InteglightHomeTypeLoader::enqueue_hometype_css
     */
    public function enqueue_hometype_css_should_pass_correct_css_path_to_style_managers(): void
    {
        // テスト用にテーマモッドの値を設定
        set_theme_mod('integlight_hometype_setting', 'home2');

        // enqueue_hometype_cssを呼び出す
        $this->homeTypeLoader->enqueue_hometype_css();

        // スタイルパスの期待値
        $expected_css_path = '/css/build/home2.css';
        $expected_styles = ['home-type' => $expected_css_path];

        // InteglightFrontendStyles::add_styles が正しいパスで呼ばれているか検証
        $frontend_styles = $this->get_static_property_value(InteglightFrontendStyles::class, 'styles');
        $this->assertArrayHasKey('home-type', $frontend_styles);
        $this->assertSame($expected_css_path, $frontend_styles['home-type']);

        // InteglightEditorStyles::add_styles が正しいパスで呼ばれているか検証
        $editor_styles = $this->get_static_property_value(InteglightEditorStyles::class, 'styles');
        $this->assertArrayHasKey('home-type', $editor_styles);
        $this->assertSame($expected_css_path, $editor_styles['home-type']);

        // InteglightDeferCss::add_deferred_styles に 'home-type' が追加されているか検証
        //$deferred_styles = $this->get_static_property_value(InteglightDeferCss::class, 'deferred_styles');
        //$this->assertContains('home-type', $deferred_styles);
    }
    /**
     * @test
     * @covers InteglightHomeTypeLoader::enqueue_hometype_css
     */
    public function enqueue_hometype_css_should_use_default_home1_if_theme_mod_not_set(): void
    {
        // テーマ設定をクリア（未設定状態にする）
        remove_theme_mod('integlight_hometype_setting');

        // enqueue_hometype_cssを呼び出す
        $this->homeTypeLoader->enqueue_hometype_css();

        // 期待されるデフォルトCSSパス
        $expected_css_path = '/css/build/home1.css';
        $expected_styles = ['home-type' => $expected_css_path];

        // InteglightFrontendStyles::add_styles に正しくパスがセットされているか
        $frontend_styles = $this->get_static_property_value(InteglightFrontendStyles::class, 'styles');
        $this->assertArrayHasKey('home-type', $frontend_styles);
        $this->assertSame($expected_css_path, $frontend_styles['home-type']);

        // InteglightEditorStyles::add_styles に正しくパスがセットされているか
        $editor_styles = $this->get_static_property_value(InteglightEditorStyles::class, 'styles');
        $this->assertArrayHasKey('home-type', $editor_styles);
        $this->assertSame($expected_css_path, $editor_styles['home-type']);

        // InteglightDeferCss::add_deferred_styles に 'home-type' が含まれているか
        //$deferred_styles = $this->get_static_property_value(InteglightDeferCss::class, 'deferred_styles');
        //$this->assertContains('home-type', $deferred_styles);
    }
    /**
     * @test
     * @covers InteglightHomeTypeLoader::enqueue_hometype_css
     */
    public function enqueue_hometype_css_should_generate_correct_path_for_each_setting(): void
    {
        $test_values = [
            'home1' => '/css/build/home1.css',
            'home2' => '/css/build/home2.css',
            // 他のパターンがあれば追加可能
        ];

        foreach ($test_values as $setting_value => $expected_path) {
            // 設定値をセット
            set_theme_mod('integlight_hometype_setting', $setting_value);

            // スタイル管理クラスの静的プロパティを初期化（リセット）
            $this->reset_static_property(InteglightFrontendStyles::class, 'styles');
            $this->reset_static_property(InteglightEditorStyles::class, 'styles');
            $this->reset_static_property(InteglightDeferCss::class, 'deferred_styles');

            // enqueue_hometype_cssを実行
            $this->homeTypeLoader->enqueue_hometype_css();

            // フロントエンド用スタイルを取得し検証
            $frontend_styles = $this->get_static_property_value(InteglightFrontendStyles::class, 'styles');
            $this->assertArrayHasKey('home-type', $frontend_styles, "FrontendStyles should have 'home-type' for {$setting_value}");
            $this->assertSame($expected_path, $frontend_styles['home-type'], "FrontendStyles path should be correct for {$setting_value}");

            // エディタ用スタイルを取得し検証
            $editor_styles = $this->get_static_property_value(InteglightEditorStyles::class, 'styles');
            $this->assertArrayHasKey('home-type', $editor_styles, "EditorStyles should have 'home-type' for {$setting_value}");
            $this->assertSame($expected_path, $editor_styles['home-type'], "EditorStyles path should be correct for {$setting_value}");
        }
    }
}
