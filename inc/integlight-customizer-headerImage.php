<?php




/* スライダーに表示するテキストにカスタマイザーでユーザーがセットしたスタイルを適用するs */
class integlight_customizer_headerImage_applyHeaderTextStyle
{

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        // wp_head に出力するためのフックを登録
        add_action('wp_head', array($this, 'integlight_headerImage_applyTextStyles'));
    }

    /**
     * カスタマイザーの設定値に基づき、.slider .text-overlay のスタイルを出力
     */
    public function integlight_headerImage_applyTextStyles()
    {

        // カスタマイザーから値を取得。未設定の場合はデフォルト値を使用
        $color = get_theme_mod('integlight_header_image_text_color', '#ffffff'); // デフォルトは白
        $left  = get_theme_mod('integlight_header_image_text_left', 30);      // デフォルト 30px
        $top   = get_theme_mod('integlight_header_image_text_top', 300);       // デフォルト 300px
        $left_mobile  = get_theme_mod('integlight_header_image_text_left_mobile', 20);      // デフォルト 30px
        $top_mobile   = get_theme_mod('integlight_header_image_text_top_mobile', 200);       // デフォルト 300px
        // フォント選択の取得（デフォルトは 'yu_gothic'）

        $font = get_theme_mod('integlight_header_image_text_font', 'yu_gothic');
        switch ($font) {
            case 'yu_mincho':
                // 游明朝の場合の font-family
                $font_family = 'Yu Mincho, 游明朝体, serif';
                break;
            case 'yu_gothic':
            default:
                // 游ゴシックの場合の font-family
                $font_family = 'Yu Gothic, 游ゴシック体, sans-serif';
                break;
        }


?>
        <style>
            .header-image .text-overlay {
                position: absolute;
                left: <?php echo absint($left); ?>px;
                top: <?php echo absint($top); ?>px;
                color: <?php echo esc_attr($color); ?>;
            }

            .header-image .text-overlay h1 {
                font-family: <?php echo esc_attr($font_family); ?>;
            }

            @media only screen and (max-width: 767px) {
                .header-image .text-overlay {
                    position: absolute;
                    left: <?php echo absint($left_mobile); ?>px;
                    top: <?php echo absint($top_mobile); ?>px;
                }
            }
        </style>
<?php
    }
}
/* スライダーに表示するテキストe */











/**
 * Adds text settings to the built-in 'header_image' Customizer section.
 */
class Integlight_Customizer_HeaderImage_Text_Settings extends Integlight_Customizer_Setting_Helper
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
        $this->labelSetting('integlight_header_image_text_heading', __('Header Image Text', 'integlight'));
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
        add_action('wp', array($this, 'register_text_style'));
    }

    public function register_text_settings($wp_customize)
    {
        $text_settings = new Integlight_Customizer_HeaderImage_Text_Settings($wp_customize, 'header_image');
        $text_settings->register_settings();
    }

    public function register_text_style($wp_customize)
    {
        new integlight_customizer_headerImage_applyHeaderTextStyle();
    }
}
new Integlight_Customizer_HeaderImage();
