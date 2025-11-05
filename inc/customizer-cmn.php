<?php



/**
 * カスタマイザーの選択肢コントロールのベースクラス
 */
abstract class Integlight_customizer_choiceCtlBase
{
	public function sanitize_choices($input, $setting)
	{
		global $wp_customize;
		$control = $wp_customize->get_control($setting->id);
		if (array_key_exists($input, $control->choices)) {
			return $input;
		}
		return $setting->default;
	}
}


// ヘッダースライダー、ヘッダー画像の設定で使うヘルパー s ///////////////////////////////////////////////////////////////////////////////
abstract class Integlight_customizer_settingHelper
{
	protected $pWp_customize;
	protected $pSectionId;

	public function __construct($wp_customize, $sectionId)
	{
		$this->pWp_customize = $wp_customize;
		$this->pSectionId = $sectionId;
	}

	protected function imageSetting($settingName, $label)
	{
		$this->pWp_customize->add_setting($settingName, array(
			'default' => '',
			'sanitize_callback' => 'absint',
		));



		$this->pWp_customize->add_control(new WP_Customize_Media_Control($this->pWp_customize, $settingName, array(
			'label'    => $label,
			'section'  => $this->pSectionId,
			'settings' => $settingName,
			'mime_type' => 'image', // 画像のみ許可
		)));
	}

	protected function textSetting($settingName, $label)
	{
		$this->pWp_customize->add_setting($settingName, array(
			'default' => $label,
			'sanitize_callback' => 'sanitize_textarea_field',
		));
		$this->pWp_customize->add_control($settingName,  array(
			'label'   => $label,
			'section' => $this->pSectionId,
			'settings' => $settingName,
			'type'    => 'textarea', // ← これを追加！

		));
	}

	protected function numberSetting($settingName, $label, $min, $step)
	{
		$this->pWp_customize->add_setting($settingName, array(
			'default' => '1',
			'sanitize_callback' => 'absint',
		));

		$this->pWp_customize->add_control($settingName, array(
			'label'    => $label,
			'section'  => $this->pSectionId,
			'type'     => 'number',
			'input_attrs' => array(
				'min' => $min,
				'step' => $step,
			),
		));
	}

	protected function labelSetting($settingName, $label, $description = '')
	{
		$this->pWp_customize->add_setting($settingName, array(
			'sanitize_callback' => 'sanitize_text_field',
		));
		$this->pWp_customize->add_control(new Integlight_customizer_creBigTitle(
			$this->pWp_customize,
			$settingName,
			array(
				'label'    => $label,
				'section'  => $this->pSectionId,
				'description' => $description

			)
		));
	}

	protected function fonttypeSetting($settingName, $label)
	{
		$this->pWp_customize->add_setting($settingName, array(
			'default'           => 'yu_gothic',
			'sanitize_callback' => 'sanitize_text_field',
		));

		$this->pWp_customize->add_control($settingName, array(
			'label'    => $label,
			'section'  => $this->pSectionId,
			'type'     => 'select',
			'choices'  => array(
				'yu_gothic' => __('yu gothic', 'integlight'),
				'yu_mincho' => __('yu mincho', 'integlight'),
			),
		));
	}

	protected function colorSetting($settingName, $label)
	{
		$this->pWp_customize->add_setting($settingName, array(
			'default'           => '#000000',
			'sanitize_callback' => 'sanitize_hex_color',
		));

		$this->pWp_customize->add_control(new WP_Customize_Color_Control(
			$this->pWp_customize,
			$settingName,
			array(
				'label'    => $label,
				'section'  => $this->pSectionId,
				'settings' => $settingName,
			)
		));
	}

	protected function effectSetting($settingName, $label)
	{
		$this->pWp_customize->add_setting($settingName, ['default' => 'slide', 'sanitize_callback' => 'sanitize_text_field']);
		$this->pWp_customize->add_control($settingName, [
			'label'    => $label,
			'section'  => $this->pSectionId,
			'type'     => 'select',
			'choices'  => [
				Integlight_customizer_slider_settings::getEffectNameFade()  => __('Fade', 'integlight'),
				Integlight_customizer_slider_settings::getEffectNameSlide() => __('Slide', 'integlight')
			],
		]);
	}
}

// ヘッダースライダー、ヘッダー画像の設定で使うヘルパー e /////////////// 

//get_theme_mod のデフォルト値をカスタマイザー登録時の default 値から取得するクラス s/////////////////////
class Integlight_getThemeMod
{

	/**
	 * カスタマイザー設定値を取得
	 * カスタマイザー登録時の default 値を自動参照
	 *
	 * @param string $setting_name 設定名
	 * @return mixed 設定値（未設定時は default 値）
	 */
	public static function getThemeMod($setting_name)
	{
		$default = self::getDefaultValue($setting_name);
		return get_theme_mod($setting_name, $default);
	}

	/**
	 * カスタマイザー登録時の default 値を取得
	 * ※ $wp_customize 経由で取得できない場合は空文字を返す
	 *
	 * @param string $setting_name 設定名
	 * @return mixed デフォルト値
	 */
	protected static function getDefaultValue($setting_name)
	{
		global $wp_customize;

		if (isset($wp_customize) && $wp_customize->get_setting($setting_name)) {
			return $wp_customize->get_setting($setting_name)->default;
		}

		// カスタマイザー外で呼ばれた場合は default を取得できないため空文字
		return '';
	}
}

//使用例：
//$value = Integlight_getThemeMod::getThemeMod( 'integlight_header_image_text_1' );

//get_theme_mod のデフォルト値をカスタマイザー登録時の default 値から取得するクラス e/////////////////////