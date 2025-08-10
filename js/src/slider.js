

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
            [settings.slide]: Integlight_SlideSlider
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
    Integlight_FadeSlider,
    Integlight_SliderManager

};
