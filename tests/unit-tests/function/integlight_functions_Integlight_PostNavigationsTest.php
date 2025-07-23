<?php
class integlight_functions_Integlight_PostNavigationsTest extends WP_UnitTestCase
{
    protected static $prev_post_id;
    protected static $current_post_id;
    protected static $next_post_id;
    protected static $prev_thumb_id;

    public static function wpSetUpBeforeClass($factory): void
    {
        self::$prev_post_id = $factory->post->create([
            'post_title' => 'Previous Post Title',
            'post_date' => '2023-01-01 10:00:00',
            'post_status' => 'publish',
        ]);
        self::$current_post_id = $factory->post->create([
            'post_title' => 'Current Post Title Which Is Very Long Indeed',
            'post_date' => '2023-01-01 11:00:00',
            'post_status' => 'publish',
        ]);
        self::$next_post_id = $factory->post->create([
            'post_title' => 'Next Post Title',
            'post_date' => '2023-01-01 12:00:00',
            'post_status' => 'publish',
        ]);

        // アイキャッチ画像を prev_post に設定
        $image_path = wp_tempnam('dummy.jpg');
        file_put_contents($image_path, 'dummy');
        self::$prev_thumb_id = $factory->attachment->create_upload_object($image_path, self::$prev_post_id);
        set_post_thumbnail(self::$prev_post_id, self::$prev_thumb_id);
    }

    public static function wpTearDownAfterClass(): void
    {
        wp_delete_post(self::$prev_post_id, true);
        wp_delete_post(self::$current_post_id, true);
        wp_delete_post(self::$next_post_id, true);
        wp_delete_attachment(self::$prev_thumb_id, true);
    }

    /**
     * @test
     */
    public function should_render_both_prev_and_next_navigation(): void
    {
        $this->go_to(get_permalink(self::$current_post_id));

        $prev_url = get_permalink(self::$prev_post_id);
        $next_url = get_permalink(self::$next_post_id);
        $prev_image_url = Integlight_PostThumbnail::getUrl(self::$prev_post_id);
        $next_image_url = Integlight_PostThumbnail::getUrl(self::$next_post_id);

        $prev_title = wp_html_excerpt(get_the_title(self::$prev_post_id), 14) . '...';
        $next_title = wp_html_excerpt(get_the_title(self::$next_post_id), 14) . '...';

        ob_start();
        Integlight_PostNavigations::get_post_navigation();
        $output = ob_get_clean();

        $this->assertStringContainsString('<nav class="post-navigation"', $output);

        // Prev
        $this->assertStringContainsString(esc_url($prev_url), $output);
        $this->assertStringContainsString(esc_url($prev_image_url), $output);
        $this->assertStringContainsString(esc_html($prev_title), $output);

        // Next
        $this->assertStringContainsString(esc_url($next_url), $output);
        $this->assertStringContainsString(esc_url($next_image_url), $output);
        $this->assertStringContainsString(esc_html($next_title), $output);
    }

    /**
     * @test
     */
    public function should_render_only_next_when_prev_is_missing(): void
    {
        $this->go_to(get_permalink(self::$prev_post_id)); // 先頭記事

        ob_start();
        Integlight_PostNavigations::get_post_navigation();
        $output = ob_get_clean();

        $this->assertStringContainsString('class="nav-next"', $output);
        $this->assertStringNotContainsString('<div class="nav-previous"', $output);
    }

    /**
     * @test
     */
    public function should_render_only_prev_when_next_is_missing(): void
    {
        $this->go_to(get_permalink(self::$next_post_id)); // 最後の記事

        ob_start();
        Integlight_PostNavigations::get_post_navigation();
        $output = ob_get_clean();

        $this->assertStringContainsString('class="nav-previous"', $output);
        $this->assertStringNotContainsString('<div class="nav-next"', $output);
    }

    /**
     * @test
     */
    public function should_render_nothing_when_no_prev_and_next(): void
    {
        // 全投稿を非公開に
        wp_update_post(['ID' => self::$prev_post_id, 'post_status' => 'draft']);
        wp_update_post(['ID' => self::$current_post_id, 'post_status' => 'draft']);
        wp_update_post(['ID' => self::$next_post_id, 'post_status' => 'draft']);

        $single_id = self::factory()->post->create([
            'post_title' => 'Only One',
            'post_status' => 'publish',
        ]);

        $this->go_to(get_permalink($single_id));

        ob_start();
        Integlight_PostNavigations::get_post_navigation();
        $output = ob_get_clean();

        $this->assertEmpty(trim($output), 'Expected no output when no prev/next post.');

        wp_delete_post($single_id, true);

        // 戻す
        wp_update_post(['ID' => self::$prev_post_id, 'post_status' => 'publish']);
        wp_update_post(['ID' => self::$current_post_id, 'post_status' => 'publish']);
        wp_update_post(['ID' => self::$next_post_id, 'post_status' => 'publish']);
    }
}
