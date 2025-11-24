<?php



/* スライダーに表示するテキストにカスタマイザーでユーザーがセットしたスタイルを適用するs */
class integlight_customizer_slider_headerTextStyle
{

	/**
	 * コンストラクタ
	 */
	public function __construct()
	{
		// wp_head に出力するためのフックを登録
		add_action('wp_head', array($this, 'ApplyTextStyles'));
	}

	/**
	 * カスタマイザーの設定値に基づき、.slider .text-overlay のスタイルを出力
	 */
	public function ApplyTextStyles()
	{
		// カスタマイザーから値を取得。未設定の場合はデフォルト値を使用
		$color = Integlight_getThemeMod::getThemeMod('integlight_slider_text_color'); // デフォルトは白
		$left  = Integlight_getThemeMod::getThemeMod('integlight_slider_text_left');      // デフォルト 30px
		$top   = Integlight_getThemeMod::getThemeMod('integlight_slider_text_top');       // デフォルト 300px
		$left_mobile  = Integlight_getThemeMod::getThemeMod('integlight_slider_text_left_mobile');      // デフォルト 30px
		$top_mobile   = Integlight_getThemeMod::getThemeMod('integlight_slider_text_top_mobile');       // デフォルト 300px
		// フォント選択の取得（デフォルトは 'yu_gothic'）

		$font = Integlight_getThemeMod::getThemeMod('integlight_slider_text_font');
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

			.slider .text-overlay h2 {
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





class Integlight_customizer_slider_display_sliderContent
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
		$id1 = $is_mobile ? Integlight_getThemeMod::getThemeMod('integlight_slider_image_mobile_1') : null;
		if (empty($id1)) {
			$id1 = Integlight_getThemeMod::getThemeMod('integlight_slider_image_1');
		}
		if (!empty($id1)) {
			$attr = Integlight_getAttr_byImageCount::getHeaderImageAttr(0);
			$images[0] = wp_get_attachment_image($id1, 'full', false, $attr);
		}

		// slide 2
		$id2 = $is_mobile ? Integlight_getThemeMod::getThemeMod('integlight_slider_image_mobile_2') : null;
		if (empty($id2)) {
			$id2 = Integlight_getThemeMod::getThemeMod('integlight_slider_image_2');
		}
		if (!empty($id2)) {
			$attr = Integlight_getAttr_byImageCount::getHeaderImageAttr(1);
			$images[1] = wp_get_attachment_image($id2, 'full', false, $attr);
		}

		// slide 3
		$id3 = $is_mobile ? Integlight_getThemeMod::getThemeMod('integlight_slider_image_mobile_3') : null;
		if (empty($id3)) {
			$id3 = Integlight_getThemeMod::getThemeMod('integlight_slider_image_3');
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
			nl2br(wp_kses_post(Integlight_getThemeMod::getThemeMod('integlight_slider_text_1'))),
			nl2br(wp_kses_post(Integlight_getThemeMod::getThemeMod('integlight_slider_text_2'))),
		];
	}
}










class integlight_customizer_slider_outerAssets
{



	public function __construct()
	{
		add_action('wp_enqueue_scripts', array($this, 'provideTOjs'));
		Integlight_outerAssets_css_forCall::regSliderCss();
		Integlight_outerAssets_js_forCall::regSliderScripts();
	}

	public function provideTOjs()
	{

		// カスタマイザーの設定値をJavaScriptに渡す
		wp_localize_script(Integlight_outerAssets_js_forCall::getSliderScriptsHandleName(), 'integlight_sliderSettings', array(
			'changeDuration' => Integlight_getThemeMod::getThemeMod('integlight_slider_change_duration', 3),
			'effect' => Integlight_getThemeMod::getThemeMod('integlight_slider_effect', Integlight_customizer_slider_settings::getEffectNameFade()),
			'homeType' => Integlight_getThemeMod::getThemeMod('integlight_hometype_setting', Integlight_customizer_slider_settings::getHomeType1Name()),
			'fadeName' => Integlight_customizer_slider_settings::getEffectNameFade(),
			'slideName' => Integlight_customizer_slider_settings::getEffectNameSlide(),
			'siteType1Name' => Integlight_customizer_slider_settings::getHomeType1Name(),
			'siteType2Name' => Integlight_customizer_slider_settings::getHomeType2Name(),
			'siteType3Name' => Integlight_customizer_slider_settings::getHomeType3Name(),
			'siteType4Name' => Integlight_customizer_slider_settings::getHomeType4Name(),
		));
	}
}







/*カスタマイザーで設定したスライダー機能をフロントでオープンしたときにロード*/
add_action('wp', function () {
	if (is_front_page()) {
		if (Integlight_customizer_selHeader_settingValues::getSlider() === Integlight_getThemeMod::getThemeMod('integlight_display_choice')) {
			if (
				Integlight_getThemeMod::getThemeMod('integlight_slider_image_mobile_1') ||
				Integlight_getThemeMod::getThemeMod('integlight_slider_image_mobile_2') ||
				Integlight_getThemeMod::getThemeMod('integlight_slider_image_mobile_3') ||
				Integlight_getThemeMod::getThemeMod('integlight_slider_image_1') ||
				Integlight_getThemeMod::getThemeMod('integlight_slider_image_2') ||
				Integlight_getThemeMod::getThemeMod('integlight_slider_image_3')
			) {
				// いずれかがセットされているときの処理
				new integlight_customizer_slider_outerAssets();
				new integlight_customizer_slider_headerTextStyle();
			}
		}
	}
});
