

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

            this.$slides.one('transitionend', () => {
                // transitionを切ってジャンプ
                this.currentIndex = 0;
                this.helperSlide(this.currentIndex, false);

            });
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

        // スライド幅（50vw）をpxで計算
        this.updateSlideWidth();

        // 左に最後の2枚クローン追加
        this.$slides.prepend(this.$slide.eq(this.slideCount - 1).clone());
        this.$slides.prepend(this.$slide.eq(this.slideCount - 2).clone());

        // 右に最初の2枚クローン追加
        this.$slides.append(this.$slide.eq(0).clone());
        this.$slides.append(this.$slide.eq(1).clone());


        // 最初の位置にセット（左に2枚分オフセット）
        this.currentIndex = 2; // 左に2枚クローン追加した分の開始位置
        this.helperSlide(this.currentIndex, false);

        setInterval(() => this.showSlide(), this.displayDuration * 1000);
    }


    updateSlideWidth() {

        this.slideWidth = this.$slide.width();// 50vwのpx換算
        this.offset = this.slideWidth * 0.5; // 25vwオフセット

    }


    helperSlide(index, animate) {
        if (animate) {
            this.$slides.css('transition', `transform ${this.changingDuration}s ease-in-out`);
        } else {
            this.$slides.css('transition', 'none');
        }
        const x = (-index * this.slideWidth) + this.offset;
        this.$slides.css('transform', `translateX(${x}px)`);
    }

    showSlide() {
        this.currentIndex++;

        // 右端クローンを越えたら次の transitionend でジャンプ処理を行う準備
        if (this.currentIndex == this.slideCount + 2) {
            this.$slides.one('transitionend', () => {
                // transitionを切ってジャンプ
                this.currentIndex = 2;
                this.helperSlide(this.currentIndex, false);

            });
        }
        this.helperSlide(this.currentIndex, true);
    }
}

class Integlight_FadeSlider2 extends Integlight_Slider {
    constructor($, settings) {
        super($, settings);
        this.settings = settings || {};
        this.$slider.addClass('fade-effect');

        // DOM から画像リストを取得
        this.images = this.$slide.map((i, el) => this.$(el).find('img').attr('src')).get();


        // 画像が3未満なら複製して最低3枚にする
        while (this.images.length < 3) {
            this.images = this.images.concat(this.images.slice(0, 3 - this.images.length));
        }

        // 内部インデックス（左側に表示する画像の配列インデックス）
        this.baseIndex = 0;


        // 既存スライド要素を破棄して 3つだけ作る（left / center / right）
        this.$slides.empty();
        this.$visible = []; // jQuery 要素配列

        const widths = ['25%', '50%', '25%'];
        const roles = ['left', 'center', 'right'];

        for (let i = 0; i < 3; i++) {
            const $s = this.$('<div/>', { class: 'slide slide-' + roles[i] });
            const $img = this.$('<img/>').attr('src', this.images[(this.baseIndex + i) % this.images.length]);
            $s.append($img);
            this.$slides.append($s);
            this.$visible.push($s);
        }



        // opacity 用トランジションを適用
        this.$visible.forEach($s => $s.css('transition', `opacity ${this.changingDuration}s ease-out`));

        // 初期はすべて表示（フェードは showSlide で制御）
        this.$visible.forEach($s => $s.css('opacity', 1));


        // 自動切替
        this._intervalId = setInterval(() => this.showSlide(), this.displayDuration * 1000);
    }




    showSlide() {

        // フェードアウト開始
        this.$visible.forEach($s => $s.css('opacity', 0));

        // setTimeoutはchangingDurationの80%くらいに短縮
        const waitTime = this.changingDuration * 1000; // ミリ秒なので * 1000省略の場合は調整してください

        setTimeout(() => {
            // 画像切替
            this.baseIndex = (this.baseIndex + 1) % this.images.length;
            for (let i = 0; i < 3; i++) {
                const src = this.images[(this.baseIndex + i) % this.images.length];
                this.$visible[i].find('img').attr('src', src);
            }
            // フェードイン開始
            this.$visible.forEach($s => $s.css('opacity', 1));
        }, waitTime);
    }


    destroy() {
        clearInterval(this._intervalId);
        this.$(window).off('resize.fadeSlider2', this._onResize);
    }
}





// Manager ////////////////////////////////////////////////////////////////
class Integlight_SliderManager {
    /**
     * @param {Object} settings
     * @param {Object<string class>} [effectRegistry] - { [effectName]: SliderClass }
     * @param {$} [$=jQuery]
     */
    constructor(settings, effectRegistry = null, $ = jQuery) {
        this.settings = settings;
        this.$ = $;
        // デフォルトレジストリ生成
        this.effectRegistry = effectRegistry || {
            [settings.fadeName + settings.home1Name]: Integlight_FadeSlider,
            [settings.fadeName + settings.home2Name]: Integlight_FadeSlider2,
            [settings.slideName + settings.home1Name]: Integlight_SlideSlider,
            [settings.slideName + settings.home2Name]: Integlight_SlideSlider2
        };
    }

    init() {

        const SliderClass = this.effectRegistry[this.settings.effect + this.settings.homeType];


        this.$(window).on('load', () => {
            new SliderClass(this.$, this.settings);
        });
    }
}

// 初期化処理 　　（デフォルト）
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
    Integlight_FadeSlider2,
    Integlight_SliderManager

};
