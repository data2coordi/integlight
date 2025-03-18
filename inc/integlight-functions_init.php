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

		//add_action('after_setup_theme', [$this, 'integlight_initSampleSetup']);
		add_action('after_switch_theme', [$this, 'integlight_initSampleSetup']);
	}


	private function initSlider()
	{
		// カスタマイザーの設定名
		set_theme_mod('integlight_display_choice', 'slider');

		set_theme_mod('integlight_slider_effect', 'slide');
		set_theme_mod('integlight_slider_change_duration', 3);

		set_theme_mod('integlight_slider_text_1', 'Turn Your Experience and Knowledge into Digital Assets with Integlight');
		set_theme_mod('integlight_slider_text_2', 'The things you casually talk about every day, as well as the knowledge and experience you gain from work or hobbies, can be valuable information for someone. By documenting them in a blog, they accumulate over time and become your "digital asset." Keep sharing, and you may create value that reaches many people.');
		set_theme_mod('integlight_slider_text_font', 'yu_gothic');
		set_theme_mod('integlight_slider_text_top', 100);
		set_theme_mod('integlight_slider_text_left', 200);


		$this->initImage('integlight_slider_image_1', 'sample_slider_pc_01.webp',  'Sample Slider pc image01');
		$this->initImage('integlight_slider_image_2', 'sample_slider_pc_02.webp',  'Sample Slider pc image02');
		$this->initImage('integlight_slider_image_mobile_1', 'sample_slider_sp_01.webp',   'Sample Slider sp image01');
		$this->initImage('integlight_slider_image_mobile_2', 'sample_slider_sp_02.webp',   'Sample Slider sp image02');
	}

	private function initImage($settingName, $imageFilename, $imageTitle)
	{
		remove_theme_mod($settingName); //debug

		// すでに設定済みなら処理しない
		if (get_theme_mod($settingName)) {
			error_log("既にget_theme_mod設定済:" . get_theme_mod($settingName));
			return;
		}

		// テーマ内の画像パス
		$srcFilePath = get_template_directory() . '/img/' . $imageFilename;

		// WordPressのアップロードディレクトリ取得
		$upload_dir = wp_upload_dir();
		$uploaded_file_path = $upload_dir['path'] . '/' . $imageFilename;

		// 画像をアップロードフォルダへコピー
		if (!file_exists($uploaded_file_path)) {
			if (!copy($srcFilePath, $uploaded_file_path)) {
				error_log("Failed to copy file: $srcFilePath to $uploaded_file_path");
				return;
			}
		}

		// MIMEタイプを取得
		$mime_type = wp_check_filetype($uploaded_file_path)['type'] ?? 'image/jpeg';
		error_log('mime:' . $mime_type);
		// 画像のURL
		$imageFileUrl = $upload_dir['url'] . '/' . $imageFilename;

		// メディアライブラリへ登録
		$attachment = array(
			'guid'           => $imageFileUrl,
			'post_mime_type' => $mime_type,
			'post_title'     => $imageTitle,
			'post_content'   => '',
			'post_status'    => 'inherit'
		);
		$attach_id = wp_insert_attachment($attachment, $uploaded_file_path);

		if (is_wp_error($attach_id) || !$attach_id) {
			error_log("Failed to insert attachment: " . print_r($attach_id, true));
			return;
		}

		// 画像のメタデータ生成
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$attach_data = wp_generate_attachment_metadata($attach_id, $uploaded_file_path);
		if ($attach_data) {
			wp_update_attachment_metadata($attach_id, $attach_data);
		} else {
			error_log("Failed to generate metadata for attachment ID: $attach_id");
		}

		// カスタマイザーに設定
		set_theme_mod($settingName, $attach_id);
	}


	public function integlight_initSampleSetup()
	{

		if (get_option('integlight_initSetup_done')) {

			return;
		}

		/*logo*/
		$this->initImage('custom_logo', 'samplelogo_white.png', 'Sample Logo TEST');
		/*slider*/
		$this->initSlider();

		//一度実行したらフラグをセット
		update_option('integlight_initSetup_done', true);
	}
}


new Integlight_initSampleSetup();
