/**
 * @jest-environment jsdom
 */

// ① ここだけ：slider.js のクラスをモック
jest.mock('../../../js/src/slider.js', () => ({
    Integlight_SlideSlider: jest.fn(),
    Integlight_FadeSlider: jest.fn(),
}));

describe('Mock Replacement Smoke Test', () => {
    it('静的 require でモックが効くか', () => {
        const mod = require('../../../js/src/slider.js');
        expect(typeof mod.Integlight_SlideSlider).toBe('function');
        expect(jest.isMockFunction(mod.Integlight_SlideSlider)).toBe(true);
    });

    it('動的 import でモックが効くか', async () => {
        const mod = await import('../../../js/src/slider.js');
        expect(typeof mod.Integlight_SlideSlider).toBe('function');
        expect(jest.isMockFunction(mod.Integlight_SlideSlider)).toBe(true);
    });
});