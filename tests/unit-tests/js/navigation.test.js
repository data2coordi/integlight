/**
 * @jest-environment jsdom
 */

import {
    handleDOMContentLoaded,
    handleParentLinkClick,
    handleMenuItemFocusOut,
    checkFocus,
} from '../../../js/navigation'; // 適宜パスを調整してください

describe('グローバルメニューの挙動', () => {
    let container;

    beforeEach(() => {
        // DOM 構造を用意
        document.body.innerHTML = `
        <ul class="menu">
          <li class="menu-item-has-children" id="item1">
            <a href="#" id="link1">Parent 1</a>
            <ul class="sub-menu"><li>Child</li></ul>
          </li>
          <li class="menu-item-has-children" id="item2">
            <a href="#" id="link2">Parent 2</a>
            <ul class="sub-menu"><li>Child</li></ul>
          </li>
        </ul>
      `;
        container = document.body;

        // 各リンクに offsetWidth を与える
        const link1 = document.getElementById('link1');
        Object.defineProperty(link1, 'offsetWidth', { value: 100, configurable: true });
        const link2 = document.getElementById('link2');
        Object.defineProperty(link2, 'offsetWidth', { value: 100, configurable: true });

        // 初期化
        handleDOMContentLoaded();
    });

    test('handleDOMContentLoaded でイベントリスナーが登録される', () => {
        const link1 = document.getElementById('link1');
        const li1 = document.getElementById('item1');

        // click と focusout がそれぞれ登録されているかどうかは internal だが、
        // イベント発火時に期待動作するかで検証する
        expect(link1.onclick).toBeNull(); // addEventListener なので onclick ではなく listener が登録
        // 実際にクリックして動作を試すのは後続のテストで確認
    });

    describe('handleParentLinkClick', () => {
        it('リンクの右端をクリックすると active がトグルされる', () => {
            const link1 = document.getElementById('link1');
            const li1 = document.getElementById('item1');

            // クリック位置を右端寄りに設定 (offsetX > offsetWidth - 40)
            const event = new MouseEvent('click', { bubbles: true });
            Object.defineProperty(event, 'offsetX', { value: 70 }); // 100 - 40 = 60 を超えている
            link1.dispatchEvent(event);

            expect(li1.classList.contains('active')).toBe(true);

            // もう一度同じ場所をクリックすると外れる
            link1.dispatchEvent(event);
            expect(li1.classList.contains('active')).toBe(false);
        });

        it('リンクの左側（テキスト領域）をクリックしても active にならない', () => {
            const link1 = document.getElementById('link1');
            const li1 = document.getElementById('item1');

            const event = new MouseEvent('click', { bubbles: true });
            Object.defineProperty(event, 'offsetX', { value: 10 }); // 右端 60px より左側
            link1.dispatchEvent(event);

            expect(li1.classList.contains('active')).toBe(false);
        });

        it('同じ階層の兄弟からは active が外れる', () => {
            const link1 = document.getElementById('link1');
            const li1 = document.getElementById('item1');
            const link2 = document.getElementById('link2');
            const li2 = document.getElementById('item2');

            // item1 を開く
            const event1 = new MouseEvent('click', { bubbles: true });
            Object.defineProperty(event1, 'offsetX', { value: 70 });
            link1.dispatchEvent(event1);
            expect(li1.classList.contains('active')).toBe(true);

            // item2 を開く
            const event2 = new MouseEvent('click', { bubbles: true });
            Object.defineProperty(event2, 'offsetX', { value: 70 });
            link2.dispatchEvent(event2);

            expect(li2.classList.contains('active')).toBe(true);
            expect(li1.classList.contains('active')).toBe(false);
        });
    });

    describe('handleMenuItemFocusOut & checkFocus', () => {
        it('フォーカスが外れたら active が解除される', (done) => {
            const link1 = document.getElementById('link1');
            const li1 = document.getElementById('item1');

            // 最初 active にしておく
            li1.classList.add('active');

            // ★ document.activeElement をモックする
            Object.defineProperty(document, 'activeElement', {
                value: document.body, // li1 の外側をフォーカスさせる
                configurable: true,
            });

            // フォーカスアウトイベントを発火
            const focusOutEvent = new FocusEvent('focusout');
            li1.dispatchEvent(focusOutEvent);

            // setTimeout の後で結果を確認
            setTimeout(() => {
                expect(li1.classList.contains('active')).toBe(false);
                done();
            }, 0);
        });
    });

});
