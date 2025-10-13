<?php




class InteglightHeaderSettings
{
    private static string $headerTypeName_slider = 'slider';
    private static string $headerTypeName_image = 'image';
    private static string $headerpanelid = 'integlight_header_panel';

    // slider を取得
    public static function getSlider(): string
    {
        return self::$headerTypeName_slider;
    }

    // image を取得
    public static function getImage(): string
    {
        return self::$headerTypeName_image;
    }
    public static function getHeaderPanelId(): string
    {
        return self::$headerpanelid;
    }
}

// 利用例
//echo InteglightHeaderSettings::getSlider(); // slider
//echo InteglightHeaderSettings::getImage();  // image
//echo InteglightHeaderSettings::getHeaderPanelId();  // image







//フロントエンドでの表示制御用a
function integlight_display_headerContents()
{

    $choice = get_theme_mod('integlight_display_choice', 'none');
    $slider = InteglightHeaderSettings::getSlider();
    $image  = InteglightHeaderSettings::getImage();

    switch ($choice) {
        case $slider:
            // 値1と一致する場合の処理
            get_template_part('template-parts/content', 'slide');

            break;

        case $image:
            if (get_header_image()) {
                echo '<img src="' . esc_url(get_header_image()) . '" class="topImage" ' .  ' alt="' . esc_attr(get_bloginfo('name')) . '">';
            }
            break;

        default:
            // どのケースにも一致しない場合の処理
    }
}




class integlight_customizer_headerImage_updSection
{

    private $pPanelId;

    public function __construct()
    {
        $this->pPanelId = InteglightHeaderSettings::getHeaderPanelId();

        add_action('customize_register', array($this, 'integlight_customizer_headerImage_updSection'));
    }
    public function integlight_customizer_headerImage_updSection($wp_customize)
    {
        if ($wp_customize->get_section('header_image')) {
            //$wp_customize->get_section('header_image')->title = __('Top Header:[Select - Slider or Image]', 'integlight');
            $wp_customize->get_section('header_image')->priority = 30; // 上に配置される
            $wp_customize->get_section('header_image')->panel = $this->pPanelId; // 上に配置される
            $wp_customize->get_section('header_image')->active_callback = function () {
                return get_theme_mod('integlight_display_choice', 'slider') === 'image';
            };
        }
    }
}


class integlight_customizer_selHeader_creSection
{

    const SLIDER_OR_IMAGE_SECTION_ID = 'sliderOrImage_section';

    public function __construct()
    {
        add_action('customize_register', array($this, 'creSection'));
    }

    public function creSection($wp_customize)
    {

        // 大セクションを追加
        // $wp_customize->add_panel(self::SLIDER_PANEL_ID, array(
        // 	'title'    => __('Top Header Setting', 'integlight'),
        // 	'description' => __('Please select whether to display a slider or an image in the top header. The settings button for the selected option will be displayed.', 'integlight'),
        // 	'priority' => 29
        // ));


        // 画像orスライダー選択セクションを追加
        $wp_customize->add_section(self::SLIDER_OR_IMAGE_SECTION_ID, array(
            'title'    => __('Select - Slider or Image', 'integlight'),
            'priority' => 29,
            'panel' => InteglightHeaderSettings::getHeaderPanelId(),
            'description' => __("Select the type of media to display on the homepage (top page).<br>The settings button for the selected type will appear on the previous screen.<br><br><b>Recommended setting:</b><br>Slider is recommended.", 'integlight')
        ));

        $msg1 =
            new Integlight_Customizer_Section_Description(
                self::SLIDER_OR_IMAGE_SECTION_ID,
                __("<br>In 1 above, select the media type to display in the header section of the homepage (top page).<br><b>◆ Slider type</b>:<br>Images slide with animation<br><b>◆ Static image type</b>:<br>Normal static image<br><br>According to the type selected in 1 above, the 'Slider' or 'Static Image' settings button will appear in 2 below. Click the button to configure.", 'integlight')

            );
    }



    public function getSliderOrImageSectionId()
    {
        return self::SLIDER_OR_IMAGE_SECTION_ID;
    }
}




class integlight_customizer_HeaderTypeSelecter
{

    private $pSectionId;

    public function __construct($headerSection)
    {
        $this->pSectionId = $headerSection->getSliderOrImageSectionId();
        add_action('customize_register', array($this, 'integlight_customizer_HeaderTypeSelecter'));
    }


    public function integlight_customizer_HeaderTypeSelecter($wp_customize)
    {

        // 選択ボックスを追加
        $wp_customize->add_setting('integlight_display_choice', array(
            'default' => 'none',
            'sanitize_callback' => 'sanitize_text_field',
        ));

        $wp_customize->add_control('integlight_display_choice', array(
            'label'    => __('Display Slider or Image', 'integlight'),
            'section'  => $this->pSectionId,
            'settings' => 'integlight_display_choice',
            'type'     => 'select',
            'choices'  => array(
                InteglightHeaderSettings::getSlider() => __('Slider', 'integlight'),
                InteglightHeaderSettings::getImage() => __('Still Image', 'integlight'),
                'none' => __('None', 'integlight'),
            ),
        ));
    }
}






class integlight_customizer_selHeader
{

    public function __construct()
    {



        $creHeaderSection = new integlight_customizer_selHeader_creSection();

        new integlight_customizer_HeaderTypeSelecter($creHeaderSection);
        new integlight_customizer_headerImage_updSection();
    }
}


$InteglightSelHeader = new integlight_customizer_selHeader();
