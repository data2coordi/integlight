document.addEventListener("DOMContentLoaded", function () {
	// サブメニューを持つすべてのリンクを取得
	const parentLinks = document.querySelectorAll(".menu-item-has-children > a");

	parentLinks.forEach(link => {
		link.addEventListener("click", function (e) {
			e.preventDefault(); // リンクの遷移を防止
			const li = this.parentElement;

			// 同じ階層の他のメニュー項目から .active を削除（ネストした場合は直接の兄弟のみ対象）
			Array.from(li.parentElement.children).forEach(sibling => {
				if (sibling !== li) {
					sibling.classList.remove("active");
				}
			});

			// 自身の .active をトグル（開いていなければ開く、開いていれば閉じる）
			li.classList.toggle("active");
		});
	});
});
