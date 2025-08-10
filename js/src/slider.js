

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

class Integlight_FadeSlider2 extends Integlight_Slider {
    constructor($, settings) {
        super($, settings);
        this.$ = $;
        this.settings = settings || {};
        this.debug = !!this.settings.debug;
        this._log = (...a) => { if (this.debug) console.log('[FadeSlider2]', ...a); };

        // 安全な duration 設定（親クラスの値がない場合のフォールバック）
        this.displayDuration = this.settings.changeDuration || this.displayDuration || 5;
        this.changingDuration = this.displayDuration / 2;

        // CSS 衝突回避クラス
        //this.$slider.addClass('fade-25-50-25').removeClass('slide-effect fade-effect');

        // safety check
        if (!this.$slider || !this.$slides) {
            this._log('missing .slider or .slides, abort');
            return;
        }

        // 元 DOM から画像リストを取得（settings.images があればそちらを優先）
        const domImgs = this.$slide.map((i, el) => this.$(el).find('img').attr('src')).get();
        this.images = (Array.isArray(this.settings.images) && this.settings.images.length)
            ? this.settings.images.slice()
            : domImgs.slice();

        if (!this.images || this.images.length === 0) {
            this._log('no images found for FadeSlider2 - abort');
            return;
        }

        // 画像が3未満なら複製して最低3枚にする
        while (this.images.length < 3) {
            this.images = this.images.concat(this.images.slice(0, 3 - this.images.length));
        }

        // 内部インデックス（左側に表示する画像の配列インデックス）
        this.baseIndex = 0;

        // 強制的に .slides の transform/transition を無効化（既存スライド動作と衝突しないように）
        this.$slides.css({
            transition: 'none',
            transform: 'none',
            width: '100%',
            display: 'flex',
            'align-items': 'stretch',
            'justify-content': 'center',
            gap: '0'
        });

        // 既存スライド要素を破棄して 3つだけ作る（left / center / right）
        this.$slides.empty();
        this.$visible = []; // jQuery 要素配列

        const widths = ['25%', '50%', '25%'];
        const roles = ['left', 'center', 'right'];

        for (let i = 0; i < 3; i++) {
            const $s = this.$('<div/>', { class: 'slide slide-' + roles[i] }).css({
                flex: '0 0 ' + widths[i],
                position: 'relative',
                overflow: 'hidden',
                boxSizing: 'border-box'
            });
            const $img = this.$('<img/>').attr('src', this.images[(this.baseIndex + i) % this.images.length]).css({
                width: '100%',
                height: '100%',
                objectFit: 'cover',
                display: 'block'
            });
            $s.append($img);
            this.$slides.append($s);
            this.$visible.push($s);
        }

        // 高さを揃える（CSSの .slider 高さを尊重）
        this.updateHeights();

        // opacity 用トランジションを適用
        this.$visible.forEach($s => $s.css('transition', `opacity ${this.changingDuration}s ease-in-out`));

        // 初期はすべて表示（フェードは showSlide で制御）
        this.$visible.forEach($s => $s.css('opacity', 1));

        // リサイズで高さ再計算
        this._onResize = () => this.updateHeights();
        this.$(window).on('resize.fadeSlider2', this._onResize);

        // 自動切替
        this._intervalId = setInterval(() => this.showSlide(), this.displayDuration * 1000);
        this._log('initialized', { displayDuration: this.displayDuration, changingDuration: this.changingDuration });
    }

    updateHeights() {
        // slider の高さに合わせる（CSSで height が指定されている前提）
        const h = Math.max(0, this.$slider.height() || Math.round(window.innerHeight * 0.35));
        this.$visible.forEach($s => $s.css('height', h + 'px'));
        // img の高さを 100% にしてフィットさせる
        this.$visible.forEach($s => $s.find('img').css('height', '100%'));
        this._log('updateHeights', h);
    }

    showSlide() {
        this._log('showSlide start', this.baseIndex);

        // フェードアウト開始
        this.$visible.forEach($s => $s.css('opacity', 0));

        // setTimeoutはchangingDurationの80%くらいに短縮
        const waitTime = this.changingDuration * 800; // ミリ秒なので * 1000省略の場合は調整してください

        setTimeout(() => {
            // 画像切替
            this.baseIndex = (this.baseIndex + 1) % this.images.length;
            for (let i = 0; i < 3; i++) {
                const src = this.images[(this.baseIndex + i) % this.images.length];
                this.$visible[i].find('img').attr('src', src);
            }
            // フェードイン開始
            this.$visible.forEach($s => $s.css('opacity', 1));
            this._log('showSlide done', this.baseIndex, this.$visible.map(v => v.find('img').attr('src')));
        }, waitTime);
    }


    destroy() {
        clearInterval(this._intervalId);
        this.$(window).off('resize.fadeSlider2', this._onResize);
        this._log('destroyed');
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
            [settings.fade + settings.home1]: Integlight_FadeSlider,
            [settings.fade + settings.home2]: Integlight_FadeSlider2,
            [settings.slide + settings.home1]: Integlight_SlideSlider,
            [settings.slide + settings.home2]: Integlight_SlideSlider2
        };
    }

    init() {
        if (this.settings.displayChoice !== this.settings.headerTypeNameSlider) {
            return;
        }

        const SliderClass = this.effectRegistry[this.settings.effect + this.settings.homeType];
        if (typeof SliderClass !== 'function') {
            return;
        }

        this.$(window).on('load', () => {
            new SliderClass(this.$, this.settings);
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
    Integlight_FadeSlider2,
    Integlight_SliderManager

};
