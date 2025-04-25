<?php

// use WP_UnitTestCase; // この行は不要

/**
 * Class template_HomeTemplateTest
 *
 * @package Integlight
 */

/**
 * home.php のテストケース
 */
class template_HomeTemplateTest extends WP_UnitTestCase // WP_UnitTestCase を直接継承
{
    /**
     * テスト用の投稿ID配列
     * @var int[]
     */
    private static $post_ids = [];

    /**
     * テストクラス全体のセットアップ
     * @param WP_UnitTest_Factory $factory
     */
    public static function wpSetUpBeforeClass($factory): void
    {
        // テスト用の投稿を複数作成 (順序が重要でなければ日付調整は不要)
        self::$post_ids = $factory->post->create_many(5, ['post_status' => 'publish']);
    }

    /**
     * テストクラス全体のティアダウン
     */
    public static function wpTearDownAfterClass(): void
    {
        // 作成した投稿を削除
        foreach (self::$post_ids as $post_id) {
            wp_delete_post($post_id, true);
        }
        self::$post_ids = [];
    }

    /**
     * 各テストメソッド実行前のセットアップ
     */
    public function set_up()
    {
        parent::set_up();
        // フロントページ設定を最新の投稿が表示されるように設定
        update_option('show_on_front', 'posts');
        update_option('page_on_front', 0);
        update_option('page_for_posts', 0);
    }

    /**
     * 各テストメソッド実行後のティアダウン
     */
    public function tear_down()
    {
        // グローバル状態のリセット
        wp_reset_query();
        wp_reset_postdata();
        unset($GLOBALS['post']);

        // フロントページ設定をリセット
        update_option('show_on_front', 'posts');
        update_option('page_on_front', 0);
        update_option('page_for_posts', 0);

        // 親クラスのティアダウンを呼び出す (重要)
        parent::tear_down();
    }

    /**
     * ヘルパー関数: 指定されたテンプレートパートの出力を取得
     * 注意: この関数はループ内で呼び出されることを想定
     * @param string $slug
     * @param string|null $name
     * @return string
     */
    private function get_template_part_output(string $slug, ?string $name = null): string
    {
        ob_start();
        get_template_part($slug, $name);
        return ob_get_clean();
    }


    /**
     * @test
     * ホームページ (投稿あり) でメインループが実行され、投稿コンテンツが表示されることを確認。
     */
    public function test_home_template_with_posts()
    {
        // ホームページにアクセス
        $this->go_to(home_url('/'));

        // home.php が使用されることを確認 (テンプレート階層の確認)
        $this->assertTrue(is_home(), 'Query should be is_home()');
        $this->assertTrue($GLOBALS['wp_query']->is_main_query(), 'Should be the main query');

        // メインループを手動でシミュレートし、出力を結合
        $main_content_output = '';
        $found_posts = 0;
        if (have_posts()) {
            while (have_posts()) {
                the_post();
                // content.php (またはデフォルト) の出力を取得
                $main_content_output .= $this->get_template_part_output('template-parts/content', get_post_format());
                $found_posts++;
            }
            wp_reset_postdata(); // ループ後にリセット
        }

        // --- シンプルなアサーション ---
        // 1. 投稿が見つかったか (ループが実行されたか)
        $this->assertGreaterThan(0, $found_posts, 'Expected have_posts() to find posts.');

        // 2. 結合された出力が空でないか
        $this->assertNotEmpty($main_content_output, 'Combined output from template parts should not be empty.');

        // 3. 少なくとも1つの投稿タイトルが含まれているか (よりシンプルに)
        $first_post = get_post(self::$post_ids[0]); // 最初に作成された投稿のIDを取得
        $this->assertStringContainsString(
            esc_html($first_post->post_title),
            $main_content_output,
            'At least one post title should be present in the output.'
        );

        // 4. (オプション) content.php が <article> を出力することを確認
        $this->assertStringContainsString(
            '<article id="post-', // IDまでチェックせず、開始タグがあるかだけ確認
            $main_content_output,
            'An <article> tag (likely from content.php) should be present.'
        );
    }

    /**
     * @test
     * ホームページ (投稿なし) でコンテンツなしのメッセージが表示されることを確認。
     */
    public function test_home_template_without_posts()
    {
        // 投稿が見つからないようにクエリを変更
        $this->go_to(home_url('/?post_type=nonexistent'));

        $this->assertTrue(is_home(), 'Query should be is_home() even with no posts found');
        $this->assertFalse(have_posts(), 'have_posts() should return false');

        // コンテンツなしテンプレートパートの出力を取得
        $content_none_output = $this->get_template_part_output('template-parts/content', 'none');

        // --- シンプルなアサーション ---
        // 1. 出力が空でないか
        $this->assertNotEmpty($content_none_output, 'Content none output should not be empty.');

        // 2. 「見つからない」ことを示す主要なテキストが含まれているか (テーマに合わせてどちらかを選択)
        $this->assertStringContainsString(
            'Nothing Found', // または 'No posts found.' など、content-none.php の実際の出力に合わせる
            $content_none_output,
            'A message indicating "nothing found" should be present.'
        );

        // 3. (オプション) content-none.php のラッパー要素を確認
        $this->assertStringContainsString(
            '<section class="no-results not-found">',
            $content_none_output,
            'The wrapper element for "content-none" should be present.'
        );
    }
}
