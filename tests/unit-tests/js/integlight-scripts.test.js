/**
 * @jest-environment jsdom // jsdom環境を指定
 */

// --- Mock Setup ---
// グローバル変数のモック設定 (テストケース内で使用)
const mockSliderSettings = {
    displayChoice: 'slider', // スライダーが有効になるような値
    headerTypeNameSlider: 'slider',
    effect: 'slide', // テスト対象に合わせて設定
    fade: 'fade',
    slide: 'slide',
    changeDuration: 2, // テストで使う値
};

// import 文は削除 または コメントアウト
// import { Integlight_SlideSlider, Integlight_FadeSlider } from '../../../js/integlight-scripts';


describe('Integlight_SlideSlider (simplified)', () => {
    // beforeEach でタイマーをモック化
    beforeEach(() => {
        jest.useFakeTimers();
        // モジュールキャッシュとグローバル変数をリセット
        jest.resetModules();
        // グローバル変数を window に設定
        window.integlight_sliderSettings = { ...mockSliderSettings };
    });

    // afterEach でタイマーモックを解除
    afterEach(() => {
        jest.useRealTimers();
    });

    it('increments currentIndex when showSlide is called', () => {
        // --- jQuery Mock ---
        // モックオブジェクトの定義
        const mockSlideElement = {
            width: jest.fn(() => 100),
            first: jest.fn(() => ({
                clone: jest.fn(() => ({})),
            })),
            length: 3,
            eq: jest.fn(() => ({ addClass: jest.fn(), removeClass: jest.fn() })),
            not: jest.fn(() => ({ addClass: jest.fn(), removeClass: jest.fn() })),
            css: jest.fn(),
        };
        const mockSlidesContainer = {
            find: jest.fn(sel => sel === '.slide' ? mockSlideElement : {}),
            append: jest.fn(),
            css: jest.fn(),
        };
        const mockSliderContainer = {
            addClass: jest.fn(),
            find: jest.fn(sel => sel === '.slides' ? mockSlidesContainer : {}),
        };

        // ★★★ 修正箇所 ★★★
        // ready メソッドのモックを修正: コールバックに mockJQ を渡す
        const mockReadyObject = {
            ready: jest.fn(cb => {
                if (typeof cb === 'function') {
                    // コールバック関数に mockJQ (jQuery関数モック) を渡して実行
                    cb(mockJQ);
                }
            })
        };

        // jest.fn() でラップする関数を定義
        const jQueryMockImplementation = (selectorOrFunction) => {
            if (selectorOrFunction === '.slider') {
                return mockSliderContainer;
            }
            // ★★★ 修正箇所 ★★★
            // document または function が渡されたら、修正した mockReadyObject を返す
            if (selectorOrFunction === document || typeof selectorOrFunction === 'function') {
                return mockReadyObject;
            }
            console.warn(`mockJQ received unexpected selector: ${selectorOrFunction}`);
            return {};
        };

        // jest.fn() でモック関数を作成
        const mockJQ = jest.fn(jQueryMockImplementation);

        // グローバルな jQuery ($) をモックに設定
        global.$ = mockJQ;
        global.jQuery = mockJQ;
        // --- End jQuery Mock ---

        // スクリプトを require で読み込む
        const { Integlight_SlideSlider } = require('../../../js/integlight-scripts');

        // スクリプト末尾の setTimeout(..., 0) を実行
        jest.advanceTimersByTime(1);
        // スクリプト末尾の ready コールバックを実行
        // ready が呼ばれたか確認 (オプション)
        expect(mockReadyObject.ready).toHaveBeenCalled();
        // ready コールバックが実行されると、その中で setTimeout が呼ばれ、
        // さらにその中で new Integlight_SlideSlider が呼ばれる

        // ready 内の setTimeout(..., 0) を実行してコンストラクタを呼び出す
        jest.advanceTimersByTime(1);

        // ★ コンストラクタが呼ばれたか確認 (オプション)
        //   (Integlight_SlideSlider が require の結果に含まれているかで代替可能)

        // コンストラクタ内で this.$('.slider') が呼ばれるはずなので、モックが呼ばれたか確認
        expect(mockJQ).toHaveBeenCalledWith('.slider');

        // Integlight_SlideSlider のインスタンスを取得する方法が必要
        // このテスト構造だとインスタンスを直接取得するのは難しい
        // 代わりに、showSlide が呼ばれた結果（currentIndex の変化）を間接的にテストする
        // または、new Integlight_SlideSlider をテストコード側で呼び出すように変更する

        // --- テストの再構成案 ---
        // 1. スクリプトを require してクラス定義を取得
        // 2. new Integlight_SlideSlider をテストコード内で呼び出す
        // これにより、インスタンスを直接操作・検証できる

        // 再構成案に基づいたコード
        const slider = new Integlight_SlideSlider(mockJQ, window.integlight_sliderSettings);

        // コンストラクタ直後の currentIndex は 0 のはず
        expect(slider.currentIndex).toBe(0);

        // showSlide を直接呼び出す
        slider.showSlide();

        // currentIndex が 1 に増えることを確認
        expect(slider.currentIndex).toBe(1);
    });
});
