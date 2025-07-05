<?php
function integlight_get_starter_content()
{
    $starter_content = array(
        'attachments' => array(
            'image-slider-1' => array(
                'post_title' => 'Sample Slider pc image01',
                'file'       => 'img/sample_slider_pc_01.webp',
            ),
            'image-slider-2' => array(
                'post_title' => 'Sample Slider pc image02',
                'file'       => 'img/sample_slider_pc_02.webp',
            ),
            'image-slider-sp-1' => array(
                'post_title' => 'Sample Slider sp image01',
                'file'       => 'img/sample_slider_sp_01.webp',
            ),
            'image-slider-sp-2' => array(
                'post_title' => 'Sample Slider sp image02',
                'file'       => 'img/sample_slider_sp_02.webp',
            ),
            'sample-logo' => array(
                'post_title' => 'Sample Logo TEST',
                'file'       => 'img/samplelogo_white.png',
            ),
        ),
        'theme_mods' => array(
            'custom_logo' => '{{sample-logo}}',
            'integlight_display_choice' => 'slider',
            'integlight_slider_effect' => 'fade',
            'integlight_slider_change_duration' => 3,
            'integlight_slider_text_1' => __('Turn Your Experience and Knowledge into Digital Assets with Integlight', 'integlight'),
            'integlight_slider_text_2' => __('The things you casually talk about every day, as well as the knowledge and experience you gain from work or hobbies, can be valuable information for someone. By documenting them in a blog, they accumulate over time and become your digital asset. Keep sharing, and you may create value that reaches many people.', 'integlight'),
            'integlight_slider_text_font' => 'yu_gothic',
            'integlight_slider_text_top' => 100,
            'integlight_slider_text_left' => 200,
            'integlight_slider_text_color' => '#ffffff',
            'integlight_slider_image_1' => '{{image-slider-1}}',
            'integlight_slider_image_2' => '{{image-slider-2}}',
            'integlight_slider_image_mobile_1' => '{{image-slider-sp-1}}',
            'integlight_slider_image_mobile_2' => '{{image-slider-sp-2}}',
        ),

        'options' => array(
            'show_on_front' => 'posts',
        ),

    );

    return apply_filters('integlight_starter_content', $starter_content);
}

add_action('after_setup_theme', function () {
    add_theme_support('starter-content', integlight_get_starter_content());
});
