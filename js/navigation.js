document.addEventListener("DOMContentLoaded", function () {
	// 既存のクリック時の処理
	const parentLinks = document.querySelectorAll(".menu-item-has-children > a");

	parentLinks.forEach(link => {
		link.addEventListener("click", function (e) {
			e.preventDefault();      // 通常のリンク遷移を防止
			e.stopPropagation();     // イベントのバブリングを停止

			const currentItem = this.parentElement;

			// 同じ階層にある兄弟要素の active クラスを除去する
			const siblings = Array.from(currentItem.parentElement.children);
			siblings.forEach(item => {
				if (item !== currentItem) {
					item.classList.remove("active");
				}
			});

			// 自身の active 状態をトグル
			currentItem.classList.toggle("active");
			// クリックしたリンクにフォーカスを戻す（念のため）
			this.focus();
		});
	});

	// 各「子メニューを持つ」項目に対して、フォーカスが外れたときに展開中のメニューを閉じる処理を追加
	const menuItemsWithSubmenu = document.querySelectorAll(".menu-item-has-children");

	menuItemsWithSubmenu.forEach(item => {
		// focusout イベントは、子孫要素のフォーカスがすべて外れたときに発生します
		item.addEventListener("focusout", function (e) {
			// 少し待ってから判定する（setTimeout 0ms）
			setTimeout(() => {
				// 現在のフォーカスがこのメニュー項目の内部にない場合、active クラスを削除
				if (!item.contains(document.activeElement)) {
					item.classList.remove("active");
				}
			}, 0);
		});
	});
});
