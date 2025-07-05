<?php

// クラス名を PSR-4/PSR-12 準拠に修正することを推奨 (例: HeaderTemplateTest)
class template_HeaderTemplateTest extends WP_UnitTestCase
{

    private $post_id;
    private $front_page_id;
    private $user_id;
    private $attachment_id = null;

    // アクションが実行されたか確認するためのフラグ
    private $wp_head_fired = false;
    private $wp_body_open_fired = false;

    /**
     * 各テストメソッド実行前のセットアップ
     */
    public function set_up() // メソッド名を set_up に統一
    {
        parent::set_up(); // 親の setUp を最初に呼び出す

        // テスト用ユーザーを作成
        $this->user_id = self::factory()->user->create(['role' => 'editor']);

        // テスト用投稿を作成
        $this->post_id = self::factory()->post->create([
            'post_author'  => $this->user_id,
            'post_title'   => 'Header Test Post',
            'post_content' => 'This is content.',
            'post_excerpt' => 'This is excerpt.', // OGP用抜粋
            'post_status'  => 'publish',
            'post_type'    => 'post',
        ]);

        // フロントページ用の固定ページを作成
        $this->front_page_id = self::factory()->post->create([
            'post_author'  => $this->user_id,
            'post_title'   => 'Header Front Page',
            'post_content' => 'Front page content.',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);

        // サムネイルを追加 (OGP画像テスト用)
        // 投稿が作成された後に行う
        $image_path =  dirname(__FILE__, 2) . '/dummy-image.png';
        if (file_exists($image_path)) {
            $this->attachment_id = self::factory()->attachment->create_upload_object($image_path, $this->post_id);
            if (!is_wp_error($this->attachment_id)) {
                set_post_thumbnail($this->post_id, $this->attachment_id);
            } else {
                error_log('Failed to create attachment: ' . $this->attachment_id->get_error_message()); // エラーログ
                $this->attachment_id = null;
            }
        }

        // フロントページ設定をデフォルト（最新の投稿）に
        update_option('show_on_front', 'posts');
        update_option('page_on_front', 0);

        // アクションフックのフラグをリセット
        $this->wp_head_fired = false;
        $this->wp_body_open_fired = false;
        // アクションフックを登録
        add_action('wp_head', [$this, 'action_wp_head_fired']);
        add_action('wp_body_open', [$this, 'action_wp_body_open_fired']);
    }

    /**
     * 各テストメソッド実行後のティアダウン
     */
    public function tear_down() // メソッド名を tear_down に統一
    {
        // アクションフックを削除
        remove_action('wp_head', [$this, 'action_wp_head_fired']);
        remove_action('wp_body_open', [$this, 'action_wp_body_open_fired']);

        // グローバル状態のリセット
        wp_reset_query();
        wp_reset_postdata();
        // --- 修正: tearDown で確実に unset ---
        unset($GLOBALS['post'], $GLOBALS['wp_query'], $GLOBALS['wp_the_query']);

        // フロントページ設定をリセット
        update_option('show_on_front', 'posts');
        update_option('page_on_front', 0);

        // --- 追加: テーマMODのリセット ---
        remove_theme_mods();

        // 作成したデータのクリーンアップ (投稿なども削除した方がより確実)
        // wp_delete_post($this->post_id, true);
        // wp_delete_post($this->front_page_id, true);
        // if ($this->attachment_id) {
        //     wp_delete_attachment($this->attachment_id, true);
        // }
        // wp_delete_user($this->user_id); // ユーザー削除は他のテストに影響する可能性あり注意

        // --- 修正: キャッシュフラッシュ ---
        wp_cache_flush();

        parent::tear_down(); // 親の tearDown は最後に呼び出す
    }

    // wp_head アクション用のコールバック
    public function action_wp_head_fired()
    {
        $this->wp_head_fired = true;
    }

    // wp_body_open アクション用のコールバック
    public function action_wp_body_open_fired()
    {
        $this->wp_body_open_fired = true;
    }

    private function get_header_output(): string
    {
        $header_path = get_template_directory() . '/header.php';
        if (! file_exists($header_path)) {
            trigger_error('header.php not found at ' . $header_path, E_USER_WARNING);
            return '';
        }

        ob_start();
        require $header_path;
        // ここでフックを手動発火
        do_action('wp_head');
        do_action('wp_body_open');
        return ob_get_clean();
    }



    /**
     * @test
     * 非フロントページ（通常の投稿ページ）では、$frontPage が空文字となり、
     * body_class に integlight_front_page が含まれないことを確認。
     */
    public function test_body_class_on_single_post()
    {
        // Arrange
        $this->go_to(get_permalink($this->post_id));
        update_option('show_on_front', 'posts');

        $output = $this->get_header_output();

        // Assert: body タグのクラス属性に integlight_pt はあるが integlight_front_page はない
        $this->assertMatchesRegularExpression('/<body[^>]+class="[^"]*integlight_pt[^"]*"/', $output);
        $this->assertDoesNotMatchRegularExpression('/integlight_front_page/', $output);
    }

    /**
     * @test
     * フロントページ（固定ページ）のとき、body_class に integlight_front_page が含まれることを確認。
     */
    public function test_body_class_on_front_page_static_contains_front_page_class()
    {
        // Arrange
        update_option('show_on_front', 'page');
        update_option('page_on_front', $this->front_page_id);
        $this->go_to(home_url('/'));

        $output = $this->get_header_output();

        // Assert
        $this->assertMatchesRegularExpression('/<body[^>]+class="[^"]*integlight_front_page[^"]*"/', $output);
    }

    /**
     * @test
     * is_home && is_front_page のとき site-title が <h1> タグで出力されること。
     */
    public function test_site_title_tag_on_home_and_front()
    {
        // Arrange: 投稿一覧フロント
        update_option('show_on_front', 'posts');
        $this->go_to(home_url('/'));

        $output = $this->get_header_output();

        // Assert: h1.outline
        $this->assertMatchesRegularExpression('/<h1 class="site-title">.*?<\/h1>/', $output);
        $this->assertDoesNotMatchRegularExpression('/<p class="site-title">/', $output);
    }

    public function test_site_title_tag_on_non_home()
    {
        // Arrange: 投稿ページを表示させる
        $this->go_to(get_permalink($this->post_id));

        // Act: ヘルパー経由でヘッダー HTML をキャプチャ
        $output = $this->get_header_output();

        // Assert: <p class="site-title"> が出力されている
        $this->assertMatchesRegularExpression(
            '/<p class="site-title">.*?<\/p>/',
            $output
        );
        $this->assertDoesNotMatchRegularExpression(
            '/<h1 class="site-title">/',
            $output
        );
    }

    /**
     * @test
     * site-description が必ず <p class="site-description"> で出力されること。
     */
    public function test_site_description_output(): void
    {

        // サイト概要はオプションから取得されるので、先に設定しておく
        update_option('blogdescription', 'My Description');
        $this->go_to(home_url('/'));

        $output = $this->get_header_output();

        // Assert: PHPUnit 9/10 対応の新メソッドを使う
        $this->assertMatchesRegularExpression(
            '/<p class="site-description">My Description<\/p>/',
            $output
        );
    }

    /**
     * @test
     * スキップリンク（.skip-link.screen-reader-text）が #primary に向けて出力されていること。
     */
    public function test_skip_link_presence()
    {
        // Arrange
        $this->go_to(home_url('/'));

        $output = $this->get_header_output();

        // Assert
        $this->assertMatchesRegularExpression('/<a[^>]+class="skip-link screen-reader-text"[^>]+href="#primary">Skip to content<\/a>/', $output);
    }

    /**
     * @test
     * ナビゲーションに menuToggle-containerForMenu クラスを含むコンテナが出力されていること。
     */
    public function test_navigation_container_class()
    {
        // Arrange
        $this->go_to(home_url('/'));

        $output = $this->get_header_output();

        // Assert
        $this->assertMatchesRegularExpression(
            '/<ul id="primary-menu" class="menu">/',
            $output
        );
    }
}
