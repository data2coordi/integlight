global.integlight_sliderSettings = {
    displayChoice: 'slider',
    headerTypeNameSlider: 'slider',
    effect: 'fade',
    fade: 'fade',
    slide: 'slide',
    changeDuration: 3
};

global.jQuery = jest.fn(() => ({
    ready: jest.fn((callback) => {
        callback(jQuery);
    }),
    fn: {
        extend: jest.fn()
    }
}));

let Integlight_FadeSlider;

describe('Integlight_FadeSlider', () => {
    let mock$, mockSlideElements, slideMocks, instance;

    beforeEach(async () => {
        jest.useFakeTimers();

        const module = await import('../../../js/src/slider.js');
        Integlight_FadeSlider = module.Integlight_FadeSlider;

        slideMocks = [
            { addClass: jest.fn() },
            { addClass: jest.fn() },
            { addClass: jest.fn() },
        ];

        mockSlideElements = {
            length: 3,
            css: jest.fn(),
            eq: jest.fn(index => slideMocks[index]),
            not: jest.fn(() => ({
                removeClass: jest.fn(),
            }))
        };

        const mockSlidesWrapper = {
            find: jest.fn(() => mockSlideElements)
        };

        const mockSliderElement = {
            find: jest.fn(() => mockSlidesWrapper),
            addClass: jest.fn()
        };


        mock$ = jest.fn((selector) => {
            if (selector === window) {
                return {
                    on: jest.fn((event, callback) => {
                        // 必要ならコールバックをすぐ呼ぶか、テストで制御
                        return this; // チェーン可能に
                    }),
                };
            }
            return mockSliderElement;
        });

        const settings = { changeDuration: 5 };

        mockSlideElements.removeClass = jest.fn(); // ← これを beforeEach に追加

        instance = new Integlight_FadeSlider(mock$, settings);
    });

    afterEach(() => {
        jest.useRealTimers();
        jest.clearAllMocks();
    });

    it('should initialize correctly', () => {
        expect(instance.currentIndex).toBe(0);
        expect(instance.$slider.addClass).toHaveBeenCalledWith('fade-effect');
        expect(mockSlideElements.css).toHaveBeenCalledWith('transition', expect.stringContaining('opacity'));
    });

    it('should update slides correctly on showSlide', () => {
        jest.advanceTimersByTime(5000);

        expect(mockSlideElements.eq).toHaveBeenCalled();
        expect(mockSlideElements.not).toHaveBeenCalled();
    });

    it('should reset currentIndex to 0 when reaching slideCount', () => {
        instance.currentIndex = 2;
        jest.advanceTimersByTime(5000);

        expect(instance.currentIndex).toBe(0);
    });

    it('should remove active class from all slides except the current one', () => {
        instance.currentIndex = 2;
        jest.advanceTimersByTime(5000);

        const targetSlide = slideMocks[2];
        const calledWithArg = mockSlideElements.not.mock.calls[0][0];

        // ここではaddClassの呼び出しが正しいかをチェックする
        expect(mockSlideElements.not).toHaveBeenCalled();
        expect(calledWithArg.addClass).toHaveBeenCalled(); // addClassが呼ばれたかをチェック

        const returnedFromNot = mockSlideElements.not.mock.results[0].value;
        expect(returnedFromNot.removeClass).toHaveBeenCalledWith('active');
    });

    it('should add active class to the current slide', () => {
        jest.advanceTimersByTime(5000);
        jest.advanceTimersByTime(5000);

        const eqCallIndex = mockSlideElements.eq.mock.calls.findIndex(args => args[0] === 2);
        const returnedFromEq = mockSlideElements.eq.mock.results[eqCallIndex]?.value;

        expect(returnedFromEq.addClass).toHaveBeenCalledWith('active');
    });
});
