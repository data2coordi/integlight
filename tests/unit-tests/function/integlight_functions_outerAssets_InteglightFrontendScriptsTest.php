<?php // tests/unit-tests/InteglightFrontendScriptsTest.php

declare(strict_types=1);

// テスト対象クラスを含むファイルを読み込む (オートロードされていない場合)
// require_once dirname(__DIR__, 2) . '/inc/integlight-functions-outerAssets.php';

// _INTEGLIGHT_S_VERSION 定数が定義されていない場合に定義 (テスト環境用)
if (!defined('_INTEGLIGHT_S_VERSION')) {
    define('_INTEGLIGHT_S_VERSION', '1.0.0');
}

/**
 * InteglightFrontendScripts クラスのユニットテスト (シンプル版)
 *
 * @coversDefaultClass InteglightFrontendScripts
 * @group assets
 * @group scripts
 */
class integlight_functions_outerAssets_InteglightFrontendScriptsTest extends WP_UnitTestCase // クラス名を修正 (PSR-4推奨) InteglightFrontendScriptsTest
{
    /**
     * テスト対象クラス名
     */
    private const TARGET_CLASS = InteglightFrontendScripts::class;

    /**
     * テスト対象の静的プロパティ名
     */
    private const SCRIPTS_PROPERTY = 'scripts';

    /**
     * 各テストの前に実行されるセットアップメソッド
     */
    public function setUp(): void
    {
        // ★★★ parent::setUp() の前にフックを削除 ★★★
        // これにより、parent::setUp() 内で after_setup_theme が実行されても
        // InteglightCommonJsAssets::init が呼び出されるのを防ぐ
        remove_action('after_setup_theme', ['InteglightCommonJsAssets', 'init']);

        parent::setUp(); // parent::setUp() を後に移動

        // 静的プロパティをリセット
        $this->set_static_property_value([]);
        // アクションフックを削除
        remove_action('wp_enqueue_scripts', [self::TARGET_CLASS, 'enqueue_frontend_scripts']);
        // WordPress のスクリプトキューをリセット
        $this->reset_scripts();
        // 再度静的プロパティをリセット (念のため)
        $this->set_static_property_value([]);

        // 依存クラス (InteglightMoveScripts) のプロパティもリセット
        $this->reset_move_scripts_property();
    }

    /**
     * 各テストの後に実行されるティアダウンメソッド
     */
    public function tearDown(): void
    {
        // 静的プロパティをリセット
        $this->set_static_property_value([]);
        // アクションフックを削除
        remove_action('wp_enqueue_scripts', [self::TARGET_CLASS, 'enqueue_frontend_scripts']);
        // WordPress のスクリプトキューをリセット
        $this->reset_scripts();

        // 依存クラス (InteglightMoveScripts) のプロパティもリセット
        $this->reset_move_scripts_property();

        // ★★★ after_setup_theme フックを元に戻す (他のテストに影響を与えないように) ★★★
        add_action('after_setup_theme', ['InteglightCommonJsAssets', 'init']);

        parent::tearDown();
    }

    /**
     * WordPress のスクリプトキューをリセットするヘルパーメソッド
     */
    private function reset_scripts(): void
    {
        global $wp_scripts;
        // ★★★ 修正: 強制的に新しいインスタンスで上書き ★★★
        $wp_scripts = new WP_Scripts();
        // wp_default_scripts($wp_scripts); // 必要に応じてデフォルトスクリプトを再登録
    }

    /**
     * InteglightMoveScripts の静的プロパティをリセットするヘルパーメソッド
     */
    private function reset_move_scripts_property(): void
    {
        try {
            $reflection = new ReflectionProperty(InteglightMoveScripts::class, 'scripts');
            if (method_exists($reflection, 'setAccessible')) {
                $reflection->setAccessible(true);
            }
            $reflection->setValue(null, []); // 静的プロパティを空配列にリセット
        } catch (ReflectionException $e) {
            $this->fail("Failed to reset static property InteglightMoveScripts::scripts: " . $e->getMessage());
        }
    }


    /**
     * Reflection を使用して静的プロパティの値を設定するヘルパーメソッド
     *
     * @param mixed $value 設定する値
     */
    private function set_static_property_value($value): void
    {
        try {
            $reflection = new ReflectionProperty(self::TARGET_CLASS, self::SCRIPTS_PROPERTY);
            if (method_exists($reflection, 'setAccessible')) {
                $reflection->setAccessible(true);
            }
            $reflection->setValue(null, $value); // 静的プロパティなので第一引数は null
        } catch (ReflectionException $e) {
            $this->fail("Failed to set static property " . self::TARGET_CLASS . "::" . self::SCRIPTS_PROPERTY . ": " . $e->getMessage());
        }
    }

    /**
     * Reflection を使用して静的プロパティの値を取得するヘルパーメソッド
     *
     * @return mixed 静的プロパティの値
     */
    private function get_static_property_value()
    {
        try {
            $reflectionClass = new ReflectionClass(self::TARGET_CLASS);
            $property = $reflectionClass->getProperty(self::SCRIPTS_PROPERTY);
            if (method_exists($property, 'setAccessible')) {
                $property->setAccessible(true);
            }
            return $property->getValue(null);
        } catch (ReflectionException $e) {
            $this->fail("Failed to get static property " . self::TARGET_CLASS . "::" . self::SCRIPTS_PROPERTY . ": " . $e->getMessage());
        }
    }

    /**
     * @test
     * @covers ::init
     * init() メソッドが wp_enqueue_scripts アクションを正しく登録するかテスト
     */
    public function test_init_adds_wp_enqueue_scripts_action(): void
    {
        // Arrange
        $this->assertFalse(has_action('wp_enqueue_scripts', [self::TARGET_CLASS, 'enqueue_frontend_scripts']));

        // Act
        InteglightFrontendScripts::init();

        // Assert
        // wp_enqueue_scripts のデフォルト優先度は 10
        $this->assertEquals(10, has_action('wp_enqueue_scripts', [self::TARGET_CLASS, 'enqueue_frontend_scripts']));
    }

    /**
     * @test
     * @covers ::add_scripts
     * 単一のスクリプトを追加できるかテスト
     */
    public function test_add_scripts_adds_single_script(): void
    {
        // Arrange
        $scripts_to_add = ['my-script' => ['path' => '/js/my-script.js', 'deps' => ['jquery']]];

        // Act
        InteglightFrontendScripts::add_scripts($scripts_to_add);

        // Assert
        $added_scripts = $this->get_static_property_value();
        $this->assertEquals($scripts_to_add, $added_scripts);
    }

    /**
     * @test
     * @covers ::add_scripts
     * 複数のスクリプトを追加・追記できるかテスト
     */
    public function test_add_scripts_adds_and_appends_multiple_scripts(): void
    {
        // Arrange: 最初にスクリプトを追加
        $initial_scripts = ['script-1' => ['path' => '/js/script-1.js', 'deps' => []]];
        InteglightFrontendScripts::add_scripts($initial_scripts);

        // Act: さらにスクリプトを追加
        $scripts_to_add = [
            'script-2' => ['path' => '/js/script-2.js', 'deps' => ['jquery']],
            'script-3' => ['path' => '/js/script-3.js', 'deps' => []],
        ];
        InteglightFrontendScripts::add_scripts($scripts_to_add);

        // Assert: 全てのスクリプトがマージされているか確認
        $expected_scripts = array_merge($initial_scripts, $scripts_to_add);
        $added_scripts = $this->get_static_property_value();
        $this->assertEquals($expected_scripts, $added_scripts);
    }

    /**
     * @test
     * @covers ::enqueue_frontend_scripts
     * add_scripts で追加されたスクリプトがエンキューされるかテスト (シンプル版)
     */
    public function test_enqueue_frontend_scripts_enqueues_added_scripts(): void
    {
        // Arrange
        $scripts_to_enqueue = [
            'script-a' => ['path' => '/js/script-a.js', 'deps' => []],
            'script-b' => ['path' => '/js/script-b.js', 'deps' => ['jquery']], // jquery に依存
        ];
        InteglightFrontendScripts::add_scripts($scripts_to_enqueue);
        InteglightFrontendScripts::init(); // フックを登録

        // Act: wp_enqueue_scripts アクションを実行
        do_action('wp_enqueue_scripts');

        // Assert: 各スクリプトがエンキューされたか、登録されたかを確認
        foreach ($scripts_to_enqueue as $handle => $data) {
            $this->assertTrue(wp_script_is($handle, 'enqueued'), "Script '{$handle}' should be enqueued.");
            $this->assertTrue(wp_script_is($handle, 'registered'), "Script '{$handle}' should be registered.");

            // 依存関係も確認 (オプション)
            global $wp_scripts;
            $registered_script = $wp_scripts->query($handle);
            if ($registered_script && isset($data['deps'])) {
                $this->assertEquals($data['deps'], $registered_script->deps, "Dependencies for script '{$handle}' should be correct.");
            }
        }
    }

    /**
     * @test
     * @covers ::enqueue_frontend_scripts
     * スクリプトが追加されていない場合に何もエンキューされないかテスト
     */
    public function test_enqueue_frontend_scripts_does_nothing_when_no_scripts_added(): void
    {
        // Arrange
        // setUp で静的プロパティとフックはリセット済み
        InteglightFrontendScripts::init(); // フックを登録

        // テスト開始時に登録されていないことを確認
        // InteglightCommonJsAssets::init で追加される可能性のあるスクリプトをチェック
        $this->assertFalse(wp_script_is('integlight-navigation', 'registered'), "Script 'integlight-navigation' should not be registered before do_action.");
        $this->assertFalse(wp_script_is('script-a', 'registered'), "Script 'script-a' should not be registered before do_action.");

        // Act
        do_action('wp_enqueue_scripts');

        // Assert
        global $wp_scripts;
        // 登録されていないことを確認
        $this->assertFalse(wp_script_is('integlight-navigation', 'registered'), "Script 'integlight-navigation' should not be registered.");
        $this->assertFalse(wp_script_is('script-a', 'registered'), "Script 'script-a' should not be registered.");
        // キューが空であることの確認 (より厳密な場合)
        // $this->assertEmpty($wp_scripts->queue, "Script queue should be empty.");
    }
}
