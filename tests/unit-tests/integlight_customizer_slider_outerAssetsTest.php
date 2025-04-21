<?php // tests/unit-tests/InteglightCustomizerSliderAssetsTest.php

declare(strict_types=1);

// テスト対象クラスと依存クラスを含むファイルを読み込む
// テスト対象クラスは setUp でインスタンス化される際に読み込まれるはずだが、
// 明示的に読み込む場合は以下を追加
// require_once dirname(__DIR__, 2) . '/inc/integlight-customizer-slider.php';

/**
 * integlight_customizer_slider_outerAssets クラスのユニットテスト
 * 主に provideTOjs メソッドによるスクリプトローカライズをテストします。
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

    /**
     * 各テストの前に実行されるセットアップメソッド
     */
    public function setUp(): void
    {
        parent::setUp();

        // テスト用のグローバル設定オブジェクトを作成
        $this->mock_slider_settings = new stdClass();
        $this->mock_slider_settings->effectName_fade = 'fade';
        $this->mock_slider_settings->effectName_slide = 'slide';
        $this->mock_slider_settings->headerTypeName_slider = 'slider';
        $this->mock_slider_settings->headerTypeName_image = 'image';

        // テスト対象クラスのインスタンスを作成
        $this->instance = new integlight_customizer_slider_outerAssets($this->mock_slider_settings);

        // テスト前に theme_mod をクリア
        foreach ($this->theme_mods_keys as $key) {
            remove_theme_mod($key);
        }

        // WordPress のスクリプト/スタイルシステムをリセット
        wp_scripts()->registered = [];
        wp_scripts()->queue = [];
        wp_scripts()->done = [];
        wp_scripts()->print_html = '';
        wp_scripts()->print_code = '';
        wp_scripts()->args = [];
        wp_scripts()->concat = '';
        wp_scripts()->concat_version = '';
        wp_scripts()->do_concat = false;
        wp_scripts()->default_dirs = [];

        wp_styles()->registered = [];
        wp_styles()->queue = [];
        wp_styles()->done = [];
        wp_styles()->print_html = '';
        wp_styles()->print_code = '';
        wp_styles()->args = [];
        wp_styles()->concat = '';
        wp_styles()->concat_version = '';
        wp_styles()->do_concat = false;
        wp_styles()->default_dirs = [];
    }

    /**
     * 各テストの後に実行されるティアダウンメソッド
     */
    public function tearDown(): void
    {
        // コンストラクタで追加されたアクションフックを削除
        remove_action('wp_enqueue_scripts', [$this->instance, 'provideTOjs']); // 正しいメソッド名に修正

        // テスト後に theme_mod をクリア
        foreach ($this->theme_mods_keys as $key) {
            remove_theme_mod($key);
        }

        // プロパティをクリア
        unset($this->instance);
        unset($this->mock_slider_settings);

        // WordPress のスクリプト/スタイルシステムを再度リセット (念のため)
        wp_scripts()->registered = [];
        wp_scripts()->queue = [];
        wp_scripts()->done = [];
        wp_styles()->registered = [];
        wp_styles()->queue = [];
        wp_styles()->done = [];


        parent::tearDown();
    }

    /**
     * @test
     * @covers ::__construct
     * コンストラクタが wp_enqueue_scripts アクションフック (provideTOjs) を正しく登録するかテスト
     */
    public function test_constructor_adds_provideTOjs_action(): void // メソッド名を変更
    {
        // setUp でインスタンスが作成され、コンストラクタが実行されている前提
        $hook_priority = has_action('wp_enqueue_scripts', [$this->instance, 'provideTOjs']); // 正しいメソッド名

        $this->assertNotFalse(
            $hook_priority,
            'Constructor should add the provideTOjs method to the wp_enqueue_scripts action.' // メッセージを修正
        );
        // provideTOjs のデフォルト優先度 (10) を確認
        $this->assertEquals(10, $hook_priority, 'The hook priority for provideTOjs should be the default (10).');
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
        do_action('wp_enqueue_scripts');

        // Assert: ローカライズされたデータを取得して検証
        $scripts = wp_scripts();
        $this->assertTrue(wp_script_is('integlight_slider-script', 'registered'), 'Script integlight_slider-script should be registered.');

        $localized_data_string = $scripts->get_data('integlight_slider-script', 'data');

        // 1. get_data が false を返さないことを確認
        $this->assertNotFalse($localized_data_string, 'Failed to get localized data. wp_localize_script might have failed or script not enqueued/registered correctly.');
        // 2. ローカライズデータが空でない文字列であることを確認
        $this->assertIsString($localized_data_string, 'Localized data should be a string.');
        $this->assertNotEmpty($localized_data_string, 'Localized data string should not be empty.');

        // 3. JSON 文字列部分を抽出 (さらに修正)
        $json_string = null; // 初期化
        if (preg_match('/var\s+integlight_sliderSettings\s*=\s*(\{.*?\})\s*;?\s*$/s', trim($localized_data_string), $matches)) {
            $json_string = $matches[1];
        } else {
            $temp_string = preg_replace('/^var\s+integlight_sliderSettings\s*=\s*/', '', trim($localized_data_string));
            $json_string = preg_replace('/;\s*$/', '', $temp_string);
        }
        $this->assertNotNull($json_string, 'Failed to extract JSON part from localized data string: [' . $localized_data_string . ']');


        // 4. JSON デコードを実行
        $decoded_data = json_decode($json_string, true);

        // 5. デコードが成功したか確認 (デバッグメッセージ修正)
        $this->assertNotNull($decoded_data, 'json_decode failed. JSON string might be invalid. String attempted to decode: [' . $json_string . '] | Original localized string: [' . $localized_data_string . ']');
        // 6. 配列であることを確認
        $this->assertIsArray($decoded_data, 'Decoded localized data should be an array.');


        // 各キーと値を確認
        $this->assertArrayHasKey('displayChoice', $decoded_data, 'Localized data should have "displayChoice" key.');
        $this->assertEquals('slider', $decoded_data['displayChoice'], 'Localized displayChoice should be "slider".');

        $this->assertArrayHasKey('changeDuration', $decoded_data, 'Localized data should have "changeDuration" key.');
        $this->assertEquals('5', $decoded_data['changeDuration'], 'Localized changeDuration should be "5".');

        $this->assertArrayHasKey('effect', $decoded_data, 'Localized data should have "effect" key.');
        $this->assertEquals('slide', $decoded_data['effect'], 'Localized effect should be "slide".');

        $this->assertArrayHasKey('fade', $decoded_data, 'Localized data should have "fade" key.');
        $this->assertEquals($this->mock_slider_settings->effectName_fade, $decoded_data['fade']);

        $this->assertArrayHasKey('slide', $decoded_data, 'Localized data should have "slide" key.');
        $this->assertEquals($this->mock_slider_settings->effectName_slide, $decoded_data['slide']);

        $this->assertArrayHasKey('headerTypeNameSlider', $decoded_data, 'Localized data should have "headerTypeNameSlider" key.');
        $this->assertEquals($this->mock_slider_settings->headerTypeName_slider, $decoded_data['headerTypeNameSlider']);
    }


    /**
     * @test
     * @covers ::provideTOjs
     * ヘッダータイプが 'image' の場合にスクリプトが正しくローカライズされるかテスト
     */
    public function test_provideTOjs_localizes_correctly_when_image(): void // 新しいテストメソッド
    {
        // Arrange: ヘッダータイプを 'image' に設定、他はデフォルト
        set_theme_mod('integlight_display_choice', $this->mock_slider_settings->headerTypeName_image); // 'image'

        // Act: wp_enqueue_scripts アクションを実行
        do_action('wp_enqueue_scripts');

        // Assert: ローカライズされたデータを取得して検証
        $scripts = wp_scripts();
        $this->assertTrue(wp_script_is('integlight_slider-script', 'registered'), 'Script integlight_slider-script should be registered.');
        $localized_data_string = $scripts->get_data('integlight_slider-script', 'data');

        // 1. get_data が false を返さないことを確認
        $this->assertNotFalse($localized_data_string, 'Failed to get localized data. wp_localize_script might have failed or script not enqueued/registered correctly.');
        // 2. ローカライズデータが空でない文字列であることを確認
        $this->assertIsString($localized_data_string, 'Localized data should be a string.');
        $this->assertNotEmpty($localized_data_string, 'Localized data string should not be empty.');

        // 3. JSON 文字列部分を抽出 (さらに修正)
        $json_string = null; // 初期化
        if (preg_match('/var\s+integlight_sliderSettings\s*=\s*(\{.*?\})\s*;?\s*$/s', trim($localized_data_string), $matches)) {
            $json_string = $matches[1];
        } else {
            $temp_string = preg_replace('/^var\s+integlight_sliderSettings\s*=\s*/', '', trim($localized_data_string));
            $json_string = preg_replace('/;\s*$/', '', $temp_string);
        }
        $this->assertNotNull($json_string, 'Failed to extract JSON part from localized data string: [' . $localized_data_string . ']');


        // 4. JSON デコードを実行
        $decoded_data = json_decode($json_string, true);

        // 5. デコードが成功したか確認 (デバッグメッセージ修正)
        $this->assertNotNull($decoded_data, 'json_decode failed. JSON string might be invalid. String attempted to decode: [' . $json_string . '] | Original localized string: [' . $localized_data_string . ']');
        // 6. 配列であることを確認
        $this->assertIsArray($decoded_data, 'Decoded localized data should be an array.');


        // displayChoice が 'image' であることを確認
        $this->assertArrayHasKey('displayChoice', $decoded_data);
        $this->assertEquals('image', $decoded_data['displayChoice'], 'Localized displayChoice should be "image".');

        // 他の値がデフォルト値で渡されていることを確認 (例: effect)
        $this->assertArrayHasKey('effect', $decoded_data);
        $this->assertEquals($this->mock_slider_settings->effectName_fade, $decoded_data['effect'], 'Localized effect should be the default ("fade").');

        $this->assertArrayHasKey('changeDuration', $decoded_data);
        $this->assertEquals('3', $decoded_data['changeDuration'], 'Localized changeDuration should be the default ("3").');
    }

    /**
     * @test
     * @covers ::provideTOjs
     * theme_mod が未設定の場合にデフォルト値でローカライズされるかテスト
     */
    public function test_provideTOjs_localizes_correctly_with_defaults(): void // 新しいテストメソッド
    {
        // Arrange: theme_mod は setUp でクリア済み

        // Act: wp_enqueue_scripts アクションを実行
        do_action('wp_enqueue_scripts');

        // Assert: ローカライズされたデータを取得して検証
        $scripts = wp_scripts();
        $this->assertTrue(wp_script_is('integlight_slider-script', 'registered'), 'Script integlight_slider-script should be registered.');
        $localized_data_string = $scripts->get_data('integlight_slider-script', 'data');

        // 1. get_data が false を返さないことを確認
        $this->assertNotFalse($localized_data_string, 'Failed to get localized data. wp_localize_script might have failed or script not enqueued/registered correctly.');
        // 2. ローカライズデータが空でない文字列であることを確認
        $this->assertIsString($localized_data_string, 'Localized data should be a string.');
        $this->assertNotEmpty($localized_data_string, 'Localized data string should not be empty.');

        // 3. JSON 文字列部分を抽出 (さらに修正)
        $json_string = null; // 初期化
        if (preg_match('/var\s+integlight_sliderSettings\s*=\s*(\{.*?\})\s*;?\s*$/s', trim($localized_data_string), $matches)) {
            $json_string = $matches[1];
        } else {
            $temp_string = preg_replace('/^var\s+integlight_sliderSettings\s*=\s*/', '', trim($localized_data_string));
            $json_string = preg_replace('/;\s*$/', '', $temp_string);
        }
        $this->assertNotNull($json_string, 'Failed to extract JSON part from localized data string: [' . $localized_data_string . ']');


        // 4. JSON デコードを実行
        $decoded_data = json_decode($json_string, true);

        // 5. デコードが成功したか確認 (デバッグメッセージ修正)
        $this->assertNotNull($decoded_data, 'json_decode failed. JSON string might be invalid. String attempted to decode: [' . $json_string . '] | Original localized string: [' . $localized_data_string . ']');
        // 6. 配列であることを確認
        $this->assertIsArray($decoded_data, 'Decoded localized data should be an array.');


        // 各値がデフォルト値であることを確認
        $this->assertArrayHasKey('displayChoice', $decoded_data);
        // get_theme_mod('integlight_display_choice') はデフォルト値が設定されていないため null を返すはず
        $this->assertNull($decoded_data['displayChoice'], 'Localized displayChoice should be null when not set.');

        $this->assertArrayHasKey('changeDuration', $decoded_data);
        $this->assertEquals('3', $decoded_data['changeDuration'], 'Localized changeDuration should be the default ("3").');

        $this->assertArrayHasKey('effect', $decoded_data);
        $this->assertEquals($this->mock_slider_settings->effectName_fade, $decoded_data['effect'], 'Localized effect should be the default ("fade").');
    }

    /**
     * @test
     * @covers ::provideTOjs
     * スライダーエフェクトが 'fade' の場合に正しいデータがローカライズされるかテスト
     */
    public function test_provideTOjs_localizes_fade_effect_correctly(): void // メソッド名を変更
    {
        // Arrange: ヘッダータイプとエフェクトを設定
        set_theme_mod('integlight_display_choice', $this->mock_slider_settings->headerTypeName_slider);
        set_theme_mod('integlight_slider_effect', $this->mock_slider_settings->effectName_fade); // 'fade'

        // Act: wp_enqueue_scripts アクションを実行
        do_action('wp_enqueue_scripts');

        // Assert: ローカライズされたデータを取得して検証
        $scripts = wp_scripts();
        $this->assertTrue(wp_script_is('integlight_slider-script', 'registered'), 'Script integlight_slider-script should be registered.');
        $localized_data_string = $scripts->get_data('integlight_slider-script', 'data');

        // 1. get_data が false を返さないことを確認
        $this->assertNotFalse($localized_data_string, 'Failed to get localized data. wp_localize_script might have failed or script not enqueued/registered correctly.');
        // 2. ローカライズデータが空でない文字列であることを確認
        $this->assertIsString($localized_data_string, 'Localized data should be a string.');
        $this->assertNotEmpty($localized_data_string, 'Localized data string should not be empty.');

        // 3. JSON 文字列部分を抽出 (さらに修正)
        $json_string = null; // 初期化
        if (preg_match('/var\s+integlight_sliderSettings\s*=\s*(\{.*?\})\s*;?\s*$/s', trim($localized_data_string), $matches)) {
            $json_string = $matches[1];
        } else {
            $temp_string = preg_replace('/^var\s+integlight_sliderSettings\s*=\s*/', '', trim($localized_data_string));
            $json_string = preg_replace('/;\s*$/', '', $temp_string);
        }
        $this->assertNotNull($json_string, 'Failed to extract JSON part from localized data string: [' . $localized_data_string . ']');


        // 4. JSON デコードを実行
        $decoded_data = json_decode($json_string, true);

        // 5. デコードが成功したか確認 (デバッグメッセージ修正)
        $this->assertNotNull($decoded_data, 'json_decode failed. JSON string might be invalid. String attempted to decode: [' . $json_string . '] | Original localized string: [' . $localized_data_string . ']');
        // 6. 配列であることを確認
        $this->assertIsArray($decoded_data, 'Decoded localized data should be an array.');


        $this->assertArrayHasKey('effect', $decoded_data);
        $this->assertEquals('fade', $decoded_data['effect'], 'Localized effect should be "fade".');
    }

    /**
     * @test
     * @covers ::provideTOjs
     * スライダーエフェクトが 'slide' の場合に正しいデータがローカライズされるかテスト
     */
    public function test_provideTOjs_localizes_slide_effect_correctly(): void // メソッド名を変更
    {
        // Arrange: ヘッダータイプとエフェクトを設定
        set_theme_mod('integlight_display_choice', $this->mock_slider_settings->headerTypeName_slider);
        set_theme_mod('integlight_slider_effect', $this->mock_slider_settings->effectName_slide); // 'slide'

        // Act: wp_enqueue_scripts アクションを実行
        do_action('wp_enqueue_scripts');

        // Assert: ローカライズされたデータを取得して検証
        $scripts = wp_scripts();
        $this->assertTrue(wp_script_is('integlight_slider-script', 'registered'), 'Script integlight_slider-script should be registered.');
        $localized_data_string = $scripts->get_data('integlight_slider-script', 'data'); // 正しいハンドル名

        // 1. get_data が false を返さないことを確認
        $this->assertNotFalse($localized_data_string, 'Failed to get localized data. wp_localize_script might have failed or script not enqueued/registered correctly.');
        // 2. ローカライズデータが空でない文字列であることを確認
        $this->assertIsString($localized_data_string, 'Localized data should be a string.');
        $this->assertNotEmpty($localized_data_string, 'Localized data string should not be empty.');

        // 3. JSON 文字列部分を抽出 (さらに修正)
        $json_string = null; // 初期化
        if (preg_match('/var\s+integlight_sliderSettings\s*=\s*(\{.*?\})\s*;?\s*$/s', trim($localized_data_string), $matches)) {
            $json_string = $matches[1];
        } else {
            $temp_string = preg_replace('/^var\s+integlight_sliderSettings\s*=\s*/', '', trim($localized_data_string));
            $json_string = preg_replace('/;\s*$/', '', $temp_string);
        }
        $this->assertNotNull($json_string, 'Failed to extract JSON part from localized data string: [' . $localized_data_string . ']');


        // 4. JSON デコードを実行
        $decoded_data = json_decode($json_string, true); // 正しいオブジェクト名

        // 5. デコードが成功したか確認 (デバッグメッセージ修正)
        $this->assertNotNull($decoded_data, 'json_decode failed. JSON string might be invalid. String attempted to decode: [' . $json_string . '] | Original localized string: [' . $localized_data_string . ']');
        // 6. 配列であることを確認
        $this->assertIsArray($decoded_data, 'Decoded localized data should be an array.');


        $this->assertArrayHasKey('effect', $decoded_data, 'Localized data should have "effect" key.');
        $this->assertEquals('slide', $decoded_data['effect'], 'Localized effect should be "slide".');
    }
}
