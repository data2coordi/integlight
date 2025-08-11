/**
 * @jest-environment jsdom
 */

let Integlight_FadeSlider2;
let mock$, mockSliderElement, mockSlidesWrapper, mockSlideElements, slideMocks;

beforeEach(async () => {
    jest.useFakeTimers();

    // モジュール読み込み（事前に global.jQuery 等がセットされている前提）
    const module = await import('../../../js/src/slider.js');
    Integlight_FadeSlider2 = module.Integlight_FadeSlider2;

    // スライドモック配列（画像はsrc属性だけ必要なので簡略化）
    slideMocks = [
        { find: jest.fn(() => ({ attr: jest.fn(() => 'img1.jpg') })) },
        { find: jest.fn(() => ({ attr: jest.fn(() => 'img2.jpg') })) },
        { find: jest.fn(() => ({ attr: jest.fn(() => 'img3.jpg') })) }
    ];

    // .slide 要素群のモック（length は 3）
    mockSlideElements = {
        length: slideMocks.length,
        map: jest.fn((cb) => slideMocks.map((el, i) => cb(i, el))),
        eq: jest.fn(index => slideMocks[index]),
        find: jest.fn(() => ({ attr: jest.fn() })),
        css: jest.fn()
    };

    // .slides ラッパーのモック
    mockSlidesWrapper = {
        empty: jest.fn(),
        append: jest.fn(),
    };

    // .slider 要素のモック
    mockSliderElement = {
        find: jest.fn(selector => {
            if (selector === '.slides') return mockSlidesWrapper;
            if (selector === '.slide') return mockSlideElements;
            return null;
        }),
        addClass: jest.fn()
    };

    // jQuery モック関数（セレクタによって返すモックを切り替え）
    mock$ = jest.fn(selector => {
        if (selector === '.slider') return mockSliderElement;
        if (selector === '.slides') return mockSlidesWrapper;
        if (selector === '.slide') return mockSlideElements;
        if (selector === window) return { on: jest.fn(), off: jest.fn() };
        return mockSliderElement;
    });
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
        // 画像を2枚にして初期化
        slideMocks = [
            { find: jest.fn(() => ({ attr: jest.fn(() => 'img1.jpg') })) },
            { find: jest.fn(() => ({ attr: jest.fn(() => 'img2.jpg') })) }
        ];
        mockSlideElements.length = slideMocks.length;
        mockSlideElements.map = jest.fn((cb) => slideMocks.map((el, i) => cb(i, el)));
        mockSlideElements.eq = jest.fn(index => slideMocks[index]);

        const inst = new Integlight_FadeSlider2(mock$, { changeDuration: 5 });

        expect(inst.images.length).toBeGreaterThanOrEqual(3);
        expect(inst.images).toEqual(expect.arrayContaining(['img1.jpg', 'img2.jpg']));
    });

    it('初期化時にスライドDOMが3つだけ作成されること', () => {
        const inst = new Integlight_FadeSlider2(mock$, { changeDuration: 5 });

        // empty() が呼ばれていること
        expect(mockSlidesWrapper.empty).toHaveBeenCalled();

        // append() が3回呼ばれていること（3つスライド作成）
        expect(mockSlidesWrapper.append).toHaveBeenCalledTimes(3);

        // append() に渡された jQuery要素のクラス名に left, center, right が含まれることを確認
        const calls = mockSlidesWrapper.append.mock.calls;
        const classNames = calls.map(call => call[0].attr('class') || call[0].prop?.('class'));
        expect(classNames.some(c => c && c.includes('slide-left'))).toBe(true);
        expect(classNames.some(c => c && c.includes('slide-center'))).toBe(true);
        expect(classNames.some(c => c && c.includes('slide-right'))).toBe(true);
    });

    it('初期表示の3つスライドはopacity 1で表示されること', () => {
        const inst = new Integlight_FadeSlider2(mock$, { changeDuration: 5 });

        // css('transition', ...) の呼び出しは opacity用
        // css('opacity', 1) の呼び出しが3回あることを期待
        // $visible は配列で3つのjQueryラッパー（モックはcssがjest.fn()）

        expect(inst.$visible.length).toBe(3);

        inst.$visible.forEach($s => {
            expect($s.css).toHaveBeenCalledWith('opacity', 1);
        });
    });

});
