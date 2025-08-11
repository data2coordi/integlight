/**
 * @jest-environment jsdom
 */


// Mock the slider module BEFORE imports
jest.mock('../../../js/src/slider.js', () => {
    // ★★★ Ensure the global variable exists within the mock factory's scope ★★★
    if (typeof global.integlight_sliderSettings === 'undefined') {
        global.integlight_sliderSettings = {
            displayChoice: 'slider',
            headerTypeNameSlider: 'slider',
            effect: 'fade',
            fadeName: 'fade',
            slideName: 'slide',
            changeDuration: 5,
            homeType: 'home1',
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





describe('Integlight_SliderManager', () => {
    // Factory function to create a mock jQuery object
    //const make$ = () => jest.fn(() => ({ ready: cb => cb() }));
    const make$ = () => {
        const $fn = jest.fn((selector) => {
            return { on: (event, cb) => cb() }; // onされると即実行
        });
        return $fn;
    };

    const $mock = make$(); // Mock jQuery
    const mockFade = jest.fn(); // Mock function for the fade effect
    const mockSlide = jest.fn();
    const registry = {
        fadehome1: class { constructor($, s) { mockFade($, s); } },
        slidehome1: class { constructor($, s) { mockSlide($, s); } }
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
