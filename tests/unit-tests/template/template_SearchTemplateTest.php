<?php

/**
 * Class template_SearchTemplateTest
 *
 * @package Integlight
 */

/**
 * search.php のテストケース
 */
class template_SearchTemplateTest extends WP_UnitTestCase
{

    private static $target_post_id;

    public static function wpSetUpBeforeClass($factory): void
    {
        self::$target_post_id = $factory->post->create([
            'post_title'   => 'Search Target Post',
            'post_content' => 'This post content is used for testing.',
            'post_status'  => 'publish',
            'post_type'    => 'post',
        ]);
    }

    public static function wpTearDownAfterClass(): void
    {
        if (self::$target_post_id && get_post(self::$target_post_id)) {
            wp_delete_post(self::$target_post_id, true);
        }
    }

    public function tear_down()
    {
        wp_reset_query();
        wp_reset_postdata();
        unset($GLOBALS['post']);
        parent::tear_down();
    }

    /**
     * @test
     * search.php テンプレートが呼び出されるかを確認
     */
    public function test_search_php_template_is_loaded()
    {
        // 検索クエリをシミュレート
        $search_term = 'Search Target Post';
        $search_url = '/?s=' . urlencode($search_term);

        // 対象 URL に移動
        $this->go_to($search_url);

        // メインクエリを実行
        $GLOBALS['wp_query']->query_vars['s'] = $search_term;
        $GLOBALS['wp_query']->is_search = true;
        $GLOBALS['wp_query']->is_main_query = true;

        // search.php がテンプレートとして使用されるかを確認
        $template = get_query_template('search');
        $this->assertNotEmpty($template, 'search.php template should be resolved.');
        $this->assertStringContainsString('search.php', $template, 'search.php should be the resolved template.');

        // テンプレートファイルの出力を取得して検証
        ob_start();
        include($template);
        $output = ob_get_clean();

        $this->assertStringContainsString('<main', $output, 'Main content area should be present in output.');
        $this->assertStringContainsString('Search Target Post', $output, 'Expected post title should appear in search output.');
    }
}
