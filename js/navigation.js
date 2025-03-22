// DOMContentLoaded イベント時の初期処理
function handleDOMContentLoaded() {
	// 「子メニューを持つ」リンクのクリックイベントを登録
	var parentLinks = document.querySelectorAll(".menu-item-has-children > a");
	for (var i = 0; i < parentLinks.length; i++) {
		parentLinks[i].addEventListener("click", handleParentLinkClick);
	}

	// 各「子メニューを持つ」項目で、フォーカスが完全に外れたときに active クラスを解除する
	var menuItemsWithSub = document.querySelectorAll(".menu-item-has-children");
	for (var j = 0; j < menuItemsWithSub.length; j++) {
		menuItemsWithSub[j].addEventListener("focusout", handleMenuItemFocusOut);
	}
}

// クリック時の処理（サブメニューの開閉）
function handleParentLinkClick(e) {
	var linkWidth = this.offsetWidth;  // aタグの全幅
	var clickPosition = e.offsetX;      // クリックされた位置（左端からの距離）

	// 矢印部分（右端20px）のみ開閉処理
	if (clickPosition > linkWidth - 20) {
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
