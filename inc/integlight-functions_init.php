<?php

/**
 * Integlight functions and definitions
 * テーマ導入時の初期設定処理
 *
 * @package Integlight
 */


class Integlight_initSampleSetup
{
	public function __construct()
	{

		add_action('after_setup_theme', [$this, 'integlight_initSampleSetup']);
		//add_action('after_switch_theme', [$this, 'integlight_initSampleSetup']);
	}


	private function initSlider()
	{
		// カスタマイザーの設定名
		set_theme_mod('integlight_display_choice', 'slider');

		set_theme_mod('integlight_slider_effect', 'slide');
		set_theme_mod('integlight_slider_change_duration', 3);

		set_theme_mod('integlight_slider_text_1', 'Integlightであなたの経験・知識をデジタル資産に');
		set_theme_mod('integlight_slider_text_2', 'あなたが日々何気なく話していることや、仕事や趣味で得た知識・経験は、誰かにとって価値ある情報です。ブログに記録することで、それは時間とともに蓄積され、あなたの「デジタル資産」となります。発信を続けることで、多くの人に届く価値を生み出してみませんか？');
		set_theme_mod('integlight_slider_text_font', 'yu_gothic');
		set_theme_mod('integlight_slider_text_top', 100);
		set_theme_mod('integlight_slider_text_left', 200);

		$this->initImage('integlight_slider_image_1', '/sample_slider_pc_01.webp', 'Sample Slider pc image01');
		$this->initImage('integlight_slider_image_2', '/sample_slider_pc_02.webp', 'Sample Slider pc image02');
		$this->initImage('integlight_slider_image_mobile_1', '/sample_slider_sp_01.webp', 'Sample Slider sp image01');
		$this->initImage('integlight_slider_image_mobile_2', '/sample_slider_sp_02.webp', 'Sample Slider sp image02');
	}

	private function initImage($settingName, $imageFilename, $imageTitle)
	{



		// すでにロゴが設定されている場合は何もしない
		if (get_theme_mod($settingName)) {
			return;
		}

		// サンプルのロゴ画像をテーマディレクトリ内に配置

		$logo_path = get_template_directory() . '/img' . $imageFilename;

		// WordPressメディアライブラリに画像を登録
		$upload_dir = wp_upload_dir();

		if (!file_exists($upload_dir['path'] . $imageFilename)) {
			copy($logo_path, $upload_dir['path'] . $imageFilename);
		}


		$logo_url = $upload_dir['url'] . $imageFilename;
		// メディアライブラリに登録
		$attachment = array(
			'guid'           => $logo_url,
			'post_mime_type' => 'image/png',
			'post_title'     => $imageTitle,
			'post_content'   => '',
			'post_status'    => 'inherit'
		);
		$attach_id = wp_insert_attachment($attachment, $upload_dir['path'] . $imageFilename);

		// 画像のメタデータを生成
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$attach_data = wp_generate_attachment_metadata($attach_id, $upload_dir['path'] . $imageFilename);
		wp_update_attachment_metadata($attach_id, $attach_data);

		// テーマカスタマイザーの `custom_logo` オプションを更新
		set_theme_mod($settingName, $attach_id);
	}

	public function integlight_initSampleSetup()
	{

		if (get_option('integlight_initSetup_done')) {

			return;
		}

		/*logo*/
		$this->initImage('custom_logo', '/samplelogo_white.png', 'Sample Logo TEST');
		/*slider*/
		$this->initSlider();

		//一度実行したらフラグをセット
		update_option('integlight_initSetup_done', true);
	}
}
update_option('integlight_initSetup_done', false);

new Integlight_initSampleSetup();
