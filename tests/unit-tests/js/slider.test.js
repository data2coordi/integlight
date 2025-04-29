//本体側import時に、実行されて、エラーになるのを防止
global.integlight_sliderSettings = {
    displayChoice: 'slider',
    headerTypeNameSlider: 'slider',
    effect: 'fade',
    fade: 'fade',
    slide: 'slide',
    changeDuration: 3
};

//本体側import時に、実行されて、エラーになるのを防止
// jQuery のモックを修正して ready メソッドを追加
global.jQuery = jest.fn(() => ({
    ready: jest.fn((callback) => {
        // テストの中で ready が呼ばれると即座にコールバックを実行
        callback(jQuery);
    }),
    // 必要に応じてその他の jQuery メソッドもモック
    fn: {
        extend: jest.fn()
    }
}));


let Integlight_FadeSlider; // ← ここではimportしない！

describe('Integlight_FadeSlider', () => {
    let mock$, mockSlideElements, instance;

    beforeEach(async () => {
        jest.useFakeTimers();

        // ★ここで動的importする　//本体側import時に、実行されて、エラーになるのを防止
        const module = await import('../../../js/src/slider.js');
        Integlight_FadeSlider = module.Integlight_FadeSlider;

        mockSlideElements = {
            length: 3,
            css: jest.fn(),
            eq: jest.fn(index => ({
                addClass: jest.fn(),
            })),
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

        mock$ = jest.fn(() => {
            return mockSliderElement;
        });

        /*
        mock$ = jest.fn(selector => {
            if (selector === '.slider') {
                return mockSliderElement;
            }
            return null;
        });
        */

        const settings = { changeDuration: 5 };

        instance = new Integlight_FadeSlider(mock$, settings);
    });

    afterEach(() => {
        jest.useRealTimers();
        jest.clearAllMocks();
    });

    it('should initialize correctly', () => {
        expect(instance.currentIndex).toBe(1);
        //expect(mock$.mock.calls[0][0]).toBe('.slider');
        expect(instance.$slider.addClass).toHaveBeenCalledWith('fade-effect');
        expect(mockSlideElements.css).toHaveBeenCalledWith('transition', expect.stringContaining('opacity'));
    });

    it('should update slides correctly on showSlide', () => {
        jest.advanceTimersByTime(5000); // 5秒進める（changeDuration）

        expect(mockSlideElements.eq).toHaveBeenCalled();
        expect(mockSlideElements.not).toHaveBeenCalled();
    });

    it('should reset currentIndex to 0 when reaching slideCount', () => {
        instance.currentIndex = 2; // スライド数（mockSlideElements.length=3）の最後の1つ前に設定
        jest.advanceTimersByTime(5000); // 5秒進める（changeDuration）

        expect(instance.currentIndex).toBe(0); // 3枚目の後なのでリセットされる
    });

    it('should remove active class from all slides except the current one', () => {
        instance.currentIndex = 2;
        jest.advanceTimersByTime(5000);

        // eq(2)が呼ばれたオブジェクトを取得
        const targetSlide = mockSlideElements.eq.mock.results[0].value;

        // notに正しい引数が渡されたか（参照が一致するかではなく、呼ばれたか）
        expect(mockSlideElements.not).toHaveBeenCalled();
        const calledWithArg = mockSlideElements.not.mock.calls[0][0];
        expect(calledWithArg).toEqual(targetSlide); // deepEqual

        // removeClassが正しく呼ばれているか
        const returnedFromNot = mockSlideElements.not.mock.results[0].value;
        expect(returnedFromNot.removeClass).toHaveBeenCalledWith('active');
    });

    it('should add active class to the current slide', () => {
        jest.advanceTimersByTime(5000); // currentIndex = 1
        jest.advanceTimersByTime(5000); // currentIndex = 2

        // eq に currentIndex=2 が渡された回を探す
        const eqCallIndex = mockSlideElements.eq.mock.calls.findIndex(args => args[0] === 2);
        const returnedFromEq = mockSlideElements.eq.mock.results[eqCallIndex]?.value;

        expect(returnedFromEq.addClass).toHaveBeenCalledWith('active');
    });



});
