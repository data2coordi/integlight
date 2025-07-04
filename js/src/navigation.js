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
// 自分の兄弟のactiveをはずし、自分をアクティブにする
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
// 自分の子にアクティブ要素がなければ自分からアクティブを外す
function integlight_handleMenuItemFocusOut(e) {
	// フォーカスがまだ、他要素に移っていない可能性を考慮し、少し待ってから、現在のフォーカスがこの項目内にないなら active クラスを解除する
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




//////////////////////////////////////
//アクセシビリティ対応　キーボード操作でサブメニュー開閉 s
//////////////////////////////////////
//Tabキー移動時にサブメニューを開く（クリックとは競合しない）

document.querySelectorAll(".menu-item-has-children > a").forEach(link => {
	link.addEventListener("keydown", (e) => {
		if (e.key === "Tab") {
			const currentItem = link.parentElement;

			// すでに active なら（＝サブが開いてるなら）何もしない＝次に進める
			if (currentItem.classList.contains("active")) {
				return;
			}

			// 同階層の他メニューを閉じる
			const siblings = Array.from(currentItem.parentElement.children);
			siblings.forEach(sibling => {
				if (sibling !== currentItem) {
					sibling.classList.remove("active");
				}
			});

			currentItem.classList.add("active");
		}
	});
});


function integlight_handleKeydownEscape(e) {
	if (e.key === "Escape") {
		const focusedElement = document.activeElement;
		const menuItem = focusedElement.closest(".menu-item-has-children.active");

		if (menuItem) {
			menuItem.classList.remove("active");

			focusedElement.blur();

		}
	}
}

document.addEventListener("keydown", integlight_handleKeydownEscape);
//////////////////////////////////////
//アクセシビリティ対応　キーボード操作でサブメニュー開閉 e
//////////////////////////////////////



//////////////////////////////////////
//モバイルの場合のアクセシビリティ対応 s
//////////////////////////////////////
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
				if (idx === 0) {
					requestAnimationFrame(() => a.focus());
				}
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
	if (window.matchMedia('(max-width: 768px)').matches) {
		integlight_initMobileMenuAccessibility({
			toggleLabel: document.querySelector('.menuToggle-label'),
			checkbox: document.querySelector('.menuToggle-checkbox'),
			container: document.querySelector('.menuToggle-containerForMenu'),
		});
	}
});
//////////////////////////////////////
//モバイルの場合のアクセシビリティ対応 e
//////////////////////////////////////



export {
	integlight_handleDOMContentLoaded,
	integlight_handleParentLinkClick,
	integlight_handleMenuItemFocusOut,
	integlight_checkFocus,
	integlight_handleKeydownEscape,
	integlight_initMobileMenuAccessibility
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


