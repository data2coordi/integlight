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
		toggleLabelSel = '.menuToggle-label',
		checkboxSel = '.menuToggle-checkbox',
		containerSel = '.menuToggle-containerForMenu',
		breakpoint = 768
	} = {}) {
		this.toggleLabel = document.querySelector(toggleLabelSel);
		this.checkbox = document.querySelector(checkboxSel);
		this.container = document.querySelector(containerSel);
		this.bp = breakpoint;
	}

	init() {
		if (!window.matchMedia(`(max-width: ${this.bp}px)`).matches) return;
		if (!this.toggleLabel || !this.checkbox || !this.container) return;

		this.toggleLabel.setAttribute('tabindex', '0');
		this.toggleLabel.setAttribute('aria-expanded', 'false');
		this.container.setAttribute('aria-hidden', 'true');
		this.container.querySelectorAll('a').forEach(a => a.setAttribute('tabindex', '-1'));

		this.checkbox.addEventListener('change', () => this.update(this.checkbox.checked));
		this.toggleLabel.addEventListener('keydown', e => {
			if (e.key === 'Enter' || e.key === ' ') {
				e.preventDefault();
				this.checkbox.checked = !this.checkbox.checked;
				this.update(this.checkbox.checked);
			}
		});
	}

	update(isOpen) {
		this.toggleLabel.setAttribute('aria-expanded', String(isOpen));
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
