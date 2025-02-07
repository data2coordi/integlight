document.addEventListener("DOMContentLoaded", function () {
	// 子メニューを持つ各リンクを取得
	const parentLinks = document.querySelectorAll(".menu-item-has-children > a");

	parentLinks.forEach(link => {
		link.addEventListener("click", function (e) {
			e.preventDefault(); // 通常のリンク遷移を防止

			// 現在の親 li を取得
			const currentItem = this.parentElement;

			// 同じ階層（同じ ul の子）のすべての子メニュー項目から active クラスを除去
			const siblings = Array.from(currentItem.parentElement.children);
			siblings.forEach(item => {
				if (item !== currentItem) {
					item.classList.remove("active");
				}
			});

			// 自身の active 状態をトグル
			currentItem.classList.toggle("active");
		});
	});
});
