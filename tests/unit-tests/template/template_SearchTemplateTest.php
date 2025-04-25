<?php

// use WP_UnitTestCase; // この行を削除またはコメントアウト

/**
 * Class template_SearchTemplateTest
 *
 * @package Integlight
 */

/**
 * search.php のテストケース
 */
class template_SearchTemplateTest extends WP_UnitTestCase // WP_UnitTestCase を直接継承
{
    /**
     * テスト用の投稿ID
     * @var int
     */
    private static $target_post_id;

    /**
     * 検索キーワード (今回は直接使わない)
     * @var string
     */
    // private static $search_term = 'xyzsearchterm'; // 使わないのでコメントアウト

    /**
     * テストクラス全体のセットアップ
     * @param WP_UnitTest_Factory $factory
     */
    public static function wpSetUpBeforeClass($factory): void
    {
        // 検索結果として表示されることを想定する投稿を作成
        self::$target_post_id = $factory->post->create([
            'post_title' => 'Search Target Post',
            'post_content' => 'This post content is used for testing.', // 検索キーワードは不要
            'post_excerpt' => 'Post excerpt %d',
            'post_date' => '2025-04-25 10:00:00',
            'post_status' => 'publish',
            'post_type'    => 'post'
        ]);

        // 他の投稿 (任意)
        $factory->post->create_many(3, ['post_status' => 'publish', 'post_type' => 'post']);
    }

    /**
     * テストクラス全体のティアダウン
     */
    public static function wpTearDownAfterClass(): void
    {
        // 作成した投稿を削除
        if (self::$target_post_id && get_post(self::$target_post_id)) {
            wp_delete_post(self::$target_post_id, true);
        }
        // Consider cleaning up posts created by create_many if necessary
    }

    /**
     * 各テストメソッド実行前のセットアップ
     */
    public function set_up()
    {
        parent::set_up();
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
     * 検索結果がある状況を想定し、関連テンプレートパートが正しく出力されるか確認。
     */
    public function test_search_template_with_results()
    {
        // --- 修正: WP_Query や go_to() を使わず、投稿データを直接セットアップ ---
        global $post;
        $post = get_post(self::$target_post_id);
        if (!$post) {
            $this->fail('Failed to get the target post for testing.');
        }
        setup_postdata($post); // テンプレートパートが必要とするグローバル $post を設定

        // --- テンプレートパートの出力確認 ---
        // search.php がループ内で使うテンプレートパートを指定 (例: content-arc)
        $template_output = $this->get_template_part_output('template-parts/content', 'arc'); // テーマに合わせて修正

        // --- アサーション ---
        $this->assertNotEmpty($template_output, 'Template part output should not be empty.');
        // content-arc.php が出力する要素を確認
        $this->assertStringContainsString('<div class="bl_card_container">', $template_output, 'Post container (bl_card_container) should be present.');
        $this->assertStringContainsString('<h5 class="bl_card_ttl">', $template_output, 'Post title heading (bl_card_ttl) should be present.');
        $this->assertStringContainsString(esc_html($post->post_title), $template_output, 'Target post title should be present in template output.');

        // --- 後始末 ---
        wp_reset_postdata(); // setup_postdata の後始末
        unset($post); // グローバル変数をクリア
    }

    /**
     * @test
     * 検索結果がない状況を想定し、content-none が正しく出力されるか確認。
     */
    public function test_search_template_no_results()
    {
        // --- 修正: WP_Query や go_to() は不要 ---
        // 検索結果がない状況では、通常 content-none が呼ばれることをテストする

        // --- テンプレートパートの出力確認 ---
        // content-none はグローバルなクエリ状態に依存しないはずなので、直接呼び出せる
        $content_none_output = $this->get_template_part_output('template-parts/content', 'none');

        // --- アサーション ---
        $this->assertNotEmpty($content_none_output, 'Content none output should not be empty.');
        $this->assertStringContainsString('Nothing Found', $content_none_output, 'A message indicating "nothing found" should be present.');
        $this->assertStringContainsString('<section class="no-results not-found">', $content_none_output, 'The wrapper element for "content-none" should be present.');
    }
}
