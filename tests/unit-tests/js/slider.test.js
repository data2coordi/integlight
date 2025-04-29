/**
 * @jest-environment jsdom // jsdom環境を指定
 */

// --- Mock Setup ---
// グローバル変数のモック設定 (テストケース内で使用)
const mockSliderSettingsBase = {
    displayChoice: 'slider', // スライダーが有効になるような値
    headerTypeNameSlider: 'slider',
    fade: 'fade',
    slide: 'slide',
    changeDuration: 2, // テストで使う値
};

// import 文は削除 または コメントアウト
// import { Integlight_SlideSlider, Integlight_FadeSlider } from '../../../js/integlight-scripts';


describe('Integlight Slider Scripts (Blackbox - Fade Initial)', () => {
    // 共通のモックやヘルパーを定義
    let mockJQ;
    let mockSlideElement;
    let mockSlidesContainer;
    let mockSliderContainer;
    let mockReadyObject;
    let Integlight_FadeSlider; // クラス定義を保持する変数

    beforeEach(() => {
        jest.useFakeTimers();
        jest.resetModules();

        // --- jQuery Mock (シンプル版) ---
        mockSlideElement = {
            width: jest.fn(() => 100),
            first: jest.fn(() => ({ clone: jest.fn(() => ({})) })),
            length: 3,
            eq: jest.fn(index => {
                const mockEqResult = {
                    _index: index,
                    addClass: jest.fn(),
                    removeClass: jest.fn()
                };
                // 結果を results 配列に格納 (アサーションで取得するため)
                const results = mockSlideElement.eq.mock.results;
                results[results.length] = { type: 'return', value: mockEqResult };
                return mockEqResult;
            }),
            not: jest.fn(excludedObject => {
                const mockNotResult = {
                    _excludedIndex: excludedObject ? excludedObject._index : null,
                    addClass: jest.fn(),
                    removeClass: jest.fn()
                };
                const results = mockSlideElement.not.mock.results;
                results[results.length] = { type: 'return', value: mockNotResult };
                return mockNotResult;
            }),
            css: jest.fn(),
        };
        mockSlidesContainer = {
            find: jest.fn(sel => sel === '.slide' ? mockSlideElement : {}),
            append: jest.fn(),
            css: jest.fn(),
        };
        mockSliderContainer = {
            addClass: jest.fn(),
            find: jest.fn(sel => sel === '.slides' ? mockSlidesContainer : {}),
        };
        mockReadyObject = {
            // ready コールバックは実行するが、引数の $ は mockJQ を渡す
            ready: jest.fn(cb => { if (typeof cb === 'function') cb(mockJQ); })
        };
        const jQueryMockImplementation = (selectorOrFunction) => {
            if (selectorOrFunction === '.slider') return mockSliderContainer;
            if (selectorOrFunction === document || typeof selectorOrFunction === 'function') return mockReadyObject;
            return {};
        };
        mockJQ = jest.fn(jQueryMockImplementation);
        global.$ = mockJQ;
        global.jQuery = mockJQ;
        // --- End jQuery Mock ---

        // DOM準備
        document.body.innerHTML = `
            <div class="slider">
                <div class="slides">
                    <div class="slide" id="slide-1">Slide 1</div>
                    <div class="slide" id="slide-2">Slide 2</div>
                    <div class="slide" id="slide-3">Slide 3</div>
                </div>
            </div>
        `;

        // Fade 用の設定
        window.integlight_sliderSettings = {
            ...mockSliderSettingsBase,
            effect: 'fade', // フェード効果を指定
        };

        // ★★★ 修正箇所 ★★★
        // スクリプトを読み込み、クラス定義を取得するだけ
        // (ready ハンドラや setTimeout はここでは実行しない)
        const scriptExports = require('../../../js/src/slider');
        // require がクラスを直接エクスポートしない場合、グローバルから取得するなどの代替策が必要
        // ここでは require が返すか、グローバルに Integlight_FadeSlider が存在すると仮定
        Integlight_FadeSlider = scriptExports.Integlight_FadeSlider || global.Integlight_FadeSlider;

        // スクリプト末尾の setTimeout(0) は進めておく (ready ハンドラ登録のため)
        jest.advanceTimersByTime(1);
        // ready ハンドラ自体は実行しておく (内部の setTimeout(0) をスケジュールさせるため)
        if (mockReadyObject.ready.mock.calls.length > 0) {
            mockReadyObject.ready.mock.calls[0][0];
        }
        // 注意: この時点ではまだ new Integlight_FadeSlider は実行されていない

    });

    afterEach(() => {
        jest.useRealTimers();
    });

    // テスト1.1: ページ読み込み時、最初のスライドが表示されていること。
    it('ページ読み込み時、最初のスライドが表示されていること', () => {
        // Arrange: クラスが定義されていることを確認
        expect(Integlight_FadeSlider).toBeDefined();

        // Act: ★★★ 修正箇所 ★★★
        // テスト内で直接インスタンスを生成する
        // これにより、コンストラクタ内の showSlide のみが実行される
        const slider = new Integlight_FadeSlider(mockJQ, window.integlight_sliderSettings);

        // Assert
        // 初期化時にクラス操作が行われることを確認
        expect(mockSliderContainer.addClass).toHaveBeenCalledWith('fade-effect');

        // コンストラクタ内の showSlide で eq が1回だけ呼ばれるはず
        expect(mockSlideElement.eq).toHaveBeenCalledTimes(1);
        expect(mockSlideElement.eq).toHaveBeenCalledWith(1); // 最初の呼び出しは index 1

        // eq(1) の結果オブジェクトに対する操作を確認
        const firstEqCallResult = mockSlideElement.eq.mock.results[0].value;
        expect(firstEqCallResult.addClass).toHaveBeenCalledWith('active');

        // not の呼び出しを確認
        expect(mockSlideElement.not).toHaveBeenCalledTimes(1);
        expect(mockSlideElement.not).toHaveBeenCalledWith(firstEqCallResult);

        // not の結果オブジェクトに対する操作を確認
        const firstNotCallResult = mockSlideElement.not.mock.results[0].value;
        expect(firstNotCallResult.removeClass).toHaveBeenCalledWith('active');

        // css の呼び出しを確認
        expect(mockSlideElement.css).toHaveBeenCalledWith('transition', expect.stringContaining('opacity'));
    });

});
