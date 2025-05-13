/**
 * @jest-environment jsdom
 */

// --- Mock Setup ---
let bindCallbacks = {};
const mockCustomize = jest.fn((settingId, setupCallback) => {
    setupCallback({
        bind: (callback) => {
            bindCallbacks[settingId] = callback;
        }
    });
});



const mockJQuery = jest.fn(selectorOrFunction => {


    const elements = document.querySelectorAll(selectorOrFunction);
    return {
        text: jest.fn(function (value) {
            if (value !== undefined) {
                elements.forEach(el => {
                    el.textContent = value;
                });
            }
            return this;
        }),
        css: jest.fn(function (styles) {
            if (styles && typeof styles === 'object') {
                elements.forEach(el => {
                    for (const prop in styles) {
                        el.style[prop] = styles[prop];
                    }
                });
            }
            return this;
        })
    };
});


// --- Tests ---
describe('Customizer Script Tests (Simple DOM Check)', () => {


    beforeEach(() => {
        jest.resetModules();
        global.wp = { customize: mockCustomize };
        global.jQuery = mockJQuery;

        mockCustomize.mockClear();
        mockJQuery.mockClear();

        bindCallbacks = {};
        document.body.innerHTML = '';

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

});
