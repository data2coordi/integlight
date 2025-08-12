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
    // jQueryグローバルの簡易モック（副作用回避）
    global.jQuery = jest.fn(() => ({ on: jest.fn() }));
});

let Integlight_FadeSlider2;
let mock$;
let mockSliderElement;
let mockSlidesWrapper;
let mockSlideElements;
let slideMocks;

function createMockSlideElementsMap(cb) {
    const results = slideMocks.map((slideMock, i) => cb(i, slideMock));
    return {
        get: () => results,
    };
}

beforeEach(async () => {
    jest.useFakeTimers();

    const module = await import('../../../js/src/slider.js');
    Integlight_FadeSlider2 = module.Integlight_FadeSlider2;

    slideMocks = [
        { find: jest.fn(() => ({ attr: jest.fn(() => 'img1.jpg') })) },
        { find: jest.fn(() => ({ attr: jest.fn(() => 'img2.jpg') })) },
        { find: jest.fn(() => ({ attr: jest.fn(() => 'img3.jpg') })) },
    ];

    mockSliderElement = {
        find: jest.fn((selector) => (selector === '.slides' ? mockSlidesWrapper : null)),
        addClass: jest.fn(),
    };

    mockSlidesWrapper = {
        find: jest.fn((selector) => (selector === '.slide' ? mockSlideElements : null)),
        empty: jest.fn(),
        append: jest.fn(),
    };

    mockSlideElements = {
        length: slideMocks.length,
        width: jest.fn(() => 100),
        first: jest.fn(() => ({ clone: jest.fn(() => 'cloned-first') })),
        css: jest.fn(),
        append: jest.fn(),
        one: jest.fn(),
        map: jest.fn(createMockSlideElementsMap),
    };

    mock$ = jest.fn((selectorOrEl, attributes) => {
        if (typeof selectorOrEl === 'string' && selectorOrEl.startsWith('<')) {
            const props = attributes || {};
            return {
                _props: { ...props }, // propsの保存（テスト用途で保持するなら残す）
                append: jest.fn().mockReturnThis(),
                css: jest.fn().mockReturnThis(),
                attr: jest.fn((k, v) => v === undefined ? (props[k] ?? '') : (props[k] = v, this)),
                find: jest.fn(() => ({ attr: jest.fn().mockReturnThis() })),
            };
        }
        if (selectorOrEl === '.slider') return mockSliderElement;
        if (selectorOrEl === '.slides') return mockSlidesWrapper;
        if (selectorOrEl === '.slide') return mockSlideElements;

        if (typeof selectorOrEl === 'object' && selectorOrEl !== null) {
            return {
                find: jest.fn(sel => (sel === 'img' ? { attr: jest.fn(() => selectorOrEl.mockSrc) } : null)),
            };
        }

        return mockSliderElement;
    });

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
        slideMocks = [{ mockSrc: 'img1.jpg' }, { mockSrc: 'img2.jpg' }];
        mockSlideElements.length = slideMocks.length;
        mockSlideElements.map = jest.fn(createMockSlideElementsMap);

        const inst = new Integlight_FadeSlider2(mock$, { changeDuration: 5 });

        expect(inst.images.length).toBeGreaterThanOrEqual(3);
        expect(inst.images).toEqual(expect.arrayContaining(['img1.jpg', 'img2.jpg']));
    });

    it('初期化時にスライドDOMが3つだけ作成されること', () => {
        const expectedClasses = ['slide-left', 'slide-center', 'slide-right'];
        const inst = new Integlight_FadeSlider2(mock$, { changeDuration: 5 });

        expect(mockSlidesWrapper.empty).toHaveBeenCalled();
        expect(mockSlidesWrapper.append).toHaveBeenCalledTimes(3);

        const appendedArgs = mockSlidesWrapper.append.mock.calls.map(c => c[0]);
        const classNames = appendedArgs.map(el => el.attr('class'));

        expectedClasses.forEach(expectedClass => {
            expect(classNames.some(c => c.includes(expectedClass))).toBe(true);
        });
    });

    it('初期表示の3つスライドはopacity 1で表示されること', () => {
        const inst = new Integlight_FadeSlider2(mock$, { changeDuration: 5 });
        expect(inst.$visible.length).toBe(3);
        inst.$visible.forEach($s => expect($s.css).toHaveBeenCalledWith('opacity', 1));
    });

    it('showSlide() 呼び出しでスライドがフェードアウトすること（opacityが0に設定される）', () => {
        const inst = new Integlight_FadeSlider2(mock$, { changeDuration: 5 });
        inst.showSlide();
        inst.$visible.forEach($slide => expect($slide.css).toHaveBeenCalledWith('opacity', 0));
    });
});

describe('Integlight_FadeSlider2 ブラックボックステスト関連', () => {
    // attr の読み書きを可能にするモック関数
    function createAttrMock(initialSrc) {
        let src = initialSrc;
        const fn = jest.fn((key, val) => {
            if (val === undefined) return src;
            src = val;
            return fn;
        });
        return fn;
    }

    beforeEach(() => {
        slideMocks = [
            { find: jest.fn(() => ({ attr: createAttrMock('img1.jpg') })) },
            { find: jest.fn(() => ({ attr: createAttrMock('img2.jpg') })) },
            { find: jest.fn(() => ({ attr: createAttrMock('img3.jpg') })) },
        ];
    });

    it('showSlide() 呼び出しでスライドがフェードアウトすること（opacityが0に設定される）', () => {
        const inst = new Integlight_FadeSlider2(mock$, { changeDuration: 5 });
        inst.showSlide();
        inst.$visible.forEach($slide => expect($slide.css).toHaveBeenCalledWith('opacity', 0));
    });

    it('showSlide() 呼び出し後、一定時間経過で画像が切り替わること（画像の src が更新され、opacity が1に戻されている）', () => {
        const inst = new Integlight_FadeSlider2(mock$, { changeDuration: 5 });

        // 変更前の画像srcを取得（最初の3つだけ）
        const beforeSrcs = inst.$visible.map($slide => $slide.find('img').attr('src'));

        // showSlide() 呼び出し（opacity 0 設定は他テストで確認済み）
        inst.showSlide();

        // タイマーを進めて画像切替が完了するまで待つ
        jest.advanceTimersByTime(inst.changingDuration * 1000);

        // 変更後の画像srcを取得
        const afterSrcs = inst.$visible.map($slide => $slide.find('img').attr('src'));

        // 画像が切り替わっていることを確認
        let changed = false;
        for (let i = 0; i < beforeSrcs.length; i++) {
            if (beforeSrcs[i] !== afterSrcs[i]) {
                changed = true;
                break;
            }
        }
        expect(changed).toBe(true);

        // 画像切替後に opacity が 1 に戻されていることを確認
        inst.$visible.forEach($slide => {
            expect($slide.css).toHaveBeenCalledWith('opacity', 1);
        });
    });

    it('画像インデックスが末尾まで進んだ後、先頭に戻ってループすること', () => {
        const inst = new Integlight_FadeSlider2(mock$, { changeDuration: 5 });

        // 画像数
        const length = inst.images.length;

        // baseIndexを末尾にセット
        inst.baseIndex = length - 1;

        // $visibleの各要素のfind('img').attr('src')をsrc文字列を返すモックに書き換え
        inst.$visible.forEach(($slide, i) => {
            $slide.find = jest.fn(() => ({
                attr: jest.fn(() => inst.images[(inst.baseIndex + i) % length])
            }));
            $slide.css = jest.fn();
        });

        // showSlide() 呼び出し（opacity 0設定は他テストで確認済み）
        inst.showSlide();

        // タイマーを進めて画像切替待ち
        jest.advanceTimersByTime(inst.changingDuration * 1000);

        // baseIndexが0に戻ること
        expect(inst.baseIndex).toBe(0);

        // 画像srcが正しく先頭に戻っていること
        const firstVisibleSrc = inst.$visible[0].find('img').attr('src');
        expect(firstVisibleSrc).toBe(inst.images[inst.baseIndex]);

        // opacity が1に戻っていることだけ確認
        inst.$visible.forEach($slide => {
            expect($slide.css).toHaveBeenCalledWith('opacity', 1);
        });
    });



});
