/**
 * @jest-environment jsdom
 */

// slider.js のインポート時に`integlight_sliderSettings`が必要なため、モックで先に定義します。
// これにより、モジュールレベルの副作用（グローバルなsliderManagerの初期化）がエラーなく実行されます。
jest.mock('../../../js/src/slider.js', () => {
    // このモックはテストの実行前に処理されるため、グローバル変数を先行して定義できます。
    global.integlight_sliderSettings = {
        fadeName: 'fade',
        slideName: 'slide',
        home1Name: 'home1',
        home2Name: 'home2',
    };
    // モック内で実際のモジュールを返し、テストでは必要なクラスのみをインポートして使用します。
    return jest.requireActual('../../../js/src/slider.js');
});
import { Integlight_SliderManager } from '../../../js/src/slider.js';

describe('Integlight_SliderManager Initialization', () => {
    let addEventListenerSpy;
    let loadCallback;

    // 各テストの前にグローバル設定とスパイをセットアップ
    beforeEach(() => {
        global.integlight_sliderSettings = {
            fadeName: 'fade',
            slideName: 'slide',
            home1Name: 'home1',
            home2Name: 'home2',
        };

        // window.addEventListenerをスパイし、コールバックをキャプチャ
        addEventListenerSpy = jest.spyOn(window, 'addEventListener').mockImplementation((event, callback) => {
            if (event === 'load') {
                loadCallback = callback;
            }
        });

        jest.clearAllMocks();
    });

    // テスト後にスパイをリストア
    afterEach(() => {
        addEventListenerSpy.mockRestore();
    });

    // test.each を使用して、エフェクトごとのテストを共通化
    test.each([
        ['fade', 'fadehome1pc'],
        ['slide', 'slidehome1pc'],
    ])('effectが "%s" の場合、対応するスライダークラスが初期化される', (effect, registryKey) => {
        // Arrange
        const mockSliderClass = jest.fn();
        const registry = {
            [registryKey]: class { constructor(s) { mockSliderClass(s); } }
        };
        const settings = { ...global.integlight_sliderSettings, effect, homeType: 'home1' };

        // Act
        const manager = new Integlight_SliderManager(settings, registry);
        manager.init();
        loadCallback(); // 'load' イベントのコールバックを直接実行

        // Assert
        expect(mockSliderClass).toHaveBeenCalledWith(settings);
    });
});

describe('Integlight_SliderManager device-specific registry', () => {
    let originalMatchMedia;
    let addEventListenerSpy;
    let loadCallback;

    beforeEach(() => {
        originalMatchMedia = window.matchMedia;
        addEventListenerSpy = jest.spyOn(window, 'addEventListener').mockImplementation((event, callback) => {
            if (event === 'load') {
                loadCallback = callback;
            }
        });
        jest.clearAllMocks();
    });

    afterEach(() => {
        window.matchMedia = originalMatchMedia;
        addEventListenerSpy.mockRestore();
    });

    // test.each を使用して、デバイスごとのテストを共通化
    test.each([
        ['PC', false, 'slidehome2pc', 'slidehome2sp'],
        ['SP', true, 'slidehome2sp', 'slidehome2pc'],
    ])('%s用のクラスが選択される', (deviceName, matches, expectedKey, otherKey) => {
        // Arrange
        window.matchMedia = jest.fn().mockReturnValue({ matches });

        const mockPcClass = jest.fn();
        const mockSpClass = jest.fn();
        const registry = {
            slidehome2pc: class { constructor(s) { mockPcClass(s); } },
            slidehome2sp: class { constructor(s) { mockSpClass(s); } }
        };
        const settings = { ...global.integlight_sliderSettings, effect: 'slide', homeType: 'home2' };
        const mocks = { slidehome2pc: mockPcClass, slidehome2sp: mockSpClass };

        // Act
        new Integlight_SliderManager(settings, registry).init();
        loadCallback(); // 'load' イベントのコールバックを直接実行

        // Assert
        expect(mocks[expectedKey]).toHaveBeenCalledWith(settings);
        expect(mocks[otherKey]).not.toHaveBeenCalled();
    });
});
