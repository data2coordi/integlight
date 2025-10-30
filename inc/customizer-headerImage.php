<?php










/**
 * Adds text settings to the built-in 'header_image' Customizer section.
 */
class Integlight_customizer_headerImage_textSettings extends Integlight_customizer_settingHelper
{
    /**
     * Registers PC and Mobile text settings.
     */
    public function register_settings()
    {
        $this->register_pc_settings();
        $this->register_mobile_settings();
    }

    private function register_pc_settings()
    {
        /* テキスト */
        $this->labelSetting('integlight_header_image_text_heading', __('Header text over the header image', 'integlight'));
        $this->textSetting('integlight_header_image_text_1', __('Header Image Text Main', 'integlight'));
        $this->textSetting('integlight_header_image_text_2', __('Header Image Text Sub', 'integlight'));
        $this->colorSetting('integlight_header_image_text_color', __('Header Image Text color', 'integlight'));
        $this->fonttypeSetting('integlight_header_image_text_font', __('Header Image Text Font', 'integlight'));
        $this->labelSetting('integlight_header_image_text_position_heading', __('Header Image Text Position', 'integlight'));
        $this->numberSetting('integlight_header_image_text_top', __('Header Image Text Position Top (px)', 'integlight'), 0, 1);
        $this->numberSetting('integlight_header_image_text_left', __('Header Image Text Position Left (px)', 'integlight'), 0, 1);
    }

    private function register_mobile_settings()
    {
        /* モバイルテキスト位置 */
        $this->labelSetting('integlight_header_image_text_position_heading_mobile', __('Header Image Text Position Mobile', 'integlight'));
        $this->numberSetting('integlight_header_image_text_top_mobile', __('Header Image Text Position Top Mobile (px)', 'integlight'), 0, 1);
        $this->numberSetting('integlight_header_image_text_left_mobile', __('Header Image Text Position Left Mobile (px)', 'integlight'), 0, 1);
    }
}

/**
 * Main class to initialize header image customizer settings.
 */
class Integlight_Customizer_HeaderImage
{
    public function __construct()
    {
        add_action('customize_register', array($this, 'register_text_settings'));
    }

    public function register_text_settings($wp_customize)
    {
        $text_settings = new Integlight_customizer_headerImage_textSettings($wp_customize, 'header_image');
        $text_settings->register_settings();
    }
}
new Integlight_Customizer_HeaderImage();
