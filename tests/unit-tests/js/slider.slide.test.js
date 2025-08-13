global.integlight_sliderSettings = {
    headerTypeNameSlider: 'slider',
    effect: 'slide',
    fadeName: 'fade',
    slideName: 'slide',
    changeDuration: 3
};

let Integlight_SlideSlider;

describe('Integlight_SlideSlider (Plain JS)', () => {
    let mockSlider, mockSlidesContainer, mockSlides, instance;

    beforeEach(async () => {
        jest.useFakeTimers();

        const module = await import('../../../js/src/slider.js');
        Integlight_SlideSlider = module.Integlight_SlideSlider;

        // ネイティブDOM APIのモックを作成
        mockSlides = [
            { offsetWidth: 100, cloneNode: jest.fn(() => ({ ...mockSlides[0], cloned: true })) },
            { offsetWidth: 100, cloneNode: jest.fn(() => ({ ...mockSlides[1], cloned: true })) },
            { offsetWidth: 100, cloneNode: jest.fn(() => ({ ...mockSlides[2], cloned: true })) },
        ];

        mockSlidesContainer = {
            querySelectorAll: jest.fn(() => mockSlides),
            appendChild: jest.fn(),
            style: {
                transition: '',
                transform: '',
            },
            addEventListener: jest.fn((event, cb, options) => {
                // `once: true`オプションを模倣するために、コールバックを直接実行
                if (event === 'transitionend' && options.once) {
                    cb();
                }
            }),
        };

        mockSlider = {
            querySelector: jest.fn(selector => {
                if (selector === '.slides') return mockSlidesContainer;
                return null;
            }),
            classList: {
                add: jest.fn()
            },
        };

        // document.querySelectorをモック化
        jest.spyOn(document, 'querySelector').mockReturnValue(mockSlider);

        const settings = { changeDuration: 5 };
        instance = new Integlight_SlideSlider(settings);
    });

    afterEach(() => {
        jest.useRealTimers();
        jest.clearAllMocks();
    });

    it('should initialize correctly and append a clone of the first slide', () => {
        expect(instance.currentIndex).toBe(0);
        expect(instance.slider.classList.add).toHaveBeenCalledWith('slide-effect');

        // cloneNodeが呼ばれているか
        expect(mockSlides[0].cloneNode).toHaveBeenCalledWith(true);
        // appendChildが呼ばれているか
        expect(mockSlidesContainer.appendChild).toHaveBeenCalled();
        // `slideWidth`が正しく設定されているか
        expect(instance.slideWidth).toBe(100);
    });

    it('should update slide position with transition on showSlide', () => {
        // showSlideメソッドを実行
        jest.advanceTimersByTime(5000);

        // トランジションが設定されているか
        expect(mockSlidesContainer.style.transition).toContain('ease-in-out');
        // transformが設定されているか
        expect(mockSlidesContainer.style.transform).toContain('translateX');
        // currentIndexが正しくインクリメントされているか
        expect(instance.currentIndex).toBe(1);
        // transformの値が正しいか
        expect(mockSlidesContainer.style.transform).toBe('translateX(-100px)');
    });

    it('should reset index after last slide and remove transition', () => {
        // 最終スライドのcurrentIndexを設定
        instance.currentIndex = 2;

        // showSlideを実行し、クローンされたスライドに移動する
        jest.advanceTimersByTime(5000);

        // `transitionend`イベントが登録されているか
        expect(mockSlidesContainer.addEventListener).toHaveBeenCalledWith(
            'transitionend',
            expect.any(Function),
            { once: true }
        );

        // `addEventListener`のモックでコールバックを直接呼び出しているので、
        // 遷移が完了した後の状態をチェックする
        expect(instance.currentIndex).toBe(0);
        expect(mockSlidesContainer.style.transition).toBe('none');
        expect(mockSlidesContainer.style.transform).toBe('translateX(0px)');
    });
});