<?php

/**
 * Class PageTemplateTest
 *
 * Tests for the page.php template file.
 *
 * @package Integlight
 */
class PageTemplateTest extends WP_UnitTestCase
{
    private $page_id;
    private $front_page_id;
    private $user_id;

    /**
     * 各テストメソッド実行前のセットアップ
     */
    public function set_up()
    {
        parent::set_up();

        // content-slide.php でのエラーを防ぐために、グローバルスライダー設定オブジェクトを初期化します。
        // これは、テスト環境がライブサイトのようにすべてのテーマグローバル変数を
        // 完全に初期化しない可能性があるため必要です。
        global $Integlight_slider_settings;
        if (!is_object($Integlight_slider_settings)) {
            $Integlight_slider_settings = new stdClass();
        }
        if (!isset($Integlight_slider_settings->headerTypeName_slider)) {
            $Integlight_slider_settings->headerTypeName_slider = 'slider'; // テスト用のデフォルト値
        }


        // テスト用ユーザーを作成
        $this->user_id = self::factory()->user->create(['role' => 'editor']);

        // 通常ページを作成
        $this->page_id = self::factory()->post->create([
            'post_author'  => $this->user_id,
            'post_title'   => 'Regular Page Title',
            'post_content' => 'This is the regular page content.',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);

        // フロントページを作成
        $this->front_page_id = self::factory()->post->create([
            'post_author'  => $this->user_id,
            'post_title'   => 'Front Page Title',
            'post_content' => 'This is the front page content.',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);

        // フロントページ設定
        update_option('show_on_front', 'page');
        update_option('page_on_front', $this->front_page_id);
    }

    /**
     * 各テストメソッド実行後のティアダウン
     */
    public function tear_down()
    {
        wp_reset_query();
        wp_reset_postdata();
        unset($GLOBALS['post'], $GLOBALS['wp_query'], $GLOBALS['wp_the_query']);

        update_option('show_on_front', 'posts');
        update_option('page_on_front', 0);

        parent::tear_down();
    }

    /**
     * ヘルパー: 実際の page.php を読み込み出力を取得
     *
     * @param string $url URL
     * @return string HTML 出力
     */
    private function get_full_template_output(string $url): string
    {
        // URL に移動
        $this->go_to($url);

        // グローバルクエリ設定
        global $wp_query, $wp_the_query, $post;
        $wp_the_query = $wp_query = $GLOBALS['wp_the_query'];

        // 投稿データをセットアップ
        if (isset($wp_query->post)) {
            $post = $wp_query->post;
            setup_postdata($post);
            $GLOBALS['post'] = $post;
        }

        // 出力キャプチャ
        ob_start();
        $template = get_page_template();
        if (file_exists($template)) {
            require $template;
        } else {
            trigger_error("page.php not found: {$template}", E_USER_WARNING);
        }
        return ob_get_clean();
    }

    /** @test */
    public function test_page_template_loads_on_regular_page()
    {
        $url = get_permalink($this->page_id);
        $output = $this->get_full_template_output($url);

        $this->assertNotEmpty($output, 'Template output should not be empty.');
        $this->assertStringContainsString('<main id="primary"', $output);
        $this->assertStringContainsString('<article id="post-' . $this->page_id . '"', $output);
        $this->assertStringContainsString('Regular Page Title', $output);
        $this->assertStringContainsString('This is the regular page content.', $output);
    }

    /** @test */
    public function test_page_template_loads_on_front_page()
    {
        $url = home_url('/');
        $output = $this->get_full_template_output($url);

        $this->assertNotEmpty($output, 'Template output should not be empty.');
        $this->assertStringContainsString('<main id="primary"', $output);
        $this->assertStringContainsString('<article id="post-' . $this->front_page_id . '"', $output);
        $this->assertStringContainsString('Front Page Title', $output);
        $this->assertStringContainsString('This is the front page content.', $output);
        $this->assertStringNotContainsString('<aside id="secondary"', $output);
    }

    /** @test */
    public function test_page_template_shows_comments_when_open()
    {
        wp_update_post(['ID' => $this->page_id, 'comment_status' => 'open']);
        self::factory()->comment->create(['comment_post_ID' => $this->page_id]);
        $url = get_permalink($this->page_id);

        $output = $this->get_full_template_output($url);

        $this->assertStringContainsString('<div id="comments"', $output);
        $this->assertStringContainsString('<div id="respond"', $output);
    }

    /** @test */
    public function test_page_template_hides_comments_when_closed()
    {
        wp_update_post(['ID' => $this->page_id, 'comment_status' => 'closed']);
        self::factory()->comment->create(['comment_post_ID' => $this->page_id]);
        $url = get_permalink($this->page_id);

        $output = $this->get_full_template_output($url);

        $this->assertStringNotContainsString('<div id="respond"', $output);
        $this->assertStringContainsString('<div id="comments"', $output);
        $this->assertStringContainsString('Comments are closed.', $output);
    }
}
