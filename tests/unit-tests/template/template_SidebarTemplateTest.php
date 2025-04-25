<?php
class template_SidebarTemplateTest extends WP_UnitTestCase
{

    public function setUp(): void
    {
        parent::setUp();
        // サイドバー登録
        register_sidebar([
            'name' => 'Sidebar 1',
            'id'   => 'sidebar-1',
        ]);
        register_sidebar([
            'name' => 'Sidebar 2',
            'id'   => 'sidebar-2',
        ]);
    }

    public function test_sidebar_not_displayed_on_page()
    {
        $page_id = $this->factory->post->create([
            'post_type' => 'page',
        ]);

        $this->go_to(get_permalink($page_id)); // これが重要！

        ob_start();
        include get_template_directory() . '/sidebar.php';
        $output = ob_get_clean();

        $this->assertEmpty(trim($output));
    }

    public function test_sidebar_1_displayed_when_active_and_position_not_none()
    {
        // 投稿（is_page() ではない）を作成
        $post_id = $this->factory->post->create([
            'post_type' => 'post',
        ]);
        $GLOBALS['post'] = get_post($post_id);
        setup_postdata($GLOBALS['post']);

        // sidebar-1 をアクティブにする
        add_filter('is_active_sidebar', function ($active, $index) {
            if ($index === 'sidebar-1') return true;
            return $active;
        }, 10, 2);

        set_theme_mod('integlight_sidebar1_position', 'right');

        ob_start();
        include get_template_directory() . '/sidebar.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('id="secondary"', $output);
        $this->assertStringContainsString('ly_site_content_widgetArea_right', $output);
    }

    public function test_sidebar_2_displayed_when_active_and_position_not_none()
    {
        $post_id = $this->factory->post->create([
            'post_type' => 'post',
        ]);
        $GLOBALS['post'] = get_post($post_id);
        setup_postdata($GLOBALS['post']);

        add_filter('is_active_sidebar', function ($active, $index) {
            if ($index === 'sidebar-2') return true;
            return $active;
        }, 10, 2);

        set_theme_mod('integlight_sidebar2_position', 'left');

        ob_start();
        include get_template_directory() . '/sidebar.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('id="third"', $output);
        $this->assertStringContainsString('ly_site_content_widgetArea_left', $output);
    }

    public function test_sidebar_1_not_displayed_when_position_is_none()
    {
        $post_id = $this->factory->post->create([
            'post_type' => 'post',
        ]);
        $this->go_to(get_permalink($post_id));

        add_filter('is_active_sidebar', function ($active, $index) {
            if ($index === 'sidebar-1') return true;
            return $active;
        }, 10, 2);

        set_theme_mod('integlight_sidebar1_position', 'none');

        ob_start();
        include get_template_directory() . '/sidebar.php';
        $output = ob_get_clean();

        $this->assertStringNotContainsString('id="secondary"', $output, 'Sidebar-1 should not be displayed when position is "none".');
    }

    public function test_sidebar_2_not_displayed_when_position_is_none()
    {
        $post_id = $this->factory->post->create([
            'post_type' => 'post',
        ]);
        $this->go_to(get_permalink($post_id));

        add_filter('is_active_sidebar', function ($active, $index) {
            if ($index === 'sidebar-2') return true;
            return $active;
        }, 10, 2);

        set_theme_mod('integlight_sidebar2_position', 'none');

        ob_start();
        include get_template_directory() . '/sidebar.php';
        $output = ob_get_clean();

        $this->assertStringNotContainsString('id="third"', $output, 'Sidebar-2 should not be displayed when position is "none".');
    }
}
