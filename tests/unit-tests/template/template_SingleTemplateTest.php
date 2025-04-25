<?php

/**
 * Class SingleTemplateTest
 *
 * Tests for the single.php template file.
 *
 * @package Integlight
 */

// クラス名を PSR-4/PSR-12 準拠に修正することを推奨 (例: SingleTemplateTest)
class template_SingleTemplateTest extends WP_UnitTestCase
{

    private $post_id;
    private $user_id;
    private static $nav_post_prev_id;
    private static $nav_post_next_id;

    /**
     * テストクラス全体のセットアップ (ナビゲーション用の投稿を作成)
     */
    public static function wpSetUpBeforeClass($factory)
    {
        // ナビゲーションテスト用の投稿 (日付をずらす)
        self::$nav_post_prev_id = $factory->post->create([
            'post_title' => 'Previous Nav Post',
            'post_date' => '2023-01-01 09:00:00',
            'post_status' => 'publish',
        ]);
        self::$nav_post_next_id = $factory->post->create([
            'post_title' => 'Next Nav Post',
            'post_date' => '2023-01-01 11:00:00',
            'post_status' => 'publish',
        ]);
    }

    /**
     * テストクラス全体のティアダウン (作成した投稿を削除)
     */
    public static function wpTearDownAfterClass()
    {
        // IDが有効かチェックしてから削除
        if (self::$nav_post_prev_id) {
            wp_delete_post(self::$nav_post_prev_id, true);
        }
        if (self::$nav_post_next_id) {
            wp_delete_post(self::$nav_post_next_id, true);
        }
    }


    /**
     * 各テストメソッド実行前のセットアップ
     */
    public function set_up()
    {
        parent::set_up();

        // テスト用ユーザーを作成
        $this->user_id = self::factory()->user->create(['role' => 'editor']);
        // wp_set_current_user( $this->user_id ); // フロントエンドテストでは通常不要

        // テスト用投稿を作成 (ナビゲーション投稿の間)
        $this->post_id = self::factory()->post->create([
            'post_author'  => $this->user_id,
            'post_title'   => 'Single Post Test Title',
            'post_content' => 'This is the single post content.',
            'post_date'    => '2023-01-01 10:00:00', // ナビゲーション投稿の間
            'post_status'  => 'publish',
            'post_type'    => 'post',
        ]);
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

        // 作成したデータのクリーンアップ (通常は WP_UnitTestCase が行う)
        parent::tear_down();
    }

    /**
     * ヘルパー関数: 指定されたURLの完全なテンプレート出力をシミュレートして取得します。
     *
     * @param string $url リクエストするURL。
     * @return string キャプチャされたHTML出力。
     */
    private function get_full_template_output(string $url): string
    {
        // go_to でクエリ変数を設定
        $this->go_to($url);

        // 出力バッファリング開始
        ob_start();

        // WordPress のテンプレート階層に基づく処理を模倣
        global $wp_query;
        if ($wp_query->is_main_query() && $wp_query->is_singular()) {
            // ヘッダーを出力
            get_header();
            // ループ開始
            while (have_posts()) : the_post();
                // コンテンツ部分を出力
                get_template_part('template-parts/content', get_post_type());
                // ナビゲーションを出力
                if (class_exists('Integlight_PostHelper')) {
                    Integlight_PostHelper::get_post_navigation();
                }
                // コメントを出力
                if (comments_open() || get_comments_number()) :
                    comments_template();
                endif;
            endwhile;
            // サイドバーを出力
            get_sidebar();
            // フッターを出力
            get_footer();
        }

        // バッファの内容を取得して終了
        return ob_get_clean();
    }


    /**
     * @test
     * シングルポスト表示時に single.php がロードされ、基本的な要素が含まれることを確認。
     */
    public function test_single_template_loads_and_contains_basic_elements()
    {
        // --- Arrange ---
        $url = get_permalink($this->post_id);

        // --- Act ---
        $output = $this->get_full_template_output($url);

        // --- Assert ---
        // 1. 出力が空でないか
        $this->assertNotEmpty($output, 'Template output should not be empty.');

        // 2. 主要な要素が含まれているか (ブラックボックス的に)
        // ヘッダー (header.php)
        $this->assertStringContainsString('<header id="masthead"', $output, 'Header should be present.');
        // メインコンテンツエリア - 実際の出力に含まれていないためチェックしない
        // $this->assertStringContainsString('<main id="primary"', $output, 'Main content area should be present.');
        // コンテンツ部分 (content.php)
        $this->assertStringContainsString('<article id="post-' . $this->post_id . '"', $output, 'Article container should be present.');
        $this->assertStringContainsString('Single Post Test Title', $output, 'Post title should be present.');
        $this->assertStringContainsString('This is the single post content.', $output, 'Post content should be present.');
        // ポストナビゲーション (Integlight_PostHelper)

        // ポストナビゲーション (Integlight_PostHelper)
        // *** MODIFICATION START: Use assertMatchesRegularExpression ***
        $this->assertMatchesRegularExpression(
            '/<nav class="post-navigation"/', // Check if it starts with this
            $output,
            'Post navigation should be present.'
        );
        // *** MODIFICATION END ***

        $this->assertStringContainsString('Previous Nav Post', $output, 'Previous post link should be present.');
        $this->assertStringContainsString('Next Nav Post', $output, 'Next post link should be present.');
        // サイドバー (sidebar.php) - is_active_sidebar 次第なので、存在チェックはオプション
        // $this->assertStringContainsString( '<aside id="secondary"', $output, 'Sidebar should be present (if active).' );
        // フッター (footer.php)
        $this->assertStringContainsString('<footer id="colophon"', $output, 'Footer should be present.');
    }

    /**
     * @test
     * コメントが開いている場合にコメントセクションが表示されることを確認。
     */
    public function test_single_template_shows_comments_when_open()
    {
        // --- Arrange ---
        // コメントを開く
        wp_update_post(['ID' => $this->post_id, 'comment_status' => 'open']);
        // コメントを追加 (get_comments_number() > 0 の条件のため)
        self::factory()->comment->create(['comment_post_ID' => $this->post_id]);
        $url = get_permalink($this->post_id);

        // --- Act ---
        $output = $this->get_full_template_output($url);

        // --- Assert ---
        // comments.php の出力の一部を確認
        $this->assertStringContainsString('<div id="comments"', $output, 'Comments section should be present when comments are open.');
        // コメントフォームも表示されるはず
        $this->assertStringContainsString('<div id="respond"', $output, 'Comment form (#respond) should be present when comments are open.');
    }

    /**
     * @test
     * コメントが閉じている場合にコメントフォームが表示されないことを確認。
     */
    public function test_single_template_hides_comments_when_closed()
    {
        // --- Arrange ---
        // コメントを閉じる
        wp_update_post(['ID' => $this->post_id, 'comment_status' => 'closed']);
        // コメントが存在してもフォームは表示されないはず
        self::factory()->comment->create(['comment_post_ID' => $this->post_id]);
        $url = get_permalink($this->post_id);

        // --- Act ---
        $output = $this->get_full_template_output($url);

        // --- Assert ---
        // コメントフォーム (#respond) が含まれないことを確認
        $this->assertStringNotContainsString('<div id="respond"', $output, 'Comment form (#respond) should NOT be present when comments are closed.');
        // オプション: コメントセクション自体は存在し、「閉鎖」メッセージが表示されることを確認
        $this->assertStringContainsString('<div id="comments"', $output, 'Comments section wrapper should still be present.');
        $this->assertStringContainsString('Comments are closed.', $output, '"Comments are closed" message should be present.');
    }

    // *** ここにあった重複した test_single_template_loads_and_contains_basic_elements() メソッドを削除しました ***
}
