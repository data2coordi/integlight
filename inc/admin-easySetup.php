
<?php

/**
 * Integlight Full Debug Starter (Class Version)
 */

class Integlight_initSetup
{

    private $images = array(
        'sample-logo'        => 'assets/samplelogo_white.png',
        'image-slider-1'     => 'assets/sample_slider_pc_01.webp',
        'image-slider-2'     => 'assets/sample_slider_pc_02.webp',
        'image-slider-3'     => 'assets/sample_slider_pc_03.webp',
        'image-slider-sp-1'  => 'assets/sample_slider_sp_01.webp',
        'image-slider-sp-2'  => 'assets/sample_slider_sp_02.webp',
        'image-slider-sp-3'  => 'assets/sample_slider_sp_03.webp',
    );

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_page'));
    }

    /**
     * Add to admin menu
     */
    public function add_admin_page()
    {
        add_theme_page(
            __('Integlight Sample Content Easy Setup', 'integlight'),
            __('Integlight Sample Content Easy Setup', 'integlight'),
            'edit_theme_options',
            'integlight-sample-easy-setup',
            array($this, 'render_admin_page')
        );
    }

    /**
     * Admin page output
     */
    public function render_admin_page()
    {
        if (isset($_POST['integlight_full_debug_setup'])) {
            $this->run_setup();
        }

        echo '<form method="post" onsubmit="return confirm(\'' . esc_js(__('This operation will overwrite existing settings such as the logo and menus. Are you sure you want to proceed?', 'integlight')) . '\');">';


        // Description block
        echo '<div style="border:1px solid #ccc; background-color:#f9f9f9; padding:10px; margin-bottom:15px;">';
        echo '<p>' . esc_html__('Clicking this button will automatically set up sample content so that even beginners can easily review the site.', 'integlight') . '</p>';

        echo '<p><strong>' . esc_html__('Contents to be set up:', 'integlight') . '</strong></p>';
        echo '<ul>';
        echo '<li>' . esc_html__('Logo', 'integlight') . '</li>';
        echo '<li>' . esc_html__('Slider', 'integlight') . '</li>';
        echo '<li>' . esc_html__('Sample Category "Blog" + Posts + Page', 'integlight') . '</li>';
        echo '<li>' . esc_html__('Add "Blog" category and page to Header Menu', 'integlight') . '</li>';
        echo '</ul>';

        echo '<p><strong>' . esc_html__('Benefits of automatic setup:', 'integlight') . '</strong></p>';
        echo '<ul>';
        echo '<li>' . esc_html__('Preview the completed theme immediately', 'integlight') . '</li>';
        echo '<li>' . esc_html__('Easy for beginners to operate', 'integlight') . '</li>';
        echo '<li>' . esc_html__('Understand the overall structure of required features', 'integlight') . '</li>';
        echo '<li>' . esc_html__('Quickly build real content by editing sample data', 'integlight') . '</li>';
        echo '</ul>';
        echo '</div>';

        // Notice Box
        echo '<div style="border:2px solid #d9534f; background-color:#f2dede; color:#a94442; padding:10px; margin-bottom:15px;">';
        echo '<strong>' . esc_html__('Notice:', 'integlight') . '</strong> ';
        echo esc_html__('This operation will overwrite existing settings such as the logo and menus.', 'integlight');
        echo '</div>';

        // Button
        echo '<p><input type="submit" class="button button-primary" name="integlight_full_debug_setup" value="' . esc_attr__('Set Up Sample Content', 'integlight') . '"></p>';

        echo '</form>';
    }

    /**
     * Setup process
     */
    private function run_setup()
    {
        $ids = array();

        foreach ($this->images as $key => $path) {
            $ids[$key] = $this->import_media($path, ucfirst(str_replace('-', ' ', $key)));
        }

        // Logo
        if (! empty($ids['sample-logo'])) {
            set_theme_mod('custom_logo', $ids['sample-logo']);
            //タイトルの表示はオフにする。
            set_theme_mod('header_textcolor', 'blank');
        }


        set_theme_mod('integlight_hometype_setting', 'siteType2');

        // Slider Images
        set_theme_mod('integlight_slider_image_1', $ids['image-slider-1'] ?? '');
        set_theme_mod('integlight_slider_image_2', $ids['image-slider-2'] ?? '');
        set_theme_mod('integlight_slider_image_3', $ids['image-slider-3'] ?? '');
        set_theme_mod('integlight_slider_image_mobile_1', $ids['image-slider-sp-1'] ?? '');
        set_theme_mod('integlight_slider_image_mobile_2', $ids['image-slider-sp-2'] ?? '');
        set_theme_mod('integlight_slider_image_mobile_3', $ids['image-slider-sp-3'] ?? '');

        // Slider Other theme mods
        set_theme_mod('integlight_display_choice', 'slider');
        set_theme_mod('integlight_slider_effect', 'slide');
        set_theme_mod('integlight_slider_change_duration', 3);
        set_theme_mod('integlight_slider_text_1', __('Turn Your Experience and Knowledge into Digital Assets with Integlight', 'integlight'));
        set_theme_mod('integlight_slider_text_2', __('The things you casually talk about every day, as well as the knowledge and experience you gain from work or hobbies, can be valuable information for someone. By documenting them in a blog, they accumulate over time and become your digital asset. Keep sharing, and you may create value that reaches many people.', 'integlight'));
        set_theme_mod('integlight_slider_text_font', 'yu_gothic');
        set_theme_mod('integlight_slider_text_top', 1);
        set_theme_mod('integlight_slider_text_left', 10);
        set_theme_mod('integlight_slider_text_color', '#ffffff');

        $sample_content = new Integlight_Sample_Content();
        $sample_content->setup();

        echo '<div class="updated"><p>' . esc_html__('Sample content (logo, slider, menu, posts) has been installed.', 'integlight') . '</p></div>';
    }

    /**
     * Media import
     */
    private function import_media($relative_path, $title)
    {
        $full_path = get_template_directory() . '/' . $relative_path;
        if (! file_exists($full_path)) {
            error_log('File not found: ' . $full_path);
            return false;
        }

        $upload_dir = wp_upload_dir();
        wp_mkdir_p($upload_dir['path']);

        $filename = wp_unique_filename($upload_dir['path'], basename($full_path));
        $new_path = $upload_dir['path'] . '/' . $filename;

        if (! copy($full_path, $new_path)) {
            error_log('Failed to copy file: ' . $relative_path);
            return false;
        }

        $filetype = wp_check_filetype(basename($new_path), null);
        $attachment = array(
            'post_mime_type' => $filetype['type'],
            'post_title'     => $title,
            'post_content'   => '',
            'post_status'    => 'inherit',
        );
        $attach_id = wp_insert_attachment($attachment, $new_path);
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $attach_data = wp_generate_attachment_metadata($attach_id, $new_path);
        wp_update_attachment_metadata($attach_id, $attach_data);

        return $attach_id;
    }
}

new Integlight_initSetup();


/**
 * Integlight Sample Content Class
 */
class Integlight_Sample_Content
{
    public function setup()
    {
        $category_id = $this->create_category(__('Blog', 'integlight'), 'blog');
        $this->create_sample_posts($category_id);
        $profile_id = $this->create_profile_page();
        $menu_id = $this->get_or_create_menu(__('Header Menu', 'integlight'));
        $this->add_menu_items($menu_id, $category_id, $profile_id);
        $this->assign_menu_location($menu_id, 'header');
    }

    private function create_category($name, $slug)
    {
        $category = get_term_by('slug', $slug, 'category');
        if (!$category) {
            $new_term = wp_insert_term($name, 'category', ['slug' => $slug]);
            if (is_wp_error($new_term)) {
                error_log('Failed to create category: ' . $new_term->get_error_message());
                return null;
            }
            return $new_term['term_id'];
        }
        return $category->term_id;
    }

    private function create_sample_posts($category_id)
    {
        $sample_posts = [
            ['post_title' => __('Sample Post 1', 'integlight'), 'post_content' => __('This is Sample Post 1.', 'integlight')],
            ['post_title' => __('Sample Post 2', 'integlight'), 'post_content' => __('This is Sample Post 2.', 'integlight')],
        ];

        foreach ($sample_posts as $post) {
            $query = new WP_Query([
                'title'         => $post['post_title'],
                'post_type'     => 'post',
                'post_status'   => 'publish',
                'posts_per_page' => 1,
                'fields'        => 'ids',
            ]);

            if (!empty($query->posts)) {
                wp_set_post_categories($query->posts[0], [$category_id], true);
                continue;
            }

            $pid = wp_insert_post([
                'post_title'   => $post['post_title'],
                'post_content' => $post['post_content'],
                'post_status'  => 'publish',
                'post_type'    => 'post',
            ]);
            if ($pid) {
                wp_set_post_categories($pid, [$category_id], true);
            }
        }
    }

    private function create_profile_page()
    {
        $query = new WP_Query([
            'name'          => 'profile',
            'post_type'     => 'page',
            'post_status'   => 'publish',
            'posts_per_page' => 1,
            'fields'        => 'ids',
        ]);

        if (!empty($query->posts)) {
            return $query->posts[0];
        }

        return wp_insert_post([
            'post_title'   => __('Profile', 'integlight'),
            'post_name'    => 'profile',
            'post_content' => __('This is a sample profile page.', 'integlight'),
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);
    }

    private function get_or_create_menu($menu_name)
    {
        $menu_obj = wp_get_nav_menu_object($menu_name);
        if ($menu_obj) {
            return $menu_obj->term_id;
        }

        $menu_id = wp_create_nav_menu($menu_name);
        if (is_wp_error($menu_id)) {
            error_log('Failed to create menu: ' . $menu_id->get_error_message());
            return null;
        }
        return $menu_id;
    }

    private function add_menu_items($menu_id, $category_id, $profile_id)
    {
        if (!$menu_id) return;

        $menu_items = wp_get_nav_menu_items($menu_id) ?: [];

        $exists_category = false;
        $exists_profile  = false;
        foreach ($menu_items as $item) {
            if ($item->object_id == $category_id && $item->object == 'category') $exists_category = true;
            if ($item->object_id == $profile_id && $item->object == 'page') $exists_profile = true;
        }

        if (!$exists_category) {
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title'     => __('Blog', 'integlight'),
                'menu-item-object'    => 'category',
                'menu-item-object-id' => $category_id,
                'menu-item-type'      => 'taxonomy',
                'menu-item-status'    => 'publish',
            ]);
        }

        if (!$exists_profile) {
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title'     => __('Profile', 'integlight'),
                'menu-item-object'    => 'page',
                'menu-item-object-id' => $profile_id,
                'menu-item-type'      => 'post_type',
                'menu-item-status'    => 'publish',
            ]);
        }
    }

    private function assign_menu_location($menu_id, $location)
    {
        if (!$menu_id) return;

        $locations = get_theme_mod('nav_menu_locations', []);
        $locations[$location] = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);
    }
}
