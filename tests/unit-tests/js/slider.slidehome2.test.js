/**
 * @jest-environment jsdom
 */

//
// グローバル初期化（import 前に用意）
// - slider.js がインポート時に末尾で自動初期化する場合に備える
//
beforeAll(() => {
    global.integlight_sliderSettings = {
        fadeName: 'fade',
        slideName: 'slide',
        home1Name: 'home1',
        home2Name: 'home2',
        changeDuration: 5,
        effect: 'slide',
        homeType: 'home2'
    };

    // 最低限の jQuery グローバル（副作用回避）
    global.jQuery = jest.fn(() => ({
        on: jest.fn(),
        fn: { extend: jest.fn() }
    }));
});

let Integlight_SlideSlider2;
let mock$, mockSliderElement, mockSlidesWrapper, mockSlideElements, slideMocks;

beforeEach(async () => {
    jest.useFakeTimers();

    // テスト対象モジュールを読み込む（global を先に用意している）
    const module = await import('../../../js/src/slider.js');
    Integlight_SlideSlider2 = module.Integlight_SlideSlider2;

    // ----- スライド要素（eq が返すオブジェクト） -----
    slideMocks = [
        { width: jest.fn(() => 100), clone: jest.fn(() => ({ /* clone-result-0 */ })) },
        { width: jest.fn(() => 100), clone: jest.fn(() => ({ /* clone-result-1 */ })) },
        { width: jest.fn(() => 100), clone: jest.fn(() => ({ /* clone-result-2 */ })) }
    ];

    // $slides.find('.slide') の戻り（jQuery ライク）
    mockSlideElements = {
        length: slideMocks.length,
        width: jest.fn(() => 100),
        eq: jest.fn(index => slideMocks[index]),
        first: jest.fn(() => ({ clone: jest.fn(() => 'cloned-first') })),
        css: jest.fn(),
        append: jest.fn(),
        one: jest.fn()
    };

    // $slider.find('.slides') が返すラッパー
    mockSlidesWrapper = {
        find: jest.fn(selector => {
            if (selector === '.slide') return mockSlideElements;
            return null;
        }),
        prepend: jest.fn(),
        append: jest.fn(),
        css: jest.fn(),
        one: jest.fn((event, cb) => {
            // テストでは transitionend を即実行しても安全な形にしておく（必要なら）
            if (event === 'transitionend' && typeof cb === 'function') {
                cb();
            }
            return mockSlidesWrapper;
        })
    };

    // .slider 要素
    mockSliderElement = {
        find: jest.fn(selector => {
            if (selector === '.slides') return mockSlidesWrapper;
            return null;
        }),
        addClass: jest.fn()
    };

    // $ 関数（セレクタに応じて返すオブジェクトを用意）
    mock$ = jest.fn(selector => {
        if (selector === '.slider') return mockSliderElement;
        if (selector === '.slides') return mockSlidesWrapper;
        if (selector === '.slide') return mockSlideElements;
        if (selector === window) return { on: jest.fn() };
        return mockSliderElement;
    });
});

afterEach(() => {
    jest.clearAllMocks();
    jest.useRealTimers();
});

describe('Integlight_SlideSlider2 初期化関連', () => {
    it('初期化時に slide-effect クラスが付与されること', () => {
        const inst = new Integlight_SlideSlider2(mock$, { changeDuration: 5 });
        expect(mockSliderElement.addClass).toHaveBeenCalledWith('slide-effect');
    });

    it('初期化時にスライド幅が取得されること', () => {
        new Integlight_SlideSlider2(mock$, { changeDuration: 5 });
        expect(mockSlideElements.width).toHaveBeenCalled();
    });

    it('左右に2枚ずつクローンが追加されること', () => {
        new Integlight_SlideSlider2(mock$, { changeDuration: 5 });
        expect(mockSlidesWrapper.prepend).toHaveBeenCalledTimes(2);
        expect(mockSlidesWrapper.append).toHaveBeenCalledTimes(2);
    });

    it('初期位置が currentIndex=2 にセットされ helperSlide が呼ばれること', () => {
        // spy を先に仕込み、インスタンス化して検証
        const spy = jest.spyOn(Integlight_SlideSlider2.prototype, 'helperSlide');
        const inst = new Integlight_SlideSlider2(mock$, { changeDuration: 5 });
        expect(spy).toHaveBeenCalledWith(2, false);
        spy.mockRestore();
    });
});
describe('Integlight_SlideSlider2 初期化関連', () => {
    /*ブラックボックステスト開始*/
    /*ブラックボックステスト開始*/
    /*ブラックボックステスト開始*/
    it('ページ読み込み後にスライダーが一定時間ごとに切り替わること', () => {
        // 初期状態のインデックスを記録
        const instance = new Integlight_SlideSlider2(mock$, { changeDuration: 5 });

        const initialIndex = instance.currentIndex;

        // changeDuration（秒）ごとにadvanceTimersByTimeで時間経過を再現
        jest.advanceTimersByTime(instance.displayDuration * 1000);

        // インデックスが変化しているか確認（ループ動作を想定）
        expect(instance.currentIndex).not.toBe(initialIndex);

        // スライド位置変更用のCSSが呼ばれていることを確認
        expect(mockSlidesWrapper.css).toHaveBeenCalledWith(
            'transform',
            expect.stringContaining('translateX')
        );
    });
    it('一定時間経過でスライドが右にスライドすること', () => {
        const instance = new Integlight_SlideSlider2(mock$, { changeDuration: 5 });

        // 初期状態の transform 呼び出しのうち最後の呼び出しを取得
        const callsBefore = mockSlidesWrapper.css.mock.calls.filter(
            call => call[0] === 'transform'
        );
        const lastTransformBefore = callsBefore.length ? callsBefore[callsBefore.length - 1][1] : null;

        const initialIndex = instance.currentIndex;

        // タイマーでスライド切替（次のスライドへ）
        jest.advanceTimersByTime(instance.displayDuration * 1000);

        expect(instance.currentIndex).toBe(initialIndex + 1);

        // transform の呼び出しのうち最後を取得
        const callsAfter = mockSlidesWrapper.css.mock.calls.filter(
            call => call[0] === 'transform'
        );
        const lastTransformAfter = callsAfter.length ? callsAfter[callsAfter.length - 1][1] : null;

        expect(lastTransformAfter).not.toBeNull();
        expect(lastTransformBefore).not.toBeNull();

        // translateX の数値を抽出する正規表現
        const rx = /translateX\((-?\d+)px\)/;
        const beforeMatch = lastTransformBefore.match(rx);
        const afterMatch = lastTransformAfter.match(rx);

        expect(beforeMatch).not.toBeNull();
        expect(afterMatch).not.toBeNull();

        const beforeX = parseInt(beforeMatch[1], 10);
        const afterX = parseInt(afterMatch[1], 10);

        // 右にスライドなので translateX のマイナス値は大きくなる（より負の値になる）
        expect(afterX).toBeLessThan(beforeX);
    });
    it('スライドが最後まで到達後に最初のスライドにループして戻ること', () => {
        const instance = new Integlight_SlideSlider2(mock$, { changeDuration: 5 });

        // 最後のスライドの次の位置まで currentIndex を進める
        instance.currentIndex = instance.slideCount + 1;

        // showSlide() を呼び出し（setInterval の代わりに直接呼ぶのもアリ）
        instance.showSlide();

        // transitionend コールバックは one() モックで即呼び出される想定
        // これにより currentIndex はループ先頭(2)にリセットされるはず

        expect(instance.currentIndex).toBe(2);

        // スライド位置変更用のCSSも呼ばれているはず
        expect(mockSlidesWrapper.css).toHaveBeenCalledWith(
            'transform',
            expect.stringContaining('translateX')
        );
    });
    it('操作がなくても自動でスライドが切り替わり続けること', () => {

        const instance = new Integlight_SlideSlider2(mock$, { changeDuration: 1 }); // 短いdurationで高速テスト

        // showSlideをスパイして呼び出し回数を監視
        const spyShowSlide = jest.spyOn(instance, 'showSlide');

        // 初期呼び出しはまだ（setIntervalはタイマー待ち）
        expect(spyShowSlide).toHaveBeenCalledTimes(0);

        // 3秒間（3回分）タイマーを進める
        jest.advanceTimersByTime(3000);

        // showSlideが3回以上呼ばれていることを期待
        expect(spyShowSlide).toHaveBeenCalledTimes(3);

        spyShowSlide.mockRestore();

        jest.useRealTimers();
    });
    it('スライド2枚でも初期化と動作が正常', () => {
        slideMocks = [
            { width: jest.fn(() => 100), clone: jest.fn(() => ({ /* clone-result-0 */ })) },
            { width: jest.fn(() => 100), clone: jest.fn(() => ({ /* clone-result-1 */ })) },
        ];

        mockSlideElements.length = 2;
        //mockSlideElements.eq = jest.fn(index => slideMocks[index]);

        const instance = new Integlight_SlideSlider2(mock$, { changeDuration: 5 });

        expect(instance.currentIndex).toBe(2); // 左に2枚クローン追加分
        expect(mockSlidesWrapper.prepend).toHaveBeenCalledTimes(2);
        expect(mockSlidesWrapper.append).toHaveBeenCalledTimes(2);

        instance.showSlide();
        expect(instance.currentIndex).toBe(3);
    });


});



/*ブラックボックステストケース
No	テスト内容	期待結果／検証方法
1	ページ読み込み後にスライダーが動作するか	実際に表示されるスライドが一定時間で切り替わること（画面上の変化）
2	一定時間経過でスライドが右にスライドするか	スライドの表示内容が変わり、スライドが右方向に移動しているように見えること
3	スライドが最後まで到達後、最初のスライドに戻るか	ループしてスライドが繰り返されること（ユーザーから見て途切れなく動く）
4	操作がなくても自動で切り替わり続けるか	自動再生が止まらず継続して動くこと
5	スライド画像が２つでも正常に動作すること

*/