<?php

class InteglightHeaderImageContent
{
	/**
	 * 3スライド分の画像HTMLを返す（存在するスライドのみを配列に入れる）
	 *
	 * @return array<string> index 0..2 の可能性がある配列（存在するスライドのみ格納）
	 */
	/**
	 * スライダー用の2行テキストを返す
	 *
	 * @return array<string> [line1, line2]
	 */
	public static function getTexts(): array
	{
		return [
			nl2br(wp_kses_post(get_theme_mod('integlight_header_image_text_1', ''))),
			nl2br(wp_kses_post(get_theme_mod('integlight_header_image_text_2', ''))),
		];
	}
}



/*カスタマイザーで設定したスライダー機能をフロントでオープンしたときにロード*/
add_action('wp', function () {
	if (is_front_page()) {
		if (InteglightHeaderSettings::getImage() === get_theme_mod('integlight_display_choice', 'none')) {
			integlight_load_css_forCall::regHeaderImageCss();
		}
	}
});
