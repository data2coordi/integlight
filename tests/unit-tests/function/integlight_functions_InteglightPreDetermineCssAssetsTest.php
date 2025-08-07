<?php

use PHPUnit\Framework\TestCase; // または WP_UnitTestCase を使用

/**
 * Test case for InteglightPreDetermineCssAssets class.
 */
class integlight_functions_InteglightPreDetermineCssAssetsTest extends WP_UnitTestCase
{
    /**
     * Reflection-based helper to get or set static properties
     */
    protected static function staticProperty(string $class, string $prop, $value = null)
    {
        $ref = new ReflectionClass($class);
        $property = $ref->getProperty($prop);
        $property->setAccessible(true);
        if (func_num_args() === 2) {
            return $property->getValue();
        }
        $property->setValue(null, $value);
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Reset dependencies
        self::staticProperty(InteglightFrontendStyles::class, 'styles', []);
        self::staticProperty(InteglightEditorStyles::class, 'styles', []);
        self::staticProperty(InteglightDeferCss::class, 'deferred_styles', []);
        // Reset CommonCssAssets to defaults
        $defaults = (new ReflectionClass(InteglightPreDetermineCssAssets::class))->getDefaultProperties();
        self::staticProperty(InteglightPreDetermineCssAssets::class, 'styles', $defaults['styles']);
        self::staticProperty(InteglightPreDetermineCssAssets::class, 'deferredStyles', $defaults['deferredStyles']);
    }

    /**
     * @dataProvider contextProvider
     */
    public function test_init_adds_correct_styles_and_deferred_handles(string $context, array $expectedFrontend)
    {
        // Arrange context
        switch ($context) {
            case 'post':
                $id = wp_insert_post([
                    'post_title'   => 'テスト投稿',
                    'post_content' => '内容',
                    'post_status'  => 'publish',
                    'post_type'    => 'post',
                ]);
                $this->go_to(get_permalink($id));
                break;

            case 'page':
                $id = wp_insert_post([
                    'post_title'   => 'テスト固定ページ',
                    'post_content' => '内容',
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                ]);
                $this->go_to(get_permalink($id));
                break;

            case 'front':
                // create and set static front page
                $id = wp_insert_post([
                    'post_title'   => 'フロントページ',
                    'post_content' => '内容',
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                ]);
                update_option('show_on_front', 'page');
                update_option('page_on_front', $id);
                $this->go_to(home_url('/'));
                break;

            case 'home':
                // blog posts index: clear front page
                update_option('show_on_front', 'posts');
                update_option('page_on_front', 0);
                $this->go_to(home_url('/'));
                break;

            default:
                throw new \InvalidArgumentException("Unknown context: {$context}");
        }

        $GLOBALS['wp']->main();

        // Confirm WP conditional
        if ($context === 'post') {
            $this->assertTrue(is_single(), 'Expected single post context');
            $this->assertFalse(is_page(), 'Not page context');
        } elseif ($context === 'page') {
            $this->assertTrue(is_page(), 'Expected page context');
            $this->assertFalse(is_single(), 'Not single post context');
        } elseif ($context === 'front') {
            $this->assertTrue(is_front_page(), 'Expected front page context');
            $this->assertFalse(is_home(), 'Not blog index context');
        } else {
            // Home (posts index) context
            $this->assertTrue(is_home(), 'Expected blog index context');
        }

        // Act
        InteglightPreDetermineCssAssets::init();

        // Assert Frontend styles
        $actual = self::staticProperty(InteglightFrontendStyles::class, 'styles');
        $this->assertEquals($expectedFrontend, $actual, "Frontend styles mismatch for {$context}");

        // Assert Editor styles
        $editor = self::staticProperty(InteglightEditorStyles::class, 'styles');
        $expectedEditor = $expectedFrontend;
        unset($expectedEditor['integlight-sp-style']);
        $this->assertEquals($expectedEditor, $editor, "Editor styles mismatch for {$context}");

        // Assert Deferred styles
        $deferred = self::staticProperty(InteglightDeferCss::class, 'deferred_styles');
        sort($deferred);
        $expectedDeferred = ['integlight-sp-style', 'wp-block-library'];
        sort($expectedDeferred);
        $this->assertEquals($expectedDeferred, $deferred, "Deferred styles mismatch for {$context}");
    }

    public function contextProvider(): array
    {
        $base = [
            'integlight-base-style-plus' => '/css/build/base-style.css',
            'integlight-style-plus'      => '/css/build/integlight-style.css',
            'integlight-sp-style'        => '/css/build/integlight-sp-style.css',
            'integlight-layout'          => '/css/build/layout.css',
            'integlight-integlight-menu' => '/css/build/integlight-menu.css',
            'integlight-module'          => '/css/build/module.css',
            'integlight-helper'          => '/css/build/helper.css',
        ];

        return [
            'post'  => ['post', array_merge($base, ['integlight-post'  => '/css/build/post.css'])],
            'page'  => ['page', array_merge($base, ['integlight-page'  => '/css/build/page.css'])],
            'front' => ['front', array_merge($base, ['integlight-page'  => '/css/build/page.css', 'integlight-front' => '/css/build/front.css'])],
            'home'  => ['home', array_merge($base, ['integlight-home'  => '/css/build/home.css'])],
        ];
    }
}
