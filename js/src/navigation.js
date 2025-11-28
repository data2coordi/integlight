/*
 * src/js/integlight-menu.js
 * 改修版：PCホバー対応 ＆ 座標計算廃止（ボタン生成方式）
 */

// =============================
// 共通ユーティリティ
// =============================
class MenuUtils {
  // 兄弟要素のメニューを閉じる
  static closeSiblings(item) {
    const parent = item.parentElement;
    if (!parent) return;

    Array.from(parent.children).forEach((sib) => {
      if (sib !== item && sib.classList.contains("menu-item-has-children")) {
        sib.classList.remove("active");
        const btn = sib.querySelector(".submenu-toggle-btn");
        if (btn) btn.setAttribute("aria-expanded", "false");
      }
    });
  }
}

// =============================
// メニュー開閉制御（デスクトップ／モバイル共通）
// =============================
class MenuController {
  constructor() {
    // サブメニューを持つすべてのli要素
    this.menuItems = document.querySelectorAll(".menu-item-has-children");
  }

  init() {
    this.menuItems.forEach((item) => {
      // 1. 開閉用ボタンを動的に生成して配置（座標計算の代わり）
      this.createToggleButton(item);

      // 2. フォーカスが外れたら閉じる（PCアクセシビリティ用）
      item.addEventListener("focusout", this.onFocusOut.bind(this));

      // 3. エスケープキー対応
      item.addEventListener("keydown", this.onItemKeydown.bind(this));
    });
  }

  /**
   * リンクの横（上）に透明なボタンを配置する
   */
  createToggleButton(item) {
    const link = item.querySelector("a");
    if (!link) return;

    // ボタン要素を作成
    const btn = document.createElement("button");
    btn.className = "submenu-toggle-btn";
    btn.type = "button";
    btn.setAttribute("aria-label", "サブメニューを開閉");
    btn.setAttribute("aria-expanded", "false");

    // ボタンクリック時のイベント
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation(); // 親へのバブリング阻止
      this.toggleMenu(item, btn);
    });

    // 構造上、aタグの直後に配置する（CSSで右端にabsolute配置）
    link.after(btn);
  }

  /**
   * メニューの開閉切り替え
   */
  toggleMenu(item, btn) {
    const isActive = item.classList.contains("active");

    // 開く場合は、同じ階層の他のメニューを閉じる
    if (!isActive) {
      MenuUtils.closeSiblings(item);
    }

    // クラスのトグル
    item.classList.toggle("active");

    // ARIA属性の更新
    const newState = item.classList.contains("active");
    btn.setAttribute("aria-expanded", String(newState));
  }

  /**
   * フォーカスアウト時の処理
   */
  onFocusOut(e) {
    const item = e.currentTarget;
    // 次のフォーカスが子要素内にあるか確認するため少し待つ
    setTimeout(() => {
      if (!item.contains(document.activeElement)) {
        item.classList.remove("active");
        const btn = item.querySelector(".submenu-toggle-btn");
        if (btn) btn.setAttribute("aria-expanded", "false");
      }
    }, 0);
  }

  /**
   * キーボード操作（Escapeキーで閉じる）
   */
  onItemKeydown(e) {
    if (e.key === "Escape") {
      const item = e.currentTarget;
      if (item.classList.contains("active")) {
        e.stopPropagation();
        item.classList.remove("active");
        const btn = item.querySelector(".submenu-toggle-btn");
        if (btn) {
          btn.setAttribute("aria-expanded", "false");
          btn.focus(); // ボタンにフォーカスを戻す
        }
      }
    }
  }
}

// =============================
// モバイルハンバーガーメニュー制御
// =============================
class MobileMenuController {
  constructor() {
    // 提供されたHTMLのIDに合わせて設定
    this.toggleButton = document.getElementById("menuToggle-button");
    this.checkbox = document.getElementById("menuToggle-checkbox");
    this.container = document.getElementById("primary-menu-container");
    this.breakpoint = 768; // スマホ表示の境界線
  }

  init() {
    // 要素がなければ終了
    if (!this.toggleButton || !this.checkbox || !this.container) return;

    // 初期化：アクセシビリティ属性
    this.updateAttributes(this.checkbox.checked);

    // チェックボックスの変化を監視（CSSで開閉している場合もJSの状態を同期）
    this.checkbox.addEventListener("change", () => {
      this.updateAttributes(this.checkbox.checked);
    });

    // ボタンクリック時の処理
    // label要素ではないbuttonタグのため、明示的にcheckboxを操作する必要がある場合に対応
    this.toggleButton.addEventListener("click", () => {
      // buttonの中にcheckboxが入っていない構造なので、連動させる
      this.checkbox.checked = !this.checkbox.checked;
      // changeイベントを発火させて同期
      this.checkbox.dispatchEvent(new Event("change"));
    });
  }

  updateAttributes(isOpen) {
    this.toggleButton.setAttribute("aria-expanded", String(isOpen));
    // メニューが開いているときはコンテナをスクリーンリーダーに隠さない
    this.container.setAttribute("aria-hidden", String(!isOpen));
  }
}

// =============================
// 初期化
// =============================
document.addEventListener("DOMContentLoaded", () => {
  // 1. メニュー項目ごとの開閉制御
  new MenuController().init();

  // 2. スマホ用ハンバーガーメニュー制御
  new MobileMenuController().init();
});

export { MenuController, MobileMenuController };

/* 想定されているメニュー構造 */
/*
<ul id="header-menu" class="menu">
  <li class="menu-item menu-item-type-taxonomy menu-item-object-category menu-item-has-children">
    <a href="#">FIRE・資産運用</a>
    <button class="submenu-toggle-btn" type="button" aria-label="サブメニューを開閉" aria-expanded="false"></button>

    <ul class="sub-menu">
      <li class="menu-item menu-item-type-taxonomy menu-item-object-category menu-item-has-children">
        <a href="#">FIRE</a>
        <button class="submenu-toggle-btn" type="button" aria-label="サブメニューを開閉" aria-expanded="false"></button>

        <ul class="sub-menu">
          <li class="menu-item menu-item-type-post_type menu-item-object-post">
            <a href="#">FIREとは？ ー自立と自由を手に入れるFIREの全貌ー</a>
          </li>
        </ul>
      </li>
    </ul>
  </li>
</ul>

*/
