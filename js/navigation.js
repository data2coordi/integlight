/**
 * File navigation.js.
 *
 * Handles toggling the navigation menu for small screens and enables TAB key
 * navigation support for dropdown menus.
 */

document.addEventListener("DOMContentLoaded", function () {
	// メニューアイテム（親メニュー）を取得
	const menuItems = document.querySelectorAll('.main-navigation .menu-item-has-children > a');

	// 各メニューアイテムにクリックイベントを設定
	menuItems.forEach(item => {
		item.addEventListener('click', function (event) {
			event.preventDefault();
			const subMenu = this.nextElementSibling;

			// サブメニューが表示されているかどうかをトグル
			if (subMenu && subMenu.classList.contains('sub-menu')) {
				subMenu.style.display = subMenu.style.display === 'block' ? 'none' : 'block';
			}
		});
	});
});
