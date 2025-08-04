<?php

/**
 * Class FunctionsTest
 *
 * Tests for the main functions.php file.
 *
 * @package Integlight
 */

// クラス名を PSR-4/PSR-12 準拠に修正することを推奨 (例: FunctionsTest)
class template_FunctionsTest extends WP_UnitTestCase
{

    /**
     * 各テストメソッド実行前のセットアップ
     */
    public function set_up()
    {
        parent::set_up();
        // functions.php はテスト環境のブートストラップで読み込まれている前提
    }

    /**
     * @test
     * 主要なアクションフックが登録されているか確認。
     */
    public function test_theme_setup_hooks_are_registered()
    {
        // after_setup_theme フック
        $this->assertEquals(10, has_action('after_setup_theme', 'integlight_setup'), 'integlight_setup should be hooked to after_setup_theme.');
        // content_width は優先度 0
        $this->assertEquals(0, has_action('after_setup_theme', 'integlight_content_width'), 'integlight_content_width should be hooked to after_setup_theme with priority 0.');

        // widgets_init フック
        $this->assertEquals(10, has_action('widgets_init', 'integlight_widgets_init'), 'integlight_widgets_init should be hooked to widgets_init.');
    }

    /**
     * @test
     * integlight_setup() で追加されるテーマサポートを確認。
     */
    public function test_theme_supports_are_added()
    {
        // *** MODIFICATION START: Remove action call ***
        // integlight_setup() はテスト実行前に呼び出されているはず
        // do_action('after_setup_theme');
        // *** MODIFICATION END ***

        $this->assertTrue(current_theme_supports('automatic-feed-links'), 'Theme should support automatic-feed-links.');
        $this->assertTrue(current_theme_supports('title-tag'), 'Theme should support title-tag.');
        $this->assertTrue(current_theme_supports('post-thumbnails'), 'Theme should support post-thumbnails.');
        //$this->assertTrue(current_theme_supports('html5'), 'Theme should support html5.');
        $this->assertTrue(current_theme_supports('custom-background'), 'Theme should support custom-background.');
        $this->assertTrue(current_theme_supports('customize-selective-refresh-widgets'), 'Theme should support customize-selective-refresh-widgets.');
        $this->assertTrue(current_theme_supports('custom-logo'), 'Theme should support custom-logo.');
        $this->assertTrue(current_theme_supports('align-wide'), 'Theme should support align-wide.');

        // html5 の詳細なサポート内容を確認 (オプション)
        $html5_support = get_theme_support('html5');
        // $this->assertIsArray($html5_support[0], 'HTML5 support args should be an array.');
        //$this->assertContains('search-form', $html5_support[0], 'HTML5 should support search-form.');
        //$this->assertContains('comment-form', $html5_support[0], 'HTML5 should support comment-form.');
        //$this->assertContains('comment-list', $html5_support[0], 'HTML5 should support comment-list.');
    }


    /**
     * @test
     * register_nav_menus() でメニューが登録されているか確認。
     */
    public function test_nav_menu_is_registered()
    {
        // integlight_setup() はテスト実行前に呼び出されているはず
        // do_action('after_setup_theme');

        $registered_menus = get_registered_nav_menus();
        $this->assertArrayHasKey('header', $registered_menus, 'Nav menu "header" should be registered.');
        $this->assertEquals('header', $registered_menus['header'], 'Nav menu "header" description should be "Primary".');

        $this->assertArrayHasKey('footer', $registered_menus, 'Nav menu "header" should be registered.');
        $this->assertEquals('footer', $registered_menus['footer'], 'Nav menu "header" description should be "Primary".');
    }

    /**
     * @test
     * integlight_content_width() でグローバル $content_width が設定されるか確認。
     */
    public function test_content_width_is_set()
    {
        global $content_width;
        // integlight_content_width() はテスト初期化時に実行されているはず
        // do_action('after_setup_theme');

        // $content_width が設定されているか確認
        $this->assertNotNull($content_width, 'Global $content_width should be set (might be null if not set).');
        if (isset($content_width)) {
            // デフォルト値 640 を確認 (フィルターで変更される可能性も考慮)
            $this->assertEquals(640, $content_width, 'Default $content_width should be 640.');
        }
    }

    /**
     * @test
     * integlight_widgets_init() でサイドバーが登録されるか確認。
     */
    public function test_sidebars_are_registered()
    {
        global $wp_registered_sidebars;

        // widgets_init アクションを実行してサイドバーを登録させる
        do_action('widgets_init');

        // 登録されたサイドバーが存在するか確認
        $this->assertArrayHasKey('sidebar-1', $wp_registered_sidebars, 'Sidebar "sidebar-1" should be registered.');
        $this->assertArrayHasKey('sidebar-2', $wp_registered_sidebars, 'Sidebar "sidebar-2" should be registered.');

        // サイドバーの詳細を確認 (オプション)
        $sidebar1 = $wp_registered_sidebars['sidebar-1'];
        $this->assertEquals('Sidebar1', $sidebar1['name'], 'Sidebar "sidebar-1" name should be correct.');
        $this->assertEquals('<section id="%1$s" class="widget %2$s">', $sidebar1['before_widget'], 'Sidebar "sidebar-1" before_widget should be correct.');
        $this->assertEquals('</section>', $sidebar1['after_widget'], 'Sidebar "sidebar-1" after_widget should be correct.');
        $this->assertEquals('<h2 class="widget-title">', $sidebar1['before_title'], 'Sidebar "sidebar-1" before_title should be correct.');
        $this->assertEquals('</h2>', $sidebar1['after_title'], 'Sidebar "sidebar-1" after_title should be correct.');

        $sidebar2 = $wp_registered_sidebars['sidebar-2'];
        $this->assertEquals('Sidebar2', $sidebar2['name'], 'Sidebar "sidebar-2" name should be correct.');
    }

    /**
     * @test
     * 必要なインクルードファイルが存在するか確認 (オプション)。
     */
    public function test_required_files_exist()
    {
        $theme_dir = get_template_directory();
        $this->assertFileExists($theme_dir . '/inc/custom-header.php', 'File inc/custom-header.php should exist.');
        $this->assertFileExists($theme_dir . '/inc/integlight-functions.php', 'File inc/integlight-functions.php should exist.');
        $this->assertFileExists($theme_dir . '/inc/integlight-customizer-base.php', 'File inc/integlight-customizer-base.php should exist.');
    }
}
