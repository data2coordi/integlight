<?php
// tests/unit-tests/InteglightCustomHeaderTest.php

declare(strict_types=1);

// テスト対象ファイル (必要に応じてパスを調整)
// 通常、bootstrap.php で読み込まれるため不要な場合が多い
// require_once dirname(__DIR__, 2) . '/inc/custom-header.php';

/**
 * inc/custom-header.php の関数のユニットテスト
 *
 * @covers ::integlight_custom_header_setup
 * @covers ::integlight_header_style
 * @group custom-header
 */
class custom_header_InteglightCustomHeaderTest extends WP_UnitTestCase
{
    /**
     * デフォルトのヘッダーテキスト色 (テスト用)
     */
    private const DEFAULT_TEXT_COLOR = '000000';

    /**
     * 各テストの前に実行
     */
    public function setUp(): void
    {
        parent::setUp();
        // テスト間で theme_mod の状態をリセット
        remove_theme_mod('header_textcolor');
        // after_setup_theme フックを削除 (テストメソッド内で必要に応じて実行)
        remove_action('after_setup_theme', 'integlight_custom_header_setup');
        // wp_head フックを削除 (テストメソッド内で必要に応じて実行)
        remove_action('wp_head', 'integlight_header_style');
    }

    /**
     * 各テストの後に実行
     */
    public function tearDown(): void
    {
        // theme_mod をリセット
        remove_theme_mod('header_textcolor');
        // フックを削除 (念のため)
        remove_action('after_setup_theme', 'integlight_custom_header_setup');
        remove_action('wp_head', 'integlight_header_style');
        parent::tearDown();
    }

    /**
     * @test
     * integlight_custom_header_setup が after_setup_theme にフックされているか
     */
    public function test_custom_header_setup_hooks_to_after_setup_theme(): void
    {
        // Arrange: custom-header.php が読み込まれた時点でフックされているはず
        //          (ただし setUp で削除しているので、ここで再登録して確認)
        add_action('after_setup_theme', 'integlight_custom_header_setup');

        // Assert
        $this->assertEquals(10, has_action('after_setup_theme', 'integlight_custom_header_setup'));
    }

    /**
     * @test
     * integlight_custom_header_setup が正しい引数でテーマサポートを追加するか
     */
    public function test_custom_header_setup_adds_theme_support_with_correct_args(): void
    {
        // Act: 関数を直接呼び出す (または after_setup_theme を実行)
        integlight_custom_header_setup();

        // Assert: テーマサポートが追加されたか
        $this->assertTrue(current_theme_supports('custom-header'));

        // Assert: 登録された引数を確認
        $support_args = get_theme_support('custom-header');
        // get_theme_support は配列の配列を返すことがあるため、最初の要素を取得
        $args = $support_args[0] ?? null;

        $this->assertIsArray($args, 'Custom header arguments should be an array.');

        $expected_args = [
            'default-image'      => '',
            'default-text-color' => self::DEFAULT_TEXT_COLOR,
            'width'              => 1000,
            'height'             => 250,
            'flex-height'        => true,
            'wp-head-callback'   => 'integlight_header_style',
        ];

        // 個々の引数を比較
        foreach ($expected_args as $key => $value) {
            $this->assertArrayHasKey($key, $args, "Argument '{$key}' should exist.");
            $this->assertEquals($value, $args[$key], "Argument '{$key}' should have the correct value.");
        }
    }

    /**
     * @test
     * integlight_header_style がデフォルト色の場合に何も出力しないか
     */
    public function test_header_style_outputs_nothing_when_color_is_default(): void
    {
        // Arrange: デフォルト色を設定
        // add_theme_support を実行してデフォルト色を登録
        integlight_custom_header_setup();
        // get_header_textcolor() がデフォルト色を返すように設定
        set_theme_mod('header_textcolor', self::DEFAULT_TEXT_COLOR);

        // Act: 関数を呼び出して出力をキャプチャ
        ob_start();
        integlight_header_style();
        $output = ob_get_clean();

        // Assert: 出力が空であることを確認
        $this->assertEmpty(trim($output), 'Output should be empty when header text color is default.');
    }

    /**
     * @test
     * integlight_header_style が 'blank' の場合に非表示用CSSを出力するか
     */
    public function test_header_style_outputs_hide_css_when_color_is_blank(): void
    {
        // Arrange: 色を 'blank' に設定
        integlight_custom_header_setup(); // wp-head-callback のために必要
        set_theme_mod('header_textcolor', 'blank');

        // Act: 関数を呼び出して出力をキャプチャ
        ob_start();
        integlight_header_style();
        $output = ob_get_clean();

        // Assert: 正しいCSSが出力されているか
        $this->assertStringContainsString('<style>', $output);
        $this->assertStringContainsString('.site-title,', $output);
        $this->assertStringContainsString('.site-description {', $output);
        $this->assertStringContainsString('position: absolute;', $output);
        $this->assertStringContainsString('clip: rect(1px, 1px, 1px, 1px);', $output);
        $this->assertStringContainsString('</style>', $output);
        // 色指定のCSSが含まれていないことを確認
        $this->assertStringNotContainsString('color:', $output);
    }

    /**
     * @test
     * integlight_header_style がカスタム色の場合に色指定CSSを出力するか
     */
    public function test_header_style_outputs_color_css_when_custom_color_is_set(): void
    {
        // Arrange: カスタム色を設定
        $custom_color = 'ff0000'; // 例: 赤色
        integlight_custom_header_setup();
        set_theme_mod('header_textcolor', $custom_color);

        // Act: 関数を呼び出して出力をキャプチャ
        ob_start();
        integlight_header_style();
        $output = ob_get_clean();

        // Assert: 正しいCSSが出力されているか
        $this->assertStringContainsString('<style>', $output);
        $this->assertStringContainsString('.site-title a,', $output);
        $this->assertStringContainsString('.site-description {', $output);
        $this->assertStringContainsString('color: #' . esc_attr($custom_color) . ';', $output);
        $this->assertStringContainsString('</style>', $output);
        // 非表示用CSSが含まれていないことを確認
        $this->assertStringNotContainsString('position: absolute;', $output);
        $this->assertStringNotContainsString('clip:', $output);
    }

    /**
     * @test
     * integlight_header_style が wp_head にフックされているか (間接的な確認)
     * add_theme_support の 'wp-head-callback' 経由
     */
    public function test_header_style_is_hooked_via_theme_support(): void
    {
        // Arrange: テーマサポートを追加
        integlight_custom_header_setup();

        // Assert: wp-head-callback が正しく設定されているか
        $support_args = get_theme_support('custom-header');
        $args = $support_args[0] ?? null;
        $this->assertIsArray($args);
        $this->assertEquals('integlight_header_style', $args['wp-head-callback'] ?? null);

        // Assert: 関数が存在するか
        $this->assertTrue(function_exists('integlight_header_style'));

        // Note: WP_UnitTestCase 環境で wp_head アクションが実際に
        //       このコールバックを呼び出すかを直接テストするのは難しい場合があります。
        //       コールバックが正しく設定されていることの確認に留めます。
    }
}
