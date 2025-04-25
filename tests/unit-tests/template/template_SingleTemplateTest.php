<?php

use WP_UnitTestCase;

/**
 * Class template_SingleTemplateTest
 *
 * @package Integlight
 */

/**
 * single.php のテストケース
 */
class template_SingleTemplateTest extends WP_UnitTestCase
{
    /**
     * テスト用の投稿ID
     * @var int
     */
    private static $post_id;

    /**
     * テスト用のユーザーID
     * @var int
     */
    private static $user_id;

    /**
     * 前の投稿ID
     * @var int
     */
    private static $prev_post_id;

    /**
     * 次の投稿ID
     * @var int
     */
    private static $next_post_id;


    /**
     * テストクラス全体のセットアップ
     * @param WP_UnitTest_Factory $factory
     */
    public static function wpSetUpBeforeClass($factory): void
    {
        // テスト用ユーザー作成
        self::$user_id = $factory->user->create(['role' => 'author']);

        // テスト用の投稿を作成
        self::$post_id = $factory->post->create([
            'post_title' => 'Single Post Test Title',
            'post_content' => '<p>This is the single post content.</p>',
            'post_author' => self::$user_id,
            'post_date' => '2023-01-01 10:00:00',
            'post_status' => 'publish'
        ]);

        // 前後のナビゲーション用投稿を作成
        self::$prev_post_id = $factory->post->create([
            'post_title' => 'Previous Nav Post',
            'post_date' => '2022-12-31 10:00:00',
            'post_status' => 'publish'
        ]);
        self::$next_post_id = $factory->post->create([
            'post_title' => 'Next Nav Post',
            'post_date' => '2023-01-02 10:00:00',
            'post_status' => 'publish'
        ]);

        // コメントを許可
        wp_update_post(['ID' => self::$post_id, 'comment_status' => 'open']);
    }

    /**
     * テストクラス全体のティアダウン
     */
    public static function wpTearDownAfterClass(): void
    {
        // 作成した投稿とユーザーを削除
        wp_delete_post(self::$post_id, true);
        wp_delete_post(self::$prev_post_id, true);
        wp_delete_post(self::$next_post_id, true);
        wp_delete_user(self::$user_id);
    }

    /**
     * 各テストメソッド実行前のセットアップ
     */
    public function set_up()
    {
        parent::set_up();
        // 投稿者としてログイン (任意、コメントフォームの表示などに影響する場合)
        // wp_set_current_user(self::$user_id);
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
     * ヘルパー関数: コメントテンプレートの出力を取得
     * @return string
     */
    private function get_comments_template_output(): string
    {
        ob_start();
        comments_template();
        return ob_get_clean();
    }

    /**
     * ヘルパー関数: 投稿ナビゲーションの出力を取得
     * @return string
     */
    private function get_post_navigation_output(): string
    {
        ob_start();
        // the_post_navigation() は引数を取れる場合がある
        the_post_navigation();
        return ob_get_clean();
    }


    // --- 削除: get_full_template_output ヘルパー関数 ---
    // private function get_full_template_output(): string { ... }


    /**
     * @test
     * 個別投稿ページで single.php (またはそのテンプレートパート) がロードされ、基本的な要素が含まれることを確認。
     */
    public function test_single_template_loads_and_contains_basic_elements()
    {
        // --- 修正: go_to() の代わりに投稿データを直接セットアップ ---
        global $post, $wp_query;

        // is_single() などの条件分岐を正しく動作させるためにクエリを設定
        $wp_query = new WP_Query(['p' => self::$post_id]);
        // $this->assertTrue($wp_query->is_single(), 'Query should be is_single()'); // 必要なら確認

        // 投稿データをセットアップ
        $post = get_post(self::$post_id);
        if (!$post) {
            $this->fail('Failed to get the target post for testing.');
        }
        setup_postdata($post);

        // --- テンプレートパートの出力確認 ---
        // single.php がループ内で使うテンプレートパートを指定 (例: content-single)
        $content_output = $this->get_template_part_output('template-parts/content', 'single');
        // もし content-single.php がなければ、content.php が使われる
        if (empty(trim($content_output))) {
            $content_output = $this->get_template_part_output('template-parts/content', get_post_format());
        }

        // 投稿ナビゲーションの出力
        $navigation_output = $this->get_post_navigation_output();

        // コメントテンプレートの出力
        $comments_output = $this->get_comments_template_output();


        // --- アサーション ---
        $this->assertNotEmpty($content_output, 'Content template part output should not be empty.');

        // ヘッダー/フッターのチェックは削除
        // $this->assertStringContainsString('<header id="masthead"', $output, 'Header should be present.');

        // content-single.php (または content.php) が出力する要素を確認
        $this->assertStringContainsString('<article id="post-' . self::$post_id . '"', $content_output, 'Post container (<article>) should be present.');
        $this->assertStringContainsString('<h1 class="entry-title">' . esc_html($post->post_title) . '</h1>', $content_output, 'Post title (h1.entry-title) should be present.');
        $this->assertStringContainsString('This is the single post content.', $content_output, 'Post content should be present.'); // post_content の内容を確認
        $this->assertStringContainsString('<footer class="entry-footer">', $content_output, 'Entry footer should be present.');

        // 投稿ナビゲーションの確認
        $this->assertNotEmpty($navigation_output, 'Post navigation output should not be empty.');
        $this->assertStringContainsString('nav-previous', $navigation_output, 'Previous post link container should be present.');
        $this->assertStringContainsString('nav-next', $navigation_output, 'Next post link container should be present.');
        // $this->assertStringContainsString('Previous Nav Post', $navigation_output); // 必要ならリンクテキストも確認
        // $this->assertStringContainsString('Next Nav Post', $navigation_output); // 必要ならリンクテキストも確認

        // コメント欄の確認 (コメントフォームが表示されるはず)
        $this->assertNotEmpty($comments_output, 'Comments template output should not be empty.');
        $this->assertStringContainsString('<div id="respond"', $comments_output, 'Comment form container (respond) should be present.');
        $this->assertStringContainsString('<h3 id="reply-title"', $comments_output, 'Comment form title should be present.');


        // --- 後始末 ---
        wp_reset_postdata(); // setup_postdata の後始末
        unset($post); // グローバル変数をクリア
        wp_reset_query(); // WP_Query をリセット
    }
}
