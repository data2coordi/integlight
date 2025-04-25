<?php

/**
 * Class TemplateTagsTest
 *
 * @package Integlight
 */

/**
 * Tests for template tag functions.
 *
 * @coversDefaultClass \Integlight\Template_Tags
 */
class template_tags_TemplateTagsTest extends WP_UnitTestCase
{

    private $post_id;
    private $user_id;

    public function set_up()
    {
        parent::set_up();

        // Create a user
        $this->user_id = self::factory()->user->create(array('role' => 'editor', 'display_name' => 'Test Author'));
        wp_set_current_user($this->user_id); // Set the current user for edit links etc.

        // Create a post
        $this->post_id = self::factory()->post->create(array(
            'post_author'  => $this->user_id,
            'post_title'   => 'Test Post Title',
            'post_content' => 'Test content.',
            'post_date'    => '2023-10-26 10:00:00', // Specific date for testing
            'post_status'  => 'publish',
            'post_type'    => 'post',
        ));

        // Set the global post object for template tags
        global $post;
        $post = get_post($this->post_id);
        setup_postdata($post); // Important for template tags like get_the_ID(), get_the_title() etc.
    }

    public function tear_down()
    {
        // Reset global post object and query
        wp_reset_postdata();
        wp_reset_query();
        unset($GLOBALS['post']);
        wp_set_current_user(0); // Log out user
        parent::tear_down();
    }

    /**
     * Test integlight_posted_on() output.
     * @covers ::integlight_posted_on
     */
    public function test_integlight_posted_on()
    {
        // Test with only published date
        ob_start();
        integlight_posted_on();
        $output = ob_get_clean();

        $expected_time_html = sprintf(
            '<time class="entry-date published updated" datetime="%1$s">%2$s</time>',
            esc_attr(get_the_date(DATE_W3C, $this->post_id)),
            esc_html(get_the_date('', $this->post_id))
        );
        $expected_link = sprintf('<a href="%s" rel="bookmark">%s</a>', esc_url(get_permalink($this->post_id)), $expected_time_html);
        $this->assertStringContainsString('<span class="posted-on">Posted on <i class="fa-solid fa-calendar-days"></i>', $output);
        $this->assertStringContainsString($expected_link . '</span>', $output);

        // Test with different modified date
        $modified_date = '2023-10-27 11:00:00';
        wp_update_post(array('ID' => $this->post_id, 'post_modified' => $modified_date));
        // Refresh post data
        global $post;
        $post = get_post($this->post_id);
        setup_postdata($post);

        ob_start();
        integlight_posted_on();
        $output_modified = ob_get_clean();

        $expected_time_html_modified = sprintf(
            '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>',
            esc_attr(get_the_date(DATE_W3C, $this->post_id)),
            esc_html(get_the_date('', $this->post_id)),
            esc_attr(get_the_modified_date(DATE_W3C, $this->post_id)),
            esc_html(get_the_modified_date('', $this->post_id))
        );
        $expected_link_modified = sprintf('<a href="%s" rel="bookmark">%s</a>', esc_url(get_permalink($this->post_id)), $expected_time_html_modified);
        $this->assertStringContainsString('<span class="posted-on">Posted on <i class="fa-solid fa-calendar-days"></i>', $output_modified);
        $this->assertStringContainsString($expected_link_modified . '</span>', $output_modified);
    }

    /**
     * Test integlight_posted_by() output.
     * @covers ::integlight_posted_by
     */
    public function test_integlight_posted_by()
    {
        ob_start();
        integlight_posted_by();
        $output = ob_get_clean();

        $expected_byline = sprintf(
            esc_html_x('by %s', 'post author', 'integlight'),
            '<span class="author vcard"><a class="url fn n" href="' . esc_url(get_author_posts_url($this->user_id)) . '">' . esc_html(get_the_author_meta('display_name', $this->user_id)) . '</a></span>'
        );
        $this->assertStringContainsString('<span class="byline"> ' . $expected_byline . '</span>', $output);
    }

    /**
     * Test integlight_entry_footer() output.
     * @covers ::integlight_entry_footer
     */
    public function test_integlight_entry_footer()
    {
        // --- Test with Categories and Tags ---
        $cat_id = self::factory()->category->create(array('name' => 'Test Category'));
        $tag_id = self::factory()->tag->create(array('name' => 'Test Tag'));
        wp_set_post_categories($this->post_id, array($cat_id));
        wp_set_post_tags($this->post_id, array('Test Tag'));

        // Refresh post data after adding terms
        global $post;
        $post = get_post($this->post_id);
        setup_postdata($post);

        ob_start();
        integlight_entry_footer();
        $output = ob_get_clean();

        // Check for category link
        $this->assertStringContainsString('<span class="cat-links">', $output);
        $this->assertStringContainsString(esc_url(get_category_link($cat_id)), $output); // Use esc_url for consistency
        $this->assertStringContainsString('Test Category', $output);

        // Check for tag link
        $this->assertStringContainsString('<span class="tags-links">', $output);
        $this->assertStringContainsString(esc_url(get_tag_link($tag_id)), $output); // Use esc_url for consistency
        $this->assertStringContainsString('Test Tag', $output);

        // --- Test Comments Link (Open) ---
        // Ensure comments are open for this post
        wp_update_post(array('ID' => $this->post_id, 'comment_status' => 'open'));
        $post = get_post($this->post_id); // Refresh post data
        setup_postdata($post);

        ob_start();
        integlight_entry_footer();
        $output_comments_open = ob_get_clean();
        $this->assertStringContainsString('<span class="comments-link">', $output_comments_open);
        $this->assertStringContainsString(__('Leave a Comment', 'integlight'), $output_comments_open);

        // --- Test Comments Link (Closed, No Comments) ---
        wp_update_post(array('ID' => $this->post_id, 'comment_status' => 'closed'));
        $post = get_post($this->post_id); // Refresh post data
        setup_postdata($post);

        ob_start();
        integlight_entry_footer();
        $output_comments_closed = ob_get_clean();
        // comments_popup_link won't output if comments are closed AND there are 0 comments
        $this->assertStringNotContainsString('<span class="comments-link">', $output_comments_closed);

        // --- Test Edit Link (User Can Edit) ---
        // User is already set to editor in setUp
        ob_start();
        integlight_entry_footer();
        $output_edit = ob_get_clean();
        $this->assertStringContainsString('<span class="edit-link">', $output_edit);
        // *** MODIFIED HERE: Use esc_url() for comparison ***
        $this->assertStringContainsString(esc_url(get_edit_post_link($this->post_id)), $output_edit);
        $this->assertStringContainsString(__('Edit', 'integlight'), $output_edit);

        // --- Test Edit Link (User Cannot Edit) ---
        wp_set_current_user(0); // Log out user
        ob_start();
        integlight_entry_footer();
        $output_no_edit = ob_get_clean();
        $this->assertStringNotContainsString('<span class="edit-link">', $output_no_edit);
        wp_set_current_user($this->user_id); // Log back in for subsequent tests

        // --- Test on a Page (No Cats/Tags) ---
        $page_id = self::factory()->post->create(array('post_type' => 'page', 'post_title' => 'Test Page'));
        $post = get_post($page_id); // Switch global post to the page
        setup_postdata($post);

        ob_start();
        integlight_entry_footer();
        $output_page = ob_get_clean();
        $this->assertStringNotContainsString('<span class="cat-links">', $output_page);
        $this->assertStringNotContainsString('<span class="tags-links">', $output_page);
        // Edit link should still show for pages if user can edit
        $this->assertStringContainsString('<span class="edit-link">', $output_page);
        $this->assertStringContainsString(esc_url(get_edit_post_link($page_id)), $output_page); // Check page edit link too

        // Restore original post for subsequent tests if any
        $post = get_post($this->post_id);
        setup_postdata($post);
    }

    /**
     * Test integlight_post_thumbnail() output.
       /**
     * Test integlight_post_thumbnail() output.
     *
     * Note: Testing the actual output of the_post_thumbnail() is complex.
     * We focus on whether the function outputs *something* within the correct wrapper.
     * @covers ::integlight_post_thumbnail
     */
    public function test_integlight_post_thumbnail()
    {
        // --- Test No Thumbnail ---
        ob_start();
        integlight_post_thumbnail();
        $output_none = ob_get_clean();
        $this->assertEmpty($output_none);

        // --- Add a Thumbnail ---
        // *** NOTE: Ensure 'dummy-image.png' exists in the same directory as this test file. ***
        $image_path = __DIR__ . '/dummy-image.png'; // 画像ファイル名を確認
        if (!file_exists($image_path)) {
            // 画像がない場合はテストをスキップ
            $this->markTestSkipped('Test image file not found at ' . $image_path);
        }
        $attachment_id = self::factory()->attachment->create_upload_object($image_path, $this->post_id);
        set_post_thumbnail($this->post_id, $attachment_id);

        // --- Test Singular View ---
        $this->go_to(get_permalink($this->post_id)); // 個別投稿ページへ移動
        $this->assertTrue(is_singular(), 'Failed asserting that the view is singular after go_to permalink.');
        // 個別表示ではメインクエリの投稿が自動的に設定されるが、明示的に setup_postdata する
        global $post;
        $post = get_post(get_the_ID()); // メインクエリの投稿を取得
        setup_postdata($post);

        ob_start();
        integlight_post_thumbnail();
        $output_singular = ob_get_clean();
        $this->assertStringStartsWith('<div class="post-thumbnail">', trim($output_singular));
        $this->assertStringEndsWith('</div><!-- .post-thumbnail -->', trim($output_singular));
        $this->assertStringContainsString('<img', $output_singular);
        wp_reset_postdata(); // クリーンアップ

        // --- Test Archive View ---
        // *** MODIFICATION START ***
        // アーカイブページ（例：ホームページ）へ移動
        $this->go_to('/');
        $this->assertFalse(is_singular(), 'Failed asserting that the view is not singular after go_to archive.');

        // メインクエリを使ってループをシミュレートし、対象の投稿を探す
        global $wp_query;
        $found_post = false;
        $output_archive = ''; // 初期化
        if ($wp_query->have_posts()) {
            while ($wp_query->have_posts()) {
                $wp_query->the_post(); // グローバル $post を設定
                if (get_the_ID() === $this->post_id) {
                    // 対象の投稿が見つかったら、関数を呼び出して出力をキャプチャ
                    ob_start();
                    integlight_post_thumbnail();
                    $output_archive = ob_get_clean();
                    $found_post = true;
                    break; // 投稿が見つかったのでループを抜ける
                }
            }
        }
        wp_reset_postdata(); // ループシミュレーション後にリセット

        $this->assertTrue($found_post, 'Test post was not found in the archive query.'); // 投稿がクエリ結果に含まれていたか確認

        // アーカイブ表示のアサーション
        if ($found_post) { // 投稿が見つかった場合のみアサーションを実行
            $this->assertStringStartsWith('<a class="post-thumbnail"', trim($output_archive));
            $this->assertStringEndsWith('</a>', trim($output_archive));
            $this->assertStringContainsString('href="' . esc_url(get_permalink($this->post_id)) . '"', $output_archive);
            $this->assertStringContainsString('<img', $output_archive);
        }
        // *** MODIFICATION END ***

        // --- Test Password Protected ---
        // パスワード保護テストのために再度 setup_postdata が必要
        $post = get_post($this->post_id);
        setup_postdata($post);
        wp_update_post(array('ID' => $this->post_id, 'post_password' => 'password'));
        // 更新後に投稿データを再取得
        $post = get_post($this->post_id);
        setup_postdata($post);

        ob_start();
        integlight_post_thumbnail();
        $output_password = ob_get_clean();
        $this->assertEmpty($output_password);

        // Clean up password
        wp_update_post(array('ID' => $this->post_id, 'post_password' => ''));
        wp_reset_postdata(); // パスワード解除後にもリセット

        // Clean up thumbnail
        delete_post_thumbnail($this->post_id);
        if (isset($attachment_id)) { // $attachment_id が設定されている場合のみ削除
            wp_delete_attachment($attachment_id, true);
        }

        // Restore query state (tear_down handles some of this)
        // wp_reset_query(); // tear_down does this
        // wp_reset_postdata(); // tear_down does this
    }

    /**
     * Test wp_body_open shim.
     * @covers ::wp_body_open
     */
    public function test_wp_body_open()
    {
        // Check if the function exists (it might be defined by core in newer WP)
        if (! function_exists('wp_body_open')) {
            // If it doesn't exist, our shim should define it.
            // NOTE: It's generally better to ensure theme files are loaded via the test bootstrap.
            require_once get_template_directory() . '/inc/template-tags.php';
        }
        $this->assertTrue(function_exists('wp_body_open'));

        // Test if the action is fired
        $action_fired = false;
        $callback = function () use (&$action_fired) {
            $action_fired = true;
        };
        add_action('wp_body_open', $callback);

        // Capture output to prevent it from interfering with test results
        ob_start();
        wp_body_open();
        ob_end_clean();

        remove_action('wp_body_open', $callback);
        $this->assertTrue($action_fired, 'The wp_body_open action was not fired.');
    }
}
