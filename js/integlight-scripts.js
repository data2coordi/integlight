

// Slider _s ////////////////////////////////////////////////////////////////
class Slider {
    constructor($, settings) {
        this.$ = $;
        this.settings = settings;
        this.$slider = this.$('.slider');
        this.$slides = this.$slider.find('.slides');
        this.$slide = this.$slides.find('.slide');
        this.slideCount = this.$slide.length;
        this.displayDuration = settings.changeDuration;
        this.changingDuration = this.displayDuration / 2;
        this.currentIndex = 0;
    }
}

class SlideSlider extends Slider {
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

class FadeSlider extends Slider {
    constructor($, settings) {
        super($, settings);
        this.currentIndex = 1;

        this.$slider.addClass('fade-effect');

        setInterval(() => this.showSlide(), this.displayDuration * 1000);
        this.showSlide();
    }

    showSlide() {
        this.currentIndex++;
        if (this.currentIndex === this.slideCount) {
            this.currentIndex = 0;
        }
        this.$slide.not(this.$slide.eq(this.currentIndex)).removeClass('active');
        this.$slide.eq(this.currentIndex).addClass('active');
        this.$slide.css('transition', 'opacity ' + this.changingDuration + 's ease-in-out');
    }
}


const settings = integlight_sliderSettings;

if (settings.displayChoice === settings.headerTypeNameSlider) {
    let SliderClass;
    if (settings.effect === settings.fade) {
        SliderClass = FadeSlider;
    } else if (settings.effect === settings.slide) {
        SliderClass = SlideSlider;
    } else {

    }

    if (typeof SliderClass === "function") {
        jQuery(document).ready(function ($) {

            new SliderClass($, settings);

        });
    }

}



// Slider _s ////////////////////////////////////////////////////////////////
