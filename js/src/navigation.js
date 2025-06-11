// DOMContentLoaded イベント時の初期処理
function integlight_handleDOMContentLoaded() {
	// 「子メニューを持つ」リンクのクリックイベントを登録
	var parentLinks = document.querySelectorAll(".menu-item-has-children > a");
	for (var i = 0; i < parentLinks.length; i++) {
		parentLinks[i].addEventListener("click", integlight_handleParentLinkClick);
	}

	// 各「子メニューを持つ」項目で、フォーカスが完全に外れたときに active クラスを解除する
	var menuItemsWithSub = document.querySelectorAll(".menu-item-has-children");
	for (var j = 0; j < menuItemsWithSub.length; j++) {
		menuItemsWithSub[j].addEventListener("focusout", integlight_handleMenuItemFocusOut);
	}
}

// クリック時の処理（サブメニューの開閉）
function integlight_handleParentLinkClick(e) {
	var linkWidth = this.offsetWidth;  // aタグの全幅
	var clickPosition = e.offsetX;      // クリックされた位置（左端からの距離）

	// 矢印部分（右端20px）のみ開閉処理
	if (clickPosition > linkWidth - 40) {
		e.preventDefault(); // デフォルトのリンク遷移を防ぐ
		e.stopPropagation(); // イベントの親要素への伝播を防ぐ

		var currentItem = this.parentElement;

		// 同じ階層の兄弟項目から active クラスを除去する
		var siblings = Array.prototype.slice.call(currentItem.parentElement.children);
		for (var i = 0; i < siblings.length; i++) {
			if (siblings[i] !== currentItem) {
				siblings[i].classList.remove("active");
			}
		}

		// 現在の項目の active 状態をトグルする
		currentItem.classList.toggle("active");
	}
}

// フォーカスが外れたときの処理
function integlight_handleMenuItemFocusOut(e) {
	// 少し待ってから、現在のフォーカスがこの項目内にないなら active クラスを解除する
	setTimeout(integlight_checkFocus, 0, this);
}

// setTimeout のコールバックとして呼び出される関数
function integlight_checkFocus(item) {
	if (!item.contains(document.activeElement)) {
		item.classList.remove("active");
	}
}

// ページ読み込み完了時に初期処理を実行
document.addEventListener("DOMContentLoaded", integlight_handleDOMContentLoaded);





//アクセシビリティ対応　キーボード操作でサブメニュー開閉 s


// フォーカスされたらサブメニューを開く（EnterではなくTab移動時）
function integlight_handleFocusOnParentLink() {
	const currentItem = this.parentElement;

	// 同階層の他メニューを閉じる
	const siblings = Array.from(currentItem.parentElement.children);
	siblings.forEach(sibling => {
		if (sibling !== currentItem) {
			sibling.classList.remove("active");
		}
	});

	currentItem.classList.add("active");
}




document.querySelectorAll(".menu-item-has-children > a").forEach(link => {
	link.addEventListener("focus", integlight_handleFocusOnParentLink);
});

//escでメニューを閉じる
function integlight_handleKeydownEscape(e) {
	if (e.key === "Escape") {
		// 現在フォーカスされているリンクの親メニューを閉じる
		const focusedElement = document.activeElement;
		const menuItem = focusedElement.closest(".menu-item-has-children.active");
		if (menuItem) {
			menuItem.classList.remove("active");
			menuItem.querySelector("a").focus(); // 親メニューにフォーカスを戻す
		}
	}
}

document.addEventListener("keydown", integlight_handleKeydownEscape);

//アクセシビリティ対応　キーボード操作でサブメニュー開閉 e



//モバイルの場合のアクセシビリティ対応 s
function integlight_initMobileMenuAccessibility({ toggleLabel, checkbox, container }) {
	if (!toggleLabel || !checkbox || !container) return;

	// 初期状態設定
	toggleLabel.setAttribute('tabindex', '0');
	toggleLabel.setAttribute('aria-expanded', 'false');
	container.setAttribute('aria-hidden', 'true');
	container.querySelectorAll('a').forEach(a => a.setAttribute('tabindex', '-1'));

	// 状態更新関数
	function updateMenuAccessibility(isOpen) {
		toggleLabel.setAttribute('aria-expanded', String(isOpen));
		container.setAttribute('aria-hidden', String(!isOpen));
		container.querySelectorAll('a').forEach((a, idx) => {
			if (isOpen) {
				a.removeAttribute('tabindex');
				if (idx === 0) a.focus();
			} else {
				a.setAttribute('tabindex', '-1');
			}
		});
	}

	// イベント登録
	checkbox.addEventListener('change', () => updateMenuAccessibility(checkbox.checked));

	toggleLabel.addEventListener('keydown', (e) => {
		if (e.key === 'Enter' || e.key === ' ') {
			e.preventDefault();
			checkbox.checked = !checkbox.checked;
			updateMenuAccessibility(checkbox.checked);
		}
	});
}


document.addEventListener('DOMContentLoaded', () => {
	integlight_initMobileMenuAccessibility({
		toggleLabel: document.querySelector('.menuToggle-label'),
		checkbox: document.querySelector('.menuToggle-checkbox'),
		container: document.querySelector('.menuToggle-containerForMenu'),
	});
});
//モバイルの場合のアクセシビリティ対応 e
// ──────────────────────────────

// 以降、既存の esc 閉じ・サブメニュー開閉ロジック…

export {
	integlight_handleDOMContentLoaded,
	integlight_handleParentLinkClick,
	integlight_handleMenuItemFocusOut,
	integlight_checkFocus,
	integlight_handleFocusOnParentLink,
	integlight_handleKeydownEscape,
	integlight_initMobileMenuAccessibility
};








