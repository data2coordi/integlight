console.log('::::::::::@@@@@@@@@@@');

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


const settings = integlight_sliderSettings;

console.log('@@@@@@@@@@1');
if (settings.displayChoice === settings.headerTypeNameSlider) {
    let Integlight_SliderClass;
    console.log('@@@@@@@@@@2');
    if (settings.effect === settings.fade) {
        console.log('@@@@@@@@@@3');
        Integlight_SliderClass = Integlight_FadeSlider;
    } else if (settings.effect === settings.slide) {
        console.log('@@@@@@@@@@4');
        Integlight_SliderClass = Integlight_SlideSlider;
    } else {
        console.log('@@@@@@@@@@5');
    }

    if (typeof Integlight_SliderClass === "function") {
        console.log('@@@@@@@@@@6');
        jQuery(document).ready(function ($) {
            console.log('@@@@@@@@@@7');
            console.log(Integlight_SliderClass);
            setTimeout(() => {
                new Integlight_SliderClass($, settings);
            }, 0); // 0秒後（ミリ秒後）に実行

        });
    }

}



// Slider _s ////////////////////////////////////////////////////////////////

// ここでまとめてexport
export {
    Integlight_Slider,
    Integlight_SlideSlider,
    Integlight_FadeSlider
};
