<?php

/**
 * Integlight functions and definitions
 * テーマ導入時の初期設定処理
 *
 * @package Integlight
 */



class Integlight_ResetSetup
{
	public function __construct()
	{
		add_action('after_setup_theme', [$this, 'reset_theme_mods_and_images']);
	}

	/**
	 * カスタマイザー設定と画像をリセット
	 */
	public function reset_theme_mods_and_images()
	{
		$this->delete_images();
		$this->reset_theme_mods();
		delete_option('integlight_initSetup_done'); // 初期化フラグ削除
	}

	/**
	 * 画像を削除する
	 */
	private function delete_images()
	{
		$image_mods = [
			'integlight_slider_image_1',
			'integlight_slider_image_2',
			'integlight_slider_image_mobile_1',
			'integlight_slider_image_mobile_2',
			'custom_logo', // 必要なら削除
		];

		foreach ($image_mods as $mod) {
			$attachment_id = get_theme_mod($mod);
			if ($attachment_id && is_numeric($attachment_id)) {
				wp_delete_attachment($attachment_id, true); // メディアライブラリから削除
			}
			remove_theme_mod($mod); // カスタマイザー設定から削除
		}
	}

	/**
	 * 画像以外のカスタマイザー設定をリセット
	 */
	private function reset_theme_mods()
	{
		$text_mods = [
			'integlight_display_choice',
			'integlight_slider_effect',
			'integlight_slider_change_duration',
			'integlight_slider_text_1',
			'integlight_slider_text_2',
			'integlight_slider_text_font',
			'integlight_slider_text_top',
			'integlight_slider_text_left',
		];

		foreach ($text_mods as $mod) {
			remove_theme_mod($mod);
		}
	}
}

// クラスをインスタンス化して実行
//new Integlight_ResetSetup();


class Integlight_initSampleSetup
{
	public function __construct()
	{

		//add_action('after_setup_theme', [$this, 'integlight_initSampleSetup']);
		add_action('after_switch_theme', [$this, 'integlight_initSampleSetup']);
	}

	private function ex_set_theme_mod($settingName, $value)
	{
		// すでに設定済みなら処理しない
		if (get_theme_mod($settingName)) {
			error_log("existing value:" . get_theme_mod($settingName));
			return;
		}
		set_theme_mod($settingName, $value);
		return;
	}

	private function initSlider()
	{
		// カスタマイザーの設定名
		$this->ex_set_theme_mod('integlight_display_choice', 'slider');

		$this->ex_set_theme_mod('integlight_slider_effect', 'slide');
		$this->ex_set_theme_mod('integlight_slider_change_duration', 3);

		$this->ex_set_theme_mod('integlight_slider_text_1', __('Turn Your Experience and Knowledge into Digital Assets with Integlight', 'integlight'));
		$this->ex_set_theme_mod('integlight_slider_text_2', __('The things you casually talk about every day, as well as the knowledge and experience you gain from work or hobbies, can be valuable information for someone. By documenting them in a blog, they accumulate over time and become your digital asset. Keep sharing, and you may create value that reaches many people.', 'integlight'));

		$this->ex_set_theme_mod('integlight_slider_text_font', 'yu_gothic');
		$this->ex_set_theme_mod('integlight_slider_text_top', 100);
		$this->ex_set_theme_mod('integlight_slider_text_left', 200);


		$this->initImage('integlight_slider_image_1', 'sample_slider_pc_01.webp',  'Sample Slider pc image01');
		$this->initImage('integlight_slider_image_2', 'sample_slider_pc_02.webp',  'Sample Slider pc image02');
		$this->initImage('integlight_slider_image_mobile_1', 'sample_slider_sp_01.webp',   'Sample Slider sp image01');
		$this->initImage('integlight_slider_image_mobile_2', 'sample_slider_sp_02.webp',   'Sample Slider sp image02');
	}

	private function initImage($settingName, $imageFilename, $imageTitle)
	{

		// すでに設定済みなら処理しない
		if (get_theme_mod($settingName)) {
			error_log("existing value:" . get_theme_mod($settingName));
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

		/*logo*/
		$this->initImage('custom_logo', 'samplelogo_white.png', 'Sample Logo TEST');
		/*slider*/
		$this->initSlider();

		//一度実行したらフラグをセット
		update_option('integlight_initSetup_done', true);
	}
}

//性能対策：一度のみ実行
if (!get_option('integlight_initSetup_done')) {

	new Integlight_initSampleSetup();
}
