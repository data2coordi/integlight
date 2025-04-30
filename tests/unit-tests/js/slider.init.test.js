/**
 * @jest-environment jsdom
 */

// ① ここだけ：slider.js のクラスをモック
jest.mock('../../../js/src/slider.js', () => ({
    Integlight_SlideSlider: jest.fn(),
    Integlight_FadeSlider: jest.fn(),
}));

import { initSlider, Integlight_SlideSlider, Integlight_FadeSlider } from '../../../js/src/slider.js';

describe('Mock Replacement Smoke Test', () => {
    it('静的 require でモックが効くか', () => {

        jest.useFakeTimers();

        // B) 最小限の jQuery(document).ready モック
        global.jQuery = jest.fn(cbOrSel => ({
            ready: cb => cb(global.jQuery),
        }));
        global.$ = global.jQuery;

        // C) グローバル設定を slide モードに
        global.integlight_sliderSettings = {
            displayChoice: 'slider',
            headerTypeNameSlider: 'slider',
            effect: 'slide',
            slide: 'slide',
            fade: 'fade',
            changeDuration: 1,
        };


        jest.runAllTimers();


        const mod = require('../../../js/src/slider.js');

        expect(typeof mod.Integlight_SlideSlider).toBe('function');
        expect(jest.isMockFunction(mod.Integlight_SlideSlider)).toBe(true);

        // F) 呼び出しを検証
        /*
        expect(mod.Integlight_SlideSlider).toHaveBeenCalledWith(
            global.jQuery,
            global.integlight_sliderSettings
        );
        */


    });

    it('動的 import でモックが効くか', async () => {
        const mod = await import('../../../js/src/slider.js');
        expect(typeof mod.Integlight_SlideSlider).toBe('function');
        expect(jest.isMockFunction(mod.Integlight_SlideSlider)).toBe(true);
    });
});