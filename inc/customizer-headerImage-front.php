<?php

class Integlight_customizer_headerImage_displayContent
{
	public static function getTexts(): array
	{
		return [
			nl2br(wp_kses_post(get_theme_mod('integlight_header_image_text_1', ''))),
			nl2br(wp_kses_post(get_theme_mod('integlight_header_image_text_2', ''))),
		];
	}
}


/* スライダーに表示するテキストにカスタマイザーでユーザーがセットしたスタイルを適用するs */
class integlight_customizer_headerImage_textStyle
{

	/**
	 * コンストラクタ
	 */
	public function __construct()
	{
		// wp_head に出力するためのフックを登録
		add_action('wp_head', array($this, 'applyTextStyles'));
	}

	/**
	 * カスタマイザーの設定値に基づき、.slider .text-overlay のスタイルを出力
	 */
	public function applyTextStyles()
	{

		// カスタマイザーから値を取得。未設定の場合はデフォルト値を使用
		$color = get_theme_mod('integlight_header_image_text_color', '#ffffff'); // デフォルトは白
		$left  = get_theme_mod('integlight_header_image_text_left', 1);      // デフォルト 30px
		$top   = get_theme_mod('integlight_header_image_text_top', 1);       // デフォルト 300px
		$left_mobile  = get_theme_mod('integlight_header_image_text_left_mobile', 1);      // デフォルト 30px
		$top_mobile   = get_theme_mod('integlight_header_image_text_top_mobile', 1);       // デフォルト 300px
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





/*カスタマイザーで設定したスライダー機能をフロントでオープンしたときにロード*/
add_action('wp', function () {
	if (is_front_page()) {
		if (Integlight_customizer_selHeader_settingValues::getImage() === get_theme_mod('integlight_display_choice', 'none')) {
			Integlight_outerAssets_css_forCall::regHeaderImageCss();
			new integlight_customizer_headerImage_textStyle();
		}
	}
});
