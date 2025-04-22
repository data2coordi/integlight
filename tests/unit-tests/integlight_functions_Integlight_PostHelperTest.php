<?php // tests/unit-tests/PostHelperTest.php

declare(strict_types=1);

// テスト対象クラスを含むファイルを読み込む (オートロードされていない場合)
// require_once dirname(__DIR__, 2) . '/inc/integlight-functions.php';

/**
 * Test case for the PostHelper class.
 *
 * @coversDefaultClass Integlight_PostHelper // クラス名を修正
 * @group functions
 * @group posts
 */
class integlight_functions_Integlight_PostHelperTest extends WP_UnitTestCase
{
    // ### ここから追加 ###
    /**
     * Post ID with thumbnail.
     * @var int|null
     */
    private ?int $post_id_with_thumb = null;

    /**
     * Attachment ID for thumbnail.
     * @var int|null
     */
    private ?int $thumb_attachment_id = null;

    /**
     * Post ID without thumbnail, with image in content.
     * @var int|null
     */
    private ?int $post_id_with_content_img = null;

    /**
     * Post ID with no images.
     * @var int|null
     */
    private ?int $post_id_no_img = null;

    /**
     * Post ID for previous post in navigation test.
     * @var int|null
     */
    private ?int $prev_post_id = null;

    /**
     * Post ID for current post in navigation test.
     * @var int|null
     */
    private ?int $current_post_id = null;

    /**
     * Post ID for next post in navigation test.
     * @var int|null
     */
    private ?int $next_post_id = null;

    /**
     * Attachment ID for previous post image.
     * @var int|null
     */
    private ?int $prev_thumb_id = null;

    /**
     * Attachment ID for next post image.
     * @var int|null
     */
    private ?int $next_thumb_id = null;
    // ### ここまで追加 ###


    /**
     * Set up the test environment before each test.
     */
    public function setUp(): void
    {
        parent::setUp();

        // --- ダミーファイルの準備 ---
        $dummy_file_path = dirname(__FILE__) . '/dummy-image.png'; // ダミー画像ファイルへのパスを指定

        // ダミーファイルが存在するか確認
        if (!file_exists($dummy_file_path)) {
            // ファイルが見つからない場合はテストをスキップ
            $this->markTestSkipped('Dummy image file not found for attachment tests: ' . $dummy_file_path);
            return; // スキップした場合は以降の処理を行わない
        }
        // --- ダミーファイルの準備ここまで ---


        // --- Posts for get_post_image tests ---
        // 1. Post with thumbnail
        $this->post_id_with_thumb = self::factory()->post->create(['post_title' => 'Post With Thumbnail']);
        // ★★★ 修正点: __FILE__ を $dummy_file_path に変更 ★★★
        $this->thumb_attachment_id = self::factory()->attachment->create_upload_object($dummy_file_path, $this->post_id_with_thumb);
        // create_upload_object が成功したか確認 (失敗時は 0 や WP_Error を返す可能性がある)
        if (is_wp_error($this->thumb_attachment_id) || empty($this->thumb_attachment_id)) {
            $this->fail('Failed to create attachment for post_id_with_thumb. Error: ' . (is_wp_error($this->thumb_attachment_id) ? $this->thumb_attachment_id->get_error_message() : 'Unknown error'));
        }
        set_post_thumbnail($this->post_id_with_thumb, $this->thumb_attachment_id);

        // 2. Post without thumbnail, with image in content
        $this->post_id_with_content_img = self::factory()->post->create([
            'post_title' => 'Post With Content Image',
            'post_content' => '<p>Some text</p><img src="http://example.com/content-image.jpg" alt="content image"><p>More text</p>',
        ]);

        // 3. Post with no images
        $this->post_id_no_img = self::factory()->post->create([
            'post_title' => 'Post With No Image',
            'post_content' => '<p>Just text here.</p>',
        ]);

        // --- Posts for get_post_navigation tests ---
        // Create posts in chronological order (oldest first for prev/next logic)
        $this->prev_post_id = self::factory()->post->create([
            'post_title' => 'Previous Post Nav',
            'post_date' => '2023-01-01 10:00:00',
        ]);
        // ★★★ 修正点: __FILE__ を $dummy_file_path に変更 ★★★
        $this->prev_thumb_id = self::factory()->attachment->create_upload_object($dummy_file_path, $this->prev_post_id);
        if (is_wp_error($this->prev_thumb_id) || empty($this->prev_thumb_id)) {
            $this->fail('Failed to create attachment for prev_post_id. Error: ' . (is_wp_error($this->prev_thumb_id) ? $this->prev_thumb_id->get_error_message() : 'Unknown error'));
        }
        set_post_thumbnail($this->prev_post_id, $this->prev_thumb_id);


        $this->current_post_id = self::factory()->post->create([
            'post_title' => 'Current Post Nav',
            'post_date' => '2023-01-01 11:00:00',
        ]);

        $this->next_post_id = self::factory()->post->create([
            'post_title' => 'Next Post Navigation Title Test', // Longer title
            'post_date' => '2023-01-01 12:00:00',
        ]);
        // ★★★ 修正点: __FILE__ を $dummy_file_path に変更 ★★★
        $this->next_thumb_id = self::factory()->attachment->create_upload_object($dummy_file_path, $this->next_post_id);
        if (is_wp_error($this->next_thumb_id) || empty($this->next_thumb_id)) {
            $this->fail('Failed to create attachment for next_post_id. Error: ' . (is_wp_error($this->next_thumb_id) ? $this->next_thumb_id->get_error_message() : 'Unknown error'));
        }
        set_post_thumbnail($this->next_post_id, $this->next_thumb_id);
    }

    /**
     * Clean up the test environment after each test.
     */
    public function tearDown(): void
    {
        // Delete posts if IDs are set
        if ($this->post_id_with_thumb) wp_delete_post($this->post_id_with_thumb, true);
        if ($this->post_id_with_content_img) wp_delete_post($this->post_id_with_content_img, true);
        if ($this->post_id_no_img) wp_delete_post($this->post_id_no_img, true);
        if ($this->prev_post_id) wp_delete_post($this->prev_post_id, true);
        if ($this->current_post_id) wp_delete_post($this->current_post_id, true);
        if ($this->next_post_id) wp_delete_post($this->next_post_id, true);

        // Delete attachments if IDs are set
        if ($this->thumb_attachment_id) wp_delete_attachment($this->thumb_attachment_id, true);
        if ($this->prev_thumb_id) wp_delete_attachment($this->prev_thumb_id, true);
        if ($this->next_thumb_id) wp_delete_attachment($this->next_thumb_id, true);

        // Reset properties (optional but good practice)
        $this->post_id_with_thumb = null;
        $this->thumb_attachment_id = null;
        $this->post_id_with_content_img = null;
        $this->post_id_no_img = null;
        $this->prev_post_id = null;
        $this->current_post_id = null;
        $this->next_post_id = null;
        $this->prev_thumb_id = null;
        $this->next_thumb_id = null;

        parent::tearDown();
    }


    // ... (他のテストメソッドは変更なし) ...
}
