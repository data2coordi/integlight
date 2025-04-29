/**
 * @jest-environment jsdom
 */

describe('Slider init wrapper (簡易)', () => {
    beforeEach(() => {
        jest.useFakeTimers();
        jest.resetModules();
        jest.clearAllMocks();

        // ① グローバル jQuery モックを先にセット
        global.jQuery = () => ({
            ready: cb => cb(global.jQuery),
        });
        global.$ = global.jQuery;
    });

    afterEach(() => {
        jest.useRealTimers();
        delete global.integlight_sliderSettings;
        delete global.jQuery;
        delete global.$;
    });

    // モック設定とインポートをまとめたヘルパー
    async function initWithMockedClasses() {
        // ② slider.js を動的にモック：実体を読みつつ、クラスのみ jest.fn() に置き換え
        jest.doMock('../../../js/src/slider.js', () => {
            const actual = jest.requireActual('../../../js/src/slider.js');
            return {
                ...actual,
                Integlight_SlideSlider: jest.fn(),
                Integlight_FadeSlider: jest.fn(),
            };
        });

        // ③ インポート（これでトップレベル初期化が走る）
        const mod = await import('../../../js/src/slider.js');

        // ④ setTimeout(…,0) を実行して ready→new を走らせる
        jest.runAllTimers();
        return mod;
    }

    it('Slideモードなら SlideSlider が new される', async () => {
        global.integlight_sliderSettings = {
            displayChoice: 'slider',
            headerTypeNameSlider: 'slider',
            effect: 'slide',
            slide: 'slide',
            fade: 'fade',
            changeDuration: 1,
        };

        const { Integlight_SlideSlider, Integlight_FadeSlider } = await initWithMockedClasses();

        expect(Integlight_SlideSlider).toHaveBeenCalledWith(
            global.jQuery,
            global.integlight_sliderSettings
        );
        expect(Integlight_FadeSlider).not.toHaveBeenCalled();
    });

    it('Fadeモードなら FadeSlider が new される', async () => {
        global.integlight_sliderSettings = {
            displayChoice: 'slider',
            headerTypeNameSlider: 'slider',
            effect: 'fade',
            slide: 'slide',
            fade: 'fade',
            changeDuration: 1,
        };

        const { Integlight_SlideSlider, Integlight_FadeSlider } = await initWithMockedClasses();

        expect(Integlight_FadeSlider).toHaveBeenCalledWith(
            global.jQuery,
            global.integlight_sliderSettings
        );
        expect(Integlight_SlideSlider).not.toHaveBeenCalled();
    });

    it('unknownモードなら何も new されない', async () => {
        global.integlight_sliderSettings = {
            displayChoice: 'slider',
            headerTypeNameSlider: 'slider',
            effect: 'unknown',
            slide: 'slide',
            fade: 'fade',
            changeDuration: 1,
        };

        const { Integlight_SlideSlider, Integlight_FadeSlider } = await initWithMockedClasses();

        expect(Integlight_SlideSlider).not.toHaveBeenCalled();
        expect(Integlight_FadeSlider).not.toHaveBeenCalled();
    });
});
