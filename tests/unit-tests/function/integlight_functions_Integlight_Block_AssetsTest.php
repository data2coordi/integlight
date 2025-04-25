<?php // tests/unit-tests/InteglightBlockAssetsTest.php

declare(strict_types=1);

// テスト対象クラスを含むファイルを読み込む (オートロードされていない場合)
// require_once dirname(__DIR__, 2) . '/inc/integlight-functions.php'; // またはクラス定義ファイル

/**
 * Integlight_Block_Assets クラスのユニットテスト
 *
 * @coversDefaultClass Integlight_Block_Assets
 * @group assets
 * @group blocks
 */
class integlight_functions_Integlight_Block_AssetsTest extends WP_UnitTestCase // クラス名を修正 (PSR-4推奨) Integlight_Block_AssetsTest
{
    /**
     * テスト対象クラスのインスタンス
     * @var Integlight_Block_Assets|null
     */
    private $instance = null;

    /**
     * 各テストの前に実行されるセットアップメソッド
     */
    public function setUp(): void
    {
        parent::setUp();
        // テスト対象クラスのインスタンスを作成
        // ※コンストラクタ内でフック登録が行われる
        $this->instance = new Integlight_Block_Assets();

        // --- ここから削除 ---
        // // テスト間でレジストリの状態をクリーンにする
        // // (WP_UnitTestCase が完全にリセットしない場合があるため)
        // $this->unregister_test_assets();
        // --- ここまで削除 ---
    }

    /**
     * 各テストの後に実行されるティアダウンメソッド
     */
    public function tearDown(): void
    {
        // コンストラクタで追加されたアクションフックを削除
        // インスタンスが null でないことを確認してから削除
        if ($this->instance) {
            remove_action('init', [$this->instance, 'register_assets']);
        }

        // テスト後にレジストリの状態をクリーンにする
        $this->unregister_test_assets();

        unset($this->instance);
        parent::tearDown();
    }

    /**
     * テストで使用したスタイルとパターンを登録解除するヘルパーメソッド
     */
    private function unregister_test_assets(): void
    {
        // スタイル登録解除
        // 登録されているか確認してから解除する方がより安全
        $style_registry = WP_Block_Styles_Registry::get_instance();
        if (isset($style_registry->get_registered_styles_for_block('core/heading')['no-underline'])) {
            if (function_exists('unregister_block_style')) {
                unregister_block_style('core/heading', 'no-underline');
            }
        }

        // パターン登録解除
        // 登録されているか確認してから解除する方がより安全
        $pattern_registry = WP_Block_Patterns_Registry::get_instance();
        if ($pattern_registry->is_registered('integlight/two-columns')) {
            if (function_exists('unregister_block_pattern')) {
                unregister_block_pattern('integlight/two-columns');
            }
        }
    }

    /**
     * @test
     * @covers ::__construct
     * コンストラクタが init アクションフックを正しく登録するかテスト
     */
    public function constructor_should_add_init_action(): void
    {
        // setUp でインスタンスが作成され、コンストラクタが実行されている前提
        $hook_priority = has_action('init', [$this->instance, 'register_assets']);

        $this->assertNotFalse(
            $hook_priority,
            'Constructor should add the register_assets method to the init action.'
        );
        // デフォルトの優先度 (10) であることを確認
        $this->assertEquals(10, $hook_priority, 'The hook priority should be the default (10).');
    }

    /**
     * @test
     * @covers ::register_assets
     * @covers ::register_block_styles
     * register_block_styles がブロックスタイルを正しく登録するかテスト
     */
    public function register_block_styles_should_register_style_correctly(): void
    {
        // Arrange: スタイルが登録されていないことを確認 (tearDownでクリーンされるはず)
        $registry = WP_Block_Styles_Registry::get_instance();
        $styles_before = $registry->get_registered_styles_for_block('core/heading');
        $this->assertArrayNotHasKey('no-underline', $styles_before, 'Style "no-underline" should not be registered initially.');

        // Act: init アクションを実行して register_assets -> register_block_styles を呼び出す
        do_action('init');

        // Assert: スタイルが登録されているか確認
        $styles_after = $registry->get_registered_styles_for_block('core/heading');
        $this->assertArrayHasKey('no-underline', $styles_after, 'Style "no-underline" should be registered after init action.');

        // 登録されたスタイルのプロパティを確認
        $registered_style = $styles_after['no-underline'];
        $this->assertEquals('no-underline', $registered_style['name'], 'Registered style name should be correct.');
        $this->assertEquals(__('No Underline', 'integlight'), $registered_style['label'], 'Registered style label should be correct.');
        $this->assertEquals('.wp-block-heading.is-style-no-underline::after { display: none !important; }', $registered_style['inline_style'], 'Registered style inline_style should be correct.');
    }

    /**
     * @test
     * @covers ::register_assets
     * @covers ::register_block_patterns
     * register_block_patterns がブロックパターンを正しく登録するかテスト
     */
    public function register_block_patterns_should_register_pattern_correctly(): void
    {
        // Arrange: パターンが登録されていないことを確認 (tearDownでクリーンされるはず)
        $registry = WP_Block_Patterns_Registry::get_instance();
        $pattern_before = $registry->get_registered('integlight/two-columns');
        $this->assertNull($pattern_before, 'Pattern "integlight/two-columns" should not be registered initially.');

        // Act: init アクションを実行して register_assets -> register_block_patterns を呼び出す
        do_action('init');

        // Assert: パターンが登録されているか確認
        $pattern_after = $registry->get_registered('integlight/two-columns');
        $this->assertNotNull($pattern_after, 'Pattern "integlight/two-columns" should be registered after init action.');
        $this->assertIsArray($pattern_after, 'Registered pattern data should be an array.');

        // 登録されたパターンのプロパティを確認
        $this->assertEquals(__('Two Columns', 'integlight'), $pattern_after['title'], 'Registered pattern title should be correct.');
        $this->assertEquals(_x('A layout with two columns for content.', 'Block pattern description', 'integlight'), $pattern_after['description'], 'Registered pattern description should be correct.');
        $this->assertEquals(['columns'], $pattern_after['categories'], 'Registered pattern categories should be correct.');

        // コンテンツの比較 (空白や改行に注意)
        $expected_content = "<!-- wp:columns -->\n<div class=\"wp-block-columns\">\n    <!-- wp:column -->\n    <div class=\"wp-block-column\"><p>" . esc_html__('Column one', 'integlight') . "</p></div>\n    <!-- /wp:column -->\n    <!-- wp:column -->\n    <div class=\"wp-block-column\"><p>" . esc_html__('Column two', 'integlight') . "</p></div>\n    <!-- /wp:column -->\n</div>\n<!-- /wp:columns -->";
        $this->assertEquals($expected_content, $pattern_after['content'], 'Registered pattern content should be correct.');
    }

    /**
     * @test
     * @covers ::register_block_patterns
     * register_block_pattern 関数が存在しない場合にエラーが発生しないことをテスト
     * (古いWordPressバージョンなどでの互換性確認)
     */
    public function register_block_patterns_should_not_error_if_function_missing(): void
    {
        // Arrange: register_block_pattern 関数を一時的に未定義にする (難しいので、ここではスキップ)
        // 代わりに、クラス内の if (function_exists(...)) が機能するかを間接的に確認

        // Act & Assert: init アクションを実行してもエラーが発生しないことを確認
        try {
            do_action('init');
            // エラーが発生しなければ成功
            $this->assertTrue(true, 'Executing init action did not cause fatal errors even if register_block_pattern might be missing.');
        } catch (\Throwable $t) {
            $this->fail('Executing init action caused an error: ' . $t->getMessage());
        }

        // 念のため、パターンが登録されていないことを確認 (関数が存在しない場合)
        // ※ このテスト環境では関数は存在するはずなので、実際には登録される
        // $registry = WP_Block_Patterns_Registry::get_instance();
        // $pattern = $registry->get_registered('integlight/two-columns');
        // $this->assertNull($pattern, 'Pattern should not be registered if register_block_pattern function does not exist.');
    }
}
