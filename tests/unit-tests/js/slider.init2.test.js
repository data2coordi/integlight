/**
 * @jest-environment jsdom
 */

// jQuery のモック
global.jQuery = jest.fn(() => ({
    ready: jest.fn((cb) => cb()), // jQuery(document).ready のモック
}));

// コンソールログのモックを解除する
beforeAll(() => {
    jest.restoreAllMocks(); // console.log を元に戻す
});

beforeEach(() => {
    jest.resetModules()
});



afterEach(() => {
    jest.clearAllMocks();
});

// グローバル設定
global.integlight_sliderSettings = {
    displayChoice: 'slider',
    headerTypeNameSlider: 'slider',
    effect: 'slide',
    slide: 'slide',
    fade: 'fade',
    changeDuration: 3,
};

// モック定義
jest.mock('../../../js/src/slider.js', () => {
    const actual = jest.requireActual('../../../js/src/slider.js');
    return {
        ...actual,
        Integlight_SlideSlider: jest.fn(),
        Integlight_FadeSlider: jest.fn(),
    };
});

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



    it('スライドクラスが正常に new されることを確認', () => {
        const mod = require('../../../js/src/slider.js');
        const settings = global.integlight_sliderSettings;

        // Integlight_SlideSlider クラスが new されたか確認
        //new mod.Integlight_SlideSlider({}, settings);

        // 期待される通りに新しいインスタンスが作成されたか確認
        expect(mod.Integlight_SlideSlider).toHaveBeenCalledTimes(1); // 1回だけ呼ばれたことを確認
        expect(mod.Integlight_SlideSlider).toHaveBeenCalledWith({}, settings); // 引数が正しく渡されたことを確認
    });

})