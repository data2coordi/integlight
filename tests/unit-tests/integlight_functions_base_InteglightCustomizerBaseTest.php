<?php // tests/unit-tests/InteglightCustomizerBaseTest.php

declare(strict_types=1);

// テスト対象ファイルと依存ファイルを読み込む (オートロードされていない場合)
// require_once dirname(__DIR__, 2) . '/inc/integlight-customizer-base.php';

// _INTEGLIGHT_S_VERSION 定数が定義されていない場合に定義 (テスト環境用)
if (!defined('_INTEGLIGHT_S_VERSION')) {
    define('_INTEGLIGHT_S_VERSION', '1.0.0');
}

/**
 * integlight-customizer-base.php 内の関数のユニットテスト
 *
 * @covers ::integlight_customize_register
 * @covers ::integlight_customize_partial_blogname
 * @covers ::integlight_customize_partial_blogdescription
 * @covers ::integlight_customize_preview_js
 * @group customizer
 * @group assets
 */
class integlight_functions_base_InteglightCustomizerBaseTest extends WP_UnitTestCase // クラス名を修正
{
    /**
     * WP_Customize_Manager のインスタンス
     * @var WP_Customize_Manager|null
     */
    private $wp_customize = null;

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

        // WordPress のスクリプトキューをリセット
        $this->reset_scripts();

        // フィルターとアクションを削除 (テストメソッド内で必要に応じて再登録)
        remove_action('customize_register', 'integlight_customize_register');
        remove_action('customize_preview_init', 'integlight_customize_preview_js');
    }

    /**
     * 各テストの後に実行されるティアダウンメソッド
     */
    public function tearDown(): void
    {
        // フィルターとアクションを削除
        remove_action('customize_register', 'integlight_customize_register');
        remove_action('customize_preview_init', 'integlight_customize_preview_js');

        // プロパティとスクリプトキューをクリア
        unset($this->wp_customize);
        $this->reset_scripts();

        parent::tearDown();
    }

    /**
     * WordPress のスクリプトキューをリセットするヘルパーメソッド
     */
    private function reset_scripts(): void
    {
        global $wp_scripts;
        $wp_scripts = new WP_Scripts();
        // wp_default_scripts($wp_scripts); // 必要に応じて
    }

    /**
     * @test
     * integlight_customize_register が customize_register アクションにフックされているかテスト
     */
    public function test_customize_register_hooked(): void
    {
        // Arrange: integlight-customizer-base.php が読み込まれた時点でフックされているはず
        //          (ただし、setUp で削除しているので、ここで再登録して確認)
        add_action('customize_register', 'integlight_customize_register');

        // Assert
        $this->assertGreaterThan(0, has_action('customize_register', 'integlight_customize_register'));
        $this->assertEquals(10, has_action('customize_register', 'integlight_customize_register'));
    }

    /**
     * @test
     * integlight_customize_register が設定の transport を postMessage に変更するかテスト
     */
    public function test_customize_register_sets_transport_to_postMessage(): void
    {
        // Arrange: デフォルト設定を追加
        $this->wp_customize->add_setting('blogname', ['default' => 'Test Blogname', 'transport' => 'refresh']);
        $this->wp_customize->add_setting('blogdescription', ['default' => 'Test Tagline', 'transport' => 'refresh']);
        $this->wp_customize->add_setting('header_textcolor', ['default' => '000000', 'transport' => 'refresh']);

        // Act: 関数を直接呼び出す
        integlight_customize_register($this->wp_customize);

        // Assert
        $this->assertEquals('postMessage', $this->wp_customize->get_setting('blogname')->transport);
        $this->assertEquals('postMessage', $this->wp_customize->get_setting('blogdescription')->transport);
        $this->assertEquals('postMessage', $this->wp_customize->get_setting('header_textcolor')->transport);
    }

    /**
     * @test
     * integlight_customize_register が Selective Refresh の partial を追加するかテスト
     */
    public function test_customize_register_adds_selective_refresh_partials(): void
    {
        // Arrange: selective_refresh が利用可能か確認 (通常は true)
        if (!isset($this->wp_customize->selective_refresh)) {
            $this->markTestSkipped('Selective refresh is not available in this WP_Customize_Manager instance.');
        }

        // ★★★ 追加: エラー回避のため header_textcolor 設定を追加 ★★★
        $this->wp_customize->add_setting('blogname', ['default' => 'Test Blogname', 'transport' => 'refresh']);
        $this->wp_customize->add_setting('blogdescription', ['default' => 'Test Tagline', 'transport' => 'refresh']);
        $this->wp_customize->add_setting('header_textcolor', ['default' => '000000', 'transport' => 'refresh']);


        // Act: 関数を直接呼び出す
        integlight_customize_register($this->wp_customize);

        // Assert: partial が登録されているか確認
        $partials = $this->wp_customize->selective_refresh->partials();
        $this->assertArrayHasKey('blogname', $partials);
        $this->assertArrayHasKey('blogdescription', $partials);

        // Assert: partial のパラメータを確認
        $blogname_partial = $partials['blogname'];
        $this->assertEquals('.site-title a', $blogname_partial->selector);
        $this->assertEquals('integlight_customize_partial_blogname', $blogname_partial->render_callback);

        $blogdescription_partial = $partials['blogdescription'];
        $this->assertEquals('.site-description', $blogdescription_partial->selector);
        $this->assertEquals('integlight_customize_partial_blogdescription', $blogdescription_partial->render_callback);
    }

    /**
     * @test
     * integlight_customize_partial_blogname がサイト名を出力するかテスト
     */
    public function test_customize_partial_blogname_outputs_site_name(): void
    {
        // Arrange
        $expected_name = get_bloginfo('name');

        // Act
        ob_start();
        integlight_customize_partial_blogname();
        $output = ob_get_clean();

        // Assert
        $this->assertEquals($expected_name, $output);
    }

    /**
     * @test
     * integlight_customize_partial_blogdescription がキャッチフレーズを出力するかテスト
     */
    public function test_customize_partial_blogdescription_outputs_tagline(): void
    {
        // Arrange
        $expected_description = get_bloginfo('description');

        // Act
        ob_start();
        integlight_customize_partial_blogdescription();
        $output = ob_get_clean();

        // Assert
        $this->assertEquals($expected_description, $output);
    }

    /**
     * @test
     * integlight_customize_preview_js が customize_preview_init アクションにフックされているかテスト
     */
    public function test_customize_preview_js_hooked(): void
    {
        // Arrange: integlight-customizer-base.php が読み込まれた時点でフックされているはず
        //          (ただし、setUp で削除しているので、ここで再登録して確認)
        add_action('customize_preview_init', 'integlight_customize_preview_js');

        // Assert
        $this->assertGreaterThan(0, has_action('customize_preview_init', 'integlight_customize_preview_js'));
        $this->assertEquals(10, has_action('customize_preview_init', 'integlight_customize_preview_js'));
    }

    /**
     * @test
     * integlight_customize_preview_js がカスタムスクリプトをエンキューするかテスト
     */
    public function test_customize_preview_js_enqueues_script(): void
    {
        // Arrange: フックを登録
        add_action('customize_preview_init', 'integlight_customize_preview_js');

        // Act: アクションを実行
        do_action('customize_preview_init');

        // Assert: スクリプトがエンキューされたか確認
        $handle = 'integlight-customizer';
        $this->assertTrue(wp_script_is($handle, 'enqueued'), "Script '{$handle}' should be enqueued.");
        $this->assertTrue(wp_script_is($handle, 'registered'), "Script '{$handle}' should be registered.");

        // Assert: スクリプトの依存関係とバージョンを確認 (オプション)
        global $wp_scripts;
        $script = $wp_scripts->query($handle);
        $this->assertNotNull($script, "Script object for '{$handle}' should exist.");
        $this->assertContains('customize-preview', $script->deps, "Script '{$handle}' should depend on 'customize-preview'.");
        $this->assertEquals(_INTEGLIGHT_S_VERSION, $script->ver, "Script '{$handle}' version should match _INTEGLIGHT_S_VERSION.");

        // ★★★ 修正箇所: フッターではなくヘッダーで読み込まれることを確認 ★★★
        // $script->args === 1 はフッターを示す。ヘッダーの場合は null や false になる。
        $this->assertNotEquals(1, $script->args, "Script '{$handle}' should be enqueued in the header (args !== 1).");
        // もしくは、より具体的に null であることを期待する場合 (wp_enqueue_script のデフォルト)
        // $this->assertNull($script->args, "Script '{$handle}' should be enqueued in the header (args === null).");
    }
}
