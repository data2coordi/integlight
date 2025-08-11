global.integlight_sliderSettings = {

    headerTypeNameSlider: 'slider',
    effect: 'slide',
    fadeName: 'fade',
    slideName: 'slide',
    changeDuration: 3
};

global.jQuery = jest.fn(() => ({

    on: jest.fn(),
    fn: {
        extend: jest.fn()
    }
}));

let Integlight_SlideSlider;

describe('Integlight_SlideSlider', () => {
    let mock$, mockSlideElements, mockSlidesWrapper, slideMocks, instance;

    beforeEach(async () => {
        jest.useFakeTimers();

        const module = await import('../../../js/src/slider.js');
        Integlight_SlideSlider = module.Integlight_SlideSlider;

        slideMocks = [
            { width: jest.fn(() => 100) },
            { width: jest.fn(() => 100) },
            { width: jest.fn(() => 100) },
        ];

        mockSlideElements = {
            length: 3,
            css: jest.fn(),
            eq: jest.fn(index => slideMocks[index]),
            first: jest.fn(() => ({ clone: jest.fn(() => 'cloned-slide') })),
            append: jest.fn(),
            width: jest.fn(() => 100),  // 👈 これを追加
            one: jest.fn(),  // ← ここを追加
        };
        mockSlidesWrapper = {
            find: jest.fn(() => mockSlideElements),
            css: jest.fn(),
            append: jest.fn(),
            one: jest.fn((event, cb) => {
                if (event === 'transitionend' && typeof cb === 'function') {
                    cb();
                }
                return mockSlidesWrapper;  // チェイン可能にする場合
            }),

        };

        const mockSliderElement = {
            find: jest.fn(() => mockSlidesWrapper),
            addClass: jest.fn()
        };

        mock$ = jest.fn(() => mockSliderElement);

        const settings = { changeDuration: 5 };
        instance = new Integlight_SlideSlider(mock$, settings);
    });

    afterEach(() => {
        jest.useRealTimers();
        jest.clearAllMocks();
    });

    it('should initialize correctly and append clone', () => {
        expect(instance.currentIndex).toBe(0);
        expect(instance.$slider.addClass).toHaveBeenCalledWith('slide-effect');
        expect(mockSlideElements.first).toHaveBeenCalled();
        expect(mockSlidesWrapper.append).toHaveBeenCalledWith('cloned-slide');
    });

    it('should update slide position with transition', () => {
        instance.currentIndex = 0;
        jest.advanceTimersByTime(5000);

        expect(mockSlidesWrapper.css).toHaveBeenCalledWith(
            'transition',
            expect.stringContaining('ease-in-out')
        );
        expect(mockSlidesWrapper.css).toHaveBeenCalledWith(
            'transform',
            expect.stringContaining('translateX')
        );
    });

    it('should reset index after last slide and remove transition', () => {
        instance.currentIndex = 2;
        jest.advanceTimersByTime(5000); // move to clone


        jest.advanceTimersByTime(instance.changingDuration * 1000); // timeout to reset

        expect(instance.currentIndex).toBe(0);
        expect(mockSlidesWrapper.css).toHaveBeenCalledWith('transition', 'none');
        expect(mockSlidesWrapper.css).toHaveBeenCalledWith('transform', 'translateX(0px)');
    });
});
