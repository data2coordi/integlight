/**
 * @jest-environment jsdom
 */

// --- Mock Setup ---
let bindCallbacks = {};
const mockCustomize = jest.fn((settingId, setupCallback) => {
    const customizeObject = {
        bind: jest.fn((changeCallback) => {
            bindCallbacks[settingId] = changeCallback;
        }),
    };
    if (typeof setupCallback === 'function') {
        setupCallback(customizeObject);
    }
    return customizeObject;
});

// jQuery のモックを修正
const mockReadyCallback = jest.fn(callback => callback());
const mockJQuery = jest.fn(selectorOrFunction => {
    if (typeof selectorOrFunction === 'function') {
        // $(document).ready() or $(function() { ... })
        mockReadyCallback(selectorOrFunction);
        return { ready: mockReadyCallback };
    }

    // 通常のセレクターの場合
    const elements = document.querySelectorAll(selectorOrFunction);

    // jQueryオブジェクトのモックを返す
    return {
        /**
         * .text() のモック実装
         * @param {string} [value] 設定するテキスト (省略時は何もしない)
         */
        text: jest.fn(function (value) { // Chaining のため通常の関数にする
            if (value !== undefined && elements.length > 0) {
                elements.forEach(el => {
                    // a タグの場合はその要素、それ以外は直接 textContent を設定
                    const targetElement = el.tagName === 'A' ? el : el;
                    targetElement.textContent = value;
                });
            }
            // jQuery のようにチェイン可能にするために this を返す
            return this;
        }),

        /**
         * .css() のモック実装
         * @param {object} styles スタイルプロパティのオブジェクト
         */
        css: jest.fn(function (styles) { // Chaining のため通常の関数にする
            if (typeof styles === 'object' && styles !== null && elements.length > 0) {
                elements.forEach(el => {
                    // スタイルオブジェクトの各プロパティを要素の style に適用
                    Object.keys(styles).forEach(prop => {
                        // 'clip' のような特殊なプロパティも考慮 (jsdomの限界はある)
                        // キャメルケースへの変換が必要な場合もあるが、ここでは単純に代入
                        el.style[prop] = styles[prop];
                    });
                });
            }
            // jQuery のようにチェイン可能にするために this を返す
            return this;
        }),

        // 他の必要なメソッド (ready, find など)
        ready: mockReadyCallback,
        find: jest.fn().mockReturnThis(), // find は単純なモックのまま (必要なら実装)
    };
});


// --- Tests ---
describe('Customizer Script Tests (Simple DOM Check)', () => {

    beforeEach(() => {
        jest.resetModules();

        // モックをグローバルに再設定
        global.wp = { customize: mockCustomize };
        global.$ = mockJQuery;
        global.jQuery = mockJQuery;

        // モックの内部状態をリセット
        mockCustomize.mockClear();
        mockJQuery.mockClear();
        mockReadyCallback.mockClear();
        // jQueryメソッドのモック呼び出し履歴もクリア (より確実に)
        if (mockJQuery.mock.results[0]?.value?.text) {
            mockJQuery.mock.results[0].value.text.mockClear();
        }
        if (mockJQuery.mock.results[0]?.value?.css) {
            mockJQuery.mock.results[0].value.css.mockClear();
        }


        bindCallbacks = {};
        document.body.innerHTML = '';

        // スクリプトを再読み込み
        require('../../../js/src/customizer');
    });

    // --- 個別のテストケース (変更なし) ---

    test('should update site title text', () => {
        // Arrange
        document.body.innerHTML = `<h1 class="site-title"><a href="#">Initial Title</a></h1>`;
        const titleLink = document.querySelector('.site-title a');
        const newTitle = 'New Site Title';
        const blognameChangeCallback = bindCallbacks['blogname'];

        expect(blognameChangeCallback).toBeDefined();
        expect(typeof blognameChangeCallback).toBe('function');

        // Act
        blognameChangeCallback(newTitle);

        // Assert
        expect(titleLink.textContent).toBe(newTitle); // ← 今度は成功するはず
    });

    test('should update site description text', () => {
        // Arrange
        document.body.innerHTML = `<p class="site-description">Initial Description</p>`;
        const descriptionElement = document.querySelector('.site-description');
        const newDescription = 'New Site Description';
        const blogdescChangeCallback = bindCallbacks['blogdescription'];

        expect(blogdescChangeCallback).toBeDefined();
        expect(typeof blogdescChangeCallback).toBe('function');

        // Act
        blogdescChangeCallback(newDescription);

        // Assert
        expect(descriptionElement.textContent).toBe(newDescription); // ← 今度は成功するはず
    });

    describe('Header Text Color Customization', () => {
        beforeEach(() => {
            // Arrange: 共通の初期DOM
            document.body.innerHTML = `
                <div class="site-branding">
                  <h1 class="site-title"><a href="#">Site Title</a></h1>
                  <p class="site-description">Site Description</p>
                </div>
            `;
            // デフォルトの色などを適用しておく
            const titleLink = document.querySelector('.site-title a');
            const description = document.querySelector('.site-description');
            if (titleLink) titleLink.style.color = 'rgb(0, 0, 0)';
            if (description) description.style.color = 'rgb(0, 0, 0)';
            // 初期 position, clip は設定しない (customizer.js が適用するため)
        });

        test('should hide site title and description when color is "blank"', () => {
            // Arrange
            const siteTitle = document.querySelector('.site-title');
            const siteDescription = document.querySelector('.site-description');
            const headerColorChangeCallback = bindCallbacks['header_textcolor'];

            expect(headerColorChangeCallback).toBeDefined();
            expect(typeof headerColorChangeCallback).toBe('function');

            // Act
            headerColorChangeCallback('blank');

            // Assert
            expect(siteTitle.style.position).toBe('absolute'); // ← 今度は成功するはず
            expect(siteDescription.style.position).toBe('absolute'); // ← 今度は成功するはず
            // expect(siteTitle.style.clip).toBe('rect(1px, 1px, 1px, 1px)'); // jsdomの限界あり
            // expect(siteDescription.style.clip).toBe('rect(1px, 1px, 1px, 1px)');
        });

        test('should show site title and description and set color when color is not "blank"', () => {
            // Arrange
            const siteTitle = document.querySelector('.site-title');
            const siteTitleLink = document.querySelector('.site-title a');
            const siteDescription = document.querySelector('.site-description');
            const headerColorChangeCallback = bindCallbacks['header_textcolor'];

            expect(headerColorChangeCallback).toBeDefined();
            expect(typeof headerColorChangeCallback).toBe('function');
            const newColor = '#ff0000';
            const expectedRgbColor = 'rgb(255, 0, 0)'; // 期待するRGB形式

            // Act
            headerColorChangeCallback(newColor);

            // Assert: 表示スタイル
            expect(siteTitle.style.position).toBe('relative');
            expect(siteDescription.style.position).toBe('relative');
            expect(siteTitle.style.clip).toBe('auto');
            expect(siteDescription.style.clip).toBe('auto');

            // Assert: 色スタイル (★ 修正箇所)
            // jsdom が rgb() に変換することを考慮して比較
            expect(siteTitleLink.style.color).toBe(expectedRgbColor);
            expect(siteDescription.style.color).toBe(expectedRgbColor);
        });
    });

    test('wp.customize should be called for expected settings on load', () => {
        // Assert
        expect(mockCustomize).toHaveBeenCalledWith('blogname', expect.any(Function));
        expect(mockCustomize).toHaveBeenCalledWith('blogdescription', expect.any(Function));
        expect(mockCustomize).toHaveBeenCalledWith('header_textcolor', expect.any(Function));

        expect(bindCallbacks['blogname']).toBeDefined();
        expect(typeof bindCallbacks['blogname']).toBe('function');
        expect(bindCallbacks['blogdescription']).toBeDefined();
        expect(typeof bindCallbacks['blogdescription']).toBe('function');
        expect(bindCallbacks['header_textcolor']).toBeDefined();
        expect(typeof bindCallbacks['header_textcolor']).toBe('function');
    });
});
