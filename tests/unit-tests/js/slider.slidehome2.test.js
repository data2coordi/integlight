/**
 * @jest-environment jsdom
 */
import { Integlight_SliderManager } from '../../../js/src/slider.js';

describe('Integlight_SliderManager', () => {
    let originalAddEventListener;

    beforeAll(() => {
        global.integlight_sliderSettings = {
            fadeName: 'fade',
            slideName: 'slide',
            home1Name: 'home1',
            home2Name: 'home2',
            changeDuration: 2.5,
        };
        // window.addEventListener をモック化
        originalAddEventListener = window.addEventListener;
        window.addEventListener = jest.fn();
    });

    afterAll(() => {
        window.addEventListener = originalAddEventListener;
    });

    // モッククラスの定義を beforeEach の外に移動
    const MockFadeSlider = jest.fn();
    const MockSlideSlider = jest.fn();

    it('should call the FadeSlider initializer when effect is fade', () => {
        const settings = { ...global.integlight_sliderSettings, effect: 'fade', homeType: 'home1' };

        // テストケースごとに registry を定義し、モッククラスを明示的に指定
        const registry = {
            fadehome1pc: MockFadeSlider,
        };

        const manager = new Integlight_SliderManager(settings, registry);

        manager.init();

        // window.addEventListener のコールバックを実行
        window.addEventListener.mock.calls[0][1]();

        expect(MockFadeSlider).toHaveBeenCalledWith(settings);
    });

    it('should call the SlideSlider initializer when effect is slide', () => {
        const settings = { ...global.integlight_sliderSettings, effect: 'slide', homeType: 'home1' };

        const registry = {
            slidehome1pc: MockSlideSlider,
        };

        const manager = new Integlight_SliderManager(settings, registry);

        manager.init();

        window.addEventListener.mock.calls[0][1]();

        expect(MockSlideSlider).toHaveBeenCalledWith(settings);
    });
});

describe('Integlight_SliderManager device-specific registry', () => {
    let originalMatchMedia;

    beforeEach(() => {
        originalMatchMedia = window.matchMedia;
    });

    afterEach(() => {
        window.matchMedia = originalMatchMedia;
        jest.clearAllMocks();
    });

    it('matches=false（PC）時に pc 用のスライダーが選択される', () => {
        window.matchMedia = jest.fn().mockReturnValue({ matches: false });

        const mockPcClass = jest.fn();
        const registry = {
            fadehome1pc: mockPcClass
        };

        const settings = { ...global.integlight_sliderSettings, effect: 'fade', homeType: 'home1' };
        const manager = new Integlight_SliderManager(settings, registry);

        manager.init();
        window.addEventListener.mock.calls[0][1]();

        expect(mockPcClass).toHaveBeenCalledWith(settings);
    });

    it('matches=true（SP）時に sp 用のスライダーが選択される', () => {
        window.matchMedia = jest.fn().mockReturnValue({ matches: true });

        const mockSpClass = jest.fn();
        const registry = {
            fadehome1sp: mockSpClass
        };

        const settings = { ...global.integlight_sliderSettings, effect: 'fade', homeType: 'home1' };
        const manager = new Integlight_SliderManager(settings, registry);

        manager.init();
        window.addEventListener.mock.calls[0][1]();

        expect(mockSpClass).toHaveBeenCalledWith(settings);
    });

    it('PC と SP で異なるクラスが選択されることを確認', () => {
        const mockPcClass = jest.fn();
        const mockSpClass = jest.fn();

        const registry = {
            slidehome2pc: mockPcClass,
            slidehome2sp: mockSpClass
        };

        const settings = { ...global.integlight_sliderSettings, effect: 'slide', homeType: 'home2' };

        // PCケース
        window.matchMedia = jest.fn().mockReturnValue({ matches: false });
        let manager = new Integlight_SliderManager(settings, registry);
        manager.init();
        window.addEventListener.mock.calls[0][1]();
        expect(mockPcClass).toHaveBeenCalledWith(settings);
        expect(mockSpClass).not.toHaveBeenCalled();

        jest.clearAllMocks();

        // SPケース
        window.matchMedia = jest.fn().mockReturnValue({ matches: true });
        manager = new Integlight_SliderManager(settings, registry);
        manager.init();
        window.addEventListener.mock.calls[0][1]();
        expect(mockSpClass).toHaveBeenCalledWith(settings);
        expect(mockPcClass).not.toHaveBeenCalled();
    });
});