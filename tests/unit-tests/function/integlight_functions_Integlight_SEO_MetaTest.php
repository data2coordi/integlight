<?php

/**
 * Tests for the Integlight_seoMeta class.
 *
 * @package Integlight
 * @group seo-meta
 */

// require_once dirname( __FILE__ ) . '/../inc/integlight-functions.php';

class integlight_functions_Integlight_seoMetaTest extends WP_UnitTestCase
{
    /**
     * Instance of the class under test.
     * @var Integlight_seoMeta
     */
    private $instance;

    /**
     * Post ID for testing.
     * @var int
     */
    private $post_id;

    /**
     * Page ID for testing.
     * @var int
     */
    private $page_id;

    /**
     * Editor user ID for testing.
     * @var int
     */
    private $editor_id;

    /**
     * Set up the test environment.
     */
    public function setUp(): void
    {
        parent::setUp();

        // Ensure DOING_AUTOSAVE is false in test context
        if (! defined('DOING_AUTOSAVE')) {
            define('DOING_AUTOSAVE', false);
        }

        // Create an editor user and set as current user
        $this->editor_id = self::factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($this->editor_id);

        // Instantiate the class under test
        $this->instance = new Integlight_seoMeta();

        // Create test post and page authored by the editor
        $this->post_id = self::factory()->post->create([
            'post_type'   => 'post',
            'post_author' => $this->editor_id,
        ]);
        $this->page_id = self::factory()->post->create([
            'post_type'   => 'page',
            'post_author' => $this->editor_id,
        ]);
    }

    /**
     * Tear down the test environment.
     */
    public function tearDown(): void
    {
        wp_delete_post($this->post_id, true);
        wp_delete_post($this->page_id, true);
        wp_set_current_user(0);
        parent::tearDown();
    }

    /**
     * Test if hooks are added correctly.
     * @covers Integlight_seoMeta::__construct
     */
    public function test_hooks_are_added()
    {
        $this->assertGreaterThan(0, has_action('add_meta_boxes', [$this->instance, 'add_meta_box']));
        $this->assertGreaterThan(0, has_action('save_post', [$this->instance, 'save_meta_data']));
        $this->assertGreaterThan(0, has_filter('document_title_parts', [$this->instance, 'filter_document_title']));
        $this->assertGreaterThan(0, has_action('wp_head', [$this->instance, 'output_meta_description']));
    }

    /**
     * Test the add_meta_box method callback exists.
     * @covers Integlight_seoMeta::add_meta_box
     */
    public function test_add_meta_box_callback_exists()
    {
        $this->assertTrue(method_exists($this->instance, 'add_meta_box'));
    }

    /**
     * Test the display_meta_box_content output.
     * @covers Integlight_seoMeta::display_meta_box_content
     */
    public function test_display_meta_box_content()
    {
        $post = get_post($this->post_id);
        $meta_key_title       = '_custom_meta_title';
        $meta_key_description = '_custom_meta_description';
        $nonce_name           = 'seo_meta_box_nonce';

        // No existing meta
        ob_start();
        $this->instance->display_meta_box_content($post);
        $output_empty = ob_get_clean();

        $this->assertStringContainsString('name="' . $nonce_name . '"', $output_empty);
        $this->assertStringContainsString('name="' . $meta_key_title . '"', $output_empty);
        $this->assertStringContainsString('value=""', $output_empty);
        $this->assertStringContainsString('</textarea>', $output_empty);

        // With existing meta
        update_post_meta($this->post_id, $meta_key_title, 'Existing Title');
        update_post_meta($this->post_id, $meta_key_description, 'Existing Description');

        ob_start();
        $this->instance->display_meta_box_content($post);
        $output_filled = ob_get_clean();

        $this->assertStringContainsString('value="' . esc_attr('Existing Title') . '"', $output_filled);
        $this->assertStringContainsString('>' . esc_textarea('Existing Description') . '</textarea>', $output_filled);
    }

    /**
     * Test the save_meta_data method functionality.
     * @covers Integlight_seoMeta::save_meta_data
     */
    public function test_save_meta_data()
    {
        $meta_key_title       = '_custom_meta_title';
        $meta_key_description = '_custom_meta_description';
        $nonce_name           = 'seo_meta_box_nonce';
        $nonce_action         = 'seo_meta_box_nonce_action';

        // Valid post save
        $_POST[$nonce_name]           = wp_create_nonce($nonce_action);
        $_POST['post_type']           = 'post';
        $_POST[$meta_key_title]       = 'Test Title <script></script>';
        $_POST[$meta_key_description] = "Test Desc Line";

        do_action('save_post', $this->post_id, get_post($this->post_id), false);

        $this->assertEquals('Test Title', get_post_meta($this->post_id, $meta_key_title, true));
        $this->assertEquals("Test Desc Line", get_post_meta($this->post_id, $meta_key_description, true));

        unset($_POST[$nonce_name], $_POST['post_type'], $_POST[$meta_key_title], $_POST[$meta_key_description]);
    }

    /**
     * Test the filter_document_title method.
     * @covers Integlight_seoMeta::filter_document_title
     */
    public function test_filter_document_title()
    {
        $meta_key_title = '_custom_meta_title';
        $original_parts = [
            'title'   => 'Orig Title',
            'page'    => '',
            'tagline' => 'Tag',
            'site'    => 'Site',
        ];

        update_post_meta($this->post_id, $meta_key_title, 'SEO Title');
        $this->go_to(get_permalink($this->post_id));

        $filtered = $this->instance->filter_document_title($original_parts);
        $this->assertEquals('SEO Title', $filtered['title']);

        delete_post_meta($this->post_id, $meta_key_title);
        $this->go_to(get_permalink($this->post_id));
        $this->assertEquals($original_parts, $this->instance->filter_document_title($original_parts));
    }

    /**
     * Test the output_meta_description method.
     * @covers Integlight_seoMeta::output_meta_description
     */
    public function test_output_meta_description()
    {
        $meta_key_description = '_custom_meta_description';

        update_post_meta($this->post_id, $meta_key_description, 'Custom Desc');
        $this->go_to(get_permalink($this->post_id));

        ob_start();
        $this->instance->output_meta_description();
        $output = ob_get_clean();
        $this->assertStringContainsString('content="Custom Desc"', $output);

        delete_post_meta($this->post_id, $meta_key_description);
    }

    public function test_home_meta_description()
    {
        $this->go_to(home_url('/'));
        ob_start();
        $this->instance->output_meta_description();
        $output = ob_get_clean();
        $this->assertStringContainsString(get_bloginfo('description'), $output);
    }
    public function test_category_meta_description()
    {
        $cat_id = self::factory()->category->create(['name' => 'Test Cat']);
        $this->go_to(get_category_link($cat_id));
        ob_start();
        $this->instance->output_meta_description();
        $output = ob_get_clean();
        $this->assertStringContainsString('Test Cat', $output);
        $this->assertStringContainsString(get_bloginfo('description'), $output);
    }

    public function test_tag_meta_description()
    {
        // タグ作成
        $tag_id = self::factory()->tag->create(['name' => 'Test Tag']);

        // タグページに移動
        $this->go_to(get_tag_link($tag_id));

        ob_start();
        $this->instance->output_meta_description();
        $output = ob_get_clean();

        $this->assertStringContainsString('Test Tag', $output);
        $this->assertStringContainsString(get_bloginfo('description'), $output);
    }
    public function test_else_archive_meta_description()
    {
        // 投稿者アーカイブを作る
        $author_id = self::factory()->user->create(['role' => 'author']);
        wp_set_current_user($author_id);

        // そのユーザーの投稿を作る
        $post_id = self::factory()->post->create(['post_author' => $author_id]);

        // 投稿者アーカイブページに移動
        $this->go_to(get_author_posts_url($author_id));

        ob_start();
        $this->instance->output_meta_description();
        $output = ob_get_clean();

        // else 分岐なのでサイトキャッチフレーズが入る
        $this->assertStringContainsString(get_bloginfo('description'), $output);
        $this->assertStringNotContainsString('_custom_meta_description', $output); // 投稿メタは使われない
    }
}
