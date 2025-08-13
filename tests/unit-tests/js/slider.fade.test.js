global.integlight_sliderSettings = {
    displayChoice: 'slider',
    headerTypeNameSlider: 'slider',
    effect: 'fade',
    fade: 'fade',
    slide: 'slide',
    changeDuration: 3
};

// JQueryは不要なので削除

let Integlight_FadeSlider;

describe('Integlight_FadeSlider (Plain JS)', () => {
    let mockSlider, mockSlidesContainer, mockSlides, instance;

    beforeEach(async () => {
        jest.useFakeTimers();

        // テスト対象のモジュールをインポート
        const module = await import('../../../js/src/slider.js');
        Integlight_FadeSlider = module.Integlight_FadeSlider;

        // ネイティブDOM APIのモックを作成
        mockSlides = [
            { classList: { add: jest.fn(), remove: jest.fn(), toggle: jest.fn() }, style: { transition: '', opacity: '' } },
            { classList: { add: jest.fn(), remove: jest.fn(), toggle: jest.fn() }, style: { transition: '', opacity: '' } },
            { classList: { add: jest.fn(), remove: jest.fn(), toggle: jest.fn() }, style: { transition: '', opacity: '' } },
        ];

        mockSlidesContainer = {
            querySelectorAll: jest.fn(() => mockSlides),
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
        instance = new Integlight_FadeSlider(settings);
    });

    afterEach(() => {
        jest.useRealTimers();
        jest.clearAllMocks();
    });

    it('should initialize correctly', () => {
        // コンストラクタで期待される初期化処理をテスト
        expect(instance.currentIndex).toBe(0);
        expect(instance.slider.classList.add).toHaveBeenCalledWith('fade-effect');

        // slidesのスタイルが正しく設定されているか
        expect(instance.slides.length).toBe(3);
        instance.slides.forEach(slide => {
            expect(slide.style.transition).toContain('opacity');
        });

        // 初期表示スライドにactiveクラスが付与されているか
        expect(instance.slides[0].classList.add).toHaveBeenCalledWith('active');
        expect(instance.slides[1].classList.remove).toHaveBeenCalledWith('active');
        expect(instance.slides[2].classList.remove).toHaveBeenCalledWith('active');
    });

    it('should advance to the next slide on each interval', () => {
        // 1回目のshowSlide()呼び出し (0 -> 1)
        jest.advanceTimersByTime(5000);
        expect(instance.currentIndex).toBe(1);
        expect(instance.slides[0].classList.toggle).toHaveBeenCalledWith('active', false);
        expect(instance.slides[1].classList.toggle).toHaveBeenCalledWith('active', true);
        expect(instance.slides[2].classList.toggle).toHaveBeenCalledWith('active', false);

        // 2回目のshowSlide()呼び出し (1 -> 2)
        jest.advanceTimersByTime(5000);
        expect(instance.currentIndex).toBe(2);
        expect(instance.slides[0].classList.toggle).toHaveBeenCalledWith('active', false);
        expect(instance.slides[1].classList.toggle).toHaveBeenCalledWith('active', false);
        expect(instance.slides[2].classList.toggle).toHaveBeenCalledWith('active', true);

        // 3回目のshowSlide()呼び出し (2 -> 0) - ループ処理
        jest.advanceTimersByTime(5000);
        expect(instance.currentIndex).toBe(0);
        expect(instance.slides[0].classList.toggle).toHaveBeenCalledWith('active', true);
        expect(instance.slides[1].classList.toggle).toHaveBeenCalledWith('active', false);
        expect(instance.slides[2].classList.toggle).toHaveBeenCalledWith('active', false);
    });

    it('should toggle active class correctly for each slide', () => {
        // 最初の状態をチェック
        expect(instance.slides[0].classList.add).toHaveBeenCalledWith('active');
        expect(instance.slides[1].classList.remove).toHaveBeenCalledWith('active');

        // 1回目のshowSlide実行
        jest.advanceTimersByTime(5000);
        expect(instance.currentIndex).toBe(1);
        expect(instance.slides[0].classList.toggle).toHaveBeenCalledWith('active', false);
        expect(instance.slides[1].classList.toggle).toHaveBeenCalledWith('active', true);

        // 2回目のshowSlide実行
        jest.advanceTimersByTime(5000);
        expect(instance.currentIndex).toBe(2);
        expect(instance.slides[1].classList.toggle).toHaveBeenCalledWith('active', false);
        expect(instance.slides[2].classList.toggle).toHaveBeenCalledWith('active', true);

        // 3回目のshowSlide実行
        jest.advanceTimersByTime(5000);
        expect(instance.currentIndex).toBe(0);
        expect(instance.slides[2].classList.toggle).toHaveBeenCalledWith('active', false);
        expect(instance.slides[0].classList.toggle).toHaveBeenCalledWith('active', true);
    });
});