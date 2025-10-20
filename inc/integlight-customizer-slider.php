<?php



// top header slider or Image select  _s ////////////////////////////////////////////////////////////////////////////////









/* スライダーに表示するテキストにカスタマイザーでユーザーがセットしたスタイルを適用するs */
class integlight_customizer_slider_applyHeaderTextStyle
{

	/**
	 * コンストラクタ
	 */
	public function __construct()
	{
		// wp_head に出力するためのフックを登録
		add_action('wp_head', array($this, 'integlight_slider_applyTextStyles'));
	}

	/**
	 * カスタマイザーの設定値に基づき、.slider .text-overlay のスタイルを出力
	 */
	public function integlight_slider_applyTextStyles()
	{
		// カスタマイザーから値を取得。未設定の場合はデフォルト値を使用
		$color = get_theme_mod('integlight_slider_text_color', '#ffffff'); // デフォルトは白
		$left  = get_theme_mod('integlight_slider_text_left', 30);      // デフォルト 30px
		$top   = get_theme_mod('integlight_slider_text_top', 300);       // デフォルト 300px
		$left_mobile  = get_theme_mod('integlight_slider_text_left_mobile', 20);      // デフォルト 30px
		$top_mobile   = get_theme_mod('integlight_slider_text_top_mobile', 200);       // デフォルト 300px
		// フォント選択の取得（デフォルトは 'yu_gothic'）

		$font = get_theme_mod('integlight_slider_text_font', 'yu_gothic');
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
			.slider .text-overlay {
				position: absolute;
				left: <?php echo absint($left); ?>px;
				top: <?php echo absint($top); ?>px;
				color: <?php echo esc_attr($color); ?>;
			}

			.slider .text-overlay h1 {
				font-family: <?php echo esc_attr($font_family); ?>;
			}

			@media only screen and (max-width: 767px) {
				.slider .text-overlay {
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
			'panel' => InteglightHeaderSettings::getHeaderPanelId(),
			'active_callback' => function () {
				return get_theme_mod('integlight_display_choice', 'none') === 'slider';
			},
		));
	}


	public function getSliderSectionId()
	{
		return self::SLIDER_SECTION_ID;
	}
}


class Integlight_Customizer_Slider_Image_Settings extends Integlight_Customizer_Setting_Helper
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

class Integlight_Customizer_Slider_Text_Settings extends Integlight_Customizer_Setting_Helper
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

class Integlight_Customizer_Slider_Effect_Settings extends Integlight_Customizer_Setting_Helper
{
	public function register_settings()
	{
		/* 効果 */
		$this->labelSetting('integlight_slider_Animation_heading', __('Slider Animation', 'integlight'));
		$this->effectSetting('integlight_slider_effect', __('Effect', 'integlight'));
		$this->numberSetting('integlight_slider_change_duration', __('Slider Change Duration (seconds)', 'integlight'), 1, 1);
	}
}

class Integlight_Customizer_Slider_Setting_Manager
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
		$effect_settings = new Integlight_Customizer_Slider_Effect_Settings($wp_customize, $this->pSectionId);
		$text_settings = new Integlight_Customizer_Slider_Text_Settings($wp_customize, $this->pSectionId);
		$image_settings = new Integlight_Customizer_Slider_Image_Settings($wp_customize, $this->pSectionId);

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

class integlight_customizer_slider
{

	public function __construct()
	{


		$creSliderSection = new integlight_customizer_slider_creSection();
		new Integlight_Customizer_Slider_Setting_Manager($creSliderSection);
		new integlight_customizer_slider_applyHeaderTextStyle();
	}
}


$InteglightSlider = new integlight_customizer_slider();




// slide customiser _e ////////////////////////////////////////////////////////////////////////////////
