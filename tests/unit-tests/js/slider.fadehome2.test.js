/**
 * @jest-environment jsdom
 */

import $ from 'jquery';

let Integlight_FadeSlider2;

beforeAll(() => {
    // グローバルに jQuery セット
    global.jQuery = $;
    global.$ = $;

    // 本体コードが参照するグローバル設定をセット
    global.integlight_sliderSettings = {
        fadeName: 'fade',
        slideName: 'slide',
        home1Name: 'home1',
        home2Name: 'home2',
        changeDuration: 1,  // テスト用に1秒に設定
        effect: 'fade',
        homeType: 'home2',
    };
});

beforeEach(async () => {
    jest.useFakeTimers();

    // モジュールを動的にimport。import時点でinteglight_sliderSettingsがあることを保証するため。
    jest.resetModules(); // キャッシュリセット（必要に応じて）
    const module = await import('../../../js/src/slider.js');
    Integlight_FadeSlider2 = module.Integlight_FadeSlider2;

    // DOMセット（最低限、.slider > .slides > .slide > img 構造）
    document.body.innerHTML = `
    <div class="slider">
      <div class="slides">
        <div class="slide"><img src="img1.jpg" /></div>
        <div class="slide"><img src="img2.jpg" /></div>
        <div class="slide"><img src="img3.jpg" /></div>
      </div>
    </div>
  `;

    // window.load イベント発火して自動初期化も行う
    window.dispatchEvent(new Event('load'));
});

afterEach(() => {
    jest.useRealTimers();
    jest.clearAllTimers();
    document.body.innerHTML = '';
});

describe('Integlight_FadeSlider2 初期化関連', () => {
    it('初期化時に .fade-effect クラスが付与されること', () => {
        const inst = new Integlight_FadeSlider2($, { changeDuration: 1 });
        expect(inst.$slider.hasClass('fade-effect')).toBe(true);
    });

    it('画像リストが3枚未満なら複製して3枚にすること', () => {
        // 2枚しかないDOMでテスト
        document.body.innerHTML = `
      <div class="slider">
        <div class="slides">
          <div class="slide"><img src="img1.jpg"></div>
          <div class="slide"><img src="img2.jpg"></div>
        </div>
      </div>
    `;
        const inst = new Integlight_FadeSlider2($, { changeDuration: 1 });
        expect(inst.images.length).toBeGreaterThanOrEqual(3);
        expect(inst.images).toEqual(expect.arrayContaining(['img1.jpg', 'img2.jpg']));
    });

    it('初期化時にスライドDOMが3つだけ作成されること', () => {
        document.body.innerHTML = `
      <div class="slider">
        <div class="slides">
          <div class="slide"><img src="img1.jpg"></div>
          <div class="slide"><img src="img2.jpg"></div>
          <div class="slide"><img src="img3.jpg"></div>
        </div>
      </div>
    `;
        const inst = new Integlight_FadeSlider2($, { changeDuration: 1 });
        expect(inst.$slides.children('.slide').length).toBe(3);
        ['slide-left', 'slide-center', 'slide-right'].forEach(cls => {
            expect(inst.$slides.children(`.${cls}`).length).toBe(1);
        });
    });

    it('初期表示の3つスライドはopacity 1で表示されること', () => {
        const inst = new Integlight_FadeSlider2($, { changeDuration: 1 });
        inst.$visible.forEach($slide => {
            expect($slide.css('opacity')).toBe('1');
        });
    });

    it('showSlide() 呼び出しでスライドがフェードアウトすること（opacityが0に設定される）', () => {
        const inst = new Integlight_FadeSlider2($, { changeDuration: 1 });
        inst.showSlide();
        inst.$visible.forEach($slide => {
            expect($slide.css('opacity')).toBe('0');
        });
    });
});

describe('Integlight_FadeSlider2 画像切替関連', () => {


    it('showSlide() 呼び出し後、一定時間経過で画像が切り替わり opacity が1に戻ること', () => {
        const inst = new Integlight_FadeSlider2($, { changeDuration: 1 });
        const beforeSrcs = inst.$visible.map($slide => $slide.find('img').attr('src'));

        inst.showSlide();

        jest.advanceTimersByTime(600); // 適宜changingDurationに合わせる

        const afterSrcs = inst.$visible.map($slide => $slide.find('img').attr('src'));

        const changed = beforeSrcs.some((src, i) => src !== afterSrcs[i]);
        expect(changed).toBe(true);

        inst.$visible.forEach($slide => {
            expect($slide.css('opacity')).toBe('1');
        });
    });

    it('画像インデックスが末尾まで進んだ後、先頭に戻ってループすること', () => {
        const inst = new Integlight_FadeSlider2($, { changeDuration: 1 });
        const length = inst.images.length;
        inst.baseIndex = length - 1;

        inst.showSlide();

        jest.advanceTimersByTime(600);

        expect(inst.baseIndex).toBe(0);
        const firstSrc = inst.$visible[0].find('img').attr('src');
        expect(firstSrc).toBe(inst.images[0]);

        inst.$visible.forEach($slide => {
            expect($slide.css('opacity')).toBe('1');
        });
    });

    it('showSlide() 呼び出しでスライドがフェードアウトすること（opacityが0に設定される）', () => {
        const inst = new Integlight_FadeSlider2($, { changeDuration: 1 });
        inst.showSlide();
        inst.$visible.forEach($slide => {
            expect($slide.css('opacity')).toBe('0');
        });
    });
});