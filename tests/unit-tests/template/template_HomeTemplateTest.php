<?php

/**
 * Class template_HomeTemplateTest
 *
 * @package Integlight
 */

/**
 * home.php のテストケース
 */
class template_HomeTemplateTest extends WP_UnitTestCase
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
        // テスト用の投稿を複数作成
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
        // フロントページ設定を「最新の投稿が表示される」に設定
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

        parent::tear_down();
    }

    /**
     * @test
     * ホームページ (投稿あり) で home.php の挙動を検証
     */
    public function test_home_template_with_posts()
    {
        // ホームページにアクセスをシミュレート
        $this->go_to(home_url('/'));

        // クエリがホームでメインクエリであることを確認
        $this->assertTrue(is_home(), 'Query should be is_home()');
        $this->assertTrue($GLOBALS['wp_query']->is_main_query(), 'Should be the main query');

        // home.php のテンプレートファイルを取得して読み込む
        $template_file = get_page_template();

        ob_start();
        require $template_file;
        $output = ob_get_clean();

        // 投稿タイトルが含まれていることを確認
        $first_post = get_post(self::$post_ids[0]);
        $this->assertStringContainsString(esc_html($first_post->post_title), $output, 'Output should contain the first post title.');

        // <article>タグが含まれていることを確認
        $this->assertStringContainsString('<article', $output, 'Output should contain article tag.');
    }

    /**
     * @test
     * ホームページ (投稿なし) で home.php の挙動を検証
     */
    public function test_home_template_without_posts()
    {
        // 投稿がないクエリをシミュレート
        $this->go_to(home_url('/?post_type=nonexistent'));

        $this->assertTrue(is_home(), 'Query should be is_home()');
        $this->assertFalse(have_posts(), 'have_posts() should return false');

        // home.php のテンプレートを取得して読み込み
        $template_file = get_page_template();

        ob_start();
        require $template_file;
        $output = ob_get_clean();


        // content-none.php のラッパー要素が含まれているかも確認
        $this->assertStringContainsString('<main id="primary" class="site-main ly_site_content_main">', $output, 'Output should contain no-results wrapper.');
    }
}
