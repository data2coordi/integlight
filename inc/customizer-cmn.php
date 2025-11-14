<?php


/**
 * テーマのデフォルト値を一元管理するクラス
 */
class Integlight_Defaults
{
	/**
	 * すべてのデフォルト値を取得
	 * @return array
	 */
	public static function get_all(): array
	{
		return [
			// Sidebar
			'integlight_sidebar1_position' => 'right',
			'integlight_sidebar2_position' => 'none',
			// Theme Color
			'integlight_base_color_setting' => 'pattern8',
			// Site Type
			'integlight_hometype_setting' => 'siteType1',
			// Header Select
			'integlight_display_choice' => 'none',
			// Footer
			'integlight_footer_copy_right' => '',
			'integlight_footer_show_credit' => true,
			// Performance
			'integlight_cache_enable' => true,
			// Slider
			'integlight_slider_image_1' => '',
			'integlight_slider_image_2' => '',
			'integlight_slider_image_3' => '',
			'integlight_slider_image_mobile_1' => '',
			'integlight_slider_image_mobile_2' => '',
			'integlight_slider_image_mobile_3' => '',
			'integlight_slider_text_1' => __('Slider Text Main', 'integlight'),
			'integlight_slider_text_2' => __('Slider Text Sub', 'integlight'),
			'integlight_slider_text_color' => '#000000',
			'integlight_slider_text_font' => 'yu_gothic',
			'integlight_slider_text_top' => '10',
			'integlight_slider_text_left' => '20',
			'integlight_slider_text_top_mobile' => '10',
			'integlight_slider_text_left_mobile' => '20',
			'integlight_slider_effect' => 'slide',
			'integlight_slider_change_duration' => '3',
		];
	}
}

/**
 * カスタマイザーの選択肢コントロールのベースクラス
 */
abstract class Integlight_customizer_choiceCtlBase
{
	public static function sanitize_choices($input, $setting)
	{
		// $setting オブジェクトからカスタマイザーマネージャーを取得
		$manager = $setting->manager;

		// マネージャー経由でコントロールを取得
		$control = $manager->get_control($setting->id);

		// コントロールが存在し、その選択肢の中に $input が存在するかチェック
		if ($control && array_key_exists($input, $control->choices)) {
			return $input;
		}

		// 条件に合わなければデフォルト値を返す
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
			'default' => Integlight_Defaults::get_all()[$settingName] ?? '',
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
			'default' => Integlight_Defaults::get_all()[$settingName] ?? $label,
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
			'default' => Integlight_Defaults::get_all()[$settingName] ?? '1',
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
			'default'           => Integlight_Defaults::get_all()[$settingName] ?? 'yu_gothic',
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
			'default'           => Integlight_Defaults::get_all()[$settingName] ?? '#000000',
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
		$this->pWp_customize->add_setting($settingName, [
			'default' => Integlight_Defaults::get_all()[$settingName] ?? 'slide',
			'sanitize_callback' => 'sanitize_text_field'
		]);
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

/**
 * get_theme_mod のラッパークラス
 * カスタマイザー画面外でも正しいデフォルト値を取得できるようにする
 */
class Integlight_getThemeMod
{
	/**
	 * 設定値を取得
	 * @param string $setting_name 設定名
	 * @param mixed|null $default_override
	 * @return mixed
	 */
	public static function getThemeMod(string $setting_name, $default_override = null)
	{
		$default = $default_override ?? self::getDefaultValue($setting_name);
		return get_theme_mod($setting_name, $default);
	}

	/**
	 * 一元管理されたデフォルト値を取得
	 * @param string $setting_name 設定名
	 * @return mixed
	 */
	protected static function getDefaultValue(string $setting_name)
	{
		$defaults = Integlight_Defaults::get_all();
		return $defaults[$setting_name] ?? '';
	}
}
