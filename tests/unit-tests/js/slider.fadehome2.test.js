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
        homeType: 'home2'
    };

    // 最低限の jQuery グローバル（副作用回避）
    global.jQuery = jest.fn(() => ({
        on: jest.fn(),
        fn: { extend: jest.fn() }
    }));
});

let Integlight_FadeSlider2;
let mock$, mockSliderElement, mockSlidesWrapper, mockSlideElements, slideMocks;

beforeEach(async () => {
    jest.useFakeTimers();

    const module = await import('../../../js/src/slider.js');
    Integlight_FadeSlider2 = module.Integlight_FadeSlider2;

    slideMocks = [
        { find: jest.fn(() => ({ attr: jest.fn(() => 'img1.jpg') })) },
        { find: jest.fn(() => ({ attr: jest.fn(() => 'img2.jpg') })) },
        { find: jest.fn(() => ({ attr: jest.fn(() => 'img3.jpg') })) }
    ];

    // mockSliderElement はlet宣言しているので再代入する
    mockSliderElement = {
        find: jest.fn(selector => {
            if (selector === '.slides') return mockSlidesWrapper;
            return null;
        }),
        addClass: jest.fn(),
    };

    mockSlidesWrapper = {
        find: jest.fn(selector => {
            if (selector === '.slide') return mockSlideElements;
            return null;
        }),
        empty: jest.fn(),
        append: jest.fn(),
    };

    mockSlideElements = {
        length: slideMocks.length,
        width: jest.fn(() => 100),
        eq: jest.fn(index => slideMocks[index]),
        first: jest.fn(() => ({ clone: jest.fn(() => 'cloned-first') })),
        css: jest.fn(),
        append: jest.fn(),
        one: jest.fn(),
        map: jest.fn(cb => {
            const results = [];
            for (let i = 0; i < slideMocks.length; i++) {
                results.push(cb(i, slideMocks[i]));
            }
            return {
                get: () => results,
            };
        }),
    };

    mock$ = jest.fn(selectorOrEl => {
        // タグ生成 (例: '<img/>')
        if (typeof selectorOrEl === 'string' && selectorOrEl.startsWith('<')) {
            return {
                attr: jest.fn().mockReturnThis(),
                append: jest.fn().mockReturnThis(),
                css: jest.fn().mockReturnThis(),
            };
        }

        // 既存のセレクタに対してモック返し
        if (selectorOrEl === '.slider') return mockSliderElement;
        if (selectorOrEl === '.slides') return mockSlidesWrapper;
        if (selectorOrEl === '.slide') return mockSlideElements;
        if (selectorOrEl === window) return { on: jest.fn(), off: jest.fn() };

        // 要素オブジェクトの場合のモック処理
        if (typeof selectorOrEl === 'object' && selectorOrEl !== null) {
            return {
                find: jest.fn(sel => {
                    if (sel === 'img') {
                        return {
                            attr: jest.fn(() => selectorOrEl.mockSrc || 'mock-img.jpg'),
                        };
                    }
                    return null;
                }),
            };
        }

        // デフォルトはmockSliderElement返し
        return mockSliderElement;
    });



    // append の呼び出しモックを初期化し直す
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
        slideMocks = [
            {
                mockSrc: 'img1.jpg',
                find: jest.fn(() => ({ attr: jest.fn(() => 'img1.jpg') }))
            },
            {
                mockSrc: 'img2.jpg',
                find: jest.fn(() => ({ attr: jest.fn(() => 'img2.jpg') }))
            }
        ];
        mockSlideElements.length = slideMocks.length;
        mockSlideElements.map = jest.fn(cb => {
            const results = [];
            for (let i = 0; i < slideMocks.length; i++) {
                results.push(cb(i, slideMocks[i]));
            }
            return {
                get: () => results
            };
        });
        mockSlideElements.eq = jest.fn(index => slideMocks[index]);

        const inst = new Integlight_FadeSlider2(mock$, { changeDuration: 5 });

        expect(inst.images.length).toBeGreaterThanOrEqual(3);
        expect(inst.images).toEqual(expect.arrayContaining(['img1.jpg', 'img2.jpg']));
    });

    // append に渡される jQuery 要素モックを作成する関数例
    function createSlideMock(className) {
        return {
            attr: jest.fn((key) => {
                if (key === 'class') return className;
                return undefined;
            }),
            css: jest.fn(),
            append: jest.fn(),
        };
    }


    // テスト内でのappend呼び出しをフックしてclassを返すモックに置換する場合
    it('初期化時にスライドDOMが3つだけ作成されること', () => {
        // append に渡されるモックは3回呼ばれる想定なので、
        // 各回の引数（slideMock）を上書きしてclass属性を返すようにする
        let callIndex = 0;
        const classes = ['slide-left', 'slide-center', 'slide-right'];
        mockSlidesWrapper.append.mockImplementation((slideMock) => {
            slideMock.attr = jest.fn((key) => (key === 'class' ? classes[callIndex++] : undefined));
            slideMock.prop = jest.fn((key) => (key === 'class' ? classes[callIndex - 1] : undefined));
            return slideMock;
        });

        const inst = new Integlight_FadeSlider2(mock$, { changeDuration: 5 });

        expect(mockSlidesWrapper.empty).toHaveBeenCalled();
        expect(mockSlidesWrapper.append).toHaveBeenCalledTimes(3);

        const calls = mockSlidesWrapper.append.mock.calls;
        const classNames = calls.map(call => {
            let cls = null;
            if (typeof call[0].attr === 'function') {
                cls = call[0].attr('class');
            } else if (typeof call[0].prop === 'function') {
                cls = call[0].prop('class');
            }
            return typeof cls === 'string' ? cls : '';
        });

        expect(classNames.some(c => c.includes('slide-left'))).toBe(true);
        expect(classNames.some(c => c.includes('slide-center'))).toBe(true);
        expect(classNames.some(c => c.includes('slide-right'))).toBe(true);
    });


    it('初期表示の3つスライドはopacity 1で表示されること', () => {
        const inst = new Integlight_FadeSlider2(mock$, { changeDuration: 5 });

        expect(inst.$visible.length).toBe(3);

        inst.$visible.forEach($s => {
            expect($s.css).toHaveBeenCalledWith('opacity', 1);
        });
    });

});
