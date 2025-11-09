<?php

/**
 * Integlight Full Debug Starter (Class Version)
 */


class Integlight_Full_Debug
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
     * 管理画面メニューに追加
     */
    public function add_admin_page()
    {
        add_theme_page(
            'Integlight Full Debug Setup',
            'Integlight Full Debug',
            'edit_theme_options',
            'integlight-full-debug-setup',
            array($this, 'render_admin_page')
        );
    }

    /**
     * 管理画面ページの出力
     */
    public function render_admin_page()
    {
        if (isset($_POST['integlight_full_debug_setup'])) {
            $this->run_setup();
        }

        echo '<form method="post">';
        echo '<p><input type="submit" class="button button-primary" name="integlight_full_debug_setup" value="フルサンプルをセットアップ"></p>';
        echo '</form>';
    }

    /**
     * セットアップ処理
     */
    private function run_setup()
    {
        $ids = array();

        foreach ($this->images as $key => $path) {
            $ids[$key] = $this->import_media($path, ucfirst(str_replace('-', ' ', $key)));
        }

        // ロゴ
        if (! empty($ids['sample-logo'])) {
            set_theme_mod('custom_logo', $ids['sample-logo']);
        }

        // スライダー画像（PC/モバイル）
        set_theme_mod('integlight_slider_image_1', $ids['image-slider-1'] ?? '');
        set_theme_mod('integlight_slider_image_2', $ids['image-slider-2'] ?? '');
        set_theme_mod('integlight_slider_image_3', $ids['image-slider-3'] ?? '');
        set_theme_mod('integlight_slider_image_mobile_1', $ids['image-slider-sp-1'] ?? '');
        set_theme_mod('integlight_slider_image_mobile_2', $ids['image-slider-sp-2'] ?? '');
        set_theme_mod('integlight_slider_image_mobile_3', $ids['image-slider-sp-3'] ?? '');

        // その他 theme_mods
        set_theme_mod('integlight_display_choice', 'slider');
        set_theme_mod('integlight_slider_effect', 'fade');
        set_theme_mod('integlight_slider_change_duration', 3);
        set_theme_mod('integlight_slider_text_1', __('Turn Your Experience and Knowledge into Digital Assets with Integlight', 'integlight'));
        set_theme_mod('integlight_slider_text_2', __('The things you casually talk about every day, as well as the knowledge and experience you gain from work or hobbies, can be valuable information for someone. By documenting them in a blog, they accumulate over time and become your digital asset. Keep sharing, and you may create value that reaches many people.', 'integlight'));
        set_theme_mod('integlight_slider_text_font', 'yu_gothic');
        set_theme_mod('integlight_slider_text_top', 100);
        set_theme_mod('integlight_slider_text_left', 200);
        set_theme_mod('integlight_slider_text_color', '#ffffff');

        // サイトタイトル
        update_option('blogname', 'Integlight Sample Site');


        $sample_content = new Integlight_Sample_Content();
        $sample_content->create_blog_menu_with_posts();



        echo '<div class="updated"><p>フルデバッグサンプル（ロゴ＋スライダー画像＋設定）を登録しました。</p></div>';
    }

    /**
     * メディア登録
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

// インスタンス化してフックを登録
new Integlight_Full_Debug();

/**
 * Integlight Sample Content Class
 * - 「ブログ」親メニュー + サンプル投稿2つを作成
 * - header メニュー位置に割り当て
 * - WP_Error チェック済み
 */


class Integlight_Sample_Content
{

    public function create_blog_menu_with_posts()
    {

        // サンプル投稿作成
        $sample_posts = array(
            array('post_title' => 'サンプル投稿1', 'post_content' => 'これはサンプル投稿1です。'),
            array('post_title' => 'サンプル投稿2', 'post_content' => 'これはサンプル投稿2です。'),
        );

        $post_ids = array();
        foreach ($sample_posts as $post) {
            $post_ids[] = wp_insert_post(array(
                'post_title'    => $post['post_title'],
                'post_content'  => $post['post_content'],
                'post_status'   => 'publish',
                'post_type'     => 'post',
            ));
        }

        // カテゴリ作成（存在する場合は取得）
        $category = get_term_by('slug', 'blog', 'category');
        if (! $category) {
            $category_id = wp_create_category('ブログ'); // slug は自動で blog になる
        } else {
            $category_id = $category->term_id;
        }

        // 作成した投稿をカテゴリに割り当て
        foreach ($post_ids as $pid) {
            wp_set_post_categories($pid, array($category_id), true);
        }

        // メニュー作成
        $menu_name = 'Header Menu';
        $menu_id   = wp_create_nav_menu($menu_name);

        if (is_wp_error($menu_id)) {
            $menu_obj = wp_get_nav_menu_object($menu_name);
            if ($menu_obj) {
                $menu_id = $menu_obj->term_id;
            } else {
                error_log('メニュー作成に失敗しました: ' . $menu_id->get_error_message());
                return;
            }
        }

        // 親メニュー: カテゴリ「ブログ」
        $parent_item_id = wp_update_nav_menu_item($menu_id, 0, array(
            'menu-item-title' => 'ブログ',
            'menu-item-object' => 'category',
            'menu-item-object-id' => $category_id,
            'menu-item-type' => 'taxonomy',
            'menu-item-status' => 'publish',
        ));


        // header に割り当て
        $locations = get_theme_mod('nav_menu_locations', array());
        $locations['header'] = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);
    }
}
