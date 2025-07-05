/**
 * @jest-environment jsdom
 */

import { MenuController, GlobalKeyController, MobileMenuController } from '../../../js/src/navigation';

describe('グローバルメニューの挙動（ブラックボックステスト）', () => {
    let container;

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

        // offsetWidth モック
        Object.defineProperty(document.getElementById('link1'), 'offsetWidth', { value: 100 });
        Object.defineProperty(document.getElementById('link2'), 'offsetWidth', { value: 100 });

        new MenuController().init();
        new GlobalKeyController().init();
    });

    afterEach(() => {
        // 各テスト後にすべてのモックを元の実装に復元
        jest.restoreAllMocks();
    });

    test('右端クリックで active トグル', () => {
        const link = document.getElementById('link1');
        const item = document.getElementById('item1');
        const evt = new MouseEvent('click', { bubbles: true });
        Object.defineProperty(evt, 'offsetX', { value: 90 });
        link.dispatchEvent(evt);
        expect(item.classList.contains('active')).toBe(true);
        link.dispatchEvent(evt);
        expect(item.classList.contains('active')).toBe(false);
    });

    test('左クリックで active 無効', () => {
        const link = document.getElementById('link1');
        const item = document.getElementById('item1');
        const evt = new MouseEvent('click', { bubbles: true });
        Object.defineProperty(evt, 'offsetX', { value: 10 });
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
        const evt = new KeyboardEvent('keydown', { key: 'Tab' });
        link.dispatchEvent(evt);
        expect(item.classList.contains('active')).toBe(true);
    });

    test('focusout による active 削除', done => {
        const item = document.getElementById('item1');
        item.classList.add('active');

        // jest.spyOn を使用して document.activeElement のゲッターをモック
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
        subLink.textContent = 'Sub';

        item.appendChild(subLink);
        item.classList.add('active');

        const blurSpy = jest.spyOn(subLink, 'blur').mockImplementation(() => { });

        // jest.spyOn を使用して document.activeElement のゲッターをモック
        jest.spyOn(document, 'activeElement', 'get').mockReturnValue(subLink);

        // Escape キーイベントを発火
        // document にイベントリスナがあるため bubbles: true が必要
        const evt = new KeyboardEvent('keydown', { key: 'Escape', bubbles: true });
        document.dispatchEvent(evt);

        // 検証
        expect(item.classList.contains('active')).toBe(false); // active クラスが削除されているか
        expect(blurSpy).toHaveBeenCalled();                    // blur() が呼び出されたか
    });


    test('子リンククリック時に active 維持', () => {
        const item = document.getElementById('item1');
        const subLink = item.querySelector('.sub-menu a');
        item.classList.add('active');
        const evt = new MouseEvent('click', { bubbles: true });
        subLink.dispatchEvent(evt);
        expect(item.classList.contains('active')).toBe(true);
    });
});

describe('モバイルメニュー操作', () => {
    let label, checkbox, container, link;

    beforeEach(() => {
        label = document.createElement('div');
        label.className = 'menuToggle-label';
        checkbox = document.createElement('input');
        checkbox.className = 'menuToggle-checkbox';
        checkbox.type = 'checkbox';
        container = document.createElement('div');
        container.className = 'menuToggle-containerForMenu';
        link = document.createElement('a');
        container.appendChild(link);
        document.body.append(label, checkbox, container);
    });

    test('初期化時に属性が設定される', () => {
        Object.defineProperty(window, 'matchMedia', {
            value: jest.fn().mockReturnValue({ matches: true }),
            configurable: true
        });
        new MobileMenuController().init();
        expect(label.getAttribute('tabindex')).toBe('0');
        expect(label.getAttribute('aria-expanded')).toBe('false');
        expect(container.getAttribute('aria-hidden')).toBe('true');
        expect(link.getAttribute('tabindex')).toBe('-1');
    });

    test('Enter/Space でトグル動作する', () => {
        // Arrange: matchMedia をモックして、コントローラーが初期化されるようにする

        Object.defineProperty(window, 'matchMedia', {
            value: jest.fn().mockReturnValue({ matches: true }),
            configurable: true
        });


        // MobileMenuController に DOM を直接注入
        const ctrl = new MobileMenuController();
        ctrl.toggleLabel = label;
        ctrl.checkbox = checkbox;
        ctrl.container = container;
        ctrl.init();
        // Arrange: MobileMenuController をインスタンス化して初期化する
        // beforeEach でDOM要素はクラス名付きで作成されているため、コンストラクタが要素を取得できる

        // Act & Assert: Enter キーでチェックボックスが true になる
        const enterEvt = new KeyboardEvent('keydown', { key: 'Enter' });
        label.dispatchEvent(enterEvt);


        expect(checkbox.checked).toBe(true);


        // Act & Assert: Space キーでチェックボックスが false になる
        const spaceEvt = new KeyboardEvent('keydown', { key: ' ' });
        label.dispatchEvent(spaceEvt);
        expect(checkbox.checked).toBe(false);
    });

    test('update(false) で属性が戻る', () => {
        const ctrl = new MobileMenuController();
        ctrl.toggleLabel = label;
        ctrl.checkbox = checkbox;
        ctrl.container = container;
        ctrl.update(false);
        expect(label.getAttribute('aria-expanded')).toBe('false');
        expect(container.getAttribute('aria-hidden')).toBe('true');
        expect(link.getAttribute('tabindex')).toBe('-1');
    });
});
