<?php // tests/unit-tests/integlight_functions_FunctionsTest.php

declare(strict_types=1);

// _INTEGLIGHT_S_VERSION 定数が定義されていない場合に定義 (テスト環境用)
// functions.php が読み込まれる前に定義する必要がある場合
if (!defined('_INTEGLIGHT_S_VERSION')) {
    define('_INTEGLIGHT_S_VERSION', '1.0.1'); // functions.php 内の値に合わせる
}

/**
 * functions.php ファイルのユニットテスト
 *
 * @covers ::integlight_setup
 * @covers ::integlight_content_width
 * @covers ::integlight_widgets_init
 * @group functions
 * @group core
 */
class integlight_functions_FunctionsTest extends WP_UnitTestCase // クラス名を修正 (ファイルパスに合わせる)
{
    /**
     * テストクラス全体のセットアップ
     * functions.php を読み込む (WP_UnitTestCase が自動で読み込む場合もある)
     */
    public static function wpSetUpBeforeClass($factory): void
    {
        // wpSetUpBeforeClass は空のまま
        // require_once get_template_directory() . '/functions.php';
    }

    /**
     * 各テストの前に実行されるセットアップメソッド
     */
    public function setUp(): void
    {
        parent::setUp();

        // グローバル変数等をリセット
        unset($GLOBALS['content_width']);
        unset($GLOBALS['wp_registered_sidebars']);
        unregister_nav_menu('menu-1');
        // 必要に応じて他のテーマサポートもリセット
        // 例: remove_theme_support('...');

        // ★★★ 維持: after_setup_theme アクションを実行 ★★★
        // これにより、integlight_setup と integlight_content_width が実行される
        do_action('after_setup_theme');
    }

    /**
     * 各テストの後に実行されるティアダウンメソッド
     */
    public function tearDown(): void
    {
        // グローバル変数をリセット
        unset($GLOBALS['content_width']);
        // サイドバー登録をリセット
        unset($GLOBALS['wp_registered_sidebars']);
        // ナビゲーションメニュー登録をリセット
        unregister_nav_menu('menu-1');
        parent::tearDown();
    }

    /**
     * @test
     * _INTEGLIGHT_S_VERSION 定数が定義されているかテスト
     * // @expectedIncorrectUsage add_theme_support( 'title-tag' ) // ★★★ 削除 ★★★
     */
    public function test_version_constant_is_defined(): void
    {
        $this->assertTrue(defined('_INTEGLIGHT_S_VERSION'), 'Constant _INTEGLIGHT_S_VERSION should be defined.');
        // 必要であれば、値も確認
        $this->assertEquals('1.0.1', _INTEGLIGHT_S_VERSION, 'Constant _INTEGLIGHT_S_VERSION should have the correct value.');
    }

    /**
     * @test
     * 主要な関数が適切なアクションフックに登録されているかテスト
     * // @expectedIncorrectUsage add_theme_support( 'title-tag' ) // ★★★ 削除 ★★★
     */
    public function test_functions_are_hooked_correctly(): void
    {
        // integlight_setup
        $setup_priority = has_action('after_setup_theme', 'integlight_setup');
        $this->assertNotFalse($setup_priority, 'integlight_setup should be hooked to after_setup_theme.');
        $this->assertEquals(10, $setup_priority, 'integlight_setup hook priority should be 10.');

        // integlight_content_width
        $content_width_priority = has_action('after_setup_theme', 'integlight_content_width');
        $this->assertNotFalse($content_width_priority, 'integlight_content_width should be hooked to after_setup_theme.');
        $this->assertEquals(0, $content_width_priority, 'integlight_content_width hook priority should be 0.');

        // integlight_widgets_init
        $widgets_init_priority = has_action('widgets_init', 'integlight_widgets_init');
        $this->assertNotFalse($widgets_init_priority, 'integlight_widgets_init should be hooked to widgets_init.');
        $this->assertEquals(10, $widgets_init_priority, 'integlight_widgets_init hook priority should be 10.');
    }

    /**
     * @test
     * integlight_setup がテーマサポートを正しく追加するかテスト
     * // @expectedIncorrectUsage add_theme_support( 'title-tag' ) // ★★★ 削除 ★★★
     */
    public function test_integlight_setup_adds_theme_supports(): void
    {
        // Arrange: setUp で do_action('after_setup_theme') を実行済み

        // Assert: 各テーマサポートが追加されているか確認
        $this->assertTrue(current_theme_supports('automatic-feed-links'), 'Theme support for "automatic-feed-links" should be added.');
        $this->assertTrue(current_theme_supports('title-tag'), 'Theme support for "title-tag" should be added.');
        $this->assertTrue(current_theme_supports('post-thumbnails'), 'Theme support for "post-thumbnails" should be added.');
        $this->assertTrue(current_theme_supports('html5'), 'Theme support for "html5" should be added.');
        $this->assertTrue(current_theme_supports('custom-background'), 'Theme support for "custom-background" should be added.');
        $this->assertTrue(current_theme_supports('customize-selective-refresh-widgets'), 'Theme support for "customize-selective-refresh-widgets" should be added.');
        $this->assertTrue(current_theme_supports('custom-logo'), 'Theme support for "custom-logo" should be added.');
        $this->assertTrue(current_theme_supports('align-wide'), 'Theme support for "align-wide" should be added.');

        // html5 の詳細を確認 (オプション)
        $html5_support = get_theme_support('html5');
        // html5 サポートが追加されている場合のみ詳細を確認
        if ($html5_support) {
            $this->assertIsArray($html5_support[0]);
            $this->assertContains('search-form', $html5_support[0]);
            $this->assertContains('comment-form', $html5_support[0]);
            $this->assertContains('comment-list', $html5_support[0]);
            // ... 他の html5 サポートも同様に確認可能
        } else {
            // html5 サポートがない場合は失敗させる
            $this->fail('HTML5 theme support was expected but not found.');
        }
    }

    /**
     * @test
     * integlight_setup がナビゲーションメニューを登録するかテスト
     * // @expectedIncorrectUsage add_theme_support( 'title-tag' ) // ★★★ 削除 ★★★
     */
    public function test_integlight_setup_registers_nav_menus(): void
    {
        // Arrange: setUp で do_action('after_setup_theme') を実行済み

        // Assert: メニューが登録されているか確認
        $registered_menus = get_registered_nav_menus();
        $this->assertArrayHasKey('menu-1', $registered_menus, 'Navigation menu "menu-1" should be registered.');
        $this->assertEquals('Primary', $registered_menus['menu-1'], 'Navigation menu "menu-1" label should be correct.');
    }

    /**
     * @test
     * integlight_content_width がグローバル変数 $content_width を設定するかテスト
     * // @expectedIncorrectUsage add_theme_support( 'title-tag' ) // ★★★ 削除 ★★★
     */
    public function test_integlight_content_width_sets_global_variable(): void
    {
        // Arrange: setUp で do_action('after_setup_theme') を実行済み
        //          integlight_content_width は優先度 0 なので integlight_setup より先に実行される

        // Assert: グローバル変数が設定されているか確認
        $this->assertArrayHasKey('content_width', $GLOBALS, '$GLOBALS["content_width"] should be set.');
        // デフォルト値を確認 (フィルターが適用される前の値)
        $this->assertEquals(640, $GLOBALS['content_width'], '$content_width should be set to the default value (640).');

        // フィルターのテスト (オプション)
        add_filter('integlight_content_width', function ($width) {
            return 800;
        });
        // 再度関数を実行してフィルター適用後の値を確認
        integlight_content_width();
        $this->assertEquals(800, $GLOBALS['content_width'], '$content_width should be filterable.');
        // フィルターを削除
        remove_all_filters('integlight_content_width');
        // 元の値に戻す
        integlight_content_width();
    }

    /**
     * @test
     * integlight_widgets_init がサイドバーを正しく登録するかテスト
     * 注意: このテストが成功するには、functions.php 内の integlight_widgets_init 関数の
     *      register_sidebar 呼び出しが正しい引数で行われている必要があります。
     * // @expectedIncorrectUsage add_theme_support( 'title-tag' ) // ★★★ 削除 ★★★
     */
    public function test_integlight_widgets_init_registers_sidebars(): void
    {
        // Arrange: widgets_init アクションを手動で実行
        do_action('widgets_init');

        // Assert: サイドバーが登録されているか確認
        global $wp_registered_sidebars;

        // エラーが発生しないことを確認 (TypeError の根本原因は functions.php にある可能性)
        // ここでは、アクションが実行され、$wp_registered_sidebars が配列であることを期待する
        $this->assertIsArray($wp_registered_sidebars, '$wp_registered_sidebars should be an array after widgets_init.');

        // $wp_registered_sidebars が配列であれば、キーの存在を確認
        if (is_array($wp_registered_sidebars)) {
            $this->assertArrayHasKey('sidebar-1', $wp_registered_sidebars, 'Sidebar "sidebar-1" should be registered.');
            $this->assertArrayHasKey('sidebar-2', $wp_registered_sidebars, 'Sidebar "sidebar-2" should be registered.');

            // sidebar-1 の詳細を確認 (オプション)
            $sidebar1 = $wp_registered_sidebars['sidebar-1'];
            $this->assertEquals('Sidebar1', $sidebar1['name']);
            $this->assertEquals('<section id="%1$s" class="widget %2$s">', $sidebar1['before_widget']);
            $this->assertEquals('</section>', $sidebar1['after_widget']);
            $this->assertEquals('<h2 class="widget-title">', $sidebar1['before_title']);
            $this->assertEquals('</h2>', $sidebar1['after_title']);

            // sidebar-2 も同様に確認可能 (エラーが解消されていれば)
            if (isset($wp_registered_sidebars['sidebar-2'])) {
                $sidebar2 = $wp_registered_sidebars['sidebar-2'];
                $this->assertEquals('Sidebar2', $sidebar2['name']);
            }
        }
    }

    /**
     * @test
     * 必要なファイルが require されているか（主要な関数/クラスが存在するか）テスト (オプション)
     * // @expectedIncorrectUsage add_theme_support( 'title-tag' ) // ★★★ 削除 ★★★
     */
    public function test_required_files_are_included(): void
    {
        // Arrange: functions.php は読み込み済みのはず
        //          setUp で do_action('after_setup_theme') を実行済み

        // Assert: 各 require ファイル内の主要な関数やクラスが存在するか確認
        $this->assertTrue(function_exists('integlight_posted_on'), 'Function integlight_posted_on from template-tags.php should exist.');
        $this->assertTrue(function_exists('integlight_entry_footer'), 'Function integlight_entry_footer from template-tags.php should exist.');
        // ... 他の template-tags.php 内の関数

        $this->assertTrue(function_exists('integlight_pingback_header'), 'Function integlight_pingback_header from template-functions.php should exist.');
        // ... 他の template-functions.php 内の関数

        $this->assertTrue(class_exists('Integlight_SEO_Meta'), 'Class Integlight_SEO_Meta from integlight-functions.php should exist.');
        $this->assertTrue(class_exists('Integlight_Excerpt_Customizer'), 'Class Integlight_Excerpt_Customizer from integlight-functions.php should exist.');
        // ... 他の integlight-functions.php 内のクラスや関数

        $this->assertTrue(function_exists('integlight_customize_register'), 'Function integlight_customize_register from integlight-customizer-base.php should exist.');
        // ... 他の integlight-customizer-base.php 内の関数

        // Jetpack はオプションなので、存在する場合のみチェック
        if (defined('JETPACK__VERSION')) {
            $this->assertTrue(function_exists('integlight_jetpack_setup'), 'Function integlight_jetpack_setup from jetpack.php should exist.');
        }

        // custom-header.php は add_theme_support('custom-header') で読み込まれるため、
        // そのサポートが有効か、または内部の関数が存在するかで確認
        $this->assertTrue(current_theme_supports('custom-header'), 'Theme support for "custom-header" should be added (implies custom-header.php loaded).');
        // または $this->assertTrue(function_exists('integlight_header_style')); // custom-header.php 内の関数
    }
}
