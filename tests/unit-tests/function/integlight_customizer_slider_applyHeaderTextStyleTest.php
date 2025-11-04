<?php // tests/unit-tests/integlight_customizer_slider_applyHeaderTextStyleTest.php
declare(strict_types=1);


/**
 * integlight_customizer_slider_headerTextStyle クラスのユニットテスト
 *
 * @coversDefaultClass integlight_customizer_slider_headerTextStyle
 * @group customizer
 * @group slider
 * @group styles
 */
class integlight_customizer_slider_applyHeaderTextStyleTest extends WP_UnitTestCase
{
    /**
     * テスト対象クラスのインスタンス
     * @var integlight_customizer_slider_headerTextStyle|null
     */
    private $instance = null;

    /**
     * テストで使用する theme_mod のキー
     * @var array
     */
    private $theme_mods_keys = [
        'integlight_slider_text_color',
        'integlight_slider_text_font',
        'integlight_slider_text_top',
        'integlight_slider_text_left',
        'integlight_slider_text_top_mobile',
        'integlight_slider_text_left_mobile',
    ];

    /**
     * 各テストの前に実行されるセットアップメソッド
     */
    public function setUp(): void
    {
        parent::setUp();
        // テスト対象クラスのインスタンスを作成
        // コンストラクタがフックを追加する
        $this->instance = new integlight_customizer_slider_headerTextStyle();

        // テスト前に theme_mod をクリア
        foreach ($this->theme_mods_keys as $key) {
            remove_theme_mod($key);
        }
    }

    /**
     * 各テストの後に実行されるティアダウンメソッド
     */
    public function tearDown(): void
    {
        // コンストラクタで追加されたアクションフックを削除
        remove_action('wp_head', [$this->instance, 'ApplyTextStyles']);

        // テスト後に theme_mod をクリア
        foreach ($this->theme_mods_keys as $key) {
            remove_theme_mod($key);
        }
        unset($this->instance);
        parent::tearDown();
    }

    /**
     * @test
     * @covers ::__construct
     * コンストラクタが wp_head アクションフックを正しく登録するかテスト
     */
    public function constructor_should_add_wp_head_action(): void
    {
        // setUp でインスタンスが作成され、コンストラクタが実行されている前提
        $hook_priority = has_action('wp_head', [$this->instance, 'ApplyTextStyles']);

        $this->assertNotFalse(
            $hook_priority,
            // アサーションメッセージ
            'Constructor should add the ApplyTextStyles method to the wp_head action.'
        );
        // デフォルトの優先度 (10) であることを確認 (もし異なる場合は修正)
        $this->assertEquals(10, $hook_priority, 'The hook priority should be the default (10).');
    }

    /**
     * @test
     * @covers ::ApplyTextStyles
     * デフォルト値の場合に正しいデフォルトCSSが出力されることをテスト
     */
    // メソッド名を変更して意図を明確にする
    public function ApplyTextStyles_should_output_correct_default_css(): void
    {
        // Arrange: theme_mod がクリアされている状態 (setUp で実行済み)

        // Act: アクションを実行して出力をキャプチャ
        ob_start();
        $this->instance->ApplyTextStyles();
        $output = ob_get_clean();

        // Assert: 出力に style タグとデフォルトのCSSルールが含まれているか確認
        $this->assertStringContainsString('<style>', $output, 'Output should contain the opening style tag.');
        $this->assertStringContainsString('</style>', $output, 'Output should contain the closing style tag.');

        // デフォルトのPCスタイルを確認 (integlight-customizer-slider.php のデフォルト値に基づく)
        $this->assertStringContainsString('.slider .text-overlay {', $output, 'Should contain PC selector.');
        $this->assertStringContainsString('color: #ffffff;', $output, 'Should contain default text color (#ffffff).');
        // デフォルトフォント (yu_gothic) の確認 (integlight-customizer-slider.php の switch 文に基づく)
        $this->assertStringContainsString('font-family: Yu Gothic, 游ゴシック体, sans-serif;', $output, 'Should contain default font family (Yu Gothic).');
        $this->assertStringContainsString('top: 300px;', $output, 'Should contain default top position (300px).');
        $this->assertStringContainsString('left: 30px;', $output, 'Should contain default left position (30px).');

        // デフォルトのモバイルスタイルを確認 (integlight-customizer-slider.php のデフォルト値に基づく)
        $this->assertStringContainsString('@media only screen and (max-width: 767px)', $output, 'Should contain mobile media query.');
        $this->assertStringContainsString('top: 200px;', $output, 'Should contain default mobile top position (200px) inside media query.');
        $this->assertStringContainsString('left: 20px;', $output, 'Should contain default mobile left position (20px) inside media query.');
    }


    /**
     * @test
     * @covers ::ApplyTextStyles
     * カスタム値が設定された場合に正しいCSSが出力されるかテスト (PC)
     */
    public function ApplyTextStyles_should_output_correct_css_with_custom_values_pc(): void
    {
        // テスト用の値を設定
        set_theme_mod('integlight_slider_text_color', '#ff0000');
        set_theme_mod('integlight_slider_text_font', 'yu_mincho');
        set_theme_mod('integlight_slider_text_top', '50');
        set_theme_mod('integlight_slider_text_left', '100');

        // アクションを実行して出力をキャプチャ
        ob_start();
        $this->instance->ApplyTextStyles();
        $output = ob_get_clean();

        // 出力に必要なCSSルールが含まれているか確認
        $this->assertStringContainsString('.slider .text-overlay {', $output, 'Should contain PC selector.');
        $this->assertStringContainsString('color: #ff0000;', $output, 'Should contain custom text color.');
        // Yu Mincho の font-family を確認 (integlight-customizer-slider.php の switch 文に基づく)
        $this->assertStringContainsString('font-family: Yu Mincho, 游明朝体, serif;', $output, 'Should contain custom font family (Yu Mincho).');
        $this->assertStringContainsString('top: 50px;', $output, 'Should contain custom top position.');
        $this->assertStringContainsString('left: 100px;', $output, 'Should contain custom left position.');

        // モバイル用の値が設定されていない場合、モバイル用メディアクエリはデフォルト値で出力されることを確認
        $this->assertStringContainsString('@media only screen and (max-width: 767px)', $output, 'Should contain mobile media query even if only PC values are set.');
        $this->assertStringContainsString('top: 200px;', $output, 'Should contain default mobile top position (200px) inside media query.'); // デフォルト値
        $this->assertStringContainsString('left: 20px;', $output, 'Should contain default mobile left position (20px) inside media query.'); // デフォルト値
    }

    /**
     * @test
     * @covers ::ApplyTextStyles
     * カスタム値が設定された場合に正しいCSSが出力されるかテスト (Mobile)
     */
    public function ApplyTextStyles_should_output_correct_css_with_custom_values_mobile(): void
    {
        // テスト用の値を設定 (PC設定も一部残す)
        set_theme_mod('integlight_slider_text_color', '#00ff00'); // PCとは違う色
        set_theme_mod('integlight_slider_text_top_mobile', '20');
        set_theme_mod('integlight_slider_text_left_mobile', '30');
        // PC用の top/left はデフォルトのまま

        // アクションを実行して出力をキャプチャ
        ob_start();
        $this->instance->ApplyTextStyles();
        $output = ob_get_clean();

        // PC用のスタイルが含まれていることを確認 (デフォルト値とカスタム値が混在)
        $this->assertStringContainsString('.slider .text-overlay {', $output, 'Should contain PC selector.');
        $this->assertStringContainsString('color: #00ff00;', $output, 'Should contain custom PC text color.');
        $this->assertStringContainsString('top: 300px;', $output, 'Should contain default PC top position (300px).'); // デフォルト値
        $this->assertStringContainsString('left: 30px;', $output, 'Should contain default PC left position (30px).'); // デフォルト値

        // モバイル用のメディアクエリとカスタムスタイルが含まれていることを確認
        $this->assertStringContainsString('@media only screen and (max-width: 767px)', $output, 'Should contain mobile media query.');
        $this->assertStringContainsString('top: 20px;', $output, 'Should contain custom mobile top position (20px) inside media query.');
        $this->assertStringContainsString('left: 30px;', $output, 'Should contain custom mobile left position (30px) inside media query.');
    }

    /**
     * @test
     * @covers ::ApplyTextStyles
     * フォント設定が yu_gothic の場合に正しいCSSが出力されるかテスト
     */
    public function ApplyTextStyles_should_output_correct_css_for_yu_gothic(): void
    {
        set_theme_mod('integlight_slider_text_font', 'yu_gothic');

        ob_start();
        $this->instance->ApplyTextStyles();
        $output = ob_get_clean();

        // Yu Gothic の font-family を確認 (integlight-customizer-slider.php の switch 文に基づく)
        $this->assertStringContainsString('font-family: Yu Gothic, 游ゴシック体, sans-serif;', $output, 'Should contain correct font family for Yu Gothic.');
    }

    // --- ヘルパーメソッド (必要に応じて) ---
    /*
    private function getMediaQueryContent(string $cssOutput): string
    {
        // メディアクエリブロックの中身を抽出する簡単な例 (より堅牢な正規表現が必要な場合あり)
        preg_match('/@media only screen and \(max-width: 767px\)\s*\{\s*([^\}]+)\s*\}/', $cssOutput, $matches);
        return $matches[1] ?? '';
    }
    */
}
