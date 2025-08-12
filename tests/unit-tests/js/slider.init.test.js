/**
 * @jest-environment jsdom
 */


// Mock the slider module BEFORE imports
jest.mock('../../../js/src/slider.js', () => {
    // ★★★ Ensure the global variable exists within the mock factory's scope ★★★
    if (typeof global.integlight_sliderSettings === 'undefined') {
        global.integlight_sliderSettings = {
            fadeName: 'fade',
            slideName: 'slide',
            home1Name: 'home1',
            home2Name: 'home2',
        };
    }

    // ★★★ Ensure jQuery exists within the mock factory's scope ★★★
    if (typeof global.jQuery === 'undefined') {
        global.jQuery = jest.fn(() => ({
            on: jest.fn(),
            // Add other methods if needed
        }));
    }

    // Now, require the actual module. The global should be defined.
    const actualSliderModule = jest.requireActual('../../../js/src/slider.js');

    // Export only the necessary class, excluding the bottom initialization code's effects
    return {
        __esModule: true, // Indicate this is an ES module mock
        Integlight_SliderManager: actualSliderModule.Integlight_SliderManager,
    };
});


// Import the mocked module (which exports the real class definition)
import { Integlight_SliderManager } from '../../../js/src/slider.js';


// Factory function to create a mock jQuery object
//const make$ = () => jest.fn(() => ({ ready: cb => cb() }));
const make$ = () => {
    const $fn = jest.fn((selector) => {
        return { on: (event, cb) => cb() }; // onされると即実行
    });
    return $fn;
};

const $mock = make$(); // Mock jQuery


describe('Integlight_SliderManager', () => {

    const mockFade = jest.fn(); // Mock function for the fade effect
    const mockSlide = jest.fn();
    const registry = {
        fadehome1pc: class { constructor($, s) { mockFade($, s); } },
        slidehome1pc: class { constructor($, s) { mockSlide($, s); } }
    };

    it('should call the FadeSlider initializer when effect is fade', () => {
        const settings = { ...global.integlight_sliderSettings, effect: 'fade', homeType: 'home1' }; // Set effect to 'fade'

        const manager = new Integlight_SliderManager(
            settings,
            registry, // Pass the mock fade function in the registry
            $mock
        );

        manager.init();
        window.dispatchEvent(new Event('load'));
        // Run only pending timers (the setTimeout(0) inside ready())

        // Expect the mock fade function to have been called with correct arguments
        expect(mockFade).toHaveBeenCalledWith($mock, settings);
    });

    it('should call the FadeSlider initializer when effect is slide', () => {
        const settings = { ...global.integlight_sliderSettings, effect: 'slide', homeType: 'home1' }; // Set effect to 'fade'

        const manager = new Integlight_SliderManager(
            settings,
            registry, // Pass the mock fade function in the registry
            $mock
        );

        manager.init();
        window.dispatchEvent(new Event('load'));
        // Run only pending timers (the setTimeout(0) inside ready())

        // Expect the mock fade function to have been called with correct arguments
        expect(mockSlide).toHaveBeenCalledWith($mock, settings);
    });



});

describe('Integlight_SliderManager device-specific registry', () => {
    let originalMatchMedia;

    beforeEach(() => {
        // 元の matchMedia を退避
        originalMatchMedia = window.matchMedia;
    });

    afterEach(() => {
        // matchMedia を元に戻す
        window.matchMedia = originalMatchMedia;
        jest.clearAllMocks();
    });

    it('matches=false（PC）時に pc 用のスライダーが選択される', () => {
        // PC判定（matches: false）
        window.matchMedia = jest.fn().mockReturnValue({ matches: false });

        const mockPcClass = jest.fn();
        const registry = {
            // PC用のキーを持つクラスをモック化
            fadehome1pc: class { constructor($, s) { mockPcClass($, s); } }
        };

        const settings = { ...global.integlight_sliderSettings, effect: 'fade', homeType: 'home1' };
        const manager = new Integlight_SliderManager(settings, registry, $mock);

        // 初期化＋loadイベント発火
        manager.init();
        window.dispatchEvent(new Event('load'));

        // PC用クラスが呼ばれていること
        expect(mockPcClass).toHaveBeenCalledWith($mock, settings);
    });

    it('matches=true（SP）時に sp 用のスライダーが選択される', () => {
        // SP判定（matches: true）
        window.matchMedia = jest.fn().mockReturnValue({ matches: true });

        const mockSpClass = jest.fn();
        const registry = {
            // SP用のキーを持つクラスをモック化
            fadehome1sp: class { constructor($, s) { mockSpClass($, s); } }
        };

        const settings = { ...global.integlight_sliderSettings, effect: 'fade', homeType: 'home1' };
        const manager = new Integlight_SliderManager(settings, registry, $mock);

        // 初期化＋loadイベント発火
        manager.init();
        window.dispatchEvent(new Event('load'));

        // SP用クラスが呼ばれていること
        expect(mockSpClass).toHaveBeenCalledWith($mock, settings);
    });

    it('PC と SP で異なるクラスが選択されることを確認', () => {
        const mockPcClass = jest.fn();
        const mockSpClass = jest.fn();

        const registry = {
            // PC用とSP用で別クラスを設定
            slidehome2pc: class { constructor($, s) { mockPcClass($, s); } },
            slidehome2sp: class { constructor($, s) { mockSpClass($, s); } }
        };

        const settings = { ...global.integlight_sliderSettings, effect: 'slide', homeType: 'home2' };

        // --- PCケース ---
        window.matchMedia = jest.fn().mockReturnValue({ matches: false });
        let manager = new Integlight_SliderManager(settings, registry, $mock);
        manager.init();
        window.dispatchEvent(new Event('load'));
        expect(mockPcClass).toHaveBeenCalled();

        jest.clearAllMocks();

        // --- SPケース ---
        window.matchMedia = jest.fn().mockReturnValue({ matches: true });
        manager = new Integlight_SliderManager(settings, registry, $mock);
        manager.init();
        window.dispatchEvent(new Event('load'));
        expect(mockSpClass).toHaveBeenCalled();
    });
});
