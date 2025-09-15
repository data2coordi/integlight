<?php

/**
 * @package integlight
 */

class template_HomeTemplateTest extends WP_UnitTestCase
{
    private string $long_title;
    private string $short_title;
    private int $parent_cat_id;

    public function setUp(): void
    {
        parent::setUp();

        // 長いタイトル（30文字）
        $this->long_title = str_repeat('a', 30);

        // 短いタイトル（10文字）
        $this->short_title = str_repeat('b', 10);

        // 抜粋・タイトル用の投稿を作成
        $this->factory()->post->create_many(10, [
            'post_status'  => 'publish',
            'post_date'    => current_time('mysql'),
            'post_content' => str_repeat('x', 300),  // 300文字の本文
            'post_title'   => $this->long_title,
        ]);
        $this->factory()->post->create_many(5, [
            'post_status'  => 'publish',
            'post_date'    => current_time('mysql'),
            'post_content' => '短い本文',
            'post_title'   => $this->short_title,
        ]);

        // カテゴリ作成
        $this->parent_cat_id = $this->factory()->category->create([
            'name'   => '親カテゴリ',
            'parent' => 0,
            'slug'   => 'parent-cat',
        ]);
    }

    public function test_home_template_is_loaded()
    {
        $this->go_to(home_url('/'));
        $this->assertTrue(is_home());

        ob_start();
        include get_template_directory() . '/home.php';
        $output = ob_get_clean();

        // home.php の main 要素確認
        $this->assertStringContainsString('<main id="primary"', $output);
    }

    public function test_post_title_trimmed()
    {
        $this->go_to(home_url('/'));

        ob_start();
        include get_template_directory() . '/home.php';
        $output = ob_get_clean();

        // 長いタイトルは25文字＋「 ...」で切り詰め
        $expected_trim = mb_substr($this->long_title, 0, 25) . ' ...';
        $this->assertStringContainsString($expected_trim, $output);

        // 短いタイトルはそのまま
        $this->assertStringContainsString($this->short_title, $output);
    }

    public function test_post_excerpt_length()
    {
        $this->go_to(home_url('/'));

        ob_start();
        include get_template_directory() . '/home.php';
        $output = ob_get_clean();

        // 抜粋部分のテキストだけ抽出
        preg_match('/<p class="post-excerpt">\s*(.+?)\s*<\/p>/s', $output, $matches);
        $excerpt_text = strip_tags($matches[1]);

        // 連続50文字の"x"がない
        $this->assertDoesNotMatchRegularExpression('/x{50,}/', $excerpt_text);

        // 抜粋文字数は200文字以下
        $this->assertLessThanOrEqual(200, mb_strlen($excerpt_text));
    }

    public function test_pagination_is_present()
    {
        $this->go_to(home_url('/'));

        ob_start();
        include get_template_directory() . '/home.php';
        $output = ob_get_clean();

        // ページネーションHTML確認
        $this->assertStringContainsString('class="page-numbers"', $output);
    }

    public function test_top_category_listed()
    {
        $this->go_to(home_url('/'));

        ob_start();
        include get_template_directory() . '/home.php';
        $output = ob_get_clean();

        // 親カテゴリ名と一覧コンテナ
        $this->assertStringContainsString('親カテゴリ', $output);
        $this->assertStringContainsString('category-list', $output);
    }

    public function test_post_thumbnail_present()
    {
        $this->go_to(home_url('/'));

        ob_start();
        include get_template_directory() . '/home.php';
        $output = ob_get_clean();

        // デフォルトサムネイル画像確認
        $this->assertMatchesRegularExpression('/<img.*src=.*default\\.webp.*>/i', $output);
    }

    public function test_post_date_displayed()
    {
        $this->go_to(home_url('/'));

        ob_start();
        include get_template_directory() . '/home.php';
        $output = ob_get_clean();

        // Published on の文言確認
        $this->assertStringContainsString('Published on', $output);
    }

    public function test_no_posts_message()
    {
        // すべての投稿を削除
        $all = get_posts(['numberposts' => -1, 'post_status' => 'publish']);
        foreach ($all as $post) {
            wp_delete_post($post->ID, true);
        }

        $this->go_to(home_url('/'));

        ob_start();
        include get_template_directory() . '/home.php';
        $output = ob_get_clean();

        // 投稿なしメッセージ確認
        $this->assertStringContainsString('No posts found.', $output);
    }

    public function test_home_type_class_is_applied()
    {
        // カスタマイザー設定を home2 に変更
        set_theme_mod('integlight_hometype_setting', 'home2');

        $this->go_to(home_url('/'));

        ob_start();
        include get_template_directory() . '/home.php';
        $output = ob_get_clean();

        // <main> のクラスが "home2" になっているか確認
        $this->assertMatchesRegularExpression(
            '/<main id="primary" class=["\']?home2["\']?>/',
            $output
        );

        // デフォルト（設定なし）の場合は "home1" になる
        remove_theme_mod('integlight_hometype_setting');
        ob_start();
        include get_template_directory() . '/home.php';
        $output = ob_get_clean();

        $this->assertMatchesRegularExpression(
            '/<main id="primary" class=["\']?home1["\']?>/',
            $output
        );
    }
}
