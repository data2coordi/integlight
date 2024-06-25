
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
        this.changeDuration = settings.changeDuration;
        this.changeDurationTime = this.changeDuration / 2;
        this.slideWidth = this.$slide.width();
        this.$slider.addClass('slide-effect');

        // クローン要素を追加
        this.$slides.append(this.$slide.first().clone());
        this.$slides.prepend(this.$slide.last().clone());

        // 初期状態の調整
        this.currentIndex = 1;
        this.showSlide(this.currentIndex, false);

        setInterval(() => this.nextSlide(), this.changeDuration * 1000);
    }

    showSlide(index, animate = true) {
        if (animate) {
            this.$slides.css('transition', 'transform ' + this.changeDurationTime / 2 + 's ease-in-out');
        } else {
            this.$slides.css('transition', 'none');
        }
        this.$slides.css('transform', 'translateX(' + (-index * this.slideWidth) + 'px)');
    }

    nextSlide() {
        this.currentIndex++;
        this.showSlide(this.currentIndex);
        if (this.currentIndex === this.slideCount) {
            this.currentIndex = 0;
            setTimeout(() => {
                this.showSlide(this.currentIndex, false);
            }, this.changeDurationTime * 1000 / 2);
        }
    }
}

class FadeSlider extends Slider {
    constructor($, settings) {
        super($, settings);
        this.fadeDuration = settings.fadeDuration;
        this.changeDuration = settings.changeDuration;
        this.currentIndex = 1;

        this.$slider.addClass('fade');

        setInterval(() => this.showSlide(), this.changeDuration * 1000);
        this.showSlide();
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

jQuery(document).ready(function ($) {
    const settings = integlight_sliderSettings;
    if (settings.effect === 'fade') {
        new FadeSlider($, settings);
    } else if (settings.effect === 'slide') {
        new SlideSlider($, settings);
    }
});


// Slider _s ////////////////////////////////////////////////////////////////

