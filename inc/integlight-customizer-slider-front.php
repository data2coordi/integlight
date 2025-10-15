<?php

class InteglightSliderContent
{
	/**
	 * 3スライド分の画像HTMLを返す（存在するスライドのみを配列に入れる）
	 *
	 * @return array<string> index 0..2 の可能性がある配列（存在するスライドのみ格納）
	 */
	public static function getImages(): array
	{
		$is_mobile = wp_is_mobile();
		$images = [];

		// slide 1
		$id1 = $is_mobile ? get_theme_mod('integlight_slider_image_mobile_1') : null;
		if (empty($id1)) {
			$id1 = get_theme_mod('integlight_slider_image_1');
		}
		if (!empty($id1)) {
			$attr = Integlight_getAttr_byImageCount::getHeaderImageAttr(0);
			$images[0] = wp_get_attachment_image($id1, 'full', false, $attr);
		}

		// slide 2
		$id2 = $is_mobile ? get_theme_mod('integlight_slider_image_mobile_2') : null;
		if (empty($id2)) {
			$id2 = get_theme_mod('integlight_slider_image_2');
		}
		if (!empty($id2)) {
			$attr = Integlight_getAttr_byImageCount::getHeaderImageAttr(1);
			$images[1] = wp_get_attachment_image($id2, 'full', false, $attr);
		}

		// slide 3
		$id3 = $is_mobile ? get_theme_mod('integlight_slider_image_mobile_3') : null;
		if (empty($id3)) {
			$id3 = get_theme_mod('integlight_slider_image_3');
		}
		if (!empty($id3)) {
			$attr = Integlight_getAttr_byImageCount::getHeaderImageAttr(2);
			$images[2] = wp_get_attachment_image($id3, 'full', false, $attr);
		}

		return $images;
	}

	/**
	 * スライダー用の2行テキストを返す
	 *
	 * @return array<string> [line1, line2]
	 */
	public static function getTexts(): array
	{
		return [
			nl2br(wp_kses_post(get_theme_mod('integlight_slider_text_1', ''))),
			nl2br(wp_kses_post(get_theme_mod('integlight_slider_text_2', ''))),
		];
	}
}










class integlight_customizer_slider_outerAssets
{



	public function __construct()
	{
		add_action('wp_enqueue_scripts', array($this, 'provideTOjs'));
		integlight_load_css_forCall::regSliderCss();
		integlight_load_scripts_forCall::regSliderScripts();
	}

	public function provideTOjs()
	{

		// カスタマイザーの設定値をJavaScriptに渡す
		wp_localize_script(integlight_load_scripts_forCall::getSliderScriptsHandleName(), 'integlight_sliderSettings', array(
			'changeDuration' => get_theme_mod('integlight_slider_change_duration', 3),
			'effect' => get_theme_mod('integlight_slider_effect', InteglightSliderSettings::getEffectNameFade()),
			'homeType' => get_theme_mod('integlight_hometype_setting', InteglightSliderSettings::getHomeType1Name()),
			'fadeName' => InteglightSliderSettings::getEffectNameFade(),
			'slideName' => InteglightSliderSettings::getEffectNameSlide(),
			'home1Name' => InteglightSliderSettings::getHomeType1Name(),
			'home2Name' => InteglightSliderSettings::getHomeType2Name(),
			'home3Name' => InteglightSliderSettings::getHomeType3Name(),
			'home4Name' => InteglightSliderSettings::getHomeType4Name(),
		));
	}
}







/*カスタマイザーで設定したスライダー機能をフロントでオープンしたときにロード*/
add_action('wp', function () {
	if (is_front_page()) {
		if (InteglightHeaderSettings::getSlider() === get_theme_mod('integlight_display_choice', 'none')) {
			if (
				get_theme_mod('integlight_slider_image_mobile_1') ||
				get_theme_mod('integlight_slider_image_mobile_2') ||
				get_theme_mod('integlight_slider_image_mobile_3') ||
				get_theme_mod('integlight_slider_image_1') ||
				get_theme_mod('integlight_slider_image_2') ||
				get_theme_mod('integlight_slider_image_3')
			) {
				// いずれかがセットされているときの処理
				new integlight_customizer_slider_outerAssets();
			}
		}
	}
});
