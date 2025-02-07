// DOMContentLoaded イベント時の初期処理
function handleDOMContentLoaded() {
	// 「子メニューを持つ」リンクのクリックイベントを登録
	var parentLinks = document.querySelectorAll(".menu-item-has-children > a");
	for (var i = 0, len = parentLinks.length; i < len; i++) {
		parentLinks[i].addEventListener("click", handleParentLinkClick);
	}

	// 各「子メニューを持つ」項目で、フォーカスが完全に外れたときに active クラスを解除する
	var menuItemsWithSub = document.querySelectorAll(".menu-item-has-children");
	for (var j = 0, len2 = menuItemsWithSub.length; j < len2; j++) {
		menuItemsWithSub[j].addEventListener("focusout", handleMenuItemFocusOut);
	}
}

// クリック時の処理（子メニューの開閉）
function handleParentLinkClick(e) {
	e.preventDefault();
	e.stopPropagation();

	var currentItem = this.parentElement;

	// 同じ階層の兄弟項目から active クラスを除去する
	var siblings = Array.prototype.slice.call(currentItem.parentElement.children);
	for (var i = 0, len = siblings.length; i < len; i++) {
		if (siblings[i] !== currentItem) {
			siblings[i].classList.remove("active");
		}
	}

	// 現在の項目の active 状態をトグルする
	currentItem.classList.toggle("active");

	// クリック後、リンクにフォーカスを設定（フォーカス管理用）
	this.focus();
}

// フォーカスが外れたときの処理
function handleMenuItemFocusOut(e) {
	// 少し待ってから、現在のフォーカスがこの項目内にないなら active クラスを解除する
	setTimeout(checkFocus, 0, this);
}

// setTimeout のコールバックとして呼び出される関数
function checkFocus(item) {
	if (!item.contains(document.activeElement)) {
		item.classList.remove("active");
	}
}

// ページ読み込み完了時に初期処理を実行
document.addEventListener("DOMContentLoaded", handleDOMContentLoaded);
