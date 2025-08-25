<?php
class Integlight_Customizer_Panel_Description_AlwaysVisible
{
	/**
	 * 対象パネルID（例: ['integlight_site_panel']）
	 * 空配列の場合はすべてのパネルを対象
	 */
	private array $panels;

	public function __construct(array $panels = [])
	{
		$this->panels = $panels;

		// JS をフッターに出力
		add_action('customize_controls_print_footer_scripts', [$this, 'inject_always_visible_script']);
	}

	public function inject_always_visible_script()
	{
		// JSON化して JS に渡す
		$panels_js = json_encode($this->panels);
?>
		<script>
			jQuery(document).ready(function($) {
				var panels = <?php echo $panels_js; ?>;

				if (panels.length === 0) {
					// 全パネル対象
					$('.customize-panel-description').each(function() {
						$(this).css({
							'display': 'block',
							'opacity': 1,
							'visibility': 'visible'
						});
					});
				} else {
					// 指定パネルのみ
					panels.forEach(function(id) {
						// パネルIDには sub-accordion-panel- プレフィックスを付ける
						var panel = $('#sub-accordion-panel-' + id);
						if (panel.length) {
							panel.find('.customize-panel-description').css({
								'display': 'block',
								'opacity': 1,
								'visibility': 'visible'
							});
						}
					});
				}
			});
		</script>
	<?php
	}
}

new Integlight_Customizer_Panel_Description_AlwaysVisible(
	[
		'integlight_site_panel',
		'integlight_menu_panel',
		'integlight_header_panel',
		'integlight_sidebar_panel',
		'integlight_footer_panel',
		'integlight_design_panel',
		'integlight_perf_panel'
	]
);


/**
 * Integlight: customizer rearrange
 * コアのセクションを独自パネルに移動する（子テーマ/プラグインは下に追加される前提）
 */

class Integlight_Customizer_Manager
{
	/** @var array */
	private $panels;

	/** @var array section_id => [ 'panel' => panel_id, 'title' => '任意名' ] */
	private $map;

	public function __construct()
	{
		$this->panels = $this->default_panels();
		$this->map = $this->default_map();

		add_action('customize_register', [$this, 'register_panels'], 15);
		add_action('customize_register', [$this, 'apply_mapping'], 20);
	}

	private function default_panels()
	{
		$site_desc = __(
			'サイト全体に関連する設定です。'
				. '<br><br><b>＜お勧めの設定＞</b>'
				. '<br>「ホームページ設定」は通常は既定のままで問題ありません。※ホーム（トップ）ページを固定ページにしたい場合は設定してください。'
				. '<br><br>「サイト基本情報」と「サイトタイプ設定」は設定することをお勧めします。',
			'integlight'
		);
		$menu_desc = __(
			'メニューに関連する設定です。'
				. '<br>※管理画面の「外観」→「メニュー」から設定することもできます。'
				. '<br><br>１．下記「メニューを新規作成」でメニューを作成してください。'
				. '<br><br>２．次に、作成したメニューの配置先を下記「メニューの位置」から設定してください。'
				. '<br>（ヘッダーとフッターの２箇所からメニューの位置を選択できます）'
				. '<br><br><b>＜お勧めの設定＞</b>'
				. '<br>2つのメニューを作成し、よく使われるメニュー項目をヘッダーに配置し、あまり使われないメニュー項目はフッターに配置',
			'integlight'
		);
		$header_desc = __('ホームページ（トップページ）のヘッダー部分に表示するメディア（スライダーまたは静止画像）の設定です。', 'integlight');
		$sidebar_desc = __(
			'投稿ページに表示するサイドバーに関連する設定です。'
				. '<br>※管理画面の「外観」→「ウィジェット」から設定することもできます。'
				. '<br><br>サイドバー１、サイドバー２の２つのサイドバーを配置することができます。'
				. '<br><br>投稿ページで本パネルをオープンすると対象のサイドバーの設定ボタンが表示されます。'
				. '<br><br><b>＜お勧めの設定＞</b>'
				. '<br>サイドバー１にウィジェットを作成して、サイドバー１の位置を右に設定',
			'integlight'
		);
		$footer_desc = __(
			'画面下部のフッターに関連する設定です。'
				. '<br><br>※フッターメニュー設定はカスタマイズのメニュー設定から行ってください。',
			'integlight'
		);
		$design_desc = __(
			'サイト全体のデザインに関連する設定です。'
				. '<br><br><b>＜お勧めの設定＞</b>'
				. '<br>配色にあるアクセントカラーを設定することで、サイトが調和します。'
				. '<br>背景画像・追加CSSは未設定のままで問題ありません。',
			'integlight'
		);
		$perf_desc = __('パフォーマンスに関連する設定です。' .
			'<br><br>通常は既定のままで問題ありません。', 'integlight');


		return [
			'integlight_site_panel'   => ['title' => __('サイト設定', 'integlight'), 'priority' => 10, 'description' => $site_desc],
			'integlight_menu_panel'   => ['title' => __('メニュー設定', 'integlight'), 'priority' => 20, 'description' => $menu_desc],
			'integlight_header_panel' => ['title' => __('ヘッダー設定', 'integlight'), 'priority' => 30, 'description' => $header_desc],
			'integlight_sidebar_panel' => ['title' => __('サイドバー設定', 'integlight'), 'priority' => 40, 'description' => $sidebar_desc],
			'integlight_footer_panel' => ['title' => __('フッター設定', 'integlight'), 'priority' => 50, 'description' => $footer_desc],
			'integlight_design_panel'  => ['title' => __('デザイン設定', 'integlight'), 'priority' => 400, 'description' => $design_desc],
			'integlight_perf_panel'   => ['title' => __('パフォーマンス設定', 'integlight'), 'priority' => 500, 'description' => $perf_desc],
		];
	}

	private function default_map()
	{
		return [
			// コアの section を独自パネルへ移動（ID は変えない）
			'title_tagline'      => ['panel' => 'integlight_site_panel',   'title' => __('サイト基本情報', 'integlight')],
			'static_front_page'  => ['panel' => 'integlight_site_panel',   'title' => __('ホームページ設定', 'integlight')],
			'colors'             => ['panel' => 'integlight_design_panel',  'title' => __('配色', 'integlight')],
			'background_image'   => ['panel' => 'integlight_design_panel',  'title' => __('背景画像', 'integlight')],
			'custom_css'         => ['panel' => 'integlight_design_panel',  'title' => __('追加CSS', 'integlight')],
			// nav_menus等はパネル -> それに属するセクションを移動する形で扱う
			'header_image'       => ['panel' => 'integlight_header_panel', 'title' => __('2.静止画像設定', 'integlight')],
			'nav_menus'          => ['panel' => 'integlight_menu_panel',   'title' => __('メニュー', 'integlight')],
			'widgets'            => ['panel' => 'integlight_sidebar_panel', 'title' => __('ウィジェット', 'integlight')],
		];
	}

	public function register_panels($wp_customize)
	{
		foreach ($this->panels as $id => $args) {
			if (! $wp_customize->get_panel($id)) {
				$wp_customize->add_panel($id, array_merge(['capability' => 'edit_theme_options'], $args));
			}
		}
	}
	public function apply_mapping($wp_customize)
	{
		foreach ($this->map as $core_id => $target) {
			// セクションが存在する場合
			$section = $wp_customize->get_section($core_id);
			if ($section) {
				$section->panel = $target['panel'];
				$section->title = $target['title'];
				continue;
			}

			// パネル（メニュー）の場合
			$panel = $wp_customize->get_panel($core_id);
			if ($panel) {
				foreach ($wp_customize->sections() as $s_id => $s_obj) {
					if (isset($s_obj->panel) && $s_obj->panel === $core_id) {
						$s_obj->panel = $target['panel'];
					}
				}
				if ($core_id != 'nav_menus') { //'nav_menus'を削除すると全て消えるため。おそらく不具合
					$wp_customize->remove_panel($core_id);
				}
				continue;
			}
		}
	}
}

// init
new Integlight_Customizer_Manager();

// セクション上に見出し作成クラス _s ////////////////////////////////////////////////////////////////////////////////

/**
 * セクション上に説明を追加する。
 */
if (class_exists('WP_Customize_Control')) {

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

// セクション上に見出し作成クラス _e ////////////////////////////////////////////////////////////////////////////////

// パネル上のセクションボタンに説明追加クラス _s ////////////////////////////////////////////////////////////////////////////////
class Integlight_Customizer_Section_Description
{
	private string $section_id;
	private string $description;
	private string $position; // 'before' か 'after'

	public function __construct(string $section_id, string $description, string $position = 'after')
	{
		$this->section_id  = $section_id;
		$this->description = $description;
		$this->position    = in_array($position, ['before', 'after'], true) ? $position : 'after';

		// PHP 側：既存セクションの description を空にする
		add_action('customize_register', [$this, 'clear_section_description']);

		// JS 側：タイトル上または下に説明文を挿入
		add_action('customize_controls_print_footer_scripts', [$this, 'inject_description_script']);
	}

	public function clear_section_description($wp_customize)
	{
		if ($wp_customize->get_section($this->section_id)) {
			$wp_customize->get_section($this->section_id)->description = '';
		}
	}

	public function inject_description_script()
	{
		$section_id_js = esc_js($this->section_id);
		$description_html = wp_kses_post($this->description);
		$position_js = esc_js($this->position);
	?>
		<script>
			jQuery(document).ready(function($) {
				var section = $('#accordion-section-<?php echo $section_id_js; ?>');
				if (section.length && !section.find('.custom-section-description').length) {
					var descriptionHTML = '<div class="custom-section-description" style="margin-bottom:6px;color:#555;font-size:13px;"><?php echo $description_html; ?></div>';

					// before/after を選択可能
					if (['before', 'after'].includes('<?php echo $position_js; ?>')) {
						section.find('h3').first()['<?php echo $position_js; ?>'](descriptionHTML);
					} else {
						section.find('h3').first().after(descriptionHTML);
					}
				}
			});
		</script>
		<style>
			#accordion-section-<?php echo $section_id_js; ?>.custom-section-description {
				line-height: 1.4;
			}
		</style>
<?php
	}
}
