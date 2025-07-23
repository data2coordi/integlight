/**
 * @jest-environment jsdom
 */

import { MenuController, GlobalKeyController, MobileMenuController } from '../../../js/src/navigation';

//
// グローバルメニューの挙動（ブラックボックステスト）
//
describe('グローバルメニューの挙動（ブラックボックステスト）', () => {
    beforeEach(() => {
        document.body.innerHTML = `
      <nav>
        <ul class="menu">
          <li class="menu-item-has-children" id="item1">
            <a href="#" id="link1">Parent 1</a>
            <ul class="sub-menu"><li><a href="#">Child 1</a></li></ul>
          </li>
          <li class="menu-item-has-children" id="item2">
            <a href="#" id="link2">Parent 2</a>
            <ul class="sub-menu"><li><a href="#">Child 2</a></li></ul>
          </li>
        </ul>
      </nav>
    `;

        // offsetWidth を十分に大きくして「右端クリック」をシミュレート
        Object.defineProperty(document.getElementById('link1'), 'offsetWidth', { value: 100 });
        Object.defineProperty(document.getElementById('link2'), 'offsetWidth', { value: 100 });

        new MenuController().init();
        new GlobalKeyController().init();
    });

    afterEach(() => {
        jest.restoreAllMocks();
    });

    test('右端クリックで active トグル', () => {
        const link = document.getElementById('link1');
        const item = document.getElementById('item1');
        const evt = new MouseEvent('click', { bubbles: true });
        Object.defineProperty(evt, 'offsetX', { value: 90 }); // 右端 100−40=60 より大きい
        link.dispatchEvent(evt);
        expect(item.classList.contains('active')).toBe(true);
        link.dispatchEvent(evt);
        expect(item.classList.contains('active')).toBe(false);
    });

    test('左クリックで active 無効', () => {
        const link = document.getElementById('link1');
        const item = document.getElementById('item1');
        const evt = new MouseEvent('click', { bubbles: true });
        Object.defineProperty(evt, 'offsetX', { value: 10 }); // 左側
        link.dispatchEvent(evt);
        expect(item.classList.contains('active')).toBe(false);
    });

    test('兄弟間で active 切り替え', () => {
        const l1 = document.getElementById('link1');
        const l2 = document.getElementById('link2');
        const i1 = document.getElementById('item1');
        const i2 = document.getElementById('item2');
        const evt = new MouseEvent('click', { bubbles: true });
        Object.defineProperty(evt, 'offsetX', { value: 90 });
        l1.dispatchEvent(evt);
        expect(i1.classList.contains('active')).toBe(true);
        l2.dispatchEvent(evt);
        expect(i2.classList.contains('active')).toBe(true);
        expect(i1.classList.contains('active')).toBe(false);
    });

    test('Tabキーで active 付与', () => {
        const link = document.getElementById('link1');
        const item = document.getElementById('item1');
        const evt = new KeyboardEvent('keydown', { key: 'Tab', bubbles: true });
        link.dispatchEvent(evt);
        expect(item.classList.contains('active')).toBe(true);
    });

    test('focusout による active 削除', done => {
        const item = document.getElementById('item1');
        item.classList.add('active');
        jest.spyOn(document, 'activeElement', 'get').mockReturnValue(document.body);
        const evt = new FocusEvent('focusout');
        item.dispatchEvent(evt);
        setTimeout(() => {
            expect(item.classList.contains('active')).toBe(false);
            done();
        }, 0);
    });

    test('Escape キーで active を解除し、フォーカスを外す', () => {
        const item = document.getElementById('item1');
        const subLink = document.createElement('a');
        subLink.href = '#';
        item.appendChild(subLink);
        item.classList.add('active');

        const blurSpy = jest.spyOn(subLink, 'blur').mockImplementation(() => { });
        jest.spyOn(document, 'activeElement', 'get').mockReturnValue(subLink);

        const evt = new KeyboardEvent('keydown', { key: 'Escape', bubbles: true });
        document.dispatchEvent(evt);

        expect(item.classList.contains('active')).toBe(false);
        expect(blurSpy).toHaveBeenCalled();
    });

    test('子リンククリック時に active 維持', () => {
        const item = document.getElementById('item1');
        item.classList.add('active');
        const subLink = item.querySelector('.sub-menu a');
        const evt = new MouseEvent('click', { bubbles: true });
        subLink.dispatchEvent(evt);
        expect(item.classList.contains('active')).toBe(true);
    });
});

//
// モバイルメニュー操作
//
describe('モバイルメニュー操作', () => {
    beforeEach(() => {
        document.body.innerHTML = `
      <nav class="main-navigation">
        <input
          type="checkbox"
          id="menuToggle-checkbox"
          class="menuToggle-checkbox"
          aria-hidden="true"
        />
        <button
          id="menuToggle-button"
          class="menuToggle-label"
          aria-controls="primary-menu-container"
          aria-expanded="false"
        >
          <span class="screen-reader-text">Menu</span>
          <span></span>
        </button>
        <div
          class="menuToggle-containerForMenu"
          id="primary-menu-container"
        >
          <ul id="primary-menu" class="menu">
            <li class="menu-item"><a href="#">Home</a></li>
            <li class="menu-item"><a href="#">About</a></li>
          </ul>
        </div>
      </nav>
    `;

        // モバイル表示を強制
        Object.defineProperty(window, 'matchMedia', {
            value: () => ({ matches: true }),
            configurable: true,
        });
    });

    test('初期化時にコンテナとリンクに属性が設定される', () => {
        new MobileMenuController().init();

        const button = document.getElementById('menuToggle-button');
        const container = document.getElementById('primary-menu-container');
        const links = container.querySelectorAll('a');

        // ボタンの aria-expanded はマークアップ側の false のまま
        expect(button.getAttribute('aria-expanded')).toBe('false');

        // JSで付与される aria-hidden と tabindex
        expect(container.getAttribute('aria-hidden')).toBe('true');
        links.forEach(link => {
            expect(link.getAttribute('tabindex')).toBe('-1');
        });
    });

    test('クリックでメニューが開き、属性が切り替わる', () => {
        const controller = new MobileMenuController();
        controller.init();

        const button = document.getElementById('menuToggle-button');
        const container = document.getElementById('primary-menu-container');
        const firstLink = container.querySelector('a');

        // 初期状態
        expect(button.getAttribute('aria-expanded')).toBe('false');
        expect(container.getAttribute('aria-hidden')).toBe('true');
        expect(firstLink.getAttribute('tabindex')).toBe('-1');

        // ボタンをクリックして開く
        button.click();

        // 開いた後
        expect(button.getAttribute('aria-expanded')).toBe('true');
        expect(container.getAttribute('aria-hidden')).toBe('false');
        expect(firstLink.hasAttribute('tabindex')).toBe(false);
    });

    test('update(false) で属性が戻る', () => {
        const controller = new MobileMenuController();
        controller.init();

        // 一度開いてから閉じるシナリオも検証できますが、
        // ここでは直接 update(false) を呼び出して属性をチェック
        controller.update(false);

        const button = document.getElementById('menuToggle-button');
        const container = document.getElementById('primary-menu-container');
        const firstLink = container.querySelector('a');

        expect(button.getAttribute('aria-expanded')).toBe('false');
        expect(container.getAttribute('aria-hidden')).toBe('true');
        expect(firstLink.getAttribute('tabindex')).toBe('-1');
    });

    test('Enter/Space でトグル動作する（click シミュレーションに置換）', () => {
        // モバイル判定を常に true に設定
        Object.defineProperty(window, 'matchMedia', {
            value: () => ({ matches: true }),
            configurable: true,
        });

        // テスト用 DOM
        document.body.innerHTML = `
    <nav class="main-navigation">
      <input
        type="checkbox"
        id="menuToggle-checkbox"
        class="menuToggle-checkbox"
        aria-hidden="true"
      />
      <button
        id="menuToggle-button"
        class="menuToggle-label"
        aria-controls="primary-menu-container"
        aria-expanded="false"
      >
        <span class="screen-reader-text">Menu</span>
        <span></span>
      </button>
      <div
        class="menuToggle-containerForMenu"
        id="primary-menu-container"
      >
        <ul id="primary-menu" class="menu">
          <li class="menu-item"><a href="#">Home</a></li>
          <li class="menu-item"><a href="#">About</a></li>
        </ul>
      </div>
    </nav>
  `;

        const controller = new MobileMenuController();
        controller.init();

        const button = document.getElementById('menuToggle-button');
        const container = document.getElementById('primary-menu-container');
        const firstLink = container.querySelector('a');

        // 初期状態
        expect(button.getAttribute('aria-expanded')).toBe('false');
        expect(container.getAttribute('aria-hidden')).toBe('true');
        expect(firstLink.getAttribute('tabindex')).toBe('-1');

        // ユーザー操作: ボタンを click で開く
        button.click();
        expect(button.getAttribute('aria-expanded')).toBe('true');
        expect(container.getAttribute('aria-hidden')).toBe('false');
        expect(firstLink.hasAttribute('tabindex')).toBe(false);

        // 再度 click で閉じる
        button.click();
        expect(button.getAttribute('aria-expanded')).toBe('false');
        expect(container.getAttribute('aria-hidden')).toBe('true');
        expect(firstLink.getAttribute('tabindex')).toBe('-1');
    });


});


/*
テストケース一覧をテキストにはりつけたい。
#	グループ	テスト名	残存
1	グローバルメニュー	右端クリックで active トグル	
2		左クリックで active 無効	
3		兄弟間で active 切り替え	
4		Tabキーで active 付与	
5		focusout による active 削除	
6		Escape キーで active を解除し、フォーカスを外す	
7		子リンククリック時に active 維持	
8	モバイルメニュー操作	初期化時にコンテナとリンクに属性が設定される	
9		クリックでメニューが開き、属性が切り替わる	
10		Enter/Space でトグル動作する	
11		update(false) で属性が戻る	
*/
