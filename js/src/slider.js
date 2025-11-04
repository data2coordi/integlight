// Slider Base ////////////////////////////////////////////////////////////////
class Integlight_Slider {
  constructor(settings) {
    this.slider = document.querySelector(".slider");
    this.slidesContainer = this.slider.querySelector(".slides");
    this.slides = Array.from(this.slidesContainer.querySelectorAll(".slide"));
    this.slideCount = this.slides.length;
    this.displayDuration = settings.changeDuration;
    this.changingDuration = this.displayDuration / 2;
    this.currentIndex = 0;
  }
}

// Slide Effect
class Integlight_SlideSlider extends Integlight_Slider {
  constructor(settings) {
    super(settings);
    this.currentIndex = 0;
    this.slider.classList.add("slide-effect");

    this.slideWidth = this.slides[0].offsetWidth;

    // クローン要素追加
    this.slidesContainer.appendChild(this.slides[0].cloneNode(true));

    setInterval(() => this.showSlide(), this.displayDuration * 1000);
    this.helperSlide(this.currentIndex, false);
  }

  helperSlide(index, animate) {
    this.slidesContainer.style.transition = animate
      ? `transform ${this.changingDuration}s ease-in-out`
      : "none";
    this.slidesContainer.style.transform = `translateX(${-(
      index * this.slideWidth
    )}px)`;
  }

  showSlide() {
    this.currentIndex++;
    this.helperSlide(this.currentIndex, true);

    if (this.currentIndex === this.slideCount) {
      this.slidesContainer.addEventListener(
        "transitionend",
        () => {
          this.currentIndex = 0;
          this.helperSlide(this.currentIndex, false);
        },
        { once: true }
      );
    }
  }
}

// Fade Effect
class Integlight_FadeSlider extends Integlight_Slider {
  constructor(settings) {
    super(settings);
    this.currentIndex = 0;

    this.slider.classList.add("fade-effect");
    this.slides.forEach((slide) => {
      slide.style.transition = `opacity ${this.changingDuration}s ease-in-out`;
    });

    // 初期表示
    this.slides.forEach((s) => s.classList.remove("active"));
    this.slides[this.currentIndex].classList.add("active");

    setInterval(() => this.showSlide(), this.displayDuration * 1000);
  }

  showSlide() {
    this.currentIndex = (this.currentIndex + 1) % this.slideCount;
    this.slides.forEach((slide, idx) => {
      slide.classList.toggle("active", idx === this.currentIndex);
    });
  }
}

// Slide Effect 2
class Integlight_SlideSlider2 extends Integlight_Slider {
  constructor(settings) {
    super(settings);
    this.slider.classList.add("slide-effect");
    this.slideCount = this.slides.length;

    this.updateSlideWidth();

    // 左に最後の2枚クローン追加
    this.slidesContainer.prepend(
      this.slides[this.slideCount - 1].cloneNode(true)
    );
    this.slidesContainer.prepend(
      this.slides[this.slideCount - 2].cloneNode(true)
    );

    // 右に最初の2枚クローン追加
    this.slidesContainer.appendChild(this.slides[0].cloneNode(true));
    this.slidesContainer.appendChild(this.slides[1].cloneNode(true));

    this.currentIndex = 2;
    this.helperSlide(this.currentIndex, false);

    setInterval(() => this.showSlide(), this.displayDuration * 1000);
  }

  updateSlideWidth() {
    this.slideWidth = this.slides[0].offsetWidth;
    this.offset = this.slideWidth * 0.5;
  }

  helperSlide(index, animate) {
    this.slidesContainer.style.transition = animate
      ? `transform ${this.changingDuration}s ease-out`
      : "none";
    const x = -index * this.slideWidth + this.offset;
    this.slidesContainer.style.transform = `translateX(${x}px)`;
  }

  showSlide() {
    this.currentIndex++;
    if (this.currentIndex === this.slideCount + 2) {
      this.slidesContainer.addEventListener(
        "transitionend",
        () => {
          this.currentIndex = 2;
          this.helperSlide(this.currentIndex, false);
        },
        { once: true }
      );
    }
    this.helperSlide(this.currentIndex, true);
  }
}

// Fade Effect 2
// 元のクラス名をそのまま使えるようにしています
class Integlight_FadeSlider2 extends Integlight_Slider {
  constructor(settings) {
    super(settings);
    this.settings = settings || {};
    this.slider.classList.add("fade-effect");

    // --- 画像リスト（文字列）を作成（元コードと同じ） ---
    this.images = this.slides.map((slide) =>
      slide.querySelector("img")?.getAttribute("src")
    );

    // 3未満なら複製（元コードと同じ）
    while (this.images.length < 3) {
      this.images = this.images.concat(
        this.images.slice(0, 3 - this.images.length)
      );
    }

    this.baseIndex = 0;

    // --- 重要: 元の <img> 要素を保存しておく（再利用する） ---
    // 元スライドDOMから <img> 要素の参照を取得しておく（これらは既にロード済みである想定）
    this.imgPool = this.slides.map((slide) => slide.querySelector("img"));

    // スライド要素差し替え（元と同様に container を初期化する）
    this.slidesContainer.innerHTML = "";
    this.visibleSlides = [];

    const roles = ["left", "center", "right"];
    for (let i = 0; i < 3; i++) {
      const slideDiv = document.createElement("div");
      slideDiv.className = `slide slide-${roles[i]}`;

      // 初期表示時は imgPool の該当要素を「移動して」追加する（src は変更しない）
      const imgEl = this.imgPool[(this.baseIndex + i) % this.imgPool.length];
      // appendChild は既存の親から自動的に移動する（再作成しない）
      slideDiv.appendChild(imgEl);

      this.slidesContainer.appendChild(slideDiv);
      this.visibleSlides.push(slideDiv);
    }

    // トランジション設定（元コードとまったく同じ）
    this.visibleSlides.forEach((slide) => {
      slide.style.transition = `opacity ${this.changingDuration}s ease-out`;
      slide.style.opacity = 1;
    });

    this._intervalId = setInterval(
      () => this.showSlide(),
      this.displayDuration * 1000
    );
  }

  showSlide() {
    // フェードアウト（元コードと同じ）
    this.visibleSlides.forEach((slide) => (slide.style.opacity = 0));

    setTimeout(() => {
      // baseIndex を順送り（元コードと同じ）
      this.baseIndex = (this.baseIndex + 1) % this.images.length;

      // --- ここが肝 ---
      // 各 wrapper 内の <img> を一旦すべて取り外してから、
      // desired order（元コードの src 割当と同じ）で imgPool から再配置する。
      // これにより「src を書き換えずに」表示内容だけを入れ替えられる。
      // 取り外し（removeChild）→ 再付与（appendChild）なので、既存の img ノードを移動するだけ。
      const removedImgs = this.visibleSlides.map((wrapper) => {
        const img = wrapper.querySelector("img");
        return img ? wrapper.removeChild(img) : null;
      });

      for (let i = 0; i < 3; i++) {
        const desiredImgIndex = (this.baseIndex + i) % this.imgPool.length;
        const desiredImgEl = this.imgPool[desiredImgIndex];
        const wrapper = this.visibleSlides[i];

        // 既に同じ要素が入っていればそのまま（remove-append の結果で null になっている可能性を考慮）
        // ここでは確実に append することで元コードと同じ「左・中央・右に該当画像を割り当てる」動作を再現
        wrapper.appendChild(desiredImgEl);
      }

      // フェードイン（元コードと同じ）
      this.visibleSlides.forEach((slide) => (slide.style.opacity = 1));
    }, this.changingDuration * 1000);
  }
}

// Manager ////////////////////////////////////////////////////////////////
class Integlight_SliderManager {
  constructor(settings, effectRegistry = null) {
    this.settings = settings;
    this.effectRegistry = effectRegistry || {
      [settings.fadeName + settings.siteType1Name + "pc"]:
        Integlight_FadeSlider,
      [settings.fadeName + settings.siteType2Name + "pc"]:
        Integlight_FadeSlider2,
      [settings.fadeName + settings.siteType3Name + "pc"]:
        Integlight_FadeSlider2,
      [settings.slideName + settings.siteType1Name + "pc"]:
        Integlight_SlideSlider,
      [settings.slideName + settings.siteType2Name + "pc"]:
        Integlight_SlideSlider2,
      [settings.slideName + settings.siteType3Name + "pc"]:
        Integlight_SlideSlider2,
      [settings.fadeName + settings.siteType1Name + "sp"]:
        Integlight_FadeSlider,
      [settings.fadeName + settings.siteType2Name + "sp"]:
        Integlight_FadeSlider,
      [settings.fadeName + settings.siteType3Name + "sp"]:
        Integlight_FadeSlider,
      [settings.slideName + settings.siteType1Name + "sp"]:
        Integlight_SlideSlider,
      [settings.slideName + settings.siteType2Name + "sp"]:
        Integlight_SlideSlider,
      [settings.slideName + settings.siteType3Name + "sp"]:
        Integlight_SlideSlider,
    };
  }

  init() {
    const isMobile = window.matchMedia("(max-width: 768px)").matches;
    const device = isMobile ? "sp" : "pc";
    const SliderClass =
      this.effectRegistry[
        this.settings.effect + this.settings.homeType + device
      ];

    window.addEventListener("load", () => {
      new SliderClass(this.settings);
    });
  }
}

// 初期化処理
const sliderManager = new Integlight_SliderManager(integlight_sliderSettings);
sliderManager.init();

// Export
export {
  Integlight_Slider,
  Integlight_SlideSlider,
  Integlight_SlideSlider2,
  Integlight_FadeSlider,
  Integlight_FadeSlider2,
  Integlight_SliderManager,
};
