/**
 * @jest-environment jsdom
 */

// Define the global variable at the top level (as a safety measure)
if (typeof global.integlight_sliderSettings === 'undefined') {
    global.integlight_sliderSettings = {
        displayChoice: 'slider',
        headerTypeNameSlider: 'slider',
        effect: 'fade', // Default value for the global setting
        fade: 'fade',
        slide: 'slide',
        changeDuration: 5
    };
}

// ★★★ Define global jQuery BEFORE jest.mock ★★★
if (typeof global.jQuery === 'undefined') {
    global.jQuery = jest.fn(() => ({
        ready: jest.fn((callback) => {
            callback(global.jQuery); // Pass the mock jQuery itself
        }),
        // Add other methods if needed by the constructor or init logic being tested indirectly
    }));
}

// Mock the slider module BEFORE imports
jest.mock('../../../js/src/slider.js', () => {
    // ★★★ Ensure the global variable exists within the mock factory's scope ★★★
    if (typeof global.integlight_sliderSettings === 'undefined') {
        global.integlight_sliderSettings = {
            displayChoice: 'slider',
            headerTypeNameSlider: 'slider',
            effect: 'fade',
            fade: 'fade',
            slide: 'slide',
            changeDuration: 5
        };
    }

    // ★★★ Ensure jQuery exists within the mock factory's scope ★★★
    if (typeof global.jQuery === 'undefined') {
        global.jQuery = jest.fn(() => ({
            ready: jest.fn((callback) => {
                callback(global.jQuery);
            }),
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

beforeAll(() => {
    // Enable timer mocks
    jest.useFakeTimers();
});

afterAll(() => {
    // Clean up the global variable
    if (Object.prototype.hasOwnProperty.call(global, 'jQuery')) {
        delete global.jQuery;
    }
    // Clean up the global variable
    if (Object.prototype.hasOwnProperty.call(global, 'integlight_sliderSettings')) {
        delete global.integlight_sliderSettings;
    }
    // Restore real timers
    jest.useRealTimers();
});

describe('Integlight_SliderManager', () => {
    // Factory function to create a mock jQuery object
    const make$ = () => jest.fn(() => ({ ready: cb => cb() }));

    afterEach(() => {
        // Clear all timers after each test
        jest.clearAllTimers();
    });

    it('should do nothing if displayChoice does not match', () => {
        const settings = {
            displayChoice: 'foo', // Non-matching value
            headerTypeNameSlider: 'slider',
            effect: 'fade',
            fade: 'fade',
            slide: 'slide',
            changeDuration: 5
        };
        const mockFade = jest.fn();
        // Instantiate the real SliderManager (imported via the mock setup)
        const manager = new Integlight_SliderManager(
            settings,
            { fade: mockFade, slide: jest.fn() }, // Pass mock effect registry
            make$() // Pass mock jQuery
        );

        manager.init();
        jest.runAllTimers(); // Run timers including the setTimeout(0) in init

        // Expect the fade effect function NOT to have been called
        expect(mockFade).not.toHaveBeenCalled();
    });

    it('should call the FadeSlider initializer when effect is fade', () => {
        const settings = { ...global.integlight_sliderSettings, effect: 'fade' }; // Set effect to 'fade'
        const mockFade = jest.fn(); // Mock function for the fade effect
        const $mock = make$(); // Mock jQuery

        const manager = new Integlight_SliderManager(
            settings,
            { fade: mockFade, slide: jest.fn() }, // Pass the mock fade function in the registry
            $mock
        );
        manager.init();

        // Run only pending timers (the setTimeout(0) inside ready())
        jest.runOnlyPendingTimers();

        // Expect the mock fade function to have been called with correct arguments
        expect(mockFade).toHaveBeenCalledWith($mock, settings);
    });

    it('should call the SlideSlider initializer when effect is slide', () => {
        const settings = {
            ...global.integlight_sliderSettings,
            effect: 'slide' // Set effect to 'slide'
        };
        const mockSlide = jest.fn(); // Mock function for the slide effect
        const $mock = make$(); // Mock jQuery

        const manager = new Integlight_SliderManager(
            settings,
            { fade: jest.fn(), slide: mockSlide }, // Pass the mock slide function in the registry
            $mock
        );
        manager.init();
        jest.runOnlyPendingTimers();

        // Expect the mock slide function to have been called with correct arguments
        expect(mockSlide).toHaveBeenCalledWith($mock, settings);
    });

    it('should do nothing if effect is not in the registry', () => {
        const settings = {
            ...global.integlight_sliderSettings,
            effect: 'unknown' // Unknown effect
        };
        const mockFade = jest.fn();
        const mockSlide = jest.fn();
        const $mock = make$();

        const manager = new Integlight_SliderManager(
            settings,
            { fade: mockFade, slide: mockSlide }, // Pass mocks for known effects
            $mock
        );
        manager.init();
        jest.runOnlyPendingTimers();

        // Expect neither mock function to have been called
        expect(mockFade).not.toHaveBeenCalled();
        expect(mockSlide).not.toHaveBeenCalled();
    });
});
