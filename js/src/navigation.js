/*
 * src/js/integlight-menu.js
 * 小規模クラスに分割した設計例（一致動作版）
 */

// =============================
// 共通ユーティリティ
// =============================
class MenuUtils {
	static closeSiblings(item) {
		Array.from(item.parentElement.children)
			.filter(sib => sib !== item)
			.forEach(sib => sib.classList.remove('active'));
	}
}

// =============================
// メニュー開閉制御（デスクトップ／モバイル共通）
// =============================
class MenuController {
	constructor(linkSelector = '.menu-item-has-children > a') {
		this.menuLinks = document.querySelectorAll(linkSelector);
		this.menuItems = document.querySelectorAll('.menu-item-has-children');
	}

	init() {
		this.menuLinks.forEach(link => {
			link.addEventListener('click', this.onClick.bind(this));
			link.addEventListener('keydown', this.onKeydown.bind(this));
		});
		this.menuItems.forEach(item => {
			item.addEventListener('focusout', this.onFocusOut.bind(this));
		});
	}

	onClick(e) {
		const link = e.currentTarget;
		const width = link.offsetWidth;
		// 右端40pxのみ開閉
		if (e.offsetX <= width - 40) return;
		e.preventDefault();
		e.stopPropagation();
		const item = link.parentElement;
		MenuUtils.closeSiblings(item);
		item.classList.toggle('active');
	}

	onKeydown(e) {
		if (e.key !== 'Tab') return;
		const item = e.currentTarget.parentElement;
		// すでに開いていれば何もしない
		if (item.classList.contains('active')) return;
		MenuUtils.closeSiblings(item);
		item.classList.add('active');
	}

	onFocusOut(e) {
		const item = e.currentTarget;
		setTimeout(() => {
			if (!item.contains(document.activeElement)) {
				item.classList.remove('active');
			}
		}, 0);
	}
}

// =============================
// グローバルキー（Escape）対応
// =============================
class GlobalKeyController {
	init() {
		document.addEventListener('keydown', e => {
			if (e.key !== 'Escape') return;
			const focused = document.activeElement;
			const menuItem = focused.closest('.menu-item-has-children.active');
			if (menuItem) {
				menuItem.classList.remove('active');
				focused.blur();
			}
		});
	}
}

// =============================
// モバイル用アクセスビリティ
// =============================
class MobileMenuController {
	constructor({
		toggleButtonSel = '#menuToggle-button', // toggleLabelSel を toggleButtonSel に変更
		checkboxSel = '.menuToggle-checkbox',
		containerSel = '.menuToggle-containerForMenu',
		breakpoint = 768
	} = {}) {
		this.toggleButton = document.querySelector(toggleButtonSel); // toggleLabel を toggleButton に変更
		this.checkbox = document.querySelector(checkboxSel);
		this.container = document.querySelector(containerSel);
		this.bp = breakpoint;
	}

	init() {
		if (!window.matchMedia(`(max-width: ${this.bp}px)`).matches) return;
		if (!this.toggleButton || !this.checkbox || !this.container) return; // toggleLabel を toggleButton に変更

		// button要素はデフォルトでtabindexが設定されるため、明示的なtabindex="0"は不要

		// 初期状態はHTMLで設定済みなので不要
		// this.toggleButton.setAttribute('aria-expanded', 'false');
		this.container.setAttribute('aria-hidden', 'true');
		this.container.querySelectorAll('a').forEach(a => a.setAttribute('tabindex', '-1'));

		this.checkbox.addEventListener('change', () => this.update(this.checkbox.checked));

		// button要素の場合、clickイベントで十分
		this.toggleButton.addEventListener('click', () => {
			this.checkbox.checked = !this.checkbox.checked;
			this.update(this.checkbox.checked);
		});

		// EnterやSpaceキーはbuttonのデフォルト動作で処理されるため、明示的なkeydownリスナーは不要だが、
		// checkboxの状態と同期させるために残す場合は以下のようになる。
		// this.toggleButton.addEventListener('keydown', e => {
		//     if (e.key === 'Enter' || e.key === ' ') {
		//         e.preventDefault(); // デフォルト動作をキャンセルしない場合は削除
		//         this.checkbox.checked = !this.checkbox.checked;
		//         this.update(this.checkbox.checked);
		//     }
		// });
	}

	update(isOpen) {
		this.toggleButton.setAttribute('aria-expanded', String(isOpen)); // toggleLabel を toggleButton に変更
		this.container.setAttribute('aria-hidden', String(!isOpen));
		this.container.querySelectorAll('a').forEach((a, idx) => {
			if (isOpen) {
				a.removeAttribute('tabindex');
				if (idx === 0) requestAnimationFrame(() => a.focus());
			} else {
				a.setAttribute('tabindex', '-1');
			}
		});
	}
}

// =============================
// エントリーポイント
// =============================
document.addEventListener('DOMContentLoaded', () => {
	new MenuController().init();
	new GlobalKeyController().init();
	new MobileMenuController().init();
});

export {
	MenuController,
	GlobalKeyController,
	MobileMenuController
};




/********************************************************:: */
/*想定しているHTML構造 s
/********************************************************:: */
/*
<nav id="site-navigation" class="main-navigation">

  <!-- ハンバーガーメニュー制御用 -->
  <input type="checkbox" id="menuToggle-checkbox" class="menuToggle-checkbox" />
  <label for="menuToggle-checkbox" class="menuToggle-label"><span></span></label>

  <!-- メニューラッパー -->
  <div class="menuToggle-containerForMenu">
	<ul id="primary-menu" class="menu">
	  
	  <!-- トップレベルメニュー1 -->
	  <li class="menu-item menu-item-has-children">
		<a href="#">親メニュー1</a>
		<ul class="sub-menu">
		  <li class="menu-item"><a href="#">子メニュー1-1</a></li>
		  <li class="menu-item"><a href="#">子メニュー1-2</a></li>
		  <li class="menu-item menu-item-has-children">
			<a href="#">子メニュー1-3</a>
			<ul class="sub-menu">
			  <li class="menu-item"><a href="#">孫メニュー1-3-1</a></li>
			</ul>
		  </li>
		</ul>
	  </li>

	  <!-- トップレベルメニュー2 -->
	  <li class="menu-item"><a href="#">親メニュー2</a></li>

	  <!-- トップレベルメニュー3 -->
	  <li class="menu-item menu-item-has-children">
		<a href="#">親メニュー3</a>
		<ul class="sub-menu">
		  <li class="menu-item"><a href="#">子メニュー3-1</a></li>
		</ul>
	  </li>

	</ul>
  </div>
</nav>
*/
/********************************************************:: */
/*想定しているHTML構造 e
/********************************************************:: */
