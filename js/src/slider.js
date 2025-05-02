

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
        // クローン要素を追加
        this.$slides.append(this.$slide.first().clone());


        setInterval(() => this.showSlide(), this.displayDuration * 1000);
        this.helperSlide(this.currentIndex, false);
    }

    helperSlide(index, flag) {
        if (flag) {
            this.$slides.css('transition', 'transform ' + this.changingDuration + 's ease-in-out');
        } else {
            this.$slides.css('transition', 'none');
        }
        this.$slides.css('transform', 'translateX(' + (-index * this.slideWidth) + 'px)');
    }

    showSlide() {
        this.currentIndex++;
        this.helperSlide(this.currentIndex, true);
        if (this.currentIndex === this.slideCount) {
            this.currentIndex = 0;
            setTimeout(() => {
                this.helperSlide(this.currentIndex, false);
            }, this.changingDuration * 1000);
        }
    }
}

class Integlight_FadeSlider extends Integlight_Slider {
    constructor($, settings) {
        super($, settings);
        this.currentIndex = 1;

        this.$slider.addClass('fade-effect');
        this.$slide.css('transition', 'opacity ' + this.changingDuration + 's ease-in-out');

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


// 新たにトップレベルのロジックをクラス化
class Integlight_SliderManager {
    constructor(settings) {
        this.settings = settings;
        this.$ = jQuery;
        this.sliderClass = null;
    }

    init() {
        if (this.settings.displayChoice === this.settings.headerTypeNameSlider) {
            if (this.settings.effect === this.settings.fade) {
                this.sliderClass = Integlight_FadeSlider;
            } else if (this.settings.effect === this.settings.slide) {
                this.sliderClass = Integlight_SlideSlider;
            }

            if (typeof this.sliderClass === "function") {
                this.$(document).ready(() => {
                    setTimeout(() => {
                        new this.sliderClass(this.$, this.settings);
                    }, 0);
                });
            }
        }
    }
}

// 初期化処理
const sliderManager = new Integlight_SliderManager(integlight_sliderSettings);
sliderManager.init();



// Slider _s ////////////////////////////////////////////////////////////////

// ここでまとめてexport
export {
    Integlight_Slider,
    Integlight_SlideSlider,
    Integlight_FadeSlider
};
