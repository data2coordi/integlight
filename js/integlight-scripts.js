
// Slider _s ////////////////////////////////////////////////////////////////
class Slider {
    constructor($, settings) {
        this.$ = $;
        this.settings = settings;
        this.$slider = this.$('.slider');
        this.$slides = this.$slider.find('.slides');
        this.$slide = this.$slides.find('.slide');
        this.slideCount = this.$slide.length;
        this.currentIndex = 0;
    }
}

class SlideSlider extends Slider {
    constructor($, settings) {
        super($, settings);
        // 初期状態の調整
        this.currentIndex = 1;
        this.displayDuration = settings.changeDuration;

        this.$slider.addClass('slide-effect');
        this.slideWidth = this.$slide.width();
        // クローン要素を追加
        this.$slides.append(this.$slide.first().clone());
        this.$slides.prepend(this.$slide.last().clone());


        setInterval(() => this.showSlide(), this.displayDuration * 1000);
        this.showSlide();
    }

    helperSlide(index, changingDuration) {
        this.$slides.css('transition', 'transform ' + changingDuration + 's ease-in-out');
        this.$slides.css('transform', 'translateX(' + (-index * this.slideWidth) + 'px)');
    }

    showSlide() {
        var changingDuration = this.displayDuration / 2;
        this.currentIndex++;
        this.helperSlide(this.currentIndex, changingDuration);
        if (this.currentIndex === this.slideCount) {
            this.currentIndex = 0;
            setTimeout(() => {
                this.helperSlide(this.currentIndex, changingDuration);
            }, this.changingDuration * 1000);
        }
    }
}

class FadeSlider extends Slider {
    constructor($, settings) {
        super($, settings);
        this.currentIndex = 1;
        this.displayDuration = settings.changeDuration;

        this.$slider.addClass('fade');

        setInterval(() => this.showSlide(), this.displayDuration * 1000);
        this.showSlide();
    }

    showSlide() {
        var changingDuration = this.displayDuration / 2;
        this.currentIndex++;
        if (this.currentIndex === this.slideCount) {
            this.currentIndex = 0;
        }
        this.$slide.not(this.$slide.eq(this.currentIndex)).removeClass('active');
        this.$slide.eq(this.currentIndex).addClass('active');
        this.$slide.css('transition', 'opacity ' + changingDuration + 's ease-in-out');
    }
}

jQuery(document).ready(function ($) {
    const settings = integlight_sliderSettings;
    if (settings.effect === 'fade') {
        new FadeSlider($, settings);
    } else if (settings.effect === 'slide') {
        new SlideSlider($, settings);
    }
});


// Slider _s ////////////////////////////////////////////////////////////////

