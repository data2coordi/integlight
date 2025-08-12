/**
 * @jest-environment jsdom
 */

beforeAll(() => {
    global.integlight_sliderSettings = {
        fadeName: 'fade',
        slideName: 'slide',
        home1Name: 'home1',
        home2Name: 'home2',
        changeDuration: 5,
        effect: 'fade',
        homeType: 'home2',
    };

    // 最低限の jQuery グローバル（副作用回避）
    global.jQuery = jest.fn(() => ({
        on: jest.fn(),
    }));
});

let Integlight_FadeSlider2;
let mock$,
    mockSliderElement,
    mockSlidesWrapper,
    mockSlideElements,
    slideMocks;

/**
 * slideMocks を使った map のモックを外に出して分かりやすく
 */
function createMockSlideElementsMap(cb) {
    const results = [];
    for (let i = 0; i < slideMocks.length; i++) {
        results.push(cb(i, slideMocks[i]));
    }
    return {
        get: () => results,
    };
}

beforeEach(async () => {
    jest.useFakeTimers();

    // 動的import（非同期）だがbeforeEachなので問題なし
    const module = await import('../../../js/src/slider.js');
    Integlight_FadeSlider2 = module.Integlight_FadeSlider2;

    // slideMocks 初期化
    slideMocks = [
        { find: jest.fn(() => ({ attr: jest.fn(() => 'img1.jpg') })) },
        { find: jest.fn(() => ({ attr: jest.fn(() => 'img2.jpg') })) },
        { find: jest.fn(() => ({ attr: jest.fn(() => 'img3.jpg') })) },
    ];

    // mockSliderElement の定義
    mockSliderElement = {
        find: jest.fn((selector) => {
            if (selector === '.slides') return mockSlidesWrapper;
            return null;
        }),
        addClass: jest.fn(),
    };

    // mockSlidesWrapper の定義
    mockSlidesWrapper = {
        find: jest.fn((selector) => {
            if (selector === '.slide') return mockSlideElements;
            return null;
        }),
        empty: jest.fn(),
        append: jest.fn(),
    };

    // mockSlideElements の定義
    mockSlideElements = {
        length: slideMocks.length,
        width: jest.fn(() => 100),
        first: jest.fn(() => ({
            clone: jest.fn(() => 'cloned-first'),
        })),
        css: jest.fn(),
        append: jest.fn(),
        one: jest.fn(),
        map: jest.fn(createMockSlideElementsMap),
    };






    /**
     * mock$のモックは分岐が多いのでコメントと処理を明確に
     */
    mock$ = jest.fn((selectorOrEl) => {
        // 新しいタグ文字列（例: '<img/>')のモック
        if (typeof selectorOrEl === 'string' && selectorOrEl.startsWith('<')) {
            return {
                attr: jest.fn().mockReturnThis(),
                append: jest.fn().mockReturnThis(),
                css: jest.fn().mockReturnThis(),
            };
        }

        // セレクタ文字列に対応したモック返し
        if (selectorOrEl === '.slider') return mockSliderElement;
        if (selectorOrEl === '.slides') return mockSlidesWrapper;
        if (selectorOrEl === '.slide') return mockSlideElements;



        function mockAttrFunction(mockSrc) {
            return jest.fn(() => mockSrc);
        }

        function mockFindFunction(selectorOrEl) {
            return jest.fn(sel => {
                if (sel === 'img') {
                    return {
                        attr: mockAttrFunction(selectorOrEl.mockSrc),
                    };
                }
                return null;
            });
        }

        // オブジェクトが渡された場合のネストされたモック返し
        if (typeof selectorOrEl === 'object' && selectorOrEl !== null) {
            return {
                find: mockFindFunction(selectorOrEl),
            };
        }

        // デフォルトは sliderElement を返す
        return mockSliderElement;
    });

    // append の呼び出しモックをリセット
    mockSlidesWrapper.append.mockClear();
});

afterEach(() => {
    jest.clearAllMocks();
    jest.useRealTimers();
});

describe('Integlight_FadeSlider2 初期化関連', () => {
    it('初期化時に .fade-effect クラスが付与されること', () => {
        const inst = new Integlight_FadeSlider2(mock$, { changeDuration: 5 });
        expect(mockSliderElement.addClass).toHaveBeenCalledWith('fade-effect');
    });

    it('画像リストが3枚未満なら複製して3枚にすること', () => {
        // 2枚に減らした slideMocks に差し替え
        slideMocks = [
            { mockSrc: 'img1.jpg' },
            { mockSrc: 'img2.jpg' },
        ];
        mockSlideElements.length = slideMocks.length;

        // map, eq も新しい slideMocks に合わせて差し替え
        mockSlideElements.map = jest.fn(createMockSlideElementsMap);

        const inst = new Integlight_FadeSlider2(mock$, { changeDuration: 5 });

        expect(inst.images.length).toBeGreaterThanOrEqual(3);
        expect(inst.images).toEqual(expect.arrayContaining(['img1.jpg', 'img2.jpg']));
    });

    it('初期化時にスライドDOMが3つだけ作成されること', () => {
        // appendに渡されるモックのクラス名を設定する
        let callIndex = 0;
        const classes = ['slide-left', 'slide-center', 'slide-right'];

        mockSlidesWrapper.append.mockImplementation((slideMock) => {
            slideMock.attr = jest.fn((key) =>
                key === 'class' ? classes[callIndex++] : undefined
            );
            slideMock.prop = jest.fn((key) =>
                key === 'class' ? classes[callIndex - 1] : undefined
            );
            return slideMock;
        });

        const inst = new Integlight_FadeSlider2(mock$, { changeDuration: 5 });

        expect(mockSlidesWrapper.empty).toHaveBeenCalled();
        expect(mockSlidesWrapper.append).toHaveBeenCalledTimes(3);

        // appendに渡された引数のクラス名を抽出
        const classNames = mockSlidesWrapper.append.mock.calls.map((call) => {
            let cls = null;
            if (typeof call[0].attr === 'function') {
                cls = call[0].attr('class');
            } else if (typeof call[0].prop === 'function') {
                cls = call[0].prop('class');
            }
            return typeof cls === 'string' ? cls : '';
        });

        // 各クラス名が存在することを検証
        expect(classNames.some((c) => c.includes('slide-left'))).toBe(true);
        expect(classNames.some((c) => c.includes('slide-center'))).toBe(true);
        expect(classNames.some((c) => c.includes('slide-right'))).toBe(true);
    });

    it('初期表示の3つスライドはopacity 1で表示されること', () => {
        const inst = new Integlight_FadeSlider2(mock$, { changeDuration: 5 });

        expect(inst.$visible.length).toBe(3);

        inst.$visible.forEach(($s) => {
            expect($s.css).toHaveBeenCalledWith('opacity', 1);
        });
    });
});
