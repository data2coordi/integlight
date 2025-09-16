<?php // tests/unit-tests/InteglightCustomizerSliderAssetsTest.php

declare(strict_types=1);

// テスト対象クラスと依存クラスを含むファイルを読み込む (オートロードされていない場合)
// require_once dirname(__DIR__, 2) . '/inc/integlight-customizer-slider.php';
// require_once dirname(__DIR__, 2) . '/inc/integlight-functions-outerAssets.php'; // 依存クラス

/**
 * integlight_customizer_slider_outerAssets クラスのユニットテスト
 * コンストラクタでのアセット登録と provideTOjs メソッドによるローカライズをテストします。
 *
 * @coversDefaultClass integlight_customizer_slider_outerAssets
 * @group customizer
 * @group slider
 * @group assets
 */
class integlight_customizer_slider_outerAssetsTest extends WP_UnitTestCase // クラス名を変更
{
    /**
     * テスト対象クラスのインスタンス
     * @var integlight_customizer_slider_outerAssets|null
     */
    private $instance = null;

    /**
     * テスト用のグローバル設定 (依存関係)
     * @var stdClass|null
     */
    private $mock_slider_settings = null;

    /**
     * テストで使用する theme_mod のキー
     * @var array
     */
    private $theme_mods_keys = [
        'integlight_display_choice',
        'integlight_slider_effect',
        'integlight_slider_change_duration',
    ];


	public function setUp(): void
	{
		parent::setUp();

		// WordPress のスクリプト/スタイルシステムをリセット
		$this->reset_wp_scripts_styles();

		// 依存クラスの静的プロパティをリセット
		$this->reset_static_property(InteglightFrontendStyles::class, 'styles');
		$this->reset_static_property(InteglightFrontendScripts::class, 'scripts');
		$this->reset_static_property(InteglightDeferJs::class, 'deferred_scripts');

		// テスト用のグローバル設定オブジェクトを作成
		$this->mock_slider_settings = new stdClass();
		$this->mock_slider_settings->effectName_fade = 'fade';
		$this->mock_slider_settings->effectName_slide = 'slide';
		$this->mock_slider_settings->headerTypeName_slider = 'slider';
		$this->mock_slider_settings->headerTypeName_image = 'image';
		$this->mock_slider_settings->homeType1Name = 'home1';
		$this->mock_slider_settings->homeType2Name = 'home2';
		$this->mock_slider_settings->homeType3Name = 'home3';
		$this->mock_slider_settings->homeType4Name = 'home4';

		// --- wpアクションテスト用の theme_mod をセット ---
		set_theme_mod('integlight_slider_image_1', 'dummy.jpg');
		set_theme_mod('integlight_slider_image_2', 'dummy.jpg');
		set_theme_mod('integlight_slider_image_3', 'dummy.jpg');
		set_theme_mod('integlight_display_choice', $this->mock_slider_settings->headerTypeName_slider);
		set_theme_mod('integlight_slider_effect', $this->mock_slider_settings->effectName_slide);
		set_theme_mod('integlight_slider_change_duration', '5');

		// --- is_front_page() をモック ---
		add_filter('pre_option_show_on_front', function() {
			return 'page';
		});

		// ※ wpアクション用にグローバル変数も用意
		global $Integlight_slider_settings;
		$Integlight_slider_settings = $this->mock_slider_settings;

		// コンストラクタ直接呼び出し用のインスタンスも作成（従来のテスト向け）
		$this->instance = new integlight_customizer_slider_outerAssets($this->mock_slider_settings);
		$this->assertTrue(true);

	}





    /**
     * 各テストの後に実行されるティアダウンメソッド
     */
    public function tearDown(): void
    {
        // コンストラクタで追加されたアクションフックを削除
        remove_action('wp_enqueue_scripts', [$this->instance, 'provideTOjs']);

        // テスト後に theme_mod をクリア
        foreach ($this->theme_mods_keys as $key) {
            remove_theme_mod($key);
        }

        // プロパティをクリア
        unset($this->instance);
        unset($this->mock_slider_settings);

        // WordPress のスクリプト/スタイルシステムを再度リセット
        $this->reset_wp_scripts_styles();

        // 依存クラスの静的プロパティをリセット
        $this->reset_static_property(InteglightFrontendStyles::class, 'styles');
        $this->reset_static_property(InteglightFrontendScripts::class, 'scripts');
        $this->reset_static_property(InteglightDeferJs::class, 'deferred_scripts');

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
            $reflection = new ReflectionProperty($className, $propertyName);
            // PHP 8.1 以降では setAccessible は不要な場合があるが、互換性のために残す
            if (method_exists($reflection, 'setAccessible')) {
                $reflection->setAccessible(true);
            }
            $reflection->setValue(null, $defaultValue); // 静的プロパティをリセット
        } catch (ReflectionException $e) {
            // プロパティが存在しないなどのエラー処理 (必要に応じて)
            $this->fail("Failed to reset static property {$className}::{$propertyName}: " . $e->getMessage());
        }
    }

    /**
     * Reflection を使用して静的プロパティの値を取得するヘルパーメソッド
     */
    private function get_static_property_value(string $className, string $propertyName)
    {
        try {
            $reflectionClass = new ReflectionClass($className);
            $property = $reflectionClass->getProperty($propertyName);
            // PHP 8.1 以降では setAccessible は不要な場合があるが、互換性のために残す
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
     * コンストラクタがフックを追加し、関連クラスにアセットを登録するかテスト
     */
    public function test_constructor_adds_hooks_and_assets(): void
    {
        // Assert: フックが登録されているか
        // setUp でインスタンスが作成される際にコンストラクタが実行されている
        $this->assertGreaterThan(0, has_action('wp_enqueue_scripts', [$this->instance, 'provideTOjs']), 'Action hook wp_enqueue_scripts for provideTOjs should be added.');
        $this->assertEquals(10, has_action('wp_enqueue_scripts', [$this->instance, 'provideTOjs']), 'Action hook wp_enqueue_scripts for provideTOjs should have default priority 10.');

        // Assert: InteglightFrontendStyles にスタイルが追加されたか
        $styles = $this->get_static_property_value(InteglightFrontendStyles::class, 'styles');
        $this->assertIsArray($styles, 'InteglightFrontendStyles::$styles should be an array.');
        $this->assertArrayHasKey('integlight-slide', $styles, 'Style "integlight-slide" should be added to InteglightFrontendStyles.');
        $this->assertEquals('/css/build/integlight-slide-style.css', $styles['integlight-slide']['path'], 'Path for "integlight-slide" style should be correct.');

        // Assert: InteglightFrontendScripts にスクリプトが追加されたか
        $scripts = $this->get_static_property_value(InteglightFrontendScripts::class, 'scripts');
        $this->assertIsArray($scripts, 'InteglightFrontendScripts::$scripts should be an array.');
        $this->assertArrayHasKey('integlight_slider-script', $scripts, 'Script "integlight_slider-script" should be added to InteglightFrontendScripts.');
        $this->assertEquals('/js/build/slider.js', $scripts['integlight_slider-script']['path'], 'Path for "integlight_slider-script" should be correct.');
        // $this->assertContains('jquery', $scripts['integlight_slider-script']['deps'], 'Dependency "jquery" for "integlight_slider-script" should be set.'); jqueryは使わないようにリファクタリング済

        // Assert: InteglightDeferJs に遅延スクリプトが追加されたか
        $deferredScripts = $this->get_static_property_value(InteglightDeferJs::class, 'deferred_scripts');
        $this->assertIsArray($deferredScripts, 'InteglightDeferJs::$deferred_scripts should be an array.');
        $this->assertContains('integlight_slider-script', $deferredScripts, 'Script "integlight_slider-script" should be added to InteglightDeferJs for deferring.');
    }


    /**
     * @test
     * @covers ::provideTOjs
     * ヘッダータイプが 'slider' の場合にスクリプトが正しくローカライズされるかテスト
     */
    public function test_provideTOjs_localizes_correctly_when_slider(): void // メソッド名を変更
    {
        // Arrange: ヘッダータイプと他の設定を設定
        set_theme_mod('integlight_display_choice', $this->mock_slider_settings->headerTypeName_slider); // 'slider'
        set_theme_mod('integlight_slider_effect', $this->mock_slider_settings->effectName_slide); // 'slide'
        set_theme_mod('integlight_slider_change_duration', '5'); // 5秒

        // Act: wp_enqueue_scripts アクションを実行
        // このアクションにより、コンストラクタで登録された provideTOjs が実行される
        // また、InteglightFrontendScripts::enqueue_frontend_scripts も実行され、
        // 'integlight_slider-script' がエンキューされる（ローカライズの前提条件）
        do_action('wp_enqueue_scripts');

        // Assert: ローカライズされたデータを取得して検証
        $scripts = wp_scripts();
        // スクリプトがエンキューされていることを確認 (ローカライズの前提)
        $this->assertTrue(wp_script_is('integlight_slider-script', 'enqueued'), 'Script integlight_slider-script should be enqueued before localization.');
        // 登録も確認
        $this->assertTrue(wp_script_is('integlight_slider-script', 'registered'), 'Script integlight_slider-script should be registered.');

        $localized_data_string = $scripts->get_data('integlight_slider-script', 'data');

        // 1. get_data が false を返さないことを確認
        $this->assertNotFalse($localized_data_string, 'Failed to get localized data. wp_localize_script might have failed or script not enqueued/registered correctly.');
        // 2. ローカライズデータが空でない文字列であることを確認
        $this->assertIsString($localized_data_string, 'Localized data should be a string.');
        $this->assertNotEmpty($localized_data_string, 'Localized data string should not be empty.');

        // --- 正規表現を使わないJSON抽出 (修正版) ---
        $startPos = strpos($localized_data_string, '{'); // 最初の '{' の位置
        if ($startPos === false) {
            $this->fail('Could not find the starting "{" in localized data string: [' . $localized_data_string . ']');
        }

        // 最初の '{' 以降で、最初の '};' を探す
        $endMarker = '};';
        $endMarkerPos = strpos($localized_data_string, $endMarker, $startPos);

        if ($endMarkerPos === false) {
            // '};' が見つからない場合のエラー処理 (必要に応じて)
            // もし ';' がない形式もありうるなら、最後の '}' を探すフォールバックも検討
            $this->fail('Could not find the ending marker "};" after "{" in localized data string: [' . $localized_data_string . ']');
        }

        // '{' から '};' の '}' までを抽出
        // $endMarkerPos は ';' の位置なので、その1つ前までが '}'
        $json_string = substr($localized_data_string, $startPos, $endMarkerPos - $startPos + 1);
        // --- JSON抽出ここまで ---

        // 4. JSON デコードを実行
        $decoded_data = json_decode($json_string, true);

        // 5. デコードが成功したか確認
        $this->assertNotNull($decoded_data, 'json_decode failed. JSON string might be invalid. String attempted to decode: [' . $json_string . '] | Original localized string: [' . $localized_data_string . ']');
        // 6. 配列であることを確認
        $this->assertIsArray($decoded_data, 'Decoded localized data should be an array.');


        // 各キーと値を確認 (ここは変更なし)

        $this->assertArrayHasKey('changeDuration', $decoded_data, 'Localized data should have "changeDuration" key.');
        $this->assertEquals('5', $decoded_data['changeDuration'], 'Localized changeDuration should be "5".');

        $this->assertArrayHasKey('effect', $decoded_data, 'Localized data should have "effect" key.');
        $this->assertEquals('slide', $decoded_data['effect'], 'Localized effect should be "slide".');

        $this->assertArrayHasKey('fadeName', $decoded_data, 'Localized data should have "fade" key.');
        $this->assertEquals($this->mock_slider_settings->effectName_fade, $decoded_data['fadeName']);

        $this->assertArrayHasKey('slideName', $decoded_data, 'Localized data should have "slide" key.');
        $this->assertEquals($this->mock_slider_settings->effectName_slide, $decoded_data['slideName']);

        $this->assertArrayHasKey('headerTypeNameSlider', $decoded_data, 'Localized data should have "headerTypeNameSlider" key.');
        $this->assertEquals($this->mock_slider_settings->headerTypeName_slider, $decoded_data['headerTypeNameSlider']);
    }

	/**
	 * @test
	 * add_action('wp', ...) 内の条件を通して
	 * インスタンス生成およびスクリプト登録が行われるかテスト
	 */













	public function test_wp_action_not_creates_instance_and_registers_scripts(): void
	{
		// --- Arrange ---

		// フロントページを true にモック
		update_option('show_on_front', 'posts'); // これだけだと false の場合もあるので
		$this->mockIsFrontPage(true);

		global $Integlight_slider_settings;
		$Integlight_slider_settings = $this->mock_slider_settings;
		
		$this->reset_static_property(InteglightFrontendScripts::class, 'scripts');


		// いずれかのスライダー画像をセット

		// --- Act ---
		do_action('wp');

		// --- Assert ---

		// スクリプトが静的プロパティに登録されているか確認
		$scripts = $this->get_static_property_value(InteglightFrontendScripts::class, 'scripts');
		$this->assertArrayNotHasKey('integlight_slider-script', $scripts, 'Slider script should be registered via wp action.');

	}
	/**
	 * ヘルパー: is_front_page() をモック
	 */
	private function mockIsFrontPage(bool $returnValue): void
	{
		// WordPress の条件関数は filter で上書き可能
		add_filter('pre_option_show_on_front', function() use ($returnValue) {
			return $returnValue ? 'page' : 'posts';
		});
	}



}
