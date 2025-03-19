<?php



// top header slider or Image select  _s ////////////////////////////////////////////////////////////////////////////////


//フロントエンドでの表示制御用
function integlight_display_headerContents()
{
	$choice = get_theme_mod('integlight_display_choice', 'slider');


	switch ($choice) {
		case 'slider':
			// 値1と一致する場合の処理
			get_template_part('template-parts/content', 'slide');

			break;

		case 'image':
			if (get_header_image()) {
				echo '<img src="' . esc_url(get_header_image()) . '" class="topImage" ' .  ' alt="' . esc_attr(get_bloginfo('name')) . '">';
			}
			break;

		default:
			// どのケースにも一致しない場合の処理
	}
}


// 見出しセクション作成クラス _s ////////////////////////////////////////////////////////////////////////////////

if (class_exists('WP_Customize_Control') && ! class_exists('integlight_customizer_creBigTitle')) {
	class integlight_customizer_creBigTitle extends WP_Customize_Control
	{
		public $type = 'heading';
		public function render_content()
		{
			if (! empty($this->label)) {
				echo '<h3 style="border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-bottom: 10px;">' . esc_html($this->label) . '</h3>';
			}
			if (!empty($this->description)) {
				echo '<span class="description customize-control-description">' . esc_html($this->description) . '</span>';
			}
		}
	}
}
// 見出しセクション作成クラス _e ////////////////////////////////////////////////////////////////////////////////


class integlight_customizer_HeaderTypeSelecter
{

	private $pSectionId;
	private $pInteglight_slider_settings;

	public function __construct($sliderSectionId, $slider_settings)
	{
		$this->pSectionId = $sliderSectionId->getSliderOrImageSectionId();
		$this->pInteglight_slider_settings = $slider_settings;
		add_action('customize_register', array($this, 'integlight_customizer_HeaderTypeSelecter'));
	}


	public function integlight_customizer_HeaderTypeSelecter($wp_customize)
	{

		// 選択ボックスを追加
		$wp_customize->add_setting('integlight_display_choice', array(
			'default' => 'header',
			'sanitize_callback' => 'sanitize_text_field',
		));

		$wp_customize->add_control('integlight_display_choice', array(
			'label'    => __('Display Slider or Image', 'integlight'),
			'section'  => $this->pSectionId,
			'settings' => 'integlight_display_choice',
			'type'     => 'select',
			'choices'  => array(
				$this->pInteglight_slider_settings->headerTypeName_slider => __('Slider', 'integlight'),
				$this->pInteglight_slider_settings->headerTypeName_image => __('Image', 'integlight'),
				'none' => __('None', 'integlight'),
			),
		));
	}
}


// top header select  _e ////////////////////////////////////////////////////////////////////////////////


// ヘッダー画像セクションの位置を変更する関数 _s 
class integlight_customizer_headerImage_updSection
{

	private $pPanelId;

	public function __construct($sliderSectionId)
	{
		$this->pPanelId = $sliderSectionId->getSliderPanelId();

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
// ヘッダー画像セクションの位置を変更する関数 _e



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
		<style type="text/css">
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

require_once get_template_directory() . '/inc/integlight-functions-outerAssets.php';

class integlight_customizer_slider_outerAssets
{

	private $pInteglight_slider_settings;


	public function __construct($slider_settings)
	{
		$this->pInteglight_slider_settings = $slider_settings;
		add_action('wp_enqueue_scripts', array($this, 'init_in_wp_enqueue_scripts'));
		add_action('wp_enqueue_scripts', array($this, 'move_jquery_to_footer'));
		add_filter('script_loader_tag', array($this, 'add_defer_to_script'), 10, 2);

		$styles = [
			'integlight-slide' => '/css/integlight-slide-style.css',
		];
		InteglightRegStyles::add_styles($styles);
	}

	public function init_in_wp_enqueue_scripts()
	{
		//wp_enqueue_style('integlight-slide', get_template_directory_uri() . '/css/integlight-slide-style.css', array(), _S_VERSION);
		wp_enqueue_script('integlight_slider-script', get_template_directory_uri() . '/js/integlight-scripts.js', array('jquery'), _S_VERSION, true);

		// カスタマイザーの設定値をJavaScriptに渡す
		wp_localize_script('integlight_slider-script', 'integlight_sliderSettings', array(
			'displayChoice' => get_theme_mod('integlight_display_choice'),
			'changeDuration' => get_theme_mod('integlight_slider_change_duration', '3'),
			'effect' => get_theme_mod('integlight_slider_effect', $this->pInteglight_slider_settings->effectName_fade),
			'fade' => $this->pInteglight_slider_settings->effectName_fade,
			'slide' => $this->pInteglight_slider_settings->effectName_slide,
			'headerTypeNameSlider' => $this->pInteglight_slider_settings->headerTypeName_slider
		));
	}


	/* レンダリングブロック、layout計算増加の防止のためのチューニング s*/
	public function add_defer_to_script($tag, $handle)
	{
		if ($handle === 'integlight_slider-script') {
			return str_replace('src', 'defer src', $tag);
		}
		return $tag;
	}

	public function move_jquery_to_footer()
	{
		if (!is_admin()) {
			wp_deregister_script('jquery'); // 既存の jQuery を解除
			wp_register_script('jquery', includes_url('/js/jquery/jquery.min.js'), array(), '3.7.1', true);
			wp_enqueue_script('jquery');
		}
	}
	/* レンダリングブロック、layout計算増加の防止のためのチューニング s*/
}














class integlight_customizer_slider_creSection
{

	const SLIDER_PANEL_ID = 'slider_panel';
	const SLIDER_SECTION_ID = 'slider_section';
	const SLIDER_OR_IMAGE_SECTION_ID = 'sliderOrImage_section';

	public function __construct()
	{
		add_action('customize_register', array($this, 'creSection'));
	}

	public function creSection($wp_customize)
	{

		// 大セクションを追加
		$wp_customize->add_panel(self::SLIDER_PANEL_ID, array(
			'title'    => integlight_g('Top Header Setting'),
			'description' => integlight_g('Please select whether to display a slider or an image in the top header. The settings button for the selected option will be displayed.'),
			'priority' => 29
		));

		// 画像orスライダー選択セクションを追加
		$wp_customize->add_section(self::SLIDER_OR_IMAGE_SECTION_ID, array(
			'title'    => integlight_g('Select - Slider or Image'),
			'priority' => 29,
			'panel' => self::SLIDER_PANEL_ID,

		));


		// スライダー作成用セクションを追加
		$wp_customize->add_section(self::SLIDER_SECTION_ID, array(
			'title'    => integlight_g('Slider Settings'),
			'priority' => 29,
			'panel' => self::SLIDER_PANEL_ID,
			'active_callback' => function () {
				return get_theme_mod('integlight_display_choice', 'slider') === 'slider';
			},
		));
	}

	public function getSliderPanelId()
	{
		return self::SLIDER_PANEL_ID;
	}

	public function getSliderSectionId()
	{
		return self::SLIDER_SECTION_ID;
	}

	public function getSliderOrImageSectionId()
	{
		return self::SLIDER_OR_IMAGE_SECTION_ID;
	}
}

class integlight_customizer_slider_setting
{

	private $pInteglight_slider_settings;
	private $pSectionId;
	private $pWp_customize;

	public function __construct($slider_settings, $sliderSectionId)
	{
		$this->pSectionId = $sliderSectionId->getSliderSectionId();
		$this->pInteglight_slider_settings = $slider_settings;
		add_action('customize_register', array($this, 'setting'));
	}

	public function setting($wp_customize)
	{
		$this->pWp_customize = $wp_customize;

		/* 効果 */
		$this->labelSetting('integlight_slider_Animation_heading', 'Slider Animation');
		$this->effectSetting('integlight_slider_effect', 'Effect');
		$this->numberSetting('integlight_slider_change_duration', 'Slider Change Duration (seconds)', 1, 1);


		/* テキスト */
		$this->labelSetting('integlight_slider_text_heading', 'Slider Text');
		$this->textSetting('integlight_slider_text_1', 'Slider Text Main');
		$this->textSetting('integlight_slider_text_2', 'Slider Text Sub');
		$this->colorSetting('integlight_slider_text_color', 'Slider Text color');
		$this->fonttypeSetting('integlight_slider_text_font', 'Slider Text Font');
		$this->labelSetting('integlight_slider_text_position_heading', 'Slider Text Position');
		$this->numberSetting('integlight_slider_text_top', 'Slider Text Position Top (px)', 0, 1);
		$this->numberSetting('integlight_slider_text_left', 'Slider Text Position Left (px)', 0, 1);
		/* 画像 */
		$this->labelSetting('integlight_slider_image_heading', 'Slider Image', 'Recommended: 1920px (width) × 1080px (height).');
		$this->imageSetting('integlight_slider_image_1', 'Slider Image 1');
		$this->imageSetting('integlight_slider_image_2', 'Slider Image 2');
		$this->imageSetting('integlight_slider_image_3', 'Slider Image 3');



		/*モバイル */
		/*テキスト*/
		$this->labelSetting('integlight_slider_text_position_heading_mobile', 'Slider Text Position Mobile');
		$this->numberSetting('integlight_slider_text_top_mobile', 'Slider Text Position Top Mobile (px)', 0, 1);
		$this->numberSetting('integlight_slider_text_left_mobile', 'Slider Text Position Left Mobile (px)', 0, 1);
		/*画像*/
		$this->labelSetting('integlight_slider_image_mobile_heading', 'Slider Image mobile *option', 'Recommended: 750px (width) × 1334px (height).*If not set, the PC version will be applied.');
		$this->imageSetting('integlight_slider_image_mobile_1', 'Slider Image mobile 1');
		$this->imageSetting('integlight_slider_image_mobile_2', 'Slider Image mobile 2');
		$this->imageSetting('integlight_slider_image_mobile_3', 'Slider Image mobile 3');
	}



	private function effectSetting($settingName, $label)
	{
		$this->pWp_customize->add_setting($settingName, array(
			'default' => 'slide',
			'sanitize_callback' => 'sanitize_text_field',
		));

		$this->pWp_customize->add_control($settingName, array(
			'label'    => integlight_g($label),
			'section'  => $this->pSectionId,
			'type'     => 'select',
			'choices'  => array(
				$this->pInteglight_slider_settings->effectName_fade  => __('Fade', 'integlight'),
				$this->pInteglight_slider_settings->effectName_slide => __('Slide', 'integlight')
			),
		));
	}

	private function imageSetting($settingName, $label)
	{
		$this->pWp_customize->add_setting($settingName, array(
			'default' => '',
			'sanitize_callback' => 'absint',
		));



		$this->pWp_customize->add_control(new WP_Customize_Media_Control($this->pWp_customize, $settingName, array(
			'label'    => integlight_g($label),
			'section'  => $this->pSectionId,
			'settings' => $settingName,
			'mime_type' => 'image', // 画像のみ許可
		)));
	}




	private function textSetting($settingName, $label)
	{
		$this->pWp_customize->add_setting($settingName, array(
			'default' => $label,
			'sanitize_callback' => 'sanitize_textarea_field',
		));
		$this->pWp_customize->add_control($settingName,  array(
			'label'   => integlight_g($label),
			'section' => $this->pSectionId,
			'settings' => $settingName,
		));
	}

	private function numberSetting($settingName, $label, $min, $step)
	{
		$this->pWp_customize->add_setting($settingName, array(
			'default' => '1',
			'sanitize_callback' => 'absint',
		));

		$this->pWp_customize->add_control($settingName, array(
			'label'    => integlight_g($label),
			'section'  => $this->pSectionId,
			'type'     => 'number',
			'input_attrs' => array(
				'min' => $min,
				'step' => $step,
			),
		));
	}

	private function labelSetting($settingName, $label, $description = '')
	{
		$this->pWp_customize->add_setting($settingName, array(
			'sanitize_callback' => 'sanitize_text_field',
		));
		$this->pWp_customize->add_control(new integlight_customizer_creBigTitle(
			$this->pWp_customize,
			$settingName,
			array(
				'label'    => integlight_g($label),
				'section'  => $this->pSectionId,
				'description' => integlight_g($description)

			)
		));
	}

	private function fonttypeSetting($settingName, $label)
	{
		$this->pWp_customize->add_setting($settingName, array(
			'default'           => 'yu_gothic',
			'sanitize_callback' => 'sanitize_text_field',
		));

		$this->pWp_customize->add_control($settingName, array(
			'label'    => integlight_g($label),
			'section'  => $this->pSectionId,
			'type'     => 'select',
			'choices'  => array(
				'yu_gothic' => integlight_g('yu gothic'),
				'yu_mincho' => integlight_g('yu mincho'),
			),
		));
	}

	private function colorSetting($settingName, $label)
	{
		$this->pWp_customize->add_setting($settingName, array(
			'default'           => '#000000',
			'sanitize_callback' => 'sanitize_hex_color',
		));

		$this->pWp_customize->add_control(new WP_Customize_Color_Control(
			$this->pWp_customize,
			$settingName,
			array(
				'label'    => integlight_g($label),
				'section'  => $this->pSectionId,
				'settings' => $settingName,
			)
		));
	}
}


class integlight_customizer_slider
{


	private $pInteglight_slider_settings;

	public function __construct()
	{


		//グローバルで使う定数を定義
		$GLOBALS['Integlight_slider_settings'] = new stdClass();
		$GLOBALS['Integlight_slider_settings']->effectName_fade = 'fade';
		$GLOBALS['Integlight_slider_settings']->effectName_slide = 'slide';
		$GLOBALS['Integlight_slider_settings']->headerTypeName_slider = 'slider';
		$GLOBALS['Integlight_slider_settings']->headerTypeName_image = 'image';
		global $Integlight_slider_settings;
		$this->pInteglight_slider_settings = $Integlight_slider_settings;
		$creSliderSectionId = new integlight_customizer_slider_creSection();
		new integlight_customizer_HeaderTypeSelecter($creSliderSectionId, $this->pInteglight_slider_settings);




		new integlight_customizer_headerImage_updSection($creSliderSectionId);
		// クラスのインスタンスを生成して処理を開始
		new integlight_customizer_slider_setting($this->pInteglight_slider_settings, $creSliderSectionId);
		new integlight_customizer_slider_applyHeaderTextStyle();


		new integlight_customizer_slider_outerAssets($this->pInteglight_slider_settings);
	}
}

$InteglightSlider = new integlight_customizer_slider();

// slide customiser _e ////////////////////////////////////////////////////////////////////////////////
