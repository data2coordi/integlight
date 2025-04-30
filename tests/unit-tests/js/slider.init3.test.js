/**
 * @jest-environment jsdom
 */

// jQuery のモック
global.jQuery = jest.fn(() => ({
    ready: jest.fn((cb) => cb()), // jQuery(document).ready のモック
}));

// console.log のモック
beforeAll(() => {
    jest.spyOn(console, 'log').mockImplementation(() => { });
});

afterEach(() => {
    // すべての console.log 呼び出しを無視する
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
        Integlight_SliderClass: jest.fn(), // Integlight_SliderClass をモック
    };
});

describe('トップレベルのコード実行確認', () => {
    it('Integlight_SliderClass が jQuery の ready イベント内で正常に new されることを確認', () => {
        const mod = require('../../../js/src/slider.js');
        const settings = global.integlight_sliderSettings;

        // トップレベルのコードを実行する
        require('../../../js/src/slider.js');

        // jQuery(document).ready が呼ばれたか確認
        expect(jQuery().ready).toHaveBeenCalled();

        // Integlight_SliderClass が正常に new されたか確認
        expect(mod.Integlight_SliderClass).toHaveBeenCalledTimes(1); // 1回だけ呼ばれたことを確認
        expect(mod.Integlight_SliderClass).toHaveBeenCalledWith(expect.any(Function), settings); // $とsettingsが渡されたか確認
    });
});
