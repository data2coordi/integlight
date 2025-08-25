<?php

/**
 * Class SingleTemplateFullTest
 *
 * @package Integlight
 */

/**
 * single.php を実際に読み込んでテストするケース
 */
class template_SingleTemplateTest extends WP_UnitTestCase
{
    private static $post_id;
    private static $user_id;
    private static $prev_post_id;
    private static $next_post_id;

    public static function wpSetUpBeforeClass($factory): void
    {
        self::$user_id = $factory->user->create(['role' => 'author']);

        self::$prev_post_id = $factory->post->create([
            'post_title' => 'Previous Post',
            'post_date'  => '2022-12-31 10:00:00',
            'post_status' => 'publish'
        ]);

        self::$post_id = $factory->post->create([
            'post_title'   => 'Single Template Test',
            'post_content' => 'This is the content of the single post.',
            'post_author'  => self::$user_id,
            'post_date'    => '2023-01-01 10:00:00',
            'post_status'  => 'publish'
        ]);

        self::$next_post_id = $factory->post->create([
            'post_title' => 'Next Post',
            'post_date'  => '2023-01-02 10:00:00',
            'post_status' => 'publish'
        ]);

        wp_update_post(['ID' => self::$post_id, 'comment_status' => 'open']);
    }

    public static function wpTearDownAfterClass(): void
    {
        wp_delete_post(self::$post_id, true);
        wp_delete_post(self::$prev_post_id, true);
        wp_delete_post(self::$next_post_id, true);
        wp_delete_user(self::$user_id);
    }

    public function set_up(): void
    {
        parent::set_up();
    }

    public function tear_down(): void
    {
        wp_reset_query();
        wp_reset_postdata();
        unset($GLOBALS['post']);
        parent::tear_down();
    }

    /**
     * @test
     * single.php を直接読み込んだときの出力を検証する
     */
    public function test_single_php_outputs_expected_html()
    {
        global $post, $wp_query;

        // クエリと投稿データのセットアップ
        $wp_query = new WP_Query(['p' => self::$post_id]);
        $post = get_post(self::$post_id);
        setup_postdata($post);

        // single.php をフルで読み込む
        ob_start();
        require get_template_directory() . '/single.php';
        $output = ob_get_clean();

        // 検証: 投稿のHTML構造
        $this->assertStringContainsString('<article id="post-' . self::$post_id . '"', $output);
        $this->assertStringContainsString('Single Template Test', $output);
        $this->assertStringContainsString('This is the content of the single post.', $output);

        // 投稿ナビゲーションの検証
        $this->assertStringContainsString('nav-previous', $output);
        $this->assertStringContainsString('nav-next', $output);

        // コメントフォームの検証
        $this->assertStringContainsString('<div id="respond"', $output);
        $this->assertStringContainsString('<h3 id="reply-title"', $output);

        // 後始末
        wp_reset_postdata();
        wp_reset_query();
        unset($post);
    }
}
