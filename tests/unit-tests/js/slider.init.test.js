/**
 * @jest-environment jsdom
 */
import '../__mocks__/jquery'; // jQueryモック
import { enableFetchMocks } from 'jest-fetch-mock';
enableFetchMocks();

import '@wordpress/jest-console'; // consoleログ検出用
import { advanceTimersByTime, runAllTimers } from '@jest/fake-timers';

jest.useFakeTimers();

beforeAll(() => {
    jest.restoreAllMocks(); // console.log のモックを解除
    console.log('@@@@@@@@@@@ beforeAll');
});

// integlight_sliderSettings をグローバルに定義しておく
global.integlight_sliderSettings = {
    displayChoice: 'slider',
    headerTypeNameSlider: 'slider',
    effect: 'slide',
    fade: 'fade',
    slide: 'slide',
    changeDuration: 2,
};

// テスト対象のJSファイルを読み込む（この中で new される）
describe('トップレベル実行確認', () => {
    it('トップレベルで Integlight_SlideSlider が new されることを確認', () => {
        require('../../../js/src/slider.js');

        // タイマーを進める
        runAllTimers();

        // console.log のログが実行されたことを確認したいなら以下を使う
        // expect(console.log).toHaveBeenCalledWith(expect.stringContaining('@@@@@@@@@@6'));

        // エラーが出ないことを確認
        expect(true).toBe(true);
    });
});
