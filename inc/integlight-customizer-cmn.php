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
			"This is a site-wide setting.<br><br><b>Recommended settings</b><br>"
				. "The 'Homepage Settings' are usually fine as default. "
				. "If you want to use a fixed page as the home (top) page, configure it here."
				. "<br><br>It is recommended to set both 'Site Basic Information' and 'Site Type Settings'.",
			'integlight'
		);
		$menu_desc = __(
			"Menu related settings."
				. "<br>You can also configure menus from \"Appearance\" → \"Menus\" in the admin dashboard."
				. "<br><br>1. First, create a menu by clicking \"Create a new menu\" below."
				. "<br><br>2. Next, assign the created menu to a location from \"Menu locations\" below."
				. "<br>(You can choose from two locations: Header and Footer.)"
				. "<br><br><b>Recommended settings:</b>"
				. "<br>Create two menus. Place frequently used items in the Header menu and less used items in the Footer menu.",
			'integlight'
		);
		$header_desc = __('Media settings (slider or static image) for the header area of the homepage (top page).', 'integlight');
		$sidebar_desc = __(
			"Sidebar settings for posts page. You can configure from \"Appearance → Widgets\".<br><br>"
				. "You can place two sidebars: Sidebar 1 and Sidebar 2.<br><br>"
				. "When opening this panel in posts page, the setting buttons for the target sidebars will be displayed.<br><br>"
				. "<b>Recommended setting:</b><br>"
				. "Create widgets in Sidebar 1 and set its position to the right.",
			'integlight'
		);
		$footer_desc = __(
			"Settings related to the footer area at the bottom of the screen.<br><br>* To set up the footer menu, please use the Menu settings in the Customizer.",
			'integlight'
		);
		$design_desc = __(
			"Settings related to the overall site design.<br><br><b>Recommended settings:</b><br>Set the accent color in the color scheme for a consistent site appearance.<br>Background image and Additional CSS can remain unset.",
			'integlight'
		);
		$perf_desc = __(
			"Settings related to performance.<br><br>In most cases, the default values are sufficient.",
			'integlight'
		);


		$panels = [
			'integlight_site_panel'   => [
				'title'       => __('Site Settings', 'integlight'),
				'priority'    => 10,
				'description' => $site_desc,
			],
			'integlight_menu_panel'   => [
				'title'       => __('Menu Settings', 'integlight'),
				'priority'    => 20,
				'description' => $menu_desc,
			],
			'integlight_header_panel' => [
				'title'       => __('Header Settings', 'integlight'),
				'priority'    => 30,
				'description' => $header_desc,
			],
			'integlight_sidebar_panel' => [
				'title'       => __('Sidebar Settings', 'integlight'),
				'priority'    => 40,
				'description' => $sidebar_desc,
			],
			'integlight_footer_panel' => [
				'title'       => __('Footer Settings', 'integlight'),
				'priority'    => 50,
				'description' => $footer_desc,
			],
			'integlight_design_panel'  => [
				'title'       => __('Design Settings', 'integlight'),
				'priority'    => 400,
				'description' => $design_desc,
			],
			'integlight_perf_panel'   => [
				'title'       => __('Performance Settings', 'integlight'),
				'priority'    => 500,
				'description' => $perf_desc,
			],
		];
		return $panels;
	}

	private function default_map()
	{
		return [
			// コアの section を独自パネルへ移動（ID は変えない）
			'title_tagline'      => ['panel' => 'integlight_site_panel',   'title' => __('Site Identity', 'integlight')],
			'static_front_page'  => ['panel' => 'integlight_site_panel',   'title' => __('Homepage Settings', 'integlight')],
			'colors'             => ['panel' => 'integlight_design_panel',  'title' => __('Colors', 'integlight')],
			'background_image'   => ['panel' => 'integlight_design_panel',  'title' => __('Background Image', 'integlight')],
			'custom_css'         => ['panel' => 'integlight_design_panel',  'title' => __('Additional CSS', 'integlight')],
			// nav_menus等はパネル -> それに属するセクションを移動する形で扱う
			'header_image'       => ['panel' => 'integlight_header_panel', 'title' => __('2.Static Image Settings', 'integlight')],
			'nav_menus'          => ['panel' => 'integlight_menu_panel',   'title' => __('Menus', 'integlight')],
			'widgets'            => ['panel' => 'integlight_sidebar_panel', 'title' => __('Widgets', 'integlight')],
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
