<?php

/**
 * Class InteglightFunctionsTest
 *
 * @package Integlight
 */

/**
 * Test integlight theme functions.
 */
class integlight_functions_FunctionsTest extends WP_UnitTestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        // 初回のテスト実行前に一度だけ実行（2重呼び出しを防ぐ）
        if (! did_action('after_setup_theme')) {
            do_action('after_setup_theme');
        }
    }

    public function test_integlight_setup_actions_registered()
    {
        $this->assertTrue(has_action('after_setup_theme', 'integlight_setup') !== false);
    }

    public function test_integlight_widgets_init_registered()
    {
        $this->assertTrue(has_action('widgets_init', 'integlight_widgets_init') !== false);
    }

    public function test_content_width_is_set()
    {
        global $content_width;
        $this->assertEquals(640, $content_width);
    }

    public function test_theme_supports_are_added()
    {
        $this->assertTrue(current_theme_supports('title-tag'));
        $this->assertTrue(current_theme_supports('post-thumbnails'));
        $this->assertTrue(current_theme_supports('automatic-feed-links'));
        $this->assertTrue(current_theme_supports('custom-background'));
        $this->assertTrue(current_theme_supports('custom-logo'));
        $this->assertTrue(current_theme_supports('customize-selective-refresh-widgets'));
        $this->assertTrue(current_theme_supports('align-wide'));
        //$this->assertTrue(current_theme_supports('html5', 'gallery'));

    }

    public function test_nav_menus_are_registered()
    {
        $menus = get_registered_nav_menus();
        $this->assertArrayHasKey('menu-1', $menus);
    }
}
