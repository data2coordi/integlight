<?php



class integlight_customizer_slider_creSection
{

	//修正対象@@@
	const SLIDER_SECTION_ID = 'slider_section';

	public function __construct()
	{
		add_action('customize_register', array($this, 'creSection'));
	}

	public function creSection($wp_customize)
	{


		// スライダー作成用セクションを追加
		$wp_customize->add_section(self::SLIDER_SECTION_ID, array(
			'title'    => __('Slider Settings', 'integlight'),
			'priority' => 29,
			'panel' => Integlight_customizer_selHeader_settingValues::getHeaderPanelId(),
			'active_callback' => function () {
				return Integlight_getThemeMod::getThemeMod('integlight_display_choice') === 'slider';
			},
		));
	}


	public function getSliderSectionId()
	{
		return self::SLIDER_SECTION_ID;
	}
}


class Integlight_customizer_slider_imageSettings extends Integlight_customizer_settingHelper
{
	public function register_pc_settings()
	{
		/* 画像 */
		$this->labelSetting('integlight_slider_image_heading', __('Slider Image', 'integlight'), __('Recommended: 1920px (width) × 1080px (height).', 'integlight'));
		$this->imageSetting('integlight_slider_image_1', __('Slider Image 1', 'integlight'));
		$this->imageSetting('integlight_slider_image_2', __('Slider Image 2', 'integlight'));
		$this->imageSetting('integlight_slider_image_3', __('Slider Image 3', 'integlight'));
	}

	public function register_mobile_settings()
	{
		/* モバイル画像 */
		$this->labelSetting('integlight_slider_image_mobile_heading', __('Slider Image mobile *option', 'integlight'), __('Recommended: 750px (width) × 1334px (height).*If not set, the PC version will be applied.', 'integlight'));
		$this->imageSetting('integlight_slider_image_mobile_1', __('Slider Image mobile 1', 'integlight'));
		$this->imageSetting('integlight_slider_image_mobile_2', __('Slider Image mobile 2', 'integlight'));
		$this->imageSetting('integlight_slider_image_mobile_3', __('Slider Image mobile 3', 'integlight'));
	}
}

class Integlight_Customizer_slider_textSettings extends Integlight_customizer_settingHelper
{
	public function register_pc_settings()
	{
		/* テキスト */
		$this->labelSetting('integlight_slider_text_heading', __('Slider Text', 'integlight'));
		$this->textSetting('integlight_slider_text_1', __('Slider Text Main', 'integlight'));
		$this->textSetting('integlight_slider_text_2', __('Slider Text Sub', 'integlight'));
		$this->colorSetting('integlight_slider_text_color', __('Slider Text color', 'integlight'));
		$this->fonttypeSetting('integlight_slider_text_font', __('Slider Text Font', 'integlight'));
		$this->labelSetting('integlight_slider_text_position_heading', __('Slider Text Position', 'integlight'));
		$this->numberSetting('integlight_slider_text_top', __('Slider Text Position Top (px)', 'integlight'), 0, 1);
		$this->numberSetting('integlight_slider_text_left', __('Slider Text Position Left (px)', 'integlight'), 0, 1);
	}
	public function register_mobile_settings()
	{
		/* モバイルテキスト位置 */
		$this->labelSetting('integlight_slider_text_position_heading_mobile', __('Slider Text Position Mobile', 'integlight'));
		$this->numberSetting('integlight_slider_text_top_mobile', __('Slider Text Position Top Mobile (px)', 'integlight'), 0, 1);
		$this->numberSetting('integlight_slider_text_left_mobile', __('Slider Text Position Left Mobile (px)', 'integlight'), 0, 1);
	}
}

class Integlight_Customizer_Slider_effectSettings extends Integlight_customizer_settingHelper
{
	public function register_settings()
	{
		/* 効果 */
		$this->labelSetting('integlight_slider_Animation_heading', __('Slider Animation', 'integlight'));
		$this->effectSetting('integlight_slider_effect', __('Effect', 'integlight'));
		$this->numberSetting('integlight_slider_change_duration', __('Slider Change Duration (seconds)', 'integlight'), 1, 1);
	}
}

class Integlight_customizer_slider_settingManager
{
	private $pSectionId;

	public function __construct($sliderSection)
	{
		$this->pSectionId = $sliderSection->getSliderSectionId();
		add_action('customize_register', array($this, 'register_settings'));
	}

	public function register_settings($wp_customize)
	{
		// 各設定クラスをインスタンス化
		$effect_settings = new Integlight_customizer_slider_effectSettings($wp_customize, $this->pSectionId);
		$text_settings = new Integlight_customizer_slider_textSettings($wp_customize, $this->pSectionId);
		$image_settings = new Integlight_customizer_slider_imageSettings($wp_customize, $this->pSectionId);

		// 元の表示順序を維持するようにメソッドを呼び出す
		$effect_settings->register_settings();

		/* PC設定 */
		$text_settings->register_pc_settings();
		$image_settings->register_pc_settings();

		/* モバイル設定 */
		$text_settings->register_mobile_settings();
		$image_settings->register_mobile_settings();
	}
}


$Integlight_creSliderSection = new integlight_customizer_slider_creSection();
new Integlight_customizer_slider_settingManager($Integlight_creSliderSection);





// slide customiser _e ////////////////////////////////////////////////////////////////////////////////
