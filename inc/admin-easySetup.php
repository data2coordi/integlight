<?php

/**
 * Integlight Full Debug Starter
 * - メディアにロゴ＋スライダー画像（PC3枚、SP3枚）を登録
 * - すべての theme_mod を反映
 * - サイトタイトルも設定
 */

function integlight_import_sample_media($relative_path, $title)
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

function integlight_full_debug_setup()
{
    $images = array(
        'sample-logo'        => 'assets/samplelogo_white.png',
        'image-slider-1'     => 'assets/sample_slider_pc_01.webp',
        'image-slider-2'     => 'assets/sample_slider_pc_02.webp',
        'image-slider-3'     => 'assets/sample_slider_pc_03.webp',
        'image-slider-sp-1'  => 'assets/sample_slider_sp_01.webp',
        'image-slider-sp-2'  => 'assets/sample_slider_sp_02.webp',
        'image-slider-sp-3'  => 'assets/sample_slider_sp_03.webp',
    );

    $ids = array();
    foreach ($images as $key => $path) {
        $ids[$key] = integlight_import_sample_media($path, ucfirst(str_replace('-', ' ', $key)));
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

    echo '<div class="updated"><p>フルデバッグサンプル（ロゴ＋スライダー画像＋設定）を登録しました。</p></div>';
}

function integlight_full_debug_setup_button()
{
    if (isset($_POST['integlight_full_debug_setup'])) {
        integlight_full_debug_setup();
    }

    echo '<form method="post">';
    echo '<p><input type="submit" class="button button-primary" name="integlight_full_debug_setup" value="フルサンプルをセットアップ"></p>';
    echo '</form>';
}

// 管理画面メニューに追加（外観 > Integlight Full Debug）
add_action('admin_menu', function () {
    add_theme_page(
        'Integlight Full Debug Setup',
        'Integlight Full Debug',
        'edit_theme_options',
        'integlight-full-debug-setup',
        'integlight_full_debug_setup_button'
    );
});
