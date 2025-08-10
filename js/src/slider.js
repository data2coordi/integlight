

// Slider _s ////////////////////////////////////////////////////////////////
class Integlight_Slider {
    constructor($, settings) {
        this.$ = $;
        this.$slider = this.$('.slider');
        this.$slides = this.$slider.find('.slides');
        this.$slide = this.$slides.find('.slide');
        this.slideCount = this.$slide.length;
        this.displayDuration = settings.changeDuration;
        this.changingDuration = this.displayDuration / 2;
        this.currentIndex = 0;
    }
}

class Integlight_SlideSlider extends Integlight_Slider {
    constructor($, settings) {
        super($, settings);
        // 初期状態の調整
        this.currentIndex = 0;
        this.$slider.addClass('slide-effect');

        this.slideWidth = this.$slide.width();
        // クローン要素を追加　理由は別紙設計書参照
        this.$slides.append(this.$slide.first().clone());


        setInterval(() => this.showSlide(), this.displayDuration * 1000);
        this.helperSlide(this.currentIndex, false);
    }

    helperSlide(index, flag) {
        if (flag) {
            this.$slides.css('transition', 'transform ' + this.changingDuration + 's ease-in-out');
        } else {
            //移動状態を初期状態に戻す
            this.$slides.css('transition', 'none');
        }
        this.$slides.css('transform', 'translateX(' + (-index * this.slideWidth) + 'px)');
    }

    showSlide() {
        this.currentIndex++;
        this.helperSlide(this.currentIndex, true);

        //ループが一周したとき
        if (this.currentIndex === this.slideCount) {
            this.currentIndex = 0;
            //スライド期間の秒数待ってから開始状態に瞬間移動させる。WAIT機能
            setTimeout(() => {
                this.helperSlide(this.currentIndex, false);
            }, this.changingDuration * 1000);
        }
    }
}

class Integlight_FadeSlider extends Integlight_Slider {
    constructor($, settings) {
        super($, settings);
        this.currentIndex = 0;

        this.$slider.addClass('fade-effect');
        this.$slide.css('transition', 'opacity ' + this.changingDuration + 's ease-in-out');

        // 初期表示のスライドに .active を付与
        this.$slide.removeClass('active');
        this.$slide.eq(this.currentIndex).addClass('active');

        setInterval(() => this.showSlide(), this.displayDuration * 1000);
    }

    showSlide() {
        this.currentIndex++;
        if (this.currentIndex === this.slideCount) {
            this.currentIndex = 0;
        }
        this.$slide.not(this.$slide.eq(this.currentIndex)).removeClass('active');
        this.$slide.eq(this.currentIndex).addClass('active');

    }
}

class Integlight_SlideSlider2 extends Integlight_Slider {
    constructor($, settings) {
        super($, settings);
        this.$slider.addClass('slide-effect');

        this.slideCount = this.$slide.length;
        this.currentIndex = 2; // 左に2枚クローン追加した分の開始位置

        // スライド幅（50vw）をpxで計算
        this.updateSlideWidth();

        // 左に最後の2枚クローン追加
        this.$slides.prepend(this.$slide.eq(this.slideCount - 1).clone());
        this.$slides.prepend(this.$slide.eq(this.slideCount - 2).clone());

        // 右に最初の2枚クローン追加
        this.$slides.append(this.$slide.eq(0).clone());
        this.$slides.append(this.$slide.eq(1).clone());

        // クローンを含む合計枚数
        const totalSlides = this.slideCount + 4;

        // .slides幅をセット
        this.$slides.css('width', totalSlides * this.slideWidth + 'px');

        // 最初の位置にセット（左に2枚分オフセット）
        this.helperSlide(this.currentIndex, false);

        // リサイズ対応
        $(window).on('resize', () => {
            this.updateSlideWidth();
            this.$slides.css('width', totalSlides * this.slideWidth + 'px');
            this.helperSlide(this.currentIndex, false);
        });

        // トランジション終了時のループ処理
        this.$slides.on('transitionend', () => {
            // 右端のクローンを越えたら本物の開始位置へジャンプ
            if (this.currentIndex >= this.slideCount + 2) {
                this.$slides.css('transition', 'none');
                this.currentIndex = 2;
                this.helperSlide(this.currentIndex, false);
                // 再描画強制してからtransition復帰
                requestAnimationFrame(() => {
                    this.$slides[0].offsetHeight;
                    this.$slides.css('transition', `transform ${this.changingDuration}s ease-in-out`);
                });
            }
            // 左端のクローンを越えたら本物の最後にジャンプ（必要なら）
            /*
            if (this.currentIndex <= 1) {
                this.$slides.css('transition', 'none');
                this.currentIndex = this.slideCount + 1;
                this.helperSlide(this.currentIndex, false);
                requestAnimationFrame(() => {
                    this.$slides[0].offsetHeight;
                    this.$slides.css('transition', `transform ${this.changingDuration}s ease-in-out`);
                });
            }
                */
        });

        setInterval(() => this.showSlide(), this.displayDuration * 1000);
    }

    updateSlideWidth() {
        this.slideWidth = this.$slider.width() * 0.5; // 50vwのpx換算
    }

    helperSlide(index, animate) {
        const offset = this.$slider.width() * 0.25; // 25vwオフセット
        if (animate) {
            this.$slides.css('transition', `transform ${this.changingDuration}s ease-in-out`);
        } else {
            this.$slides.css('transition', 'none');
        }
        const x = (-index * this.slideWidth) + offset;
        this.$slides.css('transform', `translateX(${x}px)`);
    }

    showSlide() {
        this.currentIndex++;
        this.helperSlide(this.currentIndex, true);
    }
}




// Manager ////////////////////////////////////////////////////////////////
class Integlight_SliderManager {
    /**
     * @param {Object} settings
     * @param {Object<string, class>} [effectRegistry] - { [effectName]: SliderClass }
     * @param {$} [$=jQuery]
     */
    constructor(settings, effectRegistry = null, $ = jQuery) {
        this.settings = settings;
        this.$ = $;
        // デフォルトレジストリ生成
        this.effectRegistry = effectRegistry || {
            [settings.fade]: Integlight_FadeSlider,
            //[settings.slide]: Integlight_SlideSlider,
            [settings.slide]: Integlight_SlideSlider2
        };
    }

    init() {
        if (this.settings.displayChoice !== this.settings.headerTypeNameSlider) {
            return;
        }

        const SliderClass = this.effectRegistry[this.settings.effect];
        if (typeof SliderClass !== 'function') {
            return;
        }

        this.$(document).ready(() => {
            //setTimeoutなしだとDOM構築は終わっているけど、まだ画像のロードやCSSの描画が完全じゃない可能性あり。非同期待ち行列の後ろに並ばせる。
            setTimeout(() => {
                new SliderClass(this.$, this.settings);
            }, 0);
        });
    }
}

// 初期化処理（デフォルト）
const sliderManager = new Integlight_SliderManager(
    integlight_sliderSettings
);
sliderManager.init();



// Slider _s ////////////////////////////////////////////////////////////////

// ここでまとめてexport
export {
    Integlight_Slider,
    Integlight_SlideSlider,
    Integlight_SlideSlider2,
    Integlight_FadeSlider,
    Integlight_SliderManager

};
